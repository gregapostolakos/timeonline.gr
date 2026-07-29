<?php
class ModelExtensionPaymentXpayment extends Model {
    use OCM\Traits\Front\Address;
    use OCM\Traits\Front\Common_params;
    use OCM\Traits\Front\Product;
    use OCM\Traits\Front\Validator;
    use OCM\Traits\Front\Crucify;
    use OCM\Traits\Front\Shortcode;
    use OCM\Traits\Front\Util;
    protected $registry;
    private $ocm;
    private $ext_path;
    private $mtype;
    private $mname;
    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry = $registry;
        $this->mtype = 'payment';
        $this->mname = 'xpayment';
        $this->ext_path = 'extension/payment/xpayment';
        $this->ocm = $this->registry->has('ocm_front') ? $this->registry->get('ocm_front') : new OCM\Front($this->registry);
    }
    // v4.x
    function getMethods($address) {
        return $this->getMethod($address, 0);
    }
    function getMethod($address, $total) {
        $this->load->language($this->ext_path);
        $this->load->model('checkout/order');
        $language_id = $this->config->get('config_language_id');
        $prefix = VERSION >= '3.0.0.0' ? 'payment_' : '';
        $address = $this->_replenishAddress($address);
        $compare_with = $this->_getCommonParams($address);
        $xpayments = $this->getPayments();
        $xmethods = $xpayments['xmethods'];
        $xmeta = $xpayments['xmeta'];
        $xpayment_methods = array();
        $sort_data = array();
        $xpayment_debug = $this->ocm->getConfig('xpayment_debug', $this->mtype);
        $admin_all = $this->ocm->getConfig('xpayment_admin_all', $this->mtype);
        $order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : 0;
        $order_id = isset($this->request->get['order_id']) ? $this->request->get['order_id'] : $order_id;
        $order_id = isset($this->request->request['order_id']) ? $this->request->request['order_id'] : $order_id;
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $cart_products = $this->cart->getProducts();
        $_cart_data = $this->getProductProfile($cart_products, $xmeta);
        $_xtaxes = $_cart_data['tax_data'];
        $geo_ids = array();
        if ($xmeta['geo']) {
            $geo_rows = $this->db->query("SELECT geo_zone_id FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')")->rows; 
            foreach ($geo_rows as $geo_row) {
                $geo_ids[] = $geo_row['geo_zone_id'];
            }
        }
        if ($xmeta['coupon'] && $compare_with['coupon_code']) {
            $total_mods = array(array('code' => 'coupon'));
            $xtotals = $this->ocm->getTotals($_xtaxes);
            foreach ($xtotals['totals'] as $single) {
                if ($single['code'] == 'coupon') {
                    $_cart_data['coupon'] = $single['value'];
                }
            }
        }
        $shipping_cost = isset($this->session->data['shipping_method']['cost']) ? (float)$this->session->data['shipping_method']['cost'] : 0;
        if (isset($this->session->data['default']['shipping_method']['cost'])) $shipping_cost = $this->session->data['default']['shipping_method']['cost'];
        $_cart_data['sub_coupon'] = $_cart_data['sub'] + $_cart_data['coupon'];
        $_cart_data['total_coupon'] = $_cart_data['total'] + $_cart_data['coupon'];
        $_cart_data['sub_shipping'] = $_cart_data['sub'] + $shipping_cost;
        $_cart_data['total_shipping'] = $_cart_data['total'] + $shipping_cost;
        $_cart_data['grand'] = $total; // may need to set to correct total
        $compare_with['geo'] = $geo_ids;
        $compare_with['product'] = $_cart_data['product'];
        $compare_with['category'] = $_cart_data['category'];
        $compare_with['manufacturer'] = $_cart_data['manufacturer'];
        $compare_with['option'] = $_cart_data['option'];
        $compare_with['attribute'] = $_cart_data['attribute'];
        $compare_with['total'] = $_cart_data['total'];
        $compare_with['weight'] = $_cart_data['weight'];
        $compare_with['quantity'] = $_cart_data['quantity'];
        $compare_with['free'] = $_cart_data['grand'] <= 0;
        $compare_with['out_of_stock'] = !!$_cart_data['out_of_stock'];
        $compare_with['email'] = '';
        if ($order_info) {
            $compare_with['email'] = $order_info['email'];
        } else if ($this->customer->isLogged()) {
            $compare_with['email'] = $this->customer->getEmail();
        } else if (isset($this->session->data['guest']['email'])) {
            $compare_with['email'] = $this->session->data['guest']['email'];
        }
        if (!$_cart_data['has_shipping']) {
            $compare_with['shipping_method'] = 'no_shipping';
        }
        if ($xmeta['admin_login']) {
            if (VERSION < '2.1.0.1') {
                $this->load->library('user');
            }
            $this->user = VERSION > '2.1.0.1' ? new Cart\User($this->registry) : new User($this->registry);
            $compare_with['admin_logged'] = (boolean)$this->user->isLogged();
        }
        $debugging = array();
        foreach($xmethods as $xpayment) {
            $rules = $xpayment['rules'];
            $tab_id = $xpayment['tab_id'];
            $product_or = $xpayment['product_or'];
            $debugging_message = array();
            /* adjust multiple values */
            $this->_adjustMultiValues($rules, $_cart_data['products']);
            if ($product_or) {
                $this->_adjustProductsOr($rules, $_cart_data['products']);
            }
            /* if shipping_base is found on shipping_methods list, then It had better to use baseone. */
            if (isset($rules['shipping']) && in_array($compare_with['shipping_base'], $rules['shipping']['value'])) {
                $compare_with['shipping_method'] = $compare_with['shipping_base'];
            }
            if (isset($rules['total_range'])) {
                $compare_with[$xpayment['total_type']] = $_cart_data[$xpayment['total_type']];
            }
            // Make disabled by default
            $this->config->set($prefix . $xpayment['code'] . '_status', false);
            $alive_or_dead = $this->_crucify($rules, $compare_with, $product_or);
            // set true if admin all set true
            if ($admin_all && $this->ocm->isAdmin()) {
                $alive_or_dead['status'] = true;
            }
            if (!$alive_or_dead['status']) {
                $debugging_message = $alive_or_dead['debugging'];
                $debugging[] = array('name' => $xpayment['display'],'filter' => $debugging_message,'index' => $tab_id);
            } else {
                $title = (isset($xpayment['name'][$language_id]) && $xpayment['name'][$language_id]) ? $xpayment['name'][$language_id] : 'Untitled Item';
                $xpayment_methods['xpayment'.$tab_id] = array(
                    'code'       => VERSION < '4.0.0.0' ? $xpayment['code'] : 'xpayment.' . $xpayment['code'],
                    'title'      => $this->ocm->html_decode($title),
                    'name'       => $this->ocm->html_decode($title),
                    'terms'      => '',
                    'label_reward' => '', // journal 3 has dependency on this
                    'logo'       => $xpayment['logo'],
                    'image'      => $xpayment['logo'], /* d_checkout native*/
                    'sort_order' => intval($xpayment['sort_order'])
                );
            }
        }
        /*Sorting final method*/
        $sort_order = array();
        foreach ($xpayment_methods as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $xpayment_methods);
        if ($xpayment_debug) {
           $this->ocm->writeLog($debugging, 'xpayment');
        }
        $method_data = array();
        if (VERSION < '4.0.0.0') {
            foreach ($xpayment_methods as $code => $value) {
                $this->config->set($prefix . $code . '_status', true);
                $this->registry->set('model_extension_payment_' . $code, new Xpayment($value));
            }   
        } else {
            $heading = $this->ocm->getConfig('xpayment_heading', $this->mtype);
            $heading = isset($heading[$language_id])?$heading[$language_id] : 'X-Payment';
            $sorting = $this->ocm->getConfig('xpayment_sorting', $this->mtype);
            $sorting = ($sorting)?(int)$sorting:1;
            $method_data = array(
                'code'       => 'xpayment',
                'name'       => $heading,
                'option'     => $xpayment_methods,
                'sort_order' => $sorting
            );
        }
        return $method_data;
    }
    public function getMethodDataByOrder($order_info) {
        if (!$order_info) {
            return false;
        }
        $payment_code = '';
        if (isset($order_info['payment_method']) && isset($order_info['payment_method']['code'])) {
            $payment_code = $order_info['payment_method']['code'];
        }
        if (!$payment_code && isset($order_info['payment_code'])) {
           $payment_code = $order_info['payment_code']; 
        }
        $no_of_tab = str_replace(array('xpayment', '.'), '', $payment_code);
        return $this->getMethodDataByID($no_of_tab);
    }
    public function getMethodDataByID($tab_id) {
        $xpayments = $this->getPayments();
        $xmethods = $xpayments['xmethods'];
        $xpayment = isset($xmethods[$tab_id]) ? $xmethods[$tab_id] : false;
        if ($xpayment) {
            $xpayment = $this->getMethodByLang($xpayment);
        }
        return $xpayment;
    }
    public function getNotifyParams() {
        $xpayments = $this->getPayments();
        return $xpayments['xmeta']['notify'];
    }
    private function getMethodByLang($xpayment) {
        $language_id = (int)$this->config->get('config_language_id');
        $lang_fields = array('name', 'desc', 'instruction', 'email_instruction', 'error');
        foreach ($lang_fields as $field) {
            $xpayment[$field] = isset($xpayment[$field][$language_id]) ? $this->ocm->html_decode($xpayment[$field][$language_id]) : '';
        }
        return $xpayment;
    }
    private function getPayments() {
        $disable_cache = $this->ocm->getConfig('xpayment_disable_cache', $this->mtype);
        $xpayment = $this->cache->get('ocm.xpayment');
        if (!$xpayment || $disable_cache) {
            $xmethods = array();
            $xmeta = array(
                'coupon' => false,
                'geo' => false,
                'product_query'  => false,
                'category_query' => false,
                'shipping_rule'  => false,
                'admin_login'    => false,
                'notify'         => array()
            );
            $_notify = array('_notifyId');
            $xpayment_rows = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xpayment` order by `tab_id` asc")->rows;
            foreach($xpayment_rows as $xpayment_row) {
                $tab_id = (int)$xpayment_row['tab_id'];
                $method_data = $xpayment_row['method_data'];
                $method_data = json_decode($method_data, true);
                /* cache only valid payment */
                if ($method_data && is_array($method_data) && $method_data['status']) {
                    $method_data =  $this->_resetEmptyRule($method_data);
                    $rules = $this->_findValidRules($method_data);
                    $integration = array();
                    if ($method_data['int_type']) {
                        $_key = array(
                            '_keyUrl'       => '',
                            '_keyStatus'    => '',
                            '_keyError'     => '',
                            '_keyErrorAdd'  => '',
                        );
                        $_status = array(
                            '_statusPending' => '',
                            '_statusCancel'  => '',
                            '_statusRefund'  => ''
                        );
                        $_value = array(
                            '_valuePending' => '',
                            '_valueCancel'  => '',
                            '_valueRefund'  => '',
                            '_valueIpn'     => ''
                        );
                        foreach ($method_data['additional_data'] as $p => $param) {
                            $_name = trim($param['name'], '{}');
                            if (array_key_exists($_name, $_key)) {
                                unset($method_data['additional_data'][$p]);
                                $_key[$_name] = $param['value'];
                            } else if (array_key_exists($_name, $_status)) {
                                unset($method_data['additional_data'][$p]);
                                $_status[$_name] = (int)preg_replace('/(\d+)-.*/', '$1', $param['value']);
                            } else if (array_key_exists($_name, $_value)) {
                                unset($method_data['additional_data'][$p]);
                                $_value[$_name] = $param['value'];
                            } else if (in_array($_name, $_notify)) {
                                $xmeta['notify'][] = $param['value'];
                            }
                        }
                        $integration['meta'] = array(
                            '_key'     => $_key,
                            '_status'  => $_status,
                            '_value'   => $_value
                        );
                        $integration['type'] = $method_data['int_type'];
                        $integration['name'] = $method_data['int_name'];
                        $integration['url'] = $method_data['int_url'];
                        $integration['method'] = $method_data['method_type'];
                        $integration['data'] = $method_data['int_data'];
                        $integration['header'] = $method_data['header_data'];
                        $integration['additional'] = $method_data['additional_data'];
                        $integration['success_method'] = $method_data['success_method_type'];
                        $integration['success'] = $method_data['int_success'];
                        if ($method_data['hash']) {
                            $integration['hash'] = array(
                                'algo'      => $method_data['hash_type'],
                                'str'       => $method_data['hash_str'],
                                'key'       => $method_data['hash_key'],
                                'bin'       => !!$method_data['hash_bin'],
                                'base64'    => !!$method_data['hash_base64']
                            );
                            if ($this->ocm->parseShortcode($method_data['hash_str'])) {
                                $integration['hash']['shortcode'] = true;   
                            }
                        }
                    }
                    $xmethods[$tab_id] = array(
                       'tab_id' => $tab_id,
                       'code'  => 'xpayment'.$tab_id,
                       'display' => $method_data['display'],
                       'name' => $method_data['name'],
                       'desc' => $method_data['desc'],
                       'instruction' => $method_data['instruction'],
                       'email_instruction' => $method_data['email_instruction'],
                       'error'  => $method_data['error'],
                       'order_status_id' => $method_data['order_status_id'],
                       'additional_email' => $method_data['additional_email'],
                       'product_or' => !!$method_data['product_or'],
                       'rules' => $rules,
                       'inc_email' => !!$method_data['inc_email'],
                       'hide_title' => !!$method_data['hide_title'],
                       'inc_order' => !!$method_data['inc_order'],
                       'sort_order' => (int)$method_data['sort_order'],
                       'logo' => $method_data['logo'],
                       'total_type' => $method_data['total_type'],
                       'success' => $method_data['success'],
                       'notification' => !!$method_data['callback_enable'],
                       'callback' => $method_data['callback'],
                       'callback_data' => $method_data['callback_data'],
                       'custom_css' => $method_data['custom_css'],
                       'custom_js' => $method_data['custom_js'],
                       'js_url'   => $method_data['js_url'],
                       'integration' => $integration
                    );
                    if (!!$method_data['admin_only']) {
                        $xmeta['admin_login'] =  true;
                    }
                    if ($method_data['geo_zone_all'] != 1) {
                        $xmeta['geo'] = true;
                    }
                    if ($method_data['shipping_all'] != 1) {
                        $xmeta['shipping_rule'] = true;
                    }
                    if ($method_data['total_type'] == 'sub_coupon' || $method_data['total_type'] == 'total_coupon') {
                        $xmeta['coupon'] = true;
                    }
                    if ((int)$method_data['manufacturer_rule'] > 1) {
                        $xmeta['product_query'] = true;
                    }
                    if ((int)$method_data['category'] > 1) {
                        $xmeta['category_query'] = true;
                    }
                }
            }
            $xpayment = array('xmeta' => $xmeta, 'xmethods' => $xmethods);
            $this->cache->set('ocm.xpayment', $xpayment);
        }
        return $xpayment;
    }
    private function _resetEmptyRule($data) {
        $rules = array(
            'store'            => 'store_all',
            'country'          => 'country_all', 
            'geo_zone'         => 'geo_zone_all',
            'currency'         => 'currency_all',
            'customer_group'   => 'customer_group_all',
            'shipping'         => 'shipping_all',
            'city'             => 'city_all',
            'postal'           => 'postal_all',
            'coupon'           => 'coupon_all',
            'voucher'          => 'voucher_all',
            'product_category' => 'category',
            'product_product'  => 'product',
            'product_option'   => 'option',
            'product_attribute' => 'attribute',
            'manufacturer'     => 'manufacturer_rule',
            'customers'        => 'customer_all',
            'email'            => 'email_all'
        );
        foreach ($rules as $key => $value) {
            if (!isset($data[$value])) {
                $data[$value] = '';
            }
            if (!isset($data[$key]) || !$data[$key]) {
                $data[$value] = 1;
                $data[$key] = '';
            }
            /* Make product field empty if all was chosen*/
            if ($data[$value] < 2 && in_array($key, array('product_category', 'product_product', 'product_option', 'product_attribute', 'manufacturer'))) {
                $data[$key] = array();
            }
        }
        /* reset delimitter to comma */
        $fields = array(
            'city',
            'coupon',
            'voucher',
            'postal',
            'email'
        );
        foreach ($fields as $field) {
            if ($data[$field]) {
                $data[$field] = str_replace(PHP_EOL, ',', $data[$field]);
            }
        }
        $shipping = array();
        if (isset($data['shipping']) && is_array($data['shipping'])) {
            foreach($data['shipping'] as $method) {
                $shipping[] = $method;
                $shipping[] = $method .'.'. $method;
                /* for usps */
                if (strpos($method,'international_') !== false) {
                    $shipping[] = str_replace('international_','',$method);
                }
                if (strpos($method,'domestic_') !== false) {
                    $shipping[] = str_replace('domestic_','',$method);
                }
            }
            $data['shipping'] = $shipping;
        }
        /* checkboxes */
        if (!isset($data['inc_email'])) $data['inc_email'] = '';
        if (!isset($data['hide_title'])) $data['hide_title'] = '';
        if (!isset($data['hash'])) $data['hash'] = '';
        if (!isset($data['hash_bin'])) $data['hash_bin'] = '';
        if (!isset($data['hash_str'])) $data['hash_str'] = '';
        if (!isset($data['hash_base64'])) $data['hash_base64'] = '';
        if (!isset($data['inc_order'])) $data['inc_order'] = '';
        if (!isset($data['admin_only'])) $data['admin_only'] = '';
        if (!isset($data['callback_enable'])) $data['callback_enable'] = '';
        if (!isset($data['out_of_stock'])) $data['out_of_stock'] = '';
        if (empty($data['product_or'])) $data['product_or'] = '';
        /* Reset other */
        $data['int_name']    = strtolower(preg_replace('/[^a-zA-Z0-9_\/]/', '', (string)$data['int_name']));
        $data['custom_js']   = $this->ocm->html_decode($data['custom_js']);
        $data['js_url']      = $this->ocm->html_decode($data['js_url']);
        $data['custom_css']  = $this->ocm->html_decode($data['custom_css']);
        $data['int_url']     = $this->ocm->html_decode($data['int_url']);
        $data['int_success'] = $this->ocm->html_decode($data['int_success']);
        $data['success']     = $this->ocm->html_decode($data['success']); 
        $data['hash_str']    = $this->ocm->html_decode(trim($data['hash_str'])); 
        $data['method_type'] = strtoupper($data['method_type']);
        if (!empty($data['total_start']) && empty($data['total_end'])) $data['total_end'] = PHP_INT_MAX;
        if (!empty($data['weight_start']) && empty($data['weight_end'])) $data['weight_end'] = PHP_INT_MAX;
        if (!empty($data['quantity_start']) && empty($data['quantity_end'])) $data['quantity_end'] = PHP_INT_MAX;
        if (!isset($data['days'])) $data['days'] = array();
        if (!isset($data['free'])) $data['free'] = '';
        if (!isset($data['name']) || !is_array($data['name'])) $data['name'] = array();
        if (!isset($data['instruction']) || !is_array($data['instruction'])) $data['instruction'] = array();
        if (!isset($data['email_instruction']) || !is_array($data['email_instruction'])) $data['email_instruction'] = array();
        if (!isset($data['error']) || !is_array($data['error'])) $data['error'] = array();
        if (empty($data['int_data']) || !is_array($data['int_data'])) $data['int_data'] = array();
        if (empty($data['header_data']) || !is_array($data['header_data'])) $data['header_data'] = array();
        if (empty($data['additional_data']) || !is_array($data['additional_data'])) $data['additional_data'] = array();
        if (empty($data['callback_data']) || !is_array($data['callback_data'])) $data['callback_data'] = array();
        if (empty($data['display'])) $data['display'] = 'Untitled Payment';
        return $data;
    }
    private function _findValidRules($data) {
        $rules = array();
        if ($data['store_all'] != 1) {
            $rules['store'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['store'],
                'compare_with' => 'store_id',
                'false_value' => false
            );
        }
        if ($data['geo_zone_all'] != 1) {
            $rules['geo_zone'] = array(
                'type' => 'intersect',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $data['geo_zone'],
                'compare_with' => 'geo',
                'false_value' => false
            );
        }
        if ($data['email_all'] != 1) {
            $false_value = ($data['email_rule'] == 'inclusive') ? false : true;
            $cities = explode(',',trim($data['email']));
            $cities = array_map('strtolower', $cities);
            $cities = array_map('trim', $cities);
            $rules['email'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $cities,
                'compare_with' => 'email',
                'false_value' => $false_value
            );
        }
        if ($data['customer_all'] != 1) {
            $false_value = ($data['customer_rule'] == 'inclusive') ? false : true;
            $rules['customers'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['customers'],
                'compare_with' => 'customer_id',
                'false_value' => $false_value
            );
        }
        if ($data['country_all'] != 1) {
            $rules['country'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $data['country'],
                'compare_with' => 'country_id',
                'false_value' => false
            );
        }
        if ($data['currency_all'] != 1) {
            $rules['currency'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['currency'],
                'compare_with' => 'currency_id',
                'false_value' => false
            );
        }
        if ($data['customer_group_all'] != 1) {
            $rules['customer_group'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['customer_group'],
                'compare_with' => 'customer_group_id',
                'false_value' => false
            );
        }
        if ($data['shipping_all'] != 1) {
            $rules['shipping'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['shipping'],
                'compare_with' => 'shipping_method',
                'false_value' => false
            );
        }
        if ($data['city_all'] != 1) {
            $false_value = ($data['city_rule'] == 'inclusive') ? false : true;
            $cities = explode(',',trim($data['city']));
            $cities = array_map('strtolower', $cities);
            $cities = array_map('trim', $cities);
            $rules['city'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $cities,
                'compare_with' => 'city',
                'false_value' => $false_value
            );
        }
        if ($data['postal_all'] != 1) {
            $postcodes = explode(',',trim($data['postal']));
            $postcodes = array_map('trim', $postcodes);
            $rules['postal'] = array(
                'type' => 'function',
                'func' => '_validatePostal',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $postcodes,
                'compare_with' => 'postcode',
                'rule_type' => $data['postal_rule'],
                'false_value' => false
            );
        }
        if ($data['coupon_all'] != 1) {
            $coupons = explode(',',trim($data['coupon']));
            $coupons = array_map('trim', $coupons);
            $coupons = array_map('strtolower', $coupons);
            $rules['coupon'] = array(
                'type' => 'function',
                'func' => '_validateCoupon',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $coupons,
                'compare_with' => 'coupon_code',
                'rule_type' => $data['coupon_rule'],
                'false_value' => false
            );
        }
        if ($data['voucher_all'] != 1) {
            $vouchers = explode(',',trim($data['voucher']));
            $vouchers = array_map('trim', $vouchers);
            $vouchers = array_map('strtolower', $vouchers);
            $rules['voucher'] = array(
                'type' => 'function',
                'func' => '_validateVoucher',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $vouchers,
                'compare_with' => 'voucher_code',
                'rule_type' => $data['voucher_rule'],
                'false_value' => false
            );
        }
        if ((int)$data['product'] > 1) {
            $rules['product'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_product'],
                'compare_with' => 'product',
                'rule_type' => $data['product'],
                'false_value' => false
            );
        }
        if ((int)$data['category'] > 1) {
            $rules['category'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_category'],
                'compare_with' => 'category',
                'rule_type' => $data['category'],
                'false_value' => false
            );
        }
        if ((int)$data['option'] > 1) {
            $rules['option'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_option'],
                'compare_with' => 'option',
                'rule_type' => $data['option'],
                'false_value' => false
            );
        }
        if ((int)$data['attribute'] > 1) {
            $rules['attribute'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['product_attribute'],
                'compare_with' => 'attribute',
                'rule_type' => $data['attribute'],
                'false_value' => false
            );
        }
        if ((int)$data['manufacturer_rule'] > 1) {
            $rules['manufacturer'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $data['manufacturer'],
                'compare_with' => 'manufacturer',
                'rule_type' => $data['manufacturer_rule'],
                'false_value' => false
            );
        }
        if ($data['out_of_stock']) {
            $rules['out_of_stock'] = array(
                'type' => 'equal',
                'product_rule' => false,
                'address_rule' => false,
                'value' => '',
                'compare_with' => 'out_of_stock',
                'false_value' => true
            );
        }
        if (is_array($data['days']) && $data['days'] && count($data['days']) !== 7) {
            $rules['days'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['days'],
                'compare_with' => 'day',
                'false_value' => false
            );
        }
        if ($data['date_start'] != "" && $data['date_end']) {
            $rules['date'] = array(
                'type' => 'in_between',
                'product_rule' => false,
                'address_rule' => false,
                'start' => $data['date_start'],
                'end' => $data['date_end'],
                'compare_with' => 'date'
            );
        }
        if ($data['time_start'] != "" && $data['time_end'] != "") {
            $valid_hours = array();
            $time_start = (int)$data['time_start'];
            $time_end = (int)$data['time_end'];
            if ($time_start <= $time_end) {
               for ($i = $time_start; $i < $time_end ; $i++) { 
                  $valid_hours[] = $i;
               }
            } else {
               for ($i = 0; $i < $time_end ; $i++) { 
                  $valid_hours[] = $i;
               }
               for ($i = $time_start; $i <= 23 ; $i++) { 
                  $valid_hours[] = $i;
               }
            }
            if ($valid_hours) {
                $rules['time'] = array(
                    'type' => 'in_array',
                    'product_rule' => false,
                    'address_rule' => false,
                    'value' => $valid_hours,
                    'compare_with' => 'time',
                    'false_value' => false
                );
            }
        }
        if ($data['total_start'] != "" 
            && (float)$data['total_end']) {
                $rules['total_range'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (float)$data['total_start'],
                    'end' => (float)$data['total_end'],
                    'compare_with' => $data['total_type']
                );
        }
        if ((float)$data['weight_end'] > 0) {
                $rules['weight_range'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (float)$data['weight_start'],
                    'end' => (float)$data['weight_end'],
                    'compare_with' => 'weight'
                );
        }
        if ($data['quantity_start'] != ""
            && (int)$data['quantity_end']) {
                $rules['qunatity_range'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (int)$data['quantity_start'],
                    'end' => (int)$data['quantity_end'],
                    'compare_with' => 'quantity'
                );
        }
        if ($data['admin_only']) {
            $rules['admin'] = array(
                'type' => 'equal',
                'product_rule' => false,
                'address_rule' => false,
                'value' => '',
                'compare_with' => 'admin_logged',
                'false_value' => false
            );
        }
        if ($data['free']) {
            $rules['free'] = array(
                'type' => 'equal',
                'product_rule' => false,
                'address_rule' => false,
                'value' => '',
                'compare_with' => 'free',
                'false_value' => false
            );
        }
        return $rules;
    }
    private function getPaymentResource() {
        $language_id = $this->config->get('config_language_id');
        $logo = array();
        $desc = array();
        $form_id = array();
        $script = '';
        $xpayments = $this->getPayments();
        foreach($xpayments['xmethods'] as $xpayment) {
            $tab_id = $xpayment['tab_id'];
            if (isset($xpayment['desc'][$language_id]) && $xpayment['desc'][$language_id]) {
                $desc[$tab_id] = $this->ocm->html_decode($xpayment['desc'][$language_id]);
            }
            if ($xpayment['logo']) {
                $logo[$tab_id] = $xpayment['logo'];
            }
            if ($xpayment['js_url']) {
                $js_url = explode('&', $xpayment['js_url']);
                foreach ($js_url as $url) {
                    if ($url) {
                        $script .= '<script src="' . $url . '" defer type="text/javascript"></script>';
                    }
                }
            }
            if (isset($xpayment['instruction'][$language_id]) && $xpayment['instruction'][$language_id] && preg_match('/\[xform\](\d+)\[\/xform\]/', $xpayment['instruction'][$language_id], $match)) {
                $form_id[] = $match[1];
            }
        }
        return array('logo' => $logo, 'desc' => $desc, 'shipping' => $xpayments['xmeta']['shipping_rule'], 'script' => $script, 'form_id' => $form_id);
    }
    public function getScript() {
        $js = '';
        if ($this->ocm->isCheckoutPage()) {
            $logo_info = $this->getPaymentResource();
            $_xpayment = array();
            $_xpayment['divider'] = $this->ocm->divider;
            $_xpayment['logo'] = $logo_info['logo'];
            $_xpayment['desc'] = $logo_info['desc'];
            $_xpayment['shipping'] = $logo_info['shipping'];
            $_xpayment['selector'] = array(
                'logo'  => '',
                'desc'  => '',
                'input' => ''
            );
            $_selector = $this->ocm->getConfig('xpayment_ui', $this->mtype);
            if (isset($_selector['logo']) && $_selector['logo']) {
                $_xpayment['selector']['logo'] = $this->ocm->html_decode($_selector['logo']);
            }
            if (isset($_selector['desc']) && $_selector['desc']) {
                $_xpayment['selector']['desc'] = $this->ocm->html_decode($_selector['desc']);
            }
            if (isset($_selector['input']) && $_selector['input']) {
                $_xpayment['selector']['input'] = $this->ocm->html_decode($_selector['input']);
            }
            if (isset($_selector['error']) && $_selector['error']) {
                $_xpayment['selector']['error'] = $this->ocm->html_decode($_selector['error']);
            }
            if ($logo_info['form_id'] && $this->ocm->getConfig('xform_status', 'module')) {
                $fields = array();
                $xform = new \Xform($this->registry);
                foreach ($logo_info['form_id'] as $form_id) {
                    $fields = $xform->getFormFields($form_id, array('onlyTime' => true));
                    if ($fields) {
                        break;
                    }
                }
                $js .= $xform->getAssets(false, $fields, array());
            }
            $ocm_base_url = $this->ocm->common->getOcmBaseUrl('catalog', $this->mname);
            $js .= '<script type="text/javascript">';
            $js .= 'var _xpayment = '.json_encode($_xpayment).';';
            $js .= 'if (!window.xpayment && window.Xpayment) window.xpayment = new Xpayment();';
            $js .= '</script>';
            $js .= '<style>img.xpayment-logo { max-height: 70px; vertical-align: middle;} .xpayment-desc {color: #999999;font-size: 11px;display:block;} .modal-body .xpayment-desc {margin: -5px 0px 5px 30px;} .modal-body .xpayment-logo {margin: 0px 0px 0px 5px;}</style>';
            if (isset($_selector['css']) && $_selector['css']) {
                $js .= '<style>' . $this->ocm->html_decode($_selector['css']) . '</style>';
            }
            $js .= '<script src="' . $ocm_base_url . 'view/javascript/xpayment.min.js?v=5.0.1" defer type="text/javascript"></script>';
            if ($logo_info['script']) {
                $js .= $logo_info['script'];
            }
        }
        return $js;
    }
    public function getReplacers($order_info, $product_placeholder = false) {
        $default_values = array(
            'order_id' => 0,
            'store_id' => 0,
            'store_name' => '',
            'store_url' => '',
            'total' => 0,
            'currency_code' => isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency'),
            'currency_value' => 1,
            'customer_id' => '',
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'telephone' => '',
            'fax' => '',
            'payment_address_1' => '',
            'payment_address_2' => '',
            'payment_city' => '',
            'payment_postcode' => '',
            'payment_zone' => '',
            'payment_country' => '',
            'ip' => '',
            'payment_country_id' => $this->config->get('config_country_id'),
            'payment_zone_id' => 0
        );
        if (!is_array($order_info)) $order_info = array();
        $order_info = array_merge($default_values, $order_info);
        $order_info['orderTotal'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
        $order_info['orderTotalFormatted'] =$this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value']);
        $order_info['orderTotalInPenny'] = intval((float)$order_info['orderTotal'] * 100);
        $order_info['fullName'] = $order_info['firstname'].' '.$order_info['lastname'];
        $this->load->model('localisation/country');
        $this->load->model('localisation/zone');
        $countryInfo = $this->model_localisation_country->getCountry($order_info['payment_country_id']);
        $zoneInfo = $this->model_localisation_zone->getZone($order_info['payment_zone_id']);
        if (!isset($countryInfo['iso_code_2'])) {
            $countryInfo['iso_code_2'] = '';
        }
        if (!isset($zoneInfo['code'])) {
            $zoneInfo['code'] = '';
        }
        $returnURL = $this->url->link($this->ext_path . $this->ocm->divider . 'confirm', '', true);
        $notifyURL = $this->url->link($this->ext_path . $this->ocm->divider . 'notify', '', true);
        $checkoutURL = $this->url->link('checkout/checkout', '', true);
        $successURL = $this->url->link('checkout/success');
        $placeholder = array(
            '{orderId}',
            '{orderIdUnique}',
            '{orderTotal}',
            '{orderTotalInPenny}',
            '{orderTotalFormatted}', 
            '{customerId}',
            '{fullName}',
            '{returnURL}',
            '{notifyURL}',
            '{checkoutURL}',
            '{successURL}',
            '{currencyCode}',
            '{currencyNumber}',
            '{firstName}',
            '{lastName}',
            '{streetAddress1}',
            '{streetAddress2}',
            '{city}',
            '{stateName}',
            '{zipcode}',
            '{country}',
            '{phone}',
            '{fax}',
            '{email}',
            '{storeName}',
            '{storeId}',
            '{storeURL}',
            '{ip}',
            '{timestamp}',
            '{timestampInMs}',
            '{countryCode}',
            '{stateCode}',
            '{sid}',
            '{route}'
        );
        if (!isset($order_info['fax'])) {
            $order_info['fax'] = '';
        }
        $replacer = array(
            $order_info['order_id'],
            $order_info['order_id'] . '-' . uniqid(),
            $order_info['orderTotal'],
            $order_info['orderTotalInPenny'],
            $order_info['orderTotalFormatted'],
            $order_info['customer_id'],
            $order_info['fullName'],
            $returnURL,
            $notifyURL,
            $checkoutURL,
            $successURL,
            $order_info['currency_code'],
            $this->getCurrencyNumber($order_info['currency_code']),
            $order_info['firstname'],
            $order_info['lastname'],
            $order_info['payment_address_1'],
            $order_info['payment_address_2'],
            $order_info['payment_city'],
            $order_info['payment_zone'],
            $order_info['payment_postcode'],
            $order_info['payment_country'],
            $order_info['telephone'],
            $order_info['fax'],
            $order_info['email'],
            $order_info['store_name'],
            $order_info['store_id'],
            $this->ocm->common->getSiteUrl(),
            $order_info['ip'],
            time(),
            intval(microtime(true)*1000),
            $countryInfo['iso_code_2'],
            $zoneInfo['code'],
            $this->session->getId(),
            $this->ocm->common->getExtPath('payment', 'xpayment')
        );
        /* additional data */
        $additional_data = $this->getAdditionalData($order_info);
        $list = array();
        foreach ($additional_data as $each) {
            if (substr($each['name'], -2) == '[]') {
                $each['name'] = trim($each['name'], '[]');
                if (!isset($list[$each['name']])) $list[$each['name']] = array();
                $list[$each['name']][] = $each['value'];
                continue;
            }
            $each['value'] = $this->ocm->html_decode(trim($each['value']));
            // $each['value'] = str_replace($placeholder, $replacer, $each['value']); // replacement on only value NOT key. Key must be kept intact
            $each['value'] = $this->applyPlaceholderWithFunction($each['value'], $placeholder, $replacer);
            $each['value'] = $this->sanitizeParamValue($each['value']);
            array_push($placeholder, trim($each['name']));
            array_push($replacer, $each['value']);
        }
        /* hash string */
        $hash_data = $this->getHashData($order_info, $placeholder, $replacer);
        if ($hash_data) {
            // if hash is derived from hook
            if (!empty($hash_data['hash'])) {
                $hash = $hash_data['hash'];
            } else {
                if (isset($hash_data['shortcode']) && $hash_data['shortcode']) {
                    $_datas = $this->getIntegrationData($order_info, array('placeholder' => $placeholder, 'replacer' => $replacer));
                    $hash_data['str'] = $this->applyShortcode($hash_data['str'], $_datas);
                }
                // $str = str_replace($placeholder, $replacer, $hash_data['str']);
                $str = $this->applyPlaceholderWithFunction($hash_data['str'], $placeholder, $replacer);
                $str = $this->sanitizeParamValue($str);
                if ($hash_data['key']) {
                    $key = str_replace($placeholder, $replacer, $hash_data['key']);
                    $hash = hash_hmac($hash_data['algo'], $str, $key, $hash_data['bin']);
                } else {
                    $hash = hash($hash_data['algo'], $str, $hash_data['bin']);
                }
                if ($hash_data['base64']) {
                    $hash = base64_encode($hash);
                }
            }    
            array_push($placeholder, '{hash}');
            array_push($replacer, $hash);
        }
        //placeholder from request
        if (!empty($this->request->request) && is_array($this->request->request)) {
            $ignore = array('route', 'language', 'currency', 'OCSESSID', '__atuvc');
            foreach ($this->request->request as $key => $value) {
                if (is_string($value) && !in_array($key, $ignore)) {
                    array_push($placeholder, '{'.$key.'}');
                    array_push($replacer, $value);
                }
            }
        }
        if ($product_placeholder) {
            $product_replacers = $this->getProductReplacer($order_info);
            $placeholder[] = '{productName}';
            $placeholder[] = '{productId}';
            $placeholder[] = '{productModel}';
            $placeholder[] = '{productQuantity}';
            $placeholder[] = '{productPrice}';
            $replacer[] = implode(', ', $product_replacers['productName']);
            $replacer[] = implode(', ', $product_replacers['productId']);
            $replacer[] = implode(', ', $product_replacers['productModel']);
            $replacer[] = implode(', ', $product_replacers['productQuantity']);
            $replacer[] = implode(', ', $product_replacers['productPrice']);
        }
        return array(
            'placeholder' => $placeholder,
            'replacer' => $replacer,
            'list'    => $list
        );
    }
    public function getHashData($order_info, $placeholder, $replacer) {
        $hash = array();
        if (!$order_info || !isset($order_info['payment_method'])) {
            return $hash;
        }
        $xpayment = $this->getMethodDataByOrder($order_info);
        if ($xpayment && $xpayment['integration'] && !empty($xpayment['integration']['hash'])) {
            $hash = $xpayment['integration']['hash'];
            $integration = $xpayment['integration'];
            /* hook - get hash data from hook */
            $addon = $this->getAddon($integration);
            if ($addon && (method_exists($addon, '_xHash') || property_exists($addon, '_xHash'))) {
                $hook_result = $addon->_xHash($placeholder, $replacer, $hash);
                if ($hook_result && !empty($hook_result['hash'])) {
                     $hash['hash'] = $hook_result['hash'];
                }
            }
        }
        return $hash;
    }
    public function getAdditionalData($order_info) {
        $additional_data = array();
        if (!$order_info || !isset($order_info['payment_method'])) {
            return $additional_data;
        }
        $xpayment = $this->getMethodDataByOrder($order_info);
        if ($xpayment && $xpayment['integration'] && $xpayment['integration']['additional']) {
            $additional_data = $xpayment['integration']['additional'];
        }
        return $additional_data;
    }
    public function getProductReplacer($order_info) {
        $name = array();
        $id = array();
        $model = array();
        $quantity = array();
        $price = array();
        $products = array();
        if ($order_info) {
            $products = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id = '" . (int)$order_info['order_id'] . "'")->rows;
            $currency_code = $order_info['currency_code'];
            $currency_value = isset($order_info['currency_value']) ? $order_info['currency_value'] : 1;
            foreach ($products as $product) {
                $product['price'] = $this->currency->format($product['price'], $currency_code, $currency_value,false);
                $name[] = $product['name'];
                $id[] = $product['product_id'];
                $model[] = $product['model'];
                $quantity[] = $product['quantity'];
                $price[] = $product['price'];
            }
        }
        return array(
            'productName' => $name,
            'productId' => $id,
            'productModel' => $model,
            'productQuantity' => $quantity,
            'productPrice' => $price
        );
    }
    public function getIntegrationData($order_info, $replacement = false, $return_type = 'assoc') {
        $xpayment = $this->getMethodDataByOrder($order_info);
        if (!$xpayment || !$xpayment['integration'] || !$xpayment['integration']['data']) {
            return array();
        }
        $replacement = $replacement ? $replacement : $this->getReplacers($order_info);
        $placeholder = $replacement['placeholder'];
        $replacer = $replacement['replacer'];
        /* let replace product placeholder finally */
        $product_keys = array('productId', 'productName', 'productQuantity', 'productModel', 'productPrice');
        $return_array = array();
        $return_assoc = array();
        $product_replacement = false;
        $request = $xpayment['integration']['data'];
        foreach ($request as $param) {
            $key = trim($param['name']); // keep key intact, no replacement for keys 
            $value = $this->ocm->html_decode(trim($param['value']));
            // $value = str_replace($placeholder, $replacer, $value);
            $value = $this->applyPlaceholderWithFunction($value, $placeholder, $replacer);
            $pro_key = trim($value);
            $pro_key = trim($pro_key,'{}');
            if (in_array($pro_key, $product_keys)) {
                if ($product_replacement === false) {
                    $product_replacement = $this->getProductReplacer($order_info);
                }
                if (isset($product_replacement[$pro_key]) && $product_replacement[$pro_key]) {
                    foreach ($product_replacement[$pro_key] as $count => $product_replacer) {
                        $new_key = str_replace('{count}', $count, $key);
                        $return_array[] = array(
                            'name' => $new_key,
                            'value' => $product_replacer
                        );
                        $return_assoc[$new_key] = $product_replacer;
                    }
                }
            } else {
                $value = $this->sanitizeParamValue($value);
                $return_array[] = array(
                        'name' => $key,
                        'value' => $value
                );
                $return_assoc[$key] = $value;
            }
        }
        return $return_type == 'assoc' ? $return_assoc : $return_array;
    }
    public function getHeaderData($headers, $placeholder, $replacer) {
        $return = array();
        if (!empty($headers) && is_array($headers)) {
            foreach ($headers as $each) {
                $key = trim($each['name']);
                $value = $this->ocm->html_decode(trim($each['value']));
                // $value = str_replace($placeholder, $replacer, $value);
                $value = $this->applyPlaceholderWithFunction($value, $placeholder, $replacer);
                $value = $this->sanitizeParamValue($value);
                $return[] = array(
                    'name'  => $key,
                    'value' => $value
                );
            }
        }
        return $return;
    }
    private function sanitizeParamValue($value) {
        if ($value === 'true') {
            $value = true;
        }
        else if ($value === 'false') {
            $value = false;
        }
        else if (preg_match('/(\w+)\(([\w\W]+)\)/', $value, $match)) {
            $value = $this->applyFunctions($value);
        } else if (!is_array($value)) {
            $_value = json_decode($value, true); // if it is object, lets decode
            if (is_array($_value)) {
                $value = $_value;
            }
        }
        return $value;
    }
    public function applyPlaceholderWithFunction($value, $placeholder, $replacer) {
        $callstacks = array();
        if (preg_match('/(\w+)\(([\w\W]+)\)/', $value, $match)) {
            $this->applyFunctions($value, $callstacks);
        } 
        if ($callstacks) {
            $args = $callstacks[0]['args'];
            foreach($args as $i => $arg) {
                $arg = str_replace($placeholder, $replacer, $arg);
                $args[$i] = trim($arg,  '"\''); // trim single/double quotes only. note: don't remove space
            }
            foreach($callstacks as $callstack) {
                if (!is_array($args)) {
                    $args = array($args);
                }
                $args = call_user_func_array($callstack['fn'], $args);
            }
            $value = $args;
        }
        else {
            $value = str_replace($placeholder, $replacer, $value);
        }
        return $value;
    }
    public function getInstructionView($order_id) {
        $this->load->model('checkout/order');
        $this->language->load($this->ext_path);
        $formatted_instruction = '';
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $xpayment = $this->getMethodDataByOrder($order_info);
        if ($xpayment) {
            $instruction = $xpayment['instruction'];
            /* don't need xform in email or order info so remove it*/
            $instruction = preg_replace('/\[xform\](\d+)\[\/xform\]/', '', $instruction);
            $replacement = $this->getReplacers($order_info);
            $placeholder = $replacement['placeholder'];
            $replacer = $replacement['replacer'];
            $instruction = str_replace($placeholder, $replacer, $instruction);
            $instruction = $this->ocm->html_decode($instruction);
            if ($xpayment['inc_order'] && $instruction) {
                 $formatted_instruction .= ' <table class="table table-bordered table-hover list">
                    <thead>
                      <tr>
                        <td class="text-left">'.$this->language->get('text_payment_instruction').'</td>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td class="text-left">'.$instruction.'</td>
                      </tr>
                    </tbody>
                  </table>';
            }
        }
        return $formatted_instruction;
    }
    public function guessErrorMessage($xpayment, $integration, $response) {
        $error_keys = array();
        $error = '';
        if (!empty($integration['meta']['_key'])
            && !empty($integration['meta']['_key']['_keyError'])) {
            $error_keys[] = $integration['meta']['_key']['_keyError'];
        }
        if (!empty($integration['meta']['_key'])
            && !empty($integration['meta']['_key']['_keyErrorAdd'])) {
            $error_keys[] = $integration['meta']['_key']['_keyErrorAdd'];
        }
        $error_keys[] = 'error.message';
        $error_keys[] = 'error.error';
        $error_keys[] = 'error.description';
        $error_keys[] = 'error.msg';
        $error_keys[] = 'error.desc';
        $error_keys[] = 'error';
        $error_keys[] = 'ErrMsg';
        $error_keys[] = 'response';
        foreach ($error_keys as $error_key) {
            $error = $this->getIn($error_key, $response);
            if ($error) {
                break;
            }
        }
        if ($error) {
            if (is_array($error)) {
                $error = array_pop($error);
            }
            $error = is_string($error) ? $error : '';
        } 
        if (!$error) {
            $error = $xpayment['error'];
        }
        return $error;
    }
    public function settlement($order_info) {
        $this->load->model('checkout/order');
        $redirect = $this->url->link('checkout/success');
        $xpayment = $this->getMethodDataByOrder($order_info);
        if (!$xpayment) {
            return array(
                'status'         => false,
                'error'          => '',
                'custom_success' => false,
                'redirect'       => $redirect,
                'type'           => ''
            );
        }
        $notification = $xpayment['notification'];
        $additional_email = trim($xpayment['additional_email']);
        $integration = $xpayment['integration'];
        $type = $integration ? $integration['type'] : '';
        $additional = $integration ? $integration['additional'] : '';
        $replacement = $this->getReplacers($order_info);
        $is_success = true;
        $error = $redirect = '';
        $response = array_merge($this->request->get, $this->request->post);
        if ($integration && $integration['success']) {
            $result = $this->validateSuccess($order_info, $integration, $replacement);
            $replacement = $result['replacement'];
            $is_success = $result['status'];
            $response = $result['response'];
        }
        if ($is_success) {
            $order_status_id = $this->getOrderStatus($xpayment, $integration, $response);
            $comment = $this->ocm->common->getVar('_xcomment');
            if ($xpayment['inc_email']) {
                $comment = $xpayment['email_instruction'] ? $xpayment['email_instruction'] : $xpayment['instruction'];
                $comment = str_replace($replacement['placeholder'], $replacement['replacer'], $comment);
            }
            $_status_id = $this->ocm->common->getVar('_status_id');
            $order_status_id = $_status_id ? $_status_id : $order_status_id;
            if ($order_status_id && (int)$order_status_id !== (int)$order_info['order_status_id']) {
                /* set additional email */
                $this->setAdditionalEmail($additional_email);
                $historyMethod = VERSION >= '4.0.0.0' ? 'addHistory' : 'addOrderHistory';
                $this->model_checkout_order->{$historyMethod}($order_info['order_id'], $order_status_id, $comment, true);
                /* Notify if url is set*/
                if ($notification && $xpayment['callback']) {
                    $callback = str_replace($replacement['placeholder'], $replacement['replacer'], $xpayment['callback']);
                    $curl_data = $this->ocm->common->toCurlData($xpayment['callback_data']);
                    foreach ($curl_data as $key => $value) {
                        $curl_data[$key] = str_replace($replacement['placeholder'], $replacement['replacer'], $value);
                    }
                    $this->ocm->common->curlReq($callback, 'POST', $curl_data);
                }
            }
            $redirect = $xpayment['success'] ? str_replace($replacement['placeholder'], $replacement['replacer'], $xpayment['success']) : $this->url->link('checkout/success');
        } else {
            $redirect = $this->url->link('checkout/checkout');
            $error = $this->guessErrorMessage($xpayment, $integration, $response);
            $xpayment_debug = $this->ocm->getConfig('xpayment_debug', $this->mtype);
            if ($xpayment_debug) {
                $log = 'X-Payment gateway response log (Disable debug mode of the x-payment to turn off)' . PHP_EOL;
                $log .= 'POST:' . PHP_EOL;
                $log .= print_r($this->request->post, true);
                $log .= 'GET:' . PHP_EOL;
                $log .= print_r($this->request->get, true);
                $this->log->write($log);
            }
        }
        return array(
            'status'         => $is_success,
            'error'          => $error,
            'replacement'    => $replacement,
            'redirect'       => $redirect,
            'custom_success' => !!$xpayment['success'],
            'type'           => $type
        );
    }
    private function validateSuccess($order_info, $integration, $replacement) {
        $error = '';
        $is_success = false;
        $placeholder = $replacement['placeholder'];
        $replacer = $replacement['replacer'];
        $integration['success'] = preg_replace('/\s+/','', $integration['success']);
        $integration['success'] = str_replace(array('$'), '', $integration['success']);
        $response_data = array();
        if ($integration['type'] == 'redirect') {
            if (strtolower($integration['success_method']) == 'post') {
                $response_data = $this->request->post;
            } else if (strtolower($integration['success_method']) == 'get') {
                $response_data = $this->request->get;
            } else {
                $response_data = file_get_contents('php://input');
                $response_data = json_decode($response_data, true);
            }
        } else {
            $response_data = $this->request->request;
        }
        /* add response to placeholders */ 
        foreach($response_data as $key => $value) {
            if (is_array($value)) continue; // ignore array values
            array_push($placeholder,'{'.$key.'}');
            array_push($replacer, $value);
        }
        if ($integration['type'] == 'api' && $integration['url']) {
            $response_data = $this->invokeAPI($order_info, $integration, $replacement);
        }
        /* hook - get success data from hook */
        $addon = $this->getAddon($integration);
        if ($addon && (method_exists($addon, '_xSuccess') || property_exists($addon, '_xSuccess'))) {
            $hook_result = $addon->_xSuccess($placeholder, $replacer);
            if ($hook_result && is_array($hook_result)) {
                 $response_data = array_merge($response_data, $hook_result);
            }
        }
        /* end of hook*/
        $integration['success'] = str_replace($placeholder, $replacer, $integration['success']);
        preg_match_all('/([\d\w\(\)\.]+)([\!=>]{1,3})([\d\w\'"]+)([&|]{2})?/', $integration['success'], $conditions, PREG_SET_ORDER);
        if (count($conditions) > 0) {
            foreach($conditions as $condition) {
                $left = $condition[1];
                $right = $condition[3];
                $cond = $condition[2];
                $next_cond = (isset($condition[4]) && $condition[4]) ? $condition[4]: '';
                if (preg_match('/(\w+)\((\w+)\)/', $left, $match)) {
                    $fn = (!empty($match[1]) && function_exists($match[1])) ? $match[1] : false;
                    $left = $match[2];
                } else {
                    $fn = false;
                }
                $left = $this->getIn($left, $response_data);
                $right = str_replace($placeholder, $replacer, $right);
                if ($fn) {
                    $left = $fn($left);
                }
                if ($cond =='===' || $cond =='==') {
                    $right = trim($right, '"');
                    $right = trim($right, "'");
                    $is_success = ($left == $right);
                    if (!$is_success && trim($right,'"') == "true") {
                        $is_success = ($left == "true" || $left === true);
                    }
                    if (!$is_success && trim($right,'"') == "false") {
                        $is_success = ($left == "false" || $left === false); 
                    }
                    // list from custom placeholder
                    if (!$is_success && isset($replacement['list']) && isset($replacement['list'][$right])) {
                        if (in_array($left, $replacement['list'][$right])) {
                            $is_success = true;
                        }
                    }
                } else if ($cond =='!==' || $cond =='!=') {
                    $right = trim($right, '"');
                    $right = trim($right, "'");
                    $is_success = ($left != $right);
                } else if ($cond =='>') {
                    $is_success = ($left > $right);
                } else if ($cond =='<') {
                    $is_success = ($left < $right);
                } else if ($cond =='<=') {
                    $is_success = ($left <= $right);
                } else if ($cond =='>=') {
                    $is_success = ($left >= $right);
                } else {
                    $is_success = false;
                }
                if (!$is_success && $next_cond == '&&') {
                    break;
                }
                if ($is_success && $next_cond == '||') {
                    break;
                }
            }
        }
        $replacement = array(
            'placeholder' => $placeholder,
            'replacer' => $replacer
        );
        return array(
            'status'      => $is_success,
            'response'    => $response_data,
            'replacement' => $replacement
        );
    }
    private function applyFunctions($str, &$callstacks) {
        $regex = '/(\w+)\(([\w\W]+)\)/';
        if (preg_match($regex, $str, $match)) {
            if (!empty($match[1]) && function_exists($match[1])) {
                $_args = $this->applyFunctions($match[2], $callstacks);
                if ($_args) {
                    $args = is_string($_args) ? explode(',' , $_args) : $_args;
                } else {
                    $args = array();
                }
                $callstacks[] = array('fn' => $match[1], 'args' => $args);
                return preg_replace($regex, $match[1], $str, 1);
            }
        } else {
            return $str;
        }
    }
    private function setAdditionalEmail($additional_email) {
        if (!$additional_email) return false;
        if (VERSION >= '2.3.0') {
            $this->config->set('config_mail_alert', array_merge(array('order'), (array)$this->config->get('config_mail_alert')));
            $_key = VERSION >= '3.0.0' ? 'config_mail_alert_email' : 'config_alert_email';
        } else {
            $this->config->set('config_order_mail', true);
            $_key = 'config_mail_alert';
        }
        $this->config->set($_key, $this->config->get($_key) . $additional_email);
    }
    public function getOrderStatus($xpayment, $integration, $response) {
        $order_status_id = (int)$xpayment['order_status_id'];
        if ($integration && !empty($integration['meta'])) {
            if (!empty($integration['meta']['_key'])
                && !empty($integration['meta']['_key']['_keyStatus'])
                && !empty($integration['meta']['_status'])
                && !empty($integration['meta']['_value'])) {
                $payment_status = $this->getIn($integration['meta']['_key']['_keyStatus'], $response);
                if (!empty($integration['meta']['_value']['_valuePending']) && !empty($integration['meta']['_status']['_statusPending']) && $integration['meta']['_value']['_valuePending'] == $payment_status) {
                    $order_status_id = $integration['meta']['_status']['_statusPending']; 
                } else if (!empty($integration['meta']['_value']['_valueCancel']) && !empty($integration['meta']['_status']['_statusCancel']) && $integration['meta']['_value']['_valueCancel'] == $payment_status) {
                    $order_status_id = $integration['meta']['_status']['_statusCancel']; 
                } else if (!empty($integration['meta']['_value']['_valueRefund']) && !empty($integration['meta']['_status']['_statusRefund']) && $integration['meta']['_value']['_valueRefund'] == $payment_status) {
                    $order_status_id = $integration['meta']['_status']['_statusRefund']; 
                }
            }
        }
        return $order_status_id;
    }
    public function getRequestUrl($order_info, $integration, $replacement) {
        $placeholder = $replacement['placeholder'];
        $replacer = $replacement['replacer'];
        $integration_data = $this->getIntegrationData($order_info, $replacement);
        $url = str_replace($placeholder, $replacer, $integration['url']);
        $separator = strpos($url, '?') === false ? '?' : '&';
        $query = '';
        foreach($integration_data as $key => $value) {
            $query .= ($query ? '&' : '') . $key . '=' . $value;    
        }
        return $url . $separator . $query;
    }
    public function invokeAPI($order_info, $integration, $replacement) {
        $xpayment_debug = $this->ocm->getConfig('xpayment_debug', $this->mtype);
        $placeholder = $replacement['placeholder'];
        $replacer = $replacement['replacer'];
        $integration_data = $this->getIntegrationData($order_info, $replacement);
        /* let fetch param value from get or post */
        if ($integration_data) {
            $ignore = array('route', 'language', 'currency', 'OCSESSID', '__atuvc');
            foreach($integration_data as $key => $value) {
                if (isset($this->request->request[$key]) && !in_array($key, $ignore)) {
                    $integration_data[$key] = $this->request->request[$key];
                }
            }
        }
        /* end of parse input data*/
        $integration['url'] = str_replace($placeholder, $replacer, $integration['url']);
        $header = $this->getHeaderData($integration['header'], $placeholder, $replacer);
        $header = $this->ocm->common->toCurlHeader($header);
        $api_response = array();
        if (strtolower($integration['method']) == 'get') {
            $api_response = $this->ocm->common->curlReq($integration['url'],'GET', $integration_data, $header);
        }
        else if (strtolower($integration['method']) == 'post') {
            $api_response = $this->ocm->common->curlReq($integration['url'],'POST', $integration_data, $header);
        }
        else if (strtolower($integration['method']) == 'json') {
            $integration_data = json_encode($integration_data);
            $headerType = true;
            $headerLength = true;    
            foreach($header as $each) {
                if (substr_count($each, 'Content-Type') > 0) {
                    $headerType = false;
                }
                if (substr_count($each, 'Content-Length') > 0) {
                    $headerLength = false;
                }
            }
            if ($headerType) {
                $header[] = 'Content-Type: application/json';
            }
            if ($headerLength) {
                $header[] = 'Content-Length: ' . strlen($integration_data);
            }
            $api_response = $this->ocm->common->curlReq($integration['url'],'POST', $integration_data, $header);
        }
        if ($api_response !== false && !is_array($api_response)) {
            $_api_response = json_decode($api_response, true);
            if (is_array($_api_response)) {
                $api_response = $_api_response;
            }
        }
        if (!is_array($api_response)) {
            $api_response = array();
        }
        if ($xpayment_debug) {
            $log = 'X-Payment API log (Disable debug mode of the x-payment to turn off)' . PHP_EOL;
            $log .= 'Request:' . PHP_EOL;
            $log .= 'URL ' . $integration['url'] . PHP_EOL;
            $log .= 'Method ' . $integration['method'] . PHP_EOL;
            $log .= 'Header ' . json_encode($header) . PHP_EOL;
            $log .= 'Body ' . (is_array($integration_data) ? json_encode($integration_data) : $integration_data) . PHP_EOL;
            $log .= 'Response:' . PHP_EOL;
            $log .= json_encode($api_response);
            $this->log->write($log);
        }
        return $api_response;
    }
    public function getAddon($integration) {
        if (!$integration || !$integration['name']) return false;
        $name = 'xpayment_' . trim($integration['name']);
        $path = $this->ocm->common->getExtPath('payment', 'xpayment');
        if (VERSION >= '4.0.0.0') {
            $file  = DIR_EXTENSION . 'xpayment/' . 'catalog/model/payment/' . $name . '.php';
        } else {
            $file  = DIR_APPLICATION . 'model/' . $path . $name . '.php';
        }
        if (file_exists($file)) {
            $this->load->model($path . $name);
            $key = 'model_' . str_replace('/', '_', $path) . $name;
            return $this->{$key};
        } else {
            return false;
        }
    }
    public function applyShortcode($text, $replacement) {
        $rex = '/\[(\w+)\s?([^]]*)\](.*?)\[\/\w+\]/m';
        if ($shortcodes = $this->ocm->parseShortcode($text)) {
            foreach ($shortcodes as $shortcode) {
                $render = '_' . $shortcode['name'] . 'Render';
                if (method_exists($this, $render) && $_return = $this->{$render}($shortcode, $replacement)) {
                    $output = $_return['apply'] ?  $_return['output'] : '';
                    $text = str_replace($shortcode['full'], $output, $text);
                }
            }
        }
        return $text;
    }
    //utitlty to access from addon
    public function getCurrencyNumber($currency_code) {
        $currency_numbers = array('AED'=>'784', 'AFN'=>'971', 'ALL'=>'8','AMD'=>'51','ANG'=>'532','AOA'=>'973','ARS'=>'32','AUD'=>'36','AWG'=>'533','AZN'=>'944','BAM'=>'977','BBD'=>'52','BDT'=>'50','BGN'=>'975','BHD'=>'48','BIF'=>'108','BMD'=>'60','BND'=>'96','BOB'=>'68','BOV'=>'984','BRL'=>'986','BSD'=>'44','BTN'=>'64','BWP'=>'72','BYR'=>'974','BZD'=>'84','CAD'=>'124','CDF'=>'976','CHE'=>'947','CHF'=>'756','CHW'=>'948','CLF'=>'990','CLP'=>'152','CNY'=>'156','COP'=>'170','COU'=>'970','CRC'=>'188','CUC'=>'931','CUP'=>'192','CVE'=>'132','CZK'=>'203','DJF'=>'262','DKK'=>'208','DOP'=>'214','DZD'=>'12','EGP'=>'818','ERN'=>'232','ETB'=>'230','EUR'=>'978','FJD'=>'242','FKP'=>'238','GBP'=>'826','GEL'=>'981','GHS'=>'936','GIP'=>'292','GMD'=>'270','GNF'=>'324','GTQ'=>'320','GYD'=>'328','HKD'=>'344','HNL'=>'340','HRK'=>'191','HTG'=>'332','HUF'=>'348','IDR'=>'360','ILS'=>'376','INR'=>'356','IQD'=>'368','IRR'=>'364','ISK'=>'352','JMD'=>'388','JOD'=>'400','JPY'=>'392','KES'=>'404','KGS'=>'417','KHR'=>'116','KMF'=>'174','KPW'=>'408','KRW'=>'410','KWD'=>'414','KYD'=>'136','KZT'=>'398','LAK'=>'418','LBP'=>'422','LKR'=>'144','LRD'=>'430','LSL'=>'426','LTL'=>'440','LVL'=>'428','LYD'=>'434','MAD'=>'504','MDL'=>'498','MGA'=>'969','MKD'=>'807','MMK'=>'104','MNT'=>'496','MOP'=>'446','MRO'=>'478','MUR'=>'480','MVR'=>'462','MWK'=>'454','MXN'=>'484','MXV'=>'979','MYR'=>'458','MZN'=>'943','NAD'=>'516','NGN'=>'566','NIO'=>'558','NOK'=>'578','NPR'=>'524','NZD'=>'554','OMR'=>'512','PAB'=>'590','PEN'=>'604','PGK'=>'598','PHP'=>'608','PKR'=>'586','PLN'=>'985','PYG'=>'600','QAR'=>'634','RON'=>'946','RSD'=>'941','RUB'=>'643','RWF'=>'646','SAR'=>'682','SBD'=>'90','SCR'=>'690','SDG'=>'938','SEK'=>'752','SGD'=>'702','SHP'=>'654','SLL'=>'694','SOS'=>'706','SRD'=>'968','SSP'=>'728','STD'=>'678','SYP'=>'760','SZL'=>'748','THB'=>'764','TJS'=>'972','TMT'=>'934','TND'=>'788','TOP'=>'776','TRY'=>'949','TTD'=>'780','TWD'=>'901','TZS'=>'834','UAH'=>'980','UGX'=>'800','USD'=>'840','USN'=>'997','USS'=>'998','UYI'=>'940','UYU'=>'858','UZS'=>'860','VEF'=>'937','VND'=>'704','VUV'=>'548','WST'=>'882','XAF'=>'950','XAG'=>'961','XAU'=>'959','XBA'=>'955','XBB'=>'956','XBC'=>'957','XBD'=>'958','XCD'=>'951','XDR'=>'960','XFU'=>'Nil','XOF'=>'952','XPD'=>'964','XPF'=>'953','XPT'=>'962','XTS'=>'963','XXX'=>'999','YER'=>'886','ZAR'=>'710','ZMW'=>'967');
        return isset($currency_numbers[$currency_code]) ? $currency_numbers[$currency_code] : '840';
    }
    public function getParam($params, $placeholder, $replacer) {
        return $this->ocm->interpolate($params, $placeholder, $replacer);
    }
    public function getDivider() {
        return $this->ocm->divider;
    }
    public function getAddonRoute($addon_name) {
        return rtrim($this->ext_path, 'xpayment') . $addon_name;
    }
    public function getAddonLibrary() {
        if (VERSION >= '4.0.0.0') {
            return DIR_EXTENSION . 'xpayment/system/library/';
        }
        return DIR_SYSTEM . 'library/';
    }
    public function getIn($keys, $value) {
        if (!$keys || !$value) {
            return '';
        }
        $keys = explode('.', $keys);
        $max = count($keys) - 1;
        for ($i = 0; $i <= $max; $i++) {
            $key = $keys[$i];
            $value = isset($value[$key]) ? $value[$key] : ($i == $max ? '' : array());
        }
        return $value;
    }
    public function curlReq($url, $method = 'GET', $data = false, $header = array(), $param = array()) {
        return $this->ocm->common->curlReq($url, $method, $data, $header, $param);
    }
}
/* NOT using PHP 7 anonymous class deliberately to support all OC */
class Xpayment {
    private $method;
    public function __construct($method) {
       $this->method = $method;
    }
    public function getMethod($address, $total) {
       return $this->method;
    }
}
// utility function that can be used in placeholders like other php functions
function getIfNotSet($value, $default = '') {
    return !empty($value) ? $value : $default;
}