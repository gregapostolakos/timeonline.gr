<?php
class ModelExtensionTotalXfeepro extends Model {
    use OCM\Traits\Front\Address;
    use OCM\Traits\Front\Common_params;
    use OCM\Traits\Front\Product;
    use OCM\Traits\Front\Validator;
    use OCM\Traits\Front\Range_price;
    use OCM\Traits\Front\Equation;
    use OCM\Traits\Front\Grouping;
    use OCM\Traits\Front\Distance;
    use OCM\Traits\Front\Crucify;
    use OCM\Traits\Front\Util;
    use OCM\Traits\Front\Event_hide;
    private $ocm;
    private $ext_path;
    private $mtype;
    private $mname;
    public function __construct($registry) {
        parent::__construct($registry);
        $this->mtype = 'total';
        $this->mname = 'xfeepro';
        $this->ext_path = 'extension/total/xfeepro';
        $this->ocm = $this->registry->has('ocm_front') ? $this->registry->get('ocm_front') : new OCM\Front($this->registry);
    }
    public function getTotal($total) {
        $_totals = $this->getXfeeTotal($total['totals'], $total['total'], $total['taxes']);
        $result = $this->applyTotals($total['totals'], $total['total'], $total['taxes'], $_totals);
        $total['totals'] = $result['totals'];
        $total['total'] = $result['total'];
        $total['taxes'] = $result['taxes'];
        if ($_totals)  {
            $this->ocm->setCache('xfeepro', $_totals); // expose for other module e.g. x-coupon
        }
    }
    private function applyTotals($totals, $total, $taxes, $_totals) {
        if ($_totals)  {
            $default_order = (int)$this->ocm->getConfig('sub_total_sort_order', 'total') + 1;
            foreach($_totals as $single) {
                /* Calculating tax/vat */
                if ($single['tax_class_id'] == -1) {
                    foreach ($single['taxes'] as $tax_rate_id => $tax_amount) {
                        if (!isset($taxes[$tax_rate_id])) {
                            $taxes[$tax_rate_id] = 0; // initialize 
                        }
                        if ($single['inc_vat']) {
                            $single['cost'] += $tax_amount;
                        } else {
                            $taxes[$tax_rate_id] += $tax_amount;
                        }
                    }
                }
                else if ($single['tax_class_id']) {
                    $tax_rates = $this->tax->getRates($single['cost'], $single['tax_class_id']);
                    foreach ($tax_rates as $tax_rate) {
                        if (!isset($taxes[$tax_rate['tax_rate_id']])) {
                            $taxes[$tax_rate['tax_rate_id']] = 0;
                        }
                        if ($single['inc_vat']) {
                            $single['cost'] += $tax_rate['amount'];
                        } else {
                            $taxes[$tax_rate['tax_rate_id']] += $tax_rate['amount'];
                        }
                    }
                }
                if (!$single['fake']) {
                    $total += $single['cost'];
                }
                /* End of tax*/
                if (!$single['hidden']) {
                    $totals[] = array( 
                        'extension'  => 'xfeepro',
                        'code'       => 'xfeepro', 
                        'xcode'      =>  $single['code'],
                        'title'      =>  $single['title'],
                        'value'      =>  $single['cost'],
                        'sort_order' => !$single['sort_order'] ? $default_order : (int)$single['sort_order']
                    );
                }
            }
        }
        return array(
            'totals' => $totals,
            'taxes'  => $taxes,
            'total'  => $total
        );
    }
    private function getXfeeTotal($totals, $total, $taxes) {
        $this->load->language($this->ext_path);
        $language_id = $this->config->get('config_language_id');
        $xfeepro_address = $this->ocm->getConfig('xfeepro_address', $this->mtype);
        $address = $this->_replenishAddress(array(), $xfeepro_address);
        $compare_with = $this->_getCommonParams($address);
        $method_data = array();
        $quote_data = array();
        $sort_data = array(); 
        $group = $this->ocm->getConfig('xfeepro_group', $this->mtype);
        $group_name = $this->ocm->getConfig('xfeepro_group_name', $this->mtype);
        $debug = $this->ocm->getConfig('xfeepro_debug', $this->mtype);
        $map_key = $this->ocm->getConfig('xfeepro_map_api', $this->mtype); 
        $store_geocode = $this->config->get('config_geocode');
        $group = $group ? $group : 'no_group';
        $currency_code = isset($this->session->data['currency']) ? $this->session->data['currency'] : $this->config->get('config_currency');
        $_vweight_cache = array();
        $debugging = array();
        $method_level_group = false;
        $hiddenMethods = array();
        $hiddenInactiveMethods = array();
        $xfees = $this->getFees();
        $xmethods = $xfees['xmethods'];
        $xmeta = $xfees['xmeta'];
        $cart_products = $this->cart->getProducts();
        $_cart_data =  $this->getProductProfile($cart_products, $xmeta);
        if (!$_cart_data['sub']) return array();
        foreach($totals as $single) {
            if (!isset($single['code'])) continue;
            if ($single['code'] == 'coupon') {
                $_cart_data['coupon'] = $single['value'];
            }
            if ($single['code'] == 'reward') {
                $_cart_data['reward'] = $single['value'];
            }
        }
        $_cart_data['grand'] = $total;
        $_cart_data['grand_wtax'] = $_cart_data['grand'];
        foreach ($taxes as $tax) {
            $_cart_data['grand_wtax'] -= $tax;
        }
        $_cart_data = $this->fixRounding($_cart_data);
        $_cart_data['lastXfeepro'] = 0;
        $geo_ids = array();
        if ($xmeta['geo']) {
            $geo_rows = $this->db->query("SELECT geo_zone_id FROM " . DB_PREFIX . "zone_to_geo_zone WHERE country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')")->rows; 
            foreach ($geo_rows as $geo_row) {
                $geo_ids[] = $geo_row['geo_zone_id'];
            }
        }
        if ($xmeta['first']) {
            $compare_with['first'] = false;
            if ($this->customer->isLogged()) {
                $order_row = $this->db->query("SELECT order_id FROM " . DB_PREFIX . "order WHERE customer_id = '" . (int)$this->customer->isLogged() . "' AND order_status_id != 0 LIMIT 1")->row;
                $compare_with['first'] = !$order_row;
            }
            //journal patch
            if (!empty($this->request->post['account']) && $this->request->post['account'] == 'register') {
                $compare_with['first'] = true;
            }
        }
        if ($xmeta['distance']) {
            $zone_row = $this->db->query("SELECT name FROM " . DB_PREFIX . "zone WHERE zone_id = '" . (int)$address['zone_id'] . "'")->row;
            $dest = (isset($address['address_1']) && $address['address_1']) ? $address['address_1'] : '';
            $dest .= $address['city'] ? ' '.$address['city'] : '';
            $dest .= $address['postcode'] ? ' '.$address['postcode'] : '';
            $dest .= ($zone_row && $zone_row['name']) ? ' '.$zone_row['name'] : '';
            $_cart_data['distance'] = $this->getDistance($store_geocode, $dest, $map_key, $debug);
            if ($zone_row && $zone_row['name']) {
                $address['zoneName'] = $zone_row['name'];
            }
        }
        $_cart_data['xlevel'] = $_cart_data['xdiscount'] = 0;
        if ($xmeta['xdiscount']) {
            $_cart_data['xlevel'] = $this->ocmprice && method_exists($this->ocmprice, 'getCartDiscountValue') ? $this->ocmprice->getCartDiscountValue('xlevel') : 0;
            $_cart_data['xdiscount'] = $this->ocmprice && method_exists($this->ocmprice, 'getCartDiscountValue') ? $this->ocmprice->getCartDiscountValue('xdiscount') : 0; 
        }
        $compare_with['products'] = $_cart_data['products'];
        $compare_with['geo'] = $geo_ids;
        $compare_with['product'] = $_cart_data['product'];
        $compare_with['category'] = $_cart_data['category'];
        $compare_with['manufacturer'] = $_cart_data['manufacturer'];
        $compare_with['option'] = $_cart_data['option'];
        $compare_with['attribute'] = $_cart_data['attribute'];
        $compare_with['location'] = $_cart_data['location'];
        $compare_with['total'] = $_cart_data['total'];
        $compare_with['weight'] = $_cart_data['weight'];
        $compare_with['quantity'] = $_cart_data['quantity'];
        $compare_with['logged'] = (boolean)$this->customer->isLogged();
        /* Add other totals */
        $_cart_data['sub_coupon'] = $_cart_data['sub'] + $_cart_data['coupon'] + $_cart_data['reward'];
        $_cart_data['total_coupon'] = $_cart_data['total'] + $_cart_data['coupon'] + $_cart_data['reward'];
        $acc_cf_flag = true;
        foreach($xmethods as $xfeepro) {
            $rules = $xfeepro['rules'];
            $rates = $xfeepro['rates'];
            $tab_id = $xfeepro['tab_id'];
            $product_or = $xfeepro['product_or'];
            $ingore_product_rule = $xfeepro['ingore_product_rule'];
            $debugging_message = array();
            /* adjust multiple values */
            $this->_adjustMultiValues($rules, $_cart_data['products']);
            if ($product_or) {
                $this->_adjustProductsOr($rules, $_cart_data['products']);
            }
            if (isset($rules['custom']) && $acc_cf_flag && $this->customer->isLogged()) {
                $compare_with['custom_field'] = $this->syncAccountFields($compare_with['custom_field']);
                $acc_cf_flag = false;
            }
            /* if shipping_base is found on shipping_methods list, consider that */
            if (isset($rules['shipping']) && in_array($compare_with['shipping_base'], $rules['shipping']['value'])) {
                $compare_with['shipping_method'] = $compare_with['shipping_base'];
            }
            /* if payment is found on shipping_methods list, then It had better to use baseone. */
            if (isset($rules['payment']) && in_array($compare_with['payment_base'], $rules['payment']['value'])) {
                $compare_with['payment_method'] = $compare_with['payment_base'];
            }
            $_cart_data['dimensional'] = 0;
            $_cart_data['volumetric'] = 0;
            $alive_or_dead = $this->_crucify($rules, $compare_with, $product_or, $ingore_product_rule);
            if (!$alive_or_dead['status']) {
                if ($xfeepro['need_inactive_hide_method']) {
                    $hiddenInactiveMethods[$tab_id] = array(
                        'hide' => $xfeepro['hide_inactive'],
                        'display' => $xfeepro['display']
                    );
                }
                $debugging_message = $alive_or_dead['debugging'];
                $debugging[] = array('name' => $xfeepro['display'],'filter' => $debugging_message,'index' => $tab_id);
            } else {
                $status = true;
                $applicable_cart = $this->_getApplicableProducts($rules, $_cart_data);
                if ($rates['type'] == 'dimensional' || $rates['type'] ==  'volumetric') {
                    $cache_key = (int)$rates['factor'].'_'.(int)$rates['overrule'];
                    if (isset($_vweight_cache[$cache_key]) && $_vweight_cache[$cache_key]) {
                        $vweight = $_vweight_cache[$cache_key];
                    } else {
                        $vweight = $this->_calVirtualWeight($_cart_data['products'], $rates['factor'], $rates['overrule']);
                        $_vweight_cache[$cache_key] = $vweight;
                    }
                    $_cart_data['dimensional'] = $vweight['dimensional'];
                    $_cart_data['volumetric'] = $vweight['volumetric'];
                    $_cart_data['product_dimensional'] = $vweight['product_dimensional'];
                    $_cart_data['product_volumetric'] = $vweight['product_volumetric'];
                }
                /* Calculate method wise data if needed*/
                $need_specified = ($xfeepro['have_product_specified'] && ($xfeepro['method_specific'] || ($rates['type'] == 'equation' && $rates['equation_specified_param'])));
                $method_specific_data = $this->_getMethodSpecificData($need_specified, $rules, $applicable_cart, $_cart_data, $product_or);
                $_percent_in_text = '';
                $cost = 0;
                $percent_of = $method_specific_data[$rates['percent_of']];
                $equation = $this->ocm->html_decode($rates['equation']);
                $iterate_all = preg_match('/{product\w+}/', $equation);
                if ($rates['type'] == 'flat') {
                    $cost = $rates['percent'] ? ($rates['value'] * $percent_of) : $rates['value'];
                    $_percent_in_text = $rates['percent'] ? (abs($rates['value']) * 100) . '%' : '';
                }
                else if ($rates['type'] == 'equation' && $iterate_all) {
                    $iteration_result = $this->iterateEquation($equation, 'range', $rates, $method_specific_data, $_cart_data, $quote_data, $percent_of);
                    if ($iteration_result === false) {
                        $debugging_message[] = 'Fee/DiscountBy - '.$rates['type'].' (iteration Over Products nothing matched)';
                        $status = false;
                    } else {
                        $cost = $iteration_result;
                    }
                }
                else {
                    if ($rates['type'] == 'equation') {
                        $equation_result = $this->getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of);
                        $method_specific_data['equation'] = $equation_result['value'];
                    }
                    $target_value = $method_specific_data[$rates['type']];
                    $target_value = $rates['cart_adjust'] ? $this->adjustValue($rates['cart_adjust'], $target_value) : $target_value;
                    $price_result = $this->getPrice($rates, $target_value, $percent_of);
                    if ($price_result['status']) {
                        $method_specific_data['no_block'] = $price_result['block'];
                        if (isset($xfeepro['block_req']) && $xfeepro['block_req'] && $price_result['block']) {
                            $this->setBlockInfo($method_specific_data, $price_result, $rates['type']);
                        }
                        $_percent_in_text = $price_result['in_percent'];
                    }
                    $_equation_check = $rates['type'] == 'equation' ? false : !!$rates['equation'] && empty($rates['ranges']);
                    if (!$price_result['status'] && !$_equation_check) {
                        $debugging_message[] = 'Fee/DiscountBy - '.$rates['type'].' ('.$target_value.')';
                        $status = false;
                    } else {
                        $cost = $rates['final'] == 'single' ? $price_result['cost'] : $price_result['cumulative'];
                    }
                }
                /* Price adjustment Start */
                $modifier_amount = 0;
                if ($rates['price_adjust']) {
                    if (isset($rates['price_adjust']['min'])) {
                        $min = $rates['price_adjust']['min'];
                        $min_amount = $min['percent'] ? ($min['value'] * $percent_of) : $min['value'];
                        $cost = abs($min_amount) > abs($cost) ? $min_amount : $cost;
                    }
                    if (isset($rates['price_adjust']['max'])) {
                        $max = $rates['price_adjust']['max'];
                        $max_amount = $max['percent'] ? ($max['value'] * $percent_of) : $max['value'];
                        $cost = abs($max_amount) < abs($cost) ? $max_amount : $cost;
                    }
                    if (isset($rates['price_adjust']['modifier'])) {
                        $modifier = $rates['price_adjust']['modifier'];
                        $modifier_amount = $modifier['percent'] ? ($modifier['value'] * $percent_of) : $modifier['value'];
                        $cost = $this->tiniestCalculator($cost, $modifier_amount, $modifier['operator']);
                    }
                }
                /* If method specified was not true but equation defined with method specific placeholders, let calculate method specifed values if it was not done earlier  */
                if ($rates['equation']
                    && $xfeepro['have_product_specified']
                    && $rates['equation_specified_param']
                    && !$need_specified) {
                    $method_specific_data = $this->_getMethodSpecificData(true, $rules, $applicable_cart, $_cart_data, $product_or);
                }
                if ($rates['equation'] && $rates['type'] != 'equation') {
                    if (preg_match('/{anyProduct\w+}/', $equation)) {
                        $iteration_type = strpos($equation, '@') === false ? 'single' : 'multiple';
                        $iteration_result = $this->iterateEquation($equation, $iteration_type, $rates, $method_specific_data, $_cart_data, $quote_data, $percent_of, $cost, $modifier_amount);
                        $cost = $iteration_result === false ? 0 : $iteration_result;
                    } else {
                        $equation_result = $this->getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of, $cost, $modifier_amount);
                        $cost = $equation_result['value'];
                        // Since price range return false  let's set cost to 0 so in order to method get faiied
                        //if (isset($price_result) && !$price_result['status'] && !$cost) {
                            //$cost = 0;
                        //}
                    }
                    if ($cost == 0) {
                        $status = false; 
                        $debugging_message[] = 'Final Equation  (Return '.$cost.')';
                    }
                }
                /*Ended rate cal*/
                if(!isset($xfeepro['display'])) $xfeepro['display'] = '';
                if (!$xfeepro['display']) {
                   $xfeepro['display'] = isset($xfeepro['name'][$language_id]) ? isset($xfeepro['name'][$language_id]) : '';
                }
                if (!isset($xfeepro['name'][$language_id]) || !$xfeepro['name'][$language_id]) {
                   $xfeepro['name'][$language_id] = 'Untitled Item';
                }
                if (!$status) {
                   $debugging[] = array('name' => $xfeepro['display'],'filter' => $debugging_message,'index' => $tab_id);
                }
                if (intval($xfeepro['group'])) {
                   $method_level_group = true;
                }
                /* cache for inactive hide */
                if (!$status) { 
                    if ($xfeepro['need_inactive_hide_method']) {
                        $hiddenInactiveMethods[$tab_id] = array(
                            'hide' => $xfeepro['hide_inactive'],
                            'display' => $xfeepro['display']
                        );
                    }
                }
               if ($status) {
                    if ($xfeepro['disable']) {
                        $quote_data = array();
                        break;
                    }
                    if ($xfeepro['disable_other']) {
                        $quote_data = array();
                    }
                    if ($xfeepro['need_hide_method']) {
                         $hiddenMethods[$tab_id] = array(
                            'hide' => $xfeepro['hide'],
                            'display' => $xfeepro['display']
                          );
                    }
                    // fix rounding if it is a discount since at some cases it shows disparity
                    /*
                    if ($cost < 0) {
                        $cost = $this->currency->format($cost, $currency_code, 1, false);
                    } */
                    if (!$xfeepro['fake']) {
                        $_cart_data['grand'] += $cost;
                        $_cart_data['grand_wtax'] += $cost;
                    }
                    if (!isset($xfeepro['hidden'])) $xfeepro['hidden'] = false; // legacy version - remove after few version
                    $_cart_data['lastXfeepro'] = $method_specific_data['sub']; // adjust already applied 
                    if (strpos($xfeepro['name'][$language_id], '%s') !== false) {
                        $xfeepro['name'][$language_id] = sprintf($xfeepro['name'][$language_id], $_percent_in_text);
                    }
                    // product-wise taxes
                    $_taxes = array();
                    if ($xfeepro['tax_class_id'] == -1 && $_percent_in_text) {
                        $_percent_in = (float)rtrim($_percent_in_text, '%') / 100;
                        if ($cost < 0) {
                            $_percent_in *= -1;    
                        }
                        foreach($method_specific_data['products'] as $product) {
                            if (!isset($product['tax_data'])) $product['tax_data'] = array();
                            foreach($product['tax_data'] as $tax_rate_id => $product_tax) {
                                if (!isset($_taxes[$tax_rate_id])) $_taxes[$tax_rate_id] = 0;
                                $_taxes[$tax_rate_id] += $product_tax * $_percent_in;  
                            }
                        }
                    }
                    $quote_data['xfeepro'.$tab_id] = array(
                        'code'         => 'xfeepro'.$tab_id,
                        'tab_id'       => $tab_id,
                        'xkey'         => 'xfeepro'.$tab_id,
                        'title'        => $xfeepro['name'][$language_id],
                        'display'      => $xfeepro['display'],
                        'cost'         => $cost,
                        'group'        => $xfeepro['group'],
                        'sort_order'   => $xfeepro['sort_order'],
                        'tax_class_id' => $xfeepro['tax_class_id'],
                        'taxes'        => $_taxes,
                        'inc_vat'      => $xfeepro['inc_vat'],
                        'fake'         => $xfeepro['fake'],
                        'hidden'       => $xfeepro['hidden']
                    );
                    if ($xfeepro['disable_other']) {
                        break;
                    }
                }
            } 
        }
        /* Hide methods from hide option*/
        $quote_data = $this->hideMethodsOnActive($quote_data, $hiddenMethods, $debugging);
        $quote_data = $this->hideMethodsOnInactive($quote_data, $hiddenInactiveMethods, $debugging);
        /* Finding  method level grouping  */
        if ($method_level_group) { 
            $grouping_methods = array();
            foreach($quote_data as $single) {
                $grouping_methods[$single['group']][] = $single;
            }
            $new_quote_data = array();
            foreach($grouping_methods as $group_id => $grouping_method) {
                if (count($grouping_method) == 1 || empty($group_id) || $group[$group_id] == 'no_group') {
                    $append_methods = array();
                    foreach($grouping_method as $single) {
                        $append_methods[$single['xkey']] = $single;
                    }
                    $new_quote_data = array_merge($new_quote_data, $append_methods);
                    continue;
                }
                $sub_group_type = $group[$group_id];
                $sub_group_limit = 1;
                $sub_group_name = isset($group_name[$group_id]) ? $this->ocm->html_decode($group_name[$group_id]) : '';
                if (isset($grouping_method)) {
                    $new_quote_data = array_merge($new_quote_data, $this->findGroup($grouping_method, $sub_group_type, $sub_group_limit, $sub_group_name));
                }
            }
            $quote_data = $new_quote_data;
        }
        if ($debug) {
           $this->ocm->writeLog($debugging, 'xfeepro');
        }
       /*Sorting final method*/
        $sort_order = array();
        foreach ($quote_data as $key => $value) {
            $sort_order[$key] = $value['sort_order'];
        }
        array_multisort($sort_order, SORT_ASC, $quote_data);
        return $quote_data;
    }
    private function iterateEquation($equation, $type, $rates, $method_specific_data, $_cart_data, $quote_data, $percent_of, $fee_cost = 0, $modifier_amount = 0) {
        $all = array('{productWidth}', '{productHeight}', '{productLength}', '{productWeight}', '{productQuantity}', '{productPrice}', '{productSpecialPrice}', '{productVolume}');
        $any = array('{anyProductWidth}', '{anyProductHeight}', '{anyProductLength}', '{anyProductWeight}', '{anyProductQuantity}', '{anyProductPrice}', '{anyProductSpecialPrice}', '{productVolume}');
        $multiply_not_req = trim($equation) == '{productQuantity}' || $equation == '{productPrice}' || $equation == '{productSpecialPrice}';
        $_equation_status = false;
        $_equation_cost = 0;
        $_placeholders = $type == 'range' ? $all : $any;
        $_equation = $equation;
        foreach ($method_specific_data['products'] as $product) {
            if ($rates['percent_of'] == 'price_self') {
                $percent_of = $product['price_self'];
            } else if ($rates['percent_of'] == 'price_self_tax') {
                $percent_of = $product['price_self_tax'];
            }
            $_replacers = array($product['width_self'], $product['height_self'], $product['length_self'], $product['weight_self'], $product['quantity'], $product['price_self'], $product['special_self'], $product['volume_self']);
            $equation = str_replace($_placeholders, $_replacers, $_equation);
            $equation_result = $this->getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of, $_equation_cost, $modifier_amount);
            $target_value = $equation_result['value'];
            if ($type == 'range') {
                $target_value = $rates['cart_adjust'] ? $this->adjustValue($rates['cart_adjust'], $target_value) : $target_value;
                $price_result = $this->getPrice($rates, $target_value, $percent_of);
                if ($price_result['status']) {
                    $_equation_status = true;
                    $multiple_by = $multiply_not_req ? 1 : $product['quantity'];
                    $_equation_cost += ($rates['final'] == 'single' ? $price_result['cost'] : $price_result['cumulative']) * $multiple_by;
                    $method_specific_data['no_block'] = $price_result['block'];
                }
            } else if ($target_value !== 0) {
                $_equation_status = true;
                //$method_specific_data['shipping'] = $_equation_cost; // update shipping for placeholder
                if ($type == 'single') {
                    $_equation_cost = $target_value;
                    if ($equation_result['status']) {
                        break;
                    }
                } else {
                    $_equation_cost += $target_value;
                }
            }
        }
        return $_equation_status ? $_equation_cost : false;
    }
    private function getFees() {
        $xfeepro = $this->cache->get('ocm.xfeepro');
        $disable_cache = $this->ocm->getConfig('xfeepro_disable_cache', $this->mtype);
        if (!$xfeepro || $disable_cache) {
            $language_id = $this->config->get('config_language_id');
            $xmethods = array();
            $xmeta = array(
                'geo'             => false,
                'category_query'  => false,
                'product_query'   => false,
                'attribute_query' => false,
                'distance'        => false,
                'first'           => false,
                'xdiscount'       => false
            );
            $rows = $this->db->query("SELECT * FROM `" . DB_PREFIX . "xfeepro` order by `sort_order` asc")->rows;
            foreach($rows as $row) {
                $method_data = $row['method_data'];
                $method_data = json_decode($method_data, true);
                /* cache only valid shipping */
                if ($method_data && is_array($method_data) && $method_data['status']) {
                    $method_data =  $this->_resetEmptyRule($method_data);
                    $rules = $this->_findValidRules($method_data);
                    $rates = $this->_findRawRate($method_data);
                    $have_product_specified = false;
                    if ($method_data['category'] > 1
                        || $method_data['product'] > 1
                        || $method_data['manufacturer_rule'] > 1
                        || $method_data['option'] > 1
                        || $method_data['attribute'] > 1
                        || $method_data['location_rule'] > 1) {
                            $have_product_specified = true;
                    }
                    $block_req = (stripos($method_data['equation'], '{blockPrice') !== false
                        || stripos($method_data['equation'], 'ByNoOfBlock}') !== false);
                    $xmethods[] = array(
                       'tab_id' => (int)$row['tab_id'],
                       'name' => $method_data['name'],
                       'display' => $method_data['display'],
                       'rules' => $rules,
                       'rates' => $rates,
                       'group' => (int)$method_data['group'],
                       'inc_vat' => !!$method_data['inc_vat'],
                       'fake' => !!$method_data['fake'],
                       'hidden' => !!$method_data['hidden'],
                       'first' => !!$method_data['first'],
                       'tax_class_id' => (int)$method_data['tax_class_id'],
                       'sort_order' => (int)$method_data['sort_order'],
                       'ingore_product_rule' => !!$method_data['ingore_product_rule'],
                       'product_or' => !!$method_data['product_or'],
                       'method_specific' => !!$method_data['method_specific'],
                       'disable' => !!$method_data['disable'],
                       'disable_other' => !!$method_data['disable_other'],
                       'hide' => $method_data['hide'],
                       'hide_inactive' => $method_data['hide_inactive'],
                       'need_hide_method' => !!count($method_data['hide']),
                       'need_inactive_hide_method' => !!count($method_data['hide_inactive']),
                       'have_product_specified' => $have_product_specified,
                       'block_req' => $block_req
                    );
                    if (!!$method_data['first']) {
                        $xmeta['first'] = true;
                    }
                    if ($method_data['geo_zone_all'] != 1) {
                        $xmeta['geo'] = true;
                    }
                    if ($method_data['attribute'] > 1) {
                        $xmeta['attribute_query'] = true;
                    }
                    if ($method_data['category'] > 1
                        || $method_data['rate_type'] == 'no_category'
                        || strpos($method_data['equation'], 'noOfCategory') !== false) {
                            $xmeta['category_query'] = true;
                    }
                    if ($method_data['manufacturer_rule'] > 1
                        || $method_data['location_rule'] > 1
                        || $method_data['rate_type'] == 'no_manufacturer'
                        || $method_data['rate_type'] == 'no_location'
                        || $method_data['rate_percent'] == 'original'
                        || strpos($method_data['equation'], 'noOfManufacturer') !== false
                        // || strpos($method_data['equation'], 'jan') !== false
                        // || strpos($method_data['equation'], 'ean') !== false
                        || strpos($method_data['equation'], 'actualSubTotal') !== false
                        || strpos($method_data['equation'], 'noOfLocation') !== false) {
                            $xmeta['product_query'] = true;
                    }
                    if ($method_data['rate_type'] == 'distance'
                        || strpos($method_data['equation'], 'distance') !== false) {
                            $xmeta['distance'] = true;
                    }
                    if (stripos($method_data['equation'], '{blockPrice') !== false
                        || stripos($method_data['equation'], 'ByNoOfBlock}') !== false) {
                        $xmeta['block'] = true;
                    }
                    if (stripos($method_data['equation'], '{xlevel}') !== false
                        || stripos($method_data['equation'], '{xdiscount}') !== false) {
                        $xmeta['xdiscount'] = true;
                    }
                }
            }
            $xfeepro = array('xmeta' => $xmeta, 'xmethods' => $xmethods);
            $this->cache->set('ocm.xfeepro', $xfeepro);
        }
        return $xfeepro;
    }
    private function _resetEmptyRule($data) {
        $rules = array(
            'store' => 'store_all',
            'geo_zone' => 'geo_zone_all',
            'city' => 'city_all',
            'country' => 'country_all',
            'zone' => 'zone_all',
            'customer_group' => 'customer_group_all',
            'currency' => 'currency_all',
            'payment' => 'payment_all',
            'shipping'  => 'shipping_all',
            'postal' => 'postal_all',
            'coupon' => 'coupon_all',
            'days' => 'days_all',
            'product_category' => 'category',
            'product_product' => 'product',
            'product_option' => 'option',
            'product_attribute' => 'attribute',
            'manufacturer' => 'manufacturer_rule',
            'location' => 'location_rule',
            'customers' => 'customer_all',
            'custom' => 'custom_all'
        );
        foreach ($rules as $key => $value) {
            if (!isset($data[$value])) {
                $data[$value] = '';
            }
            if (!isset($data[$key]) || !$data[$key]) {
                $data[$value] = 1;
            }
            /* make empty product entry if all is selected */
            if ($data[$value] < 2 && in_array($key, array('product_category', 'product_product', 'product_option', 'product_attribute', 'manufacturer', 'location'))) {
                $data[$key] = array();
            }
        }
        /* reset delimitter to comma */
        $fields = array(
            'city',
            'coupon',
            'postal'
        );
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field]) {
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
        /* reset cost params  */ 
        if (empty($data['additional_per'])) $data['additional_per'] = 1;
        if (empty($data['additional_limit'])) $data['additional_limit'] = PHP_INT_MAX;
        if (empty($data['dimensional_factor'])) $data['dimensional_factor'] = 5000;
        if (empty($data['dimensional_overfule'])) $data['dimensional_overfule'] = '';
        /* checkboxes */ 
        if (empty($data['ingore_product_rule'])) $data['ingore_product_rule'] = '';
        if (empty($data['product_or'])) $data['product_or'] = '';
        if (empty($data['method_specific'])) $data['method_specific'] = '';
        if (empty($data['dimensional_overfule'])) $data['dimensional_overfule'] = '';
        if (empty($data['inc_vat'])) $data['inc_vat'] = '';
        if (empty($data['fake'])) $data['fake'] = '';
        if (empty($data['hidden'])) $data['hidden'] = '';
        if (empty($data['first'])) $data['first'] = '';
        if (empty($data['logged'])) $data['logged'] = '';
        if (empty($data['disable'])) $data['disable'] = '';
        if (empty($data['disable_other'])) $data['disable_other'] = '';
        if (empty($data['display'])) $data['display'] = 'Untitled Item';
        /* Reset other */
        if (empty($data['ranges'])) $data['ranges'] = array();
        if (empty($data['hook'])) $data['hook'] = array();
        if (empty($data['days'])) $data['days'] = array();
        if (empty($data['name']) || !is_array($data['name'])) $data['name']=array();
        if (empty($data['hide']) || !is_array($data['hide'])) $data['hide']=array();
        if (empty($data['hide_inactive']) || !is_array($data['hide_inactive'])) $data['hide_inactive']=array();
        //free version to pro cause warning issue so reset
        $proversion = array('date_start', 'time_start', 'max_length', 'max_width', 'max_height', 'weight_start', 'quantity_start', 'equation', 'rate_final', 'cart_adjust', 'rate_min', 'rate_max', 'rate_add', 'additional', 'group', 'logo', 'order_total_start');
        foreach ($proversion as $field) {
            if (!isset($data[$field])) {
                $data[$field] = '';
            }
        }
        if (!isset($data['rate_percent'])) {
            $data['rate_percent'] = 'sub';
        }
        if (!empty($data['order_total_start']) && empty($data['order_total_end'])) $data['order_total_end'] = PHP_INT_MAX;
        if (!empty($data['weight_start']) && empty($data['weight_end'])) $data['weight_end'] = PHP_INT_MAX;
        if (!empty($data['quantity_start']) && empty($data['quantity_end'])) $data['quantity_end'] = PHP_INT_MAX;
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
        if ($data['zone_all'] != 1) {
            $rules['zone'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => true,
                'value' => $data['zone'],
                'compare_with' => 'zone_id',
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
        if ($data['payment_all'] != 1) {
            $rules['payment'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['payment'],
                'compare_with' => 'payment_method',
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
            $false_value = ($data['coupon_rule'] == 'inclusive') ? false : true;
            $coupons = explode(',',trim($data['coupon']));
            $coupons = array_map('trim', $coupons);
            $coupons = array_map('strtolower', $coupons);
            $rules['coupon'] = array(
                'type' => 'in_array',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $coupons,
                'compare_with' => 'coupon_code',
                'false_value' => $false_value
            );
        }
        if ($data['custom_all'] != 1) {
            $rules['custom'] = array(
                'type' => 'intersect',
                'product_rule' => false,
                'address_rule' => false,
                'value' => $data['custom'],
                'compare_with' => 'custom_field',
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
        if ((int)$data['location_rule'] > 1) {
            $location = array_map('strtolower', $data['location']);
            $location = array_map('trim', $location);
            $rules['location'] = array(
                'type' => 'function',
                'func' => '_validateProduct',
                'product_rule' => true,
                'address_rule' => false,
                'value' => $location,
                'compare_with' => 'location',
                'rule_type' => $data['location_rule'],
                'false_value' => false
            );
        }
        if ($data['days_all'] != 1 && is_array($data['days']) && $data['days'] && count($data['days']) !== 7) {
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
        /* Special rule if only ending time and date range set */
        if ($data['date_start'] != "" && $data['date_end'] && !$data['time_start'] && $data['time_end']) {
            $valid_hours = array();
            $time_start = 0;
            $time_end = (int)$data['time_end'];
            for ($i = $time_start; $i < $time_end ; $i++) { 
                  $valid_hours[] = $i;
            }
            $rules['date_time'] = array(
                'type' => 'in_array_not_equal',
                'product_rule' => false,
                'value' => $valid_hours,
                'compare_with' => 'time',
                'not_equal_value' => $data['date_end'],
                'not_equal_with' => 'date',
                'false_value' => false
            );
        }
        if ($data['rate_type'] != 'sub'
            && $data['rate_type'] != 'total'
            && $data['rate_type'] != 'sub_coupon'
            && $data['rate_type'] != 'total_coupon'
            && $data['rate_type'] != 'grand_wtax'
            && $data['rate_type'] != 'grand'
            && $data['order_total_start'] != "" 
            && (float)$data['order_total_end']) {
                $rules['additional_total'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (float)$data['order_total_start'],
                    'end' => (float)$data['order_total_end'],
                    'compare_with' => 'total'
                );
        }
        if ($data['rate_type'] != 'weight'
            && $data['weight_start'] != ""
            && (float)$data['weight_end']) {
                $rules['additional_weight'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (float)$data['weight_start'],
                    'end' => (float)$data['weight_end'],
                    'compare_with' => 'weight'
                );
        }
        if ($data['rate_type'] != 'quantity'
            && $data['quantity_start'] != ""
            && (int)$data['quantity_end']) {
                $rules['additional_qunatity'] = array(
                    'type' => 'in_between',
                    'product_rule' => false,
                    'address_rule' => false,
                    'start' => (int)$data['quantity_start'],
                    'end' => (int)$data['quantity_end'],
                    'compare_with' => 'quantity'
                );
        }
        if ($data['first']) {
            $rules['first'] = array(
                'type' => 'equal',
                'product_rule' => false,
                'address_rule' => false,
                'value' => '',
                'compare_with' => 'first',
                'false_value' => false
            );
        }
        if ($data['logged']) {
            $rules['logged'] = array(
                'type' => 'equal',
                'product_rule' => false,
                'address_rule' => false,
                'value' => '',
                'compare_with' => 'logged',
                'false_value' => false
            );
        }
        /* Hooking fields */
        if ($data['hook']) {
            foreach ($data['hook'] as $key => $value) {
                $rules[$key] = array(
                    'type' => 'function',
                    'func' => 'hook_' . $key,
                    'product_rule' => false,
                    'address_rule' => false,
                    'value' => $value,
                    'false_value' => false,
                    'rule_type' => $key,
                    'compare_with' => 'products'
                );
            }
        }
        return $rules;
    }
    private function _findRawRate($data) {
        $operators= array('+','-','/','*');
        $rates = array();
        $rates['type'] = $data['rate_type'];
        $rates['equation'] = $data['equation'];
        $rates['equation_specified_param'] = (strpos($data['equation'], 'PerProductRule') !== false);
        $rates['final'] = $data['rate_final'];
        $rates['percent_of'] = $data['rate_percent'];
        $rates['overrule'] = !!$data['dimensional_overfule'];
        $rates['factor'] = $data['dimensional_factor'];
        $rates['additional'] = array();
        $rates['cart_adjust'] = array();
        $rates['price_adjust'] = array();
        /* Shipping Cost */
        if ($data['rate_type'] == 'flat') {
            $cost = trim($data['cost']);
            if (substr($cost, -1) == '%') {
                $cost = rtrim($cost,'%');
                $rates['percent'] = true;
                $rates['value'] = (float)$cost / 100;
            } else {
                $rates['percent'] = false;
                $rates['value'] = (float)$cost;
            }
        } else {
           $ranges = array();
           foreach($data['ranges'] as $range) {
               $start = (float)$range['start'];
               $end = (float)$range['end'];
               $cost = trim($range['cost']);
               $block = (float)$range['block'];
               $partial = (int)$range['partial'];
               if (substr($cost, -1) == '%') {
                    $cost = rtrim($cost,'%');
                    $percent = true;
                    $value = (float)$cost / 100;
                } else {
                    $percent = false;
                    $value = (float)$cost;
                }
                $ranges[] = array('start' => round($start, 8), 'end' => round($end, 8), 'percent' => $percent, 'value' => $value, 'block' => $block, 'partial' => $partial);
            }
            $rates['ranges'] = $ranges;
        }
       /* Other price parameters */
       if ($data['cart_adjust']) {
            $operator = substr(trim($data['cart_adjust']),0,1);
            $operator = in_array($operator,$operators) ? $operator : '+';
            $adjust = ltrim($data['cart_adjust'], '+-*/');
            if (substr($adjust, -1) == '%') {
                $adjust = rtrim($adjust,'%');
                $rates['cart_adjust']['percent'] = true;
                $rates['cart_adjust']['value'] = (float)$adjust / 100;
                $rates['cart_adjust']['operator'] = $operator;
            } else {
                $rates['cart_adjust']['percent'] = false;
                $rates['cart_adjust']['value'] = (float)$adjust;
                $rates['cart_adjust']['operator'] = $operator;
            }
        }
        if ($data['rate_min'] && $data['rate_type'] != 'flat') {
             $rate_min = $data['rate_min'];
             $rates['price_adjust']['min'] = array();
             if (substr($rate_min, -1) == '%') {
                $rate_min = rtrim($rate_min,'%');
                $rates['price_adjust']['min']['percent'] = true;
                $rates['price_adjust']['min']['value'] = (float)$rate_min / 100;
             } else {
                $rates['price_adjust']['min']['percent'] = false;
                $rates['price_adjust']['min']['value'] = (float)$rate_min;
             }
        }
        if ($data['rate_max'] && $data['rate_type'] != 'flat') {
             $rate_max = $data['rate_max'];
             $rates['price_adjust']['max'] = array();
             if (substr($rate_max, -1) == '%') {
                $rate_max = rtrim($rate_max,'%');
                $rates['price_adjust']['max']['percent'] = true;
                $rates['price_adjust']['max']['value'] = (float)$rate_max / 100;
             } else {
                $rates['price_adjust']['max']['percent'] = false;
                $rates['price_adjust']['max']['value'] = (float)$rate_max;
             }
        }
        if ($data['rate_add'] && $data['rate_type'] != 'flat') {
            $modifier = $data['rate_add'];
            $rates['price_adjust']['modifier'] = array();
            $operator = substr(trim($modifier),0,1);
            $operator = in_array($operator,$operators) ? $operator : '+';
            $modifier = ltrim($modifier, '+-*/');
            if (substr($modifier, -1) == '%') {
                $modifier = rtrim($modifier,'%');
                $rates['price_adjust']['modifier']['percent'] = true;
                $rates['price_adjust']['modifier']['value'] = (float)$modifier / 100;
                $rates['price_adjust']['modifier']['operator'] = $operator;
            } else {
                $rates['price_adjust']['modifier']['percent'] = false;
                $rates['price_adjust']['modifier']['value'] = (float)$modifier;
                $rates['price_adjust']['modifier']['operator'] = $operator;
            }
        }
        if ($data['additional']) {
             $additional = $data['additional'];
             if (substr($additional, -1) == '%') {
                $additional = rtrim($additional,'%');
                $rates['additional']['percent'] = true;
                $rates['additional']['value'] = (float)$additional / 100;
             } else {
                $rates['additional']['percent'] = false;
                $rates['additional']['value'] = (float)$additional;
             }
             $rates['additional']['block'] = (float)$data['additional_per'];
             $rates['additional']['max'] = (float)$data['additional_limit'];
        }
        return $rates;
    }
    private function adjustValue($adjust_rate, $value) {
        $amount = $adjust_rate['percent'] ? ($adjust_rate['value'] * $value) : $adjust_rate['value'];
        return $this->tiniestCalculator($value, $amount, $adjust_rate['operator']);
    }
    private function getEquationValue($equation, $_cart_data, $method_specific_data, $quote_data, $percent_of, $fee_cost = 0, $modifier_amount = 0) {
        $placholder = array(
            '{subTotal}',
            '{subTotalWithTax}',
            '{special}',
            '{quantity}',
            '{weight}',
            '{volume}',
            '{dimensional}',
            '{volumetric}',
            '{noOfProduct}', 
            '{noOfCategory}', 
            '{noOfCategoryAsPerProductRule}', 
            '{noOfCategorySpecified}',
            '{noOfManufacturer}', 
            '{noOfLocation}',
            '{noOfProductAsPerProductRule}', 
            '{noOfFreeProduct}', 
            '{noOfBlock}',
            '{blockPriceAsc}',
            '{blockPriceDesc}',
            '{priceAscByNoOfBlock}',
            '{priceDescByNoOfBlock}',
            '{subTotalAsPerProductRule}',
            '{subTotalWithTaxAsPerProductRule}',
            '{quantityAsPerProductRule}',
            '{weightAsPerProductRule}',
            '{volumeAsPerProductRule}',
            '{couponValue}',
            '{rewardValue}',
            '{vouchers}',
            '{shippingCost}',
            '{xfeepro}',
            '{modifier}',
            '{grandTotal}',
            '{grandWithoutTax}',
            '{roundedGrandTotal}',
            '{distance}',
            '{highest}',
            '{lowest}',
            '{highestQnty}',
            '{lowestQnty}',
            '{average}',
            '{nonMethodSub}',
            '{nonMethodQnty}',
            '{alreadyAppliedXfeepro}',
            '{actualSubTotal}',
            '{xlevel}',
            '{xdiscount}',
            '@'
        );
        $replacer = array(
            $_cart_data['sub'],
            $_cart_data['total'],
            $method_specific_data['special'],
            $_cart_data['quantity'],
            $_cart_data['weight'],
            $_cart_data['volume'],
            $_cart_data['dimensional'],
            $_cart_data['volumetric'],
            $_cart_data['no_product'],
            $_cart_data['no_category'],
            $method_specific_data['no_category'],
            $method_specific_data['no_category_specified'],
            $_cart_data['no_manufacturer'],
            $_cart_data['no_location'],
            $method_specific_data['no_product'],
            $method_specific_data['no_of_free'],
            $method_specific_data['no_block'],
            $method_specific_data['block_asc'],
            $method_specific_data['block_desc'],
            $method_specific_data['no_block_asc'],
            $method_specific_data['no_block_desc'],
            $method_specific_data['sub'],
            $method_specific_data['total'],
            $method_specific_data['quantity'],
            $method_specific_data['weight'],
            $method_specific_data['volume'],
            $_cart_data['coupon'],
            $_cart_data['reward'],
            $_cart_data['vouchers'],
            $_cart_data['shipping'],
            $fee_cost,
            $modifier_amount,
            $_cart_data['grand'],
            $_cart_data['grand_wtax'],
            round($_cart_data['grand']),
            $_cart_data['distance'],
            $method_specific_data['highest'],
            $method_specific_data['lowest'],
            $method_specific_data['highest_qnty'],
            $method_specific_data['lowest_qnty'],
            $method_specific_data['average'],
            $method_specific_data['non_method_sub'],
            $method_specific_data['non_method_quantity'],
            $_cart_data['lastXfeepro'],
            $method_specific_data['original'],
            $_cart_data['xlevel'],
            $_cart_data['xdiscount'],
            ''
        );
        if (preg_match('/minHeight|maxHeight|sumHeight|minWidth|maxWidth|sumWidth|minLength|maxLength|sumLength/', $equation)) {
            $placholder[] = '{minHeight}';
            $placholder[] = '{maxHeight}';
            $placholder[] = '{sumHeight}';
            $placholder[] = '{minWidth}';
            $placholder[] = '{maxWidth}';
            $placholder[] = '{sumWidth}';
            $placholder[] = '{minLength}';
            $placholder[] = '{maxLength}';
            $placholder[] = '{sumLength}';
            $minHeight = $minWidth = $minLength = PHP_INT_MAX;
            $maxHeight = $maxWidth = $maxLength = PHP_INT_MIN;
            $sumHeight = $sumWidth = $sumLength = 0;
            foreach ($method_specific_data['products'] as $product) {
                $sumHeight += ($product['height_self'] * $product['quantity']);
                if ($minHeight > $product['height_self']) {
                    $minHeight = $product['height_self'];
                }
                if ($maxHeight < $product['height_self']) {
                    $maxHeight = $product['height_self'];
                }
                $sumWidth += ($product['width_self'] * $product['quantity']);
                if ($minWidth > $product['width_self']) {
                    $minWidth = $product['width_self'];
                }
                if ($maxWidth < $product['width_self']) {
                    $maxWidth = $product['width_self'];
                }
                $sumLength += ($product['length_self'] * $product['quantity']);
                if ($minLength > $product['length_self']) {
                    $minLength = $product['length_self'];
                }
                if ($maxLength < $product['length_self']) {
                    $maxLength = $product['length_self'];
                }
            }
            $replacer[] = $minHeight;
            $replacer[] = $maxHeight;
            $replacer[] = $sumHeight;
            $replacer[] = $minWidth;
            $replacer[] = $maxWidth;
            $replacer[] = $sumWidth;
            $replacer[] = $minLength;
            $replacer[] = $maxLength;
            $replacer[] = $sumLength;
        }
        /* append other  method cost as placeholders */
        foreach ($quote_data as $value) {
            $placholder[] = '{xfeepro'.$value['tab_id'].'}';
            $replacer[] = $value['cost'];
        }
        $equation = str_replace($placholder, $replacer, $equation);
        /* replace percentage value finally so it won't replace mod operator */
        $equation = preg_replace('/(\d+)%/', '$1*' . ($percent_of/100), $equation);
        /* Removing unwanted placeholder */
        if (strpos($equation, '{') !== false) {
            $equation = preg_replace('/{.*?}/', 0, $equation);
        }
        $condition_status = false;
        $value = (float)$this->calculate_string($equation, $condition_status);
        return array(
            'value'    => $value,
            'status'  => $condition_status 
        );
    }
    private function syncAccountFields($custom_field) {
        $this->load->model('account/customer');
        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $acc_custom_field = VERSION >= '2.1.0.0' ? json_decode($customer_info['custom_field'], true) : unserialize($customer_info['custom_field']);
        if (is_array($acc_custom_field)) {
            foreach ($acc_custom_field as $_custom_field) {
                if (is_array($_custom_field)) {
                    $custom_field = array_merge($custom_field, $_custom_field);
                } else {
                    $custom_field[] = $_custom_field;
                }
            }
        }
        return $custom_field;
    }
    private function setBlockInfo(&$method_specific_data, $price_result, $type) {
        $sort_order = array();
        $products = $method_specific_data['products'];
        foreach ($products as $key => $product) {
            $sort_order[$key] = $product['price'];
        }
        array_multisort($sort_order, SORT_ASC, $products);
        $block_asc = 0;
        $block_value = 0;
        $no_block_asc = 0;
        $no_block = 0;
        foreach ($products as $product) {
            for ($i=1; $i <= $product['quantity']; $i++) { 
                if ($block_value < $price_result['blockValue']) {
                    $block_asc += $product['price'];
                    $block_value += $product[$type] / $product['quantity'];
                }
                if ($no_block < $price_result['block']) {
                    $no_block_asc += $product['price'];
                    $no_block++;
                }
            }
            if ($block_value >= $price_result['blockValue'] && $no_block >= $price_result['block']) {
                break;
            }
        }
        array_multisort($sort_order, SORT_DESC, $products);
        $block_desc = 0;
        $block_value = 0;
        $no_block_desc = 0;
        $no_block = 0;
        foreach ($products as $product) {
            for ($i=1; $i <= $product['quantity']; $i++) { 
                if ($block_value < $price_result['blockValue']) {
                    $block_desc += $product['price'];
                    $block_value += $product[$type] / $product['quantity'];
                }
                if ($no_block < $price_result['block']) {
                    $no_block_desc += $product['price'];
                    $no_block++;
                }
            }
            if ($block_value >= $price_result['blockValue'] && $no_block >= $price_result['block']) {
                break;
            }
        }
        $method_specific_data['block_asc'] = $block_asc;
        $method_specific_data['block_desc'] = $block_desc;
        $method_specific_data['no_block_asc'] = $no_block_asc;
        $method_specific_data['no_block_desc'] = $no_block_desc;
    }
    /* HOOK METHOD HERE */
    /* must start with hook_
    public function hook_custom_field($value, $cart_products, $name) {
        return true;
    } */
}