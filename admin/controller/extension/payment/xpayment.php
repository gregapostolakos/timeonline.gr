<?php
class ControllerExtensionPaymentXpayment extends Controller {
     use OCM\Traits\Back\Controller\Common;
     use OCM\Traits\Back\Controller\Crud;
     use OCM\Traits\Back\Controller\Product;
     use OCM\Traits\Back\Controller\Util;
     private $error = array();
     private $ocm;
     private $ext_path;
     private $ext_key;
     private $meta = array(
        'id'       => '16593',
        'type'     => 'payment',
        'name'     => 'xpayment',
        'path'     => 'extension/payment/',
        'title'    => 'X-Payment',
        'version'  => '5.0.1',
        'ocmod'    => true,
        'event'    => true
    );
    /* Config with default values  Special keyword __LANG__ denotes array of languages e.g 'name' => array('__LANG__' => 'xyz') */
    private $setting = array(
        'xpayment_status'    => '',
        'xpayment_sort_order' => '',
        'xpayment_heading'   => array('__LANG__' => 'Payment Options'),
        'xpayment_admin_all' => false,
        'xpayment_admin_menu'  => '',
        'xpayment_disable_cache'  => '',
        'xpayment_ui'        => array(
            'logo'  => '',
            'desc'  => '',
            'input' => '',
            'error' => '',
            'css'   => ''
        ),
        'xpayment_debug'     => '0'
    );
    private $events = array(
        array(
            'trigger' => 'catalog/view/account/order_info/before',
            'action'  => 'extension/payment/xpayment.onOrderView'
        ),
        array(
            'trigger' => 'admin/view/sale/order_info/before',
            'action'  => 'extension/payment/xpayment.onOrderView'
        )
    );
    private $tables = array(
        'xpayment' => array (
            array('name'=> 'sort_order', 'option' => 'int(8) NULL')
        )
    );
    public function __construct($registry) {
        parent::__construct($registry);
        $this->ocm = new OCM\Back($registry, $this->meta);
        $this->ext_path = $this->meta['path'] . $this->meta['name'];
        $this->ext_key = 'model_' . str_replace('/', '_', $this->ext_path);

        if (VERSION < '4.0.0.0') {
           $this->events[] = array(
                'trigger' => 'catalog/controller/extension/payment/xpayment*/before',
                'action'  => 'extension/payment/xpayment/onPaymentController'
            ); 
        }
    }
    public function index() {
        $ext_lang = $this->load->language($this->ext_path);
        $this->load->model($this->ext_path);
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        /* Some help lang modificaiton */
        $ext_lang['help_time'] = sprintf($this->language->get('help_time'), date('h:i:s A'));
        $ext_lang['help_date'] = sprintf($this->language->get('help_date'), date('Y-m-d')  . ' in ' . date_default_timezone_get() . ' timezone');
        $ext_lang['more_instruction'] = true; // will replace by placeholder 

        $data = array();
        $data = array_merge($data, $ext_lang);

        /* লাইসেন্স বেরিফিকেসন  */
        $rs = $this->ocm->rpd();
        $data['_v'] = $rs ? '' : $this->ocm->vs();
        /* লাইসেন্স শেষ */
        $this->ocm->checkOCMOD();
        $this->upgrade();

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            if (isset($this->request->files['file_import']['tmp_name'])) {
                $this->import();
                $this->response->redirect($this->ocm->url->getExtensionURL());
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->ocm->url->getExtensionsURL());
        }

        $data['heading_title'] = $this->language->get('heading_title');
        $data['x_name'] = $this->meta['name'];
        $data['x_path'] = $this->meta['path'] . $this->meta['name'];

        $this->error['warning'] = $this->{$this->ext_key}->checkPermission($data['warn_permission']);
        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }
        if (isset($this->session->data['warning'])) {
            $data['error_warning'] = $this->session->data['warning'];
            unset($this->session->data['warning']);
        }
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->ocm->url->link('common/dashboard', '', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->ocm->url->getExtensionsURL()
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->ocm->url->getExtensionURL()
        );

        $data['action'] = $this->ocm->url->getExtensionURL();
        $data['cancel'] = $this->ocm->url->getExtensionsURL();
        $data['export'] = $this->ocm->url->link($this->ext_path . '/export', '', true);
        $data['export_gw'] = $this->ocm->url->link($this->ext_path . '/exportGW', '', true);
        
        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $data['languages'] = $this->ocm->url->getLangImage($languages);
        $data['language_id'] = $this->config->get('config_language_id');
        
        $data['method_data'] = $this->{$this->ext_key}->getData();
        /* All required options */
        $options = $this->getFormBuilderOptions($data);
        /* set form data */
        $this->ocm->form->setLangs($ext_lang)->setOptions($options);
        $returnURL = $this->ocm->common->getSiteUrl() . 'index.php?route=' . $this->ext_path . '/confirm';
        $notifyURL = $this->ocm->common->getSiteUrl() . 'index.php?route=' . $this->ext_path . '/notify';
        /* Avail placeholder */
        $more_help = array();
        $placeholders = array(
            '{orderId}'             => 'Order ID',
            '{orderIdUnique}'       => 'Order ID with a random suffix e.g. 12-ab32332s',
            '{orderTotal}'          => 'Order amount without currency code e.g 975.50',
            '{orderTotalInPenny}'   => 'Order amount in penny without currency code e.g 97550',
            '{orderTotalFormatted}' => 'Order amount with currency code e.g $975',
            '{customerId}'          => 'Customer ID',
            '{fullName}'            => 'Full Name of the customer i.e First Name + Last Name',
            '{firstName}'           => 'Customer First Name',
            '{lastName}'            => 'Customer Last Name',
            '{streetAddress1}'      => 'Customer Street Adress1',
            '{streetAddress2}'      => 'Customer Street Adress2',
            '{city}'                => 'Customer City',
            '{stateName}'           => 'Full State Name e.g. New York',
            '{stateCode}'           => 'Customer State Code e.g NY',
            '{zipcode}'             => 'Customer Zip/Postal Code',
            '{countryName}'         => 'Full Country Name e.g United States',
            '{countryCode}'         => 'ISO2 Country Code e.g US',
            '{phone}'               => 'Customer Phone Number',
            '{fax}'                 => 'Customer Fax',
            '{email}'               => 'Customer Email',
            '{productId}'           => 'Product ID of an order product. It will iterate Product ID of each product.',
            '{productModel}'        => 'Product Model of an order product. It will iterate Product Model of each product.',
            '{productQuantity}'     => 'Product quantity of an order product. It will iterate Product quantity of each product.',
            '{productPrice}'        => 'Product price of an order product. It will iterate Product price of each product.',
            '{currencyCode}'        => 'Currency code of the order e.g USD',
            '{currencyNumber}'      => 'Currency numeric code e.g 840',
            '{timestamp}'           => 'Unix Timestamp e.g 1560846235',
            '{timestampInMs}'       => 'Unix Timestamp in ms e.g 1655589236423',
            '{ip}'                  => 'IP of the customer',
            '{storeId}'             => 'Store ID',
            '{storeName}'           => 'Store Name',
            '{returnURL}'           => 'It is X-Payment confirm the URL to finalize the order. In most cases, you have to use it as a return URL OR success URL in case of third party payment gateway integration. If you need to define it in the merchant account, the URL would be: <b>' . $returnURL . '</b>',
            '{notifyURL}'           => 'Background processing callback/IPN Url. You must adjust the value of the param `{_notifyId}` under the `Custom placeholders` section according to the gateway response. If you need to define it in the merchant account, the URL would be: <br> <b>' . $notifyURL . '</b>',
            '{checkoutURL}'         => 'Store Checkout Page URL',
            '{successURL}'          => 'Order Success Page URL.',
            '{storeURL}'            => 'Store URL',
            '{hash}'                => 'The hash value if you define it.'
        );
        
        $avail_placeholders = $this->getPlaceholderList($placeholders);
        $text_init_data = $this->ocm->misc->getHelpTag($this->language->get('more_int_data'));
        $text_header_data = $this->ocm->misc->getHelpTag($this->language->get('more_header_data'));
        $text_form_input = $this->ocm->misc->getHelpTag($this->language->get('text_form_input'));
        $text_count_keyword = $this->ocm->misc->getHelpTag($this->language->get('text_count_keyword'));
        $text_function = $this->ocm->misc->getHelpTag($this->language->get('text_function'));
        $text_callback_data = $this->ocm->misc->getHelpTag($this->language->get('more_callback_data'));
        $text_additional_data = $this->ocm->misc->getHelpTag($this->language->get('more_additional_data'));
        $text_warn_placeholder = $this->ocm->misc->getHelpTag($this->language->get('text_warn_placeholder'), 'danger');
        $text_special_placeholder = $this->ocm->misc->getHelpTag($this->language->get('text_special_placeholder'));
        $sp_placeholders = array(
            '{_keyUrl}'        => 'The key that will be used to extract URL value from the response e.g. paymentUrl or response.url',
            '{_keyStatus}'     => 'The key that will be used to extract payment status from the response e.g. status or result.status',
            '{_keyError}'      => 'The key that will be used to extract error message from the response e.g. error',
            '{_keyErrorAdd}'   => 'The additional key that will be used to extract error message from the response e.g. error',
            '{_statusPending}' => 'The order status is to set when it finds the payment status is pending',
            '{_statusCancel}'  => 'The order status is to set when it finds the payment status is cancel',
            '{_statusRefund}'  => 'The order status is to set when it finds the payment status is Refunded',
            '{_valuePending}'  => 'The value to determine the payment status `Pending` e.g. pending',
            '{_valueCancel}'   => 'The value to determine the payment status `Cancel` e.g. cancel',
            '{_valueRefund}'   => 'The value to determine the payment status `Refund` e.g. refund',
            '{_valueIpn}'      => 'The response text of the IPN/Notify URL',
            '{_notifyId}'      => 'The value of this param will be used to fetch orderId from the response during IPN/background/callback processing.'
        );
        $sp_placeholders = $this->getPlaceholderList($sp_placeholders);
        $more_help['int_data'] = $text_init_data . $avail_placeholders . $text_count_keyword . $text_function . $text_form_input;
        $more_help['header_data'] = $text_header_data . $text_function;
        $more_help['instruction'] = $avail_placeholders;
        $more_help['callback_data'] = $text_callback_data . $avail_placeholders;
        $more_help['int_success'] = $this->ocm->misc->getHelpTag($this->language->get('more_int_success'));
        $more_help['additional_data'] = $text_additional_data . $text_warn_placeholder . $text_special_placeholder . $sp_placeholders;
        $more_help['coupon'] = $this->ocm->misc->getHelpTag($this->language->get('more_coupon'));
        $more_help['voucher'] = $this->ocm->misc->getHelpTag($this->language->get('more_voucher'));
        $more_help['hash_str'] = $this->ocm->misc->getHelpTag($this->language->get('more_hash_str'));
        $more_help['hash_key'] = $this->language->get('more_hash_key');

        $data['placeholders'] = json_encode(array_keys($placeholders));
        //status placeholders
        $this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();
        $status_placeholers = array();
        foreach ($order_statuses as $order_statuse) {
            $status_placeholers[] = $order_statuse['order_status_id'] .'--' . $order_statuse['name'];
        }
        $data['status_placeholers'] = json_encode($status_placeholers);

        $data['more_help']= json_encode($more_help);
        $data['oc_4'] = VERSION >= '4.0.0.0';
        $data['oc_2_1'] = VERSION < '2.2.0.0';
        $data['summer_js'] = VERSION >= '2.3.0.0' && VERSION < '4.0.0.0';
        $data['ckeditor_js'] = VERSION >= '4.0.0.0';
        $data['divider'] = $this->ocm->divider; 
        $data['ocm_base_url'] = $this->ocm->common->getOcmBaseUrl('admin', $this->meta['name']);
        $data['global'] = $this->getConfigForm($data);
        $data['tpl'] = json_encode(array(
            'method'    =>  $this->getFormData($data, true)
        ));
        $data['methods'] = $this->getMethodList($data['method_data']);
        $data['form_data'] = $this->getFormData($data);

        // apply version-wise template changes
        $data = $this->ocm->misc->arrayVersionize($data, array('global', 'tpl', 'form_data')); 
        $data['dataPrefix'] = $this->ocm->common->getCustomConfig("dataPrefix");
    
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->ocm->view($this->ext_path, $data));
    }
    // local events - on save method
    private function onSave($save) {
       $this->load->model('setting/setting');
       /* Creating file */
       $language_id = $this->config->get('config_language_id');
       $code = $this->meta['name'] . (int)$this->request->post['tab_id'];
       $title = isset($this->request->post[$this->meta['name']]['name'][$language_id]) ? $this->request->post[$this->meta['name']]['name'][$language_id] : 'Untitled Payment';
       $title = $this->language->get('heading_title') .' - ' . $title;
       if ($this->config->get($this->ocm->prefix . 'xpayment_status')) {
            $this->{$this->ext_key}->createPayment($code, $title);
       }
       //addon install
       $addon_name = $this->request->post[$this->meta['name']]['int_name'];
       $path = $this->ocm->common->getExtPath('payment');
       $addon = $this->{$this->ext_key}->getAddon($addon_name, $path);
       if ($addon && (method_exists($addon, 'install') || property_exists($addon, 'install') || isset($addon->install))) {
           $addon->install();
       }
       // set config
       $config_key = $this->ocm->prefix . $this->meta['name'] . $save['tab_id'];
       $status = (int)$this->request->post[$this->meta['name']]['status'];
       $config_value = array($config_key . '_status' => $status);
       $this->model_setting_setting->editSetting($config_key, $config_value);
    }
    // local events - on save general method
    private function onSaveGeneral($save) {
        if (!$save['value'][$this->ocm->prefix . 'xpayment_status']) {
            $this->{$this->ext_key}->removeAllExtension();
        } else {
            $this->{$this->ext_key}->clearExtensions();
        }
    }
    // local events - on delete method
    private function onDelete($method_data) {
        $this->load->model('setting/setting');
        // delete payment
        $this->{$this->ext_key}->deletePayment($this->request->post['tab_id']);
        if ($method_data) {
            $addon_name = $method_data['method_data']['int_name'];
            $path = $this->ocm->common->getExtPath('payment');
            $addon = $this->{$this->ext_key}->getAddon($addon_name, $path);
            if ($addon && (method_exists($addon, 'uninstall') || property_exists($addon, 'uninstall') || isset($addon->uninstall))) {
               $addon->uninstall();
            }
        } 
    }
    // local events - on uninstall
    private function onUninstall() {
        $this->load->model($this->ext_path);
        $this->{$this->ext_key}->removeAllExtension();    
    }
    private function getConfigForm($data) {
        /* Set base name for form inputs */
        $setting = $this->ocm->setting->getSetting($this->setting, $data['languages']);
        $this->ocm->form->setBasename($this->ocm->prefix . 'xpayment_', 'prefix');
        $this->ocm->form->setPreset($setting)->setIDPostfix('');

        $return = '';
        if (VERSION >= '4.0.0.0') {
            $return .= $this->ocm->misc->getLangTabs('language_heading', $data['languages']);
            $active = ' active';
            $return .= '<div class="tab-content">';
            foreach ($data['languages'] as $language) { 
                $language_id = $language['language_id'];
                $return .= '<div class="tab-pane' . $active . '" id="language_heading' . '-' . $language_id . '">';

                $param = array(
                    'name'  => 'heading[' . $language_id . ']',
                    'required' => true
                );
                $return .= $this->ocm->form->get('input', $param);

                $return .= '</div>';
                $active = '';
            }
            $return .= '</div>';
        }
        $tabs = array(
            'global-general'     => $data['tab_global_general'],
            'global-integration' => $data['tab_integration'],
            'global-export'      => $data['tab_import_export'],
            'global-help'        => $data['tab_help']
        );
        $return .= $this->ocm->misc->getTabs('global-tab', $tabs);
        $return .= '<div class="tab-content">';

        $return .= '<div class="tab-pane active" id="global-general">';
        $return .= $this->ocm->form->get('checkbox', array('name' => 'admin_all', 'label' => $data['text_admin_all']));
        $return .= $this->ocm->form->get('checkbox', array('name' => 'admin_menu', 'label' => $data['text_admin_menu']));
        $return .= $this->ocm->form->get('checkbox', array('name' => 'disable_cache', 'label' => $data['text_disable_cache']));
        if (VERSION >= '4.0.0.0') {
            $return .= $this->ocm->form->get('input', array('name' => 'sort_order', 'help' => $data['help_module_sort_order']));
        }
        $return .= $this->ocm->form->get('select', array('name' => 'status', 'title' => $data['entry_module_status'], 'help' => $data['help_module_status']));
        $return .= $this->ocm->form->get('select', 'debug');
        $return .= '</div>';

        $return .= '<div class="tab-pane" id="global-integration">';
        $return .= $this->ocm->form->get('textarea', 'ui[css]');
        $return .= $this->ocm->misc->getHelpTag($data['help_ui_detail']);
        $return .= $this->ocm->form->get('input', 'ui[logo]');
        $return .= $this->ocm->form->get('input', 'ui[desc]');
        $return .= $this->ocm->form->get('input', 'ui[input]');
        $return .= $this->ocm->form->get('input', 'ui[error]');
        $return .= '</div>';

        $return .= '<div class="tab-pane" id="global-export">';
        $element = '<input type="file" class="form-control ocm-import" id="input-import" accept="text/txt" name="file_import" />&nbsp<button type="submit" form="form-ocm" data-toggle="tooltip" name="action" value="import" class="btn btn-primary">'.$data['text_import_all'].'</button>';
        $return .= $this->ocm->form->get('bare', array('name' => 'import', 'element' => $element));
        $element = '<a href="' . $data['export'] . '" target="_blank" class="btn btn-primary">' . $data['text_export_all'] . '</a>';
        $return .= $this->ocm->form->get('bare', array('name' => 'export', 'element' => $element));
        $return .= '</div>';

        $return .= '<div class="tab-pane" id="global-help">';
        $return .= '<div class="ocm-debug-button"><a class="btn btn-danger" href="javascript:debugBrowser();" role="button">'.$data['text_debug_button'].'</a></div>';
        $return .= $this->ocm->misc->getOCMInfo();
        $return .= '</div>';
        
        $return .= '</div>';

        return $return;
    }
    private function getFormData($data, $new_tab = false) {
       $this->load->model('catalog/category');
       $this->load->model('catalog/product');
       $this->load->model('catalog/option');
       $this->load->model('catalog/manufacturer');
       $cg_path = (VERSION >= '2.1.0.1') ? 'customer' : 'sale';
        $this->load->model($cg_path . '/customer');
       if ($new_tab) {
            $data['method_data'] = array(
                array('tab_id' => '__INDEX__', 'method_data' => array())
            );
        }
        /* Set base name for form inputs */
        $this->ocm->form->setBasename($this->meta['name']);

        $fields_lang = array(
            'name'               => 'Untitled Payment',
            'desc'               => '',
            'instruction'        => '',
            'email_instruction'  => '',
            'error'              => ''
        );
        $fields_all = array(
            'store'          => 'store_all',
            'geo_zone'       => 'geo_zone_all',
            'country'        => 'country_all', 
            'currency'       => 'currency_all',
            'customer_group' => 'customer_group_all',
            'shipping'       => 'shipping_all',
            'city'           => 'city_all',
            'postal'         => 'postal_all',
            'coupon'         => 'coupon_all',
            'voucher'        => 'voucher_all',
            'days'           => 'days_all',
            'customers'      => 'customer_all',
            'email'          => 'email_all'
        );
        $default_values = $this->getDefaultValues();
        $return = '';
        $count = 0;
        foreach($data['method_data'] as $single_method) {
            $count++;
            $no_of_tab   = $single_method['tab_id'];
            $method_data = $single_method['method_data'];
            $method_data = $this->resetEmptyAll($method_data, $fields_all);
            $method_data = array_merge($default_values, $method_data);
            $method_data = $this->setDefaultByLangs($method_data, $data['languages'], $fields_lang);
            if (!$method_data['display']) {
                $method_data['display'] = $this->getLangField($method_data, 'name', 'Untitled Payment');
            }
            $this->ocm->form->setPreset($method_data)->setIDPostfix($no_of_tab);

            $return .= '<div id="ocm-method-'.$no_of_tab.'" class="tab-pane xpayment ocm-method">';
            if (!$new_tab && $count > 1) {
               $return .= '</div>';
               continue; 
            }
            $return .= '<div class="ocm-action-btn">';
            $return .= $this->ocm->misc->getButton(array('type' => 'success', 'help' => $data['text_method_save'], 'class' => 'btn-ocm-save btn-sm', 'icon' => 'fa-save'));
            $return .= $this->ocm->misc->getButton(array('type' => 'warning', 'help' => $data['text_method_copy'], 'class' => 'btn-ocm-copy btn-sm', 'icon' => 'fa-copy'));
            $return .= $this->ocm->misc->getButton(array('type' => 'danger', 'help' => $data['text_method_remove'], 'class' => 'btn-ocm-delete btn-sm', 'icon' => 'fa-trash fa-trash-alt'));
            $return .= '</div>';

            $return .= $this->ocm->form->get('input', 'display');
            $return .= $this->ocm->misc->getLangTabs('language_' . $no_of_tab, $data['languages']);

            $active = ' active';
            $return .= '<div class="tab-content">';
            foreach ($data['languages'] as $language) { 
                $language_id = $language['language_id'];
                $return .= '<div class="tab-pane' . $active . '" id="language_' . $no_of_tab . '-' . $language_id . '">';

                $param = array(
                    'name'  => 'name[' . $language_id . ']',
                    'required' => true
                );
                $return .= $this->ocm->form->get('input', $param);
                $return .= $this->ocm->form->get('input', 'desc[' . $language_id . ']');

                $param = array(
                    'name'  => 'instruction[' . $language_id . ']',
                    'id'    => 'instruction_' . $language_id . '_',
                    'preset' => $method_data['instruction'][$language_id],
                    'class' => 'editor',
                    'rows'  => 10
                );
                $return .= $this->ocm->form->get('textarea', $param);

                $param = array(
                    'name'  => 'email_instruction[' . $language_id . ']',
                    'id'    => 'email_instruction' . $language_id . '_',
                    'preset' => $method_data['email_instruction'][$language_id],
                    'class' => 'editor',
                    'attr'   => 'inc_email',
                    'visible' => $method_data['inc_email'] != '',
                    'rows'  => 10
                );
                $return .= $this->ocm->form->get('textarea', $param);

                $param = array(
                    'name'    => 'error[' . $language_id . ']',
                    'class'   => 'ocm-hide redirect api',
                    'attr'    => 'int_type',
                    'visible' => $method_data['int_type'] != '',
                    'rows'    => 2
                );
                $return .= $this->ocm->form->get('textarea', $param);

                $return .= '</div>';
                $active = '';
            }
            $return .= '</div>';

            $tabs = array(
                'common_' . $no_of_tab      => $data['tab_general'],
                'criteria_' . $no_of_tab    => $data['tab_criteria_setting'],
                'catprod_' . $no_of_tab     => $data['tab_category_product'],
                'price_' . $no_of_tab       => $data['tab_price_setting'],
                'other_' . $no_of_tab       => $data['tab_others'],
                'integration_' . $no_of_tab => $data['tab_integration']
            );
            $return .= $this->ocm->misc->getTabs('method-tab' . $no_of_tab, $tabs);

            $return .= '<div class="tab-content">';
            $return .= '<div class="tab-pane active" id="common_' . $no_of_tab . '">';
            $element = '<div class="ocm-id">xpayment' . $no_of_tab . '</div>';
            $return .= $this->ocm->form->get('bare', array('name' => 'id', 'element' => $element));
            $return .= $this->ocm->form->get('checkbox', 'hide_title');
            $return .= $this->ocm->form->get('checkbox', 'inc_email');
            $return .= $this->ocm->form->get('checkbox', 'inc_order');
            $return .= $this->ocm->form->get('checkbox', 'admin_only');
            $return .= $this->ocm->form->get('select', 'order_status_id');
            $return .= $this->ocm->form->get('input', 'logo');
            $return .= $this->ocm->form->get('input', 'sort_order');
            $return .= $this->ocm->form->get('select', 'status');

            $return .= '</div>';

            $return .= '<div class="tab-pane" id="criteria_'.$no_of_tab.'">';
            $return .= $this->ocm->form->get('checkgroup', 'store[]');
            $return .= $this->ocm->form->get('checkgroup', array('name' => 'geo_zone[]', 'search' => true));
            $return .= $this->ocm->form->get('checkgroup', array('name' => 'country[]', 'search' => true));
            $return .= $this->ocm->form->get('checkgroup', 'currency[]');
            $return .= $this->ocm->form->get('checkgroup', 'customer_group[]');
            $return .= $this->ocm->form->get('checkgroup', 'shipping[]');

            /* cunstomers */
            $customers = array();
            foreach ($method_data['customers'] as $customer_id) {
                $customer_info = $this->{'model_' . $cg_path . '_customer'}->getCustomer($customer_id);
                if ($customer_info) {
                    $name = strip_tags(html_entity_decode($customer_info['firstname'], ENT_QUOTES, 'UTF-8')) . ' ' . strip_tags(html_entity_decode($customer_info['lastname'], ENT_QUOTES, 'UTF-8')) . ' (ID: '.$customer_id.')';
                    $customers[] = array(
                        'customer_id' => $customer_id,
                        'name'  => $name
                    );
                }
            }
            /* Customer */
            $visible = !$method_data['customer_all'];
            $customers = $this->ocm->form->getOptions($customers, 'customer_id');
            $groups = array();
            $groups['checkbox'] = array('name' => 'customer_all', 'label' => $data['text_any']);
            $groups['autofill'] = array('name' => 'customers[]', 'attr'  => 'customer_all', 'options' => $customers, 'visible' => $visible);
            $groups['select'] = array('name' => 'customer_rule', 'attr' => 'customer_all', 'visible' => $visible);
            $return .= $this->ocm->form->getFrom($groups);
            /* end of customers */

             /* Email */
            $visible = $method_data['email_all'] != '1';
            $groups = array();
            $groups['checkbox'] = array('name' => 'email_all', 'label' => $data['text_any']);
            $groups['textarea'] = array('name' => 'email', 'attr' => 'email_all', 'visible' => $visible);
            $groups['select'] = array('name' => 'email_rule', 'attr' => 'email_all', 'visible' => $visible);
            $return .= $this->ocm->form->getFrom($groups);
            /* end of city */

            /* City */
            $visible = $method_data['city_all'] != '1';
            $groups = array();
            $groups['checkbox'] = array('name' => 'city_all', 'label' => $data['text_any']);
            $groups['textarea'] = array('name' => 'city', 'attr' => 'city_all', 'visible' => $visible);
            $groups['select'] = array('name' => 'city_rule', 'attr' => 'city_all', 'visible' => $visible);
            $return .= $this->ocm->form->getFrom($groups);
            /* end of city */

            /* Postal */
            $visible = $method_data['postal_all'] != '1';
            $groups = array();
            $groups['checkbox'] = array('name' => 'postal_all', 'label' => $data['text_any']);
            $groups['textarea'] = array('name' => 'postal', 'attr' => 'postal_all', 'visible' => $visible);
            $groups['select'] = array('name' => 'postal_rule', 'attr' => 'postal_all', 'visible' => $visible);
            $return .= $this->ocm->form->getFrom($groups);
            /* end of postal */

            /* Coupon */
            $visible = $method_data['coupon_all'] != '1';
            $groups = array();
            $groups['checkbox'] = array('name' => 'coupon_all', 'label' => $data['text_any']);
            $groups['textarea'] = array('name' => 'coupon', 'attr' => 'coupon_all', 'visible' => $visible);
            $groups['select'] = array('name' => 'coupon_rule', 'attr' => 'coupon_all', 'visible' => $visible);
            $return .= $this->ocm->form->getFrom($groups);
            /* end of coupon */

            /* Voucher */
            $visible = $method_data['voucher_all'] != '1';
            $groups = array();
            $groups['checkbox'] = array('name' => 'voucher_all', 'label' => $data['text_any']);
            $groups['textarea'] = array('name' => 'voucher', 'attr' => 'voucher_all', 'visible' => $visible);
            $groups['select'] = array('name' => 'voucher_rule', 'attr' => 'voucher_all', 'visible' => $visible);
            $return .= $this->ocm->form->getFrom($groups);
            /* end of Voucher */

            $return .= $this->ocm->form->get('checkgroup', 'days[]');
            $return .= $this->ocm->form->get('range', array('name' => 'time', 'type' => 'time'));
            $return .= $this->ocm->form->get('range', array('name' => 'date', 'type' => 'date'));
            $return .= $this->ocm->form->get('checkbox', array('name' => 'out_of_stock', 'label' => $data['text_out_of_stock']));

            $return .= '</div>';
            $return .= '<div class="tab-pane" id="catprod_'.$no_of_tab.'">';

            /* categories */
            $categories = array();
            foreach ($method_data['product_category'] as $category_id) {
               $category_info = $this->model_catalog_category->getCategory($category_id);
                if ($category_info) {
                    if ($category_info['path']) $category_info['path'] .=  '&nbsp;&nbsp;&gt;&nbsp;&nbsp;';
                    $categories[] = array(
                        'category_id' => $category_id,
                        'name'       => $category_info['path'].$category_info['name']
                    );
                }
            }

            $visible = (int)$method_data['category'] > 1;
            $return .= $this->ocm->form->get('select', 'category');
            $param = array(
                'name'  => 'product_category[]',
                'options' => $this->ocm->form->getOptions($categories, 'category_id'),
                'attr'  => 'category',
                'browser' => 'category',
                'visible' => $visible
            );
            $return .= $this->ocm->form->get('autofill', $param);

            /* Products  */
            $products = array();
            foreach ($method_data['product_product'] as $product_id) {
               $product_info = $this->model_catalog_product->getProduct($product_id);
               if ($product_info) {
                    $products[] = array(
                        'product_id' => $product_id,
                        'name'       => $product_info['name']
                    );
               }
            }
            
            $visible = (int)$method_data['product'] > 1;
            $return .= $this->ocm->form->get('select', 'product');
            $param = array(
                'name'  => 'product_product[]',
                'options' => $this->ocm->form->getOptions($products, 'product_id'),
                'attr'  => 'product',
                'browser' => 'product',
                'visible' => $visible
            );
            $return .= $this->ocm->form->get('autofill', $param);

            /* Product Options  */
            $options = array();
            foreach ($method_data['product_option'] as $option_value_id) {
               if (VERSION < '4.0.0.0') {
                    $option_value_info = $this->model_catalog_option->getOptionValue($option_value_id);
               } else {
                    $option_value_info = $this->model_catalog_option->getValue($option_value_id);
               }
               if ($option_value_info) {
                    $option_info = $this->model_catalog_option->getOption($option_value_info['option_id']);
                    if ($option_info) {
                        $options[] = array(
                            'option_value_id' => $option_value_id,
                            'name'            => strip_tags(html_entity_decode($option_info['name'], ENT_QUOTES, 'UTF-8')).'&nbsp;&nbsp;&gt;&nbsp;&nbsp;' . strip_tags(html_entity_decode($option_value_info['name'], ENT_QUOTES, 'UTF-8'))
                        );
                    }
               }
            }
            
            $visible = (int)$method_data['option'] > 1;
            $return .= $this->ocm->form->get('select', 'option');
            $param = array(
                'name'  => 'product_option[]',
                'options' => $this->ocm->form->getOptions($options, 'option_value_id'),
                'attr'  => 'option',
                'visible' => $visible
            );
            $return .= $this->ocm->form->get('autofill', $param);

            /* Product attribute  */
            $options = array();
            foreach ($method_data['product_attribute'] as $attribute_id) {
                $attribute = $this->{$this->ext_key}->getAttribute($attribute_id);
                if ($attribute) {
                    $options[] = array(
                        'attribute_id' => $attribute_id,
                        'name'         => strip_tags(html_entity_decode($attribute['attribute_group'], ENT_QUOTES, 'UTF-8')) . '&nbsp;&nbsp;&gt;&nbsp;&nbsp;' . strip_tags(html_entity_decode($attribute['name'], ENT_QUOTES, 'UTF-8'))
                    );
                }
            }
            
            $visible = (int)$method_data['attribute'] > 1;
            $return .= $this->ocm->form->get('select', 'attribute');
            $param = array(
                'name'  => 'product_attribute[]',
                'options' => $this->ocm->form->getOptions($options, 'attribute_id'),
                'attr'  => 'attribute',
                'visible' => $visible
            );
            $return .= $this->ocm->form->get('autofill', $param);

            /* Manufacturer  */
            $manufacturers = array();
            foreach ($method_data['manufacturer'] as $manufacturer_id) {
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);
                if ($manufacturer_info) {
                    $manufacturers[] = array(
                        'manufacturer_id' => $manufacturer_id,
                        'name'       => $manufacturer_info['name']
                    );
                }
            }

            $visible = (int)$method_data['manufacturer_rule'] > 1;
            $return .= $this->ocm->form->get('select', 'manufacturer_rule');
            $param = array(
                'name'    => 'manufacturer[]',
                'options' => $this->ocm->form->getOptions($manufacturers, 'manufacturer_id'),
                'attr'    => 'manufacturer_rule',
                'browser' => 'manufacturer',
                'visible' => $visible
            );
            $return .= $this->ocm->form->get('autofill', $param);
            $return .= $this->ocm->form->get('select', 'product_or');
            $return .= '</div>';

            $return .= '<div class="tab-pane" id="price_'.$no_of_tab.'">';
            $return .= $this->ocm->form->get('select', 'total_type');
            $return .= $this->ocm->form->get('range', 'total');
            $return .= $this->ocm->form->get('range', 'weight');
            $return .= $this->ocm->form->get('range', 'quantity');
            $return .= $this->ocm->form->get('checkbox', array('name' => 'free', 'label' => $data['text_free']));
            $return .= '</div>';
           
            $return .= '<div class="tab-pane" id="other_'.$no_of_tab.'">';
            $return .= $this->ocm->form->get('input', 'additional_email');
            $return .= $this->ocm->form->get('textarea', 'custom_css');
            $return .= $this->ocm->form->get('textarea', 'custom_js');
            $return .= $this->ocm->form->get('input', 'success');
            $return .= $this->ocm->form->get('checkbox', array('name' => 'callback_enable', 'label' => $data['text_callback_enable']));
            $visible = !empty($method_data['callback_enable']);
            $return .= $this->ocm->form->get('input', array('name' => 'callback', 'attr' => 'callback_enable', 'visible' => $visible));
            $element = $this->getParams('callback_data', $method_data['callback_data'], $data, 'param');
            $return .= $this->ocm->form->get('bare', array('name' => 'callback_data', 'element' => $element, 'attr' => 'callback_enable', 'visible' => $visible, 'class' => 'callback_data-param'));
            $return .= '</div>';

            $return .= '<div class="tab-pane" id="integration_'.$no_of_tab.'">';
            
            $return .='<div class="gateway-import">';
            $return .=  $this->ocm->misc->getButton(array('type' => 'primary', 'title'=> $data['text_import'], 'class' => 'btn-sm btn-warning ocm-import-gw'));
            $return .= '   <a target="_blank" href="'.$data['export_gw'].'&no='.$no_of_tab.'" class="btn btn-info btn-sm ocm-export-gw" role="button">'.$data['text_export'].'</a>';
            $return .= '</div>';

            $return .= $this->ocm->form->get('input', 'js_url');
            /* Integration Type */
            $visible = $method_data['int_type'] != '';
            $return .= $this->ocm->form->get('select', 'int_type');
            $return .= $this->ocm->form->get('input', array('name' => 'int_name', 'attr' => 'int_type', 'visible' => $visible));
            $return .= $this->ocm->form->get('input', array('name' => 'int_url', 'attr' => 'int_type', 'visible' => $visible));
            $return .= $this->ocm->form->get('select', array('name' => 'method_type', 'attr' => 'int_type', 'visible' => $visible));
            
            // laegacy version - will remove future version
            if (!is_array($method_data['int_data'])) {
                $method_data['int_data'] = @json_decode(html_entity_decode($method_data['int_data'], ENT_QUOTES, 'UTF-8'), true);
                $params = array();
                if (is_array($method_data['int_data'])) {
                    foreach ($method_data['int_data'] as $name => $value) {
                        $params[] = array(
                            'name'  => $name,
                            'value' => $value
                        );
                    }
                }
                $method_data['int_data'] = $params;
            }
            /* end of legacy */
            $element = $this->getParams('int_data', $method_data['int_data'], $data, 'param');
            $return .= $this->ocm->form->get('bare', array('name' => 'int_data', 'element' => $element, 'attr' => 'int_type', 'visible' => $visible , 'class' => 'int_data-param'));

            $element = $this->getParams('header_data', $method_data['header_data'], $data, 'param'); 
            $return .= $this->ocm->form->get('bare', array('name' => 'header_data', 'element' => $element, 'attr' => 'int_type', 'visible' => $visible && ($method_data['int_type'] == 'api' || $method_data['int_type'] == 'fetch_redirect'), 'class' => 'int_header-param'));

            // laegacy version - will remove future version
            if (!is_array($method_data['additional_data'])) {
                $method_data['additional_data'] = @json_decode(html_entity_decode($method_data['additional_data'], ENT_QUOTES, 'UTF-8'), true);
                $params = array();
                if (is_array($method_data['additional_data'])) {
                    foreach ($method_data['additional_data'] as $name => $value) {
                        $params[] = array(
                            'name'  => $name,
                            'value' => $value
                        );
                    }
                }
                $method_data['additional_data'] = $params;
            }
            /* end of legacy */
            $element = $this->getParams('additional_data', $method_data['additional_data'], $data, 'custom'); 
            $return .= $this->ocm->form->get('bare', array('name' => 'additional_data', 'element' => $element, 'attr' => 'int_type', 'visible' => $visible, 'class' => 'additional_data-param'));

            //$return .= $this->ocm->form->get('input', array('name' => 'fetch_key', 'attr' => 'int_type', 'visible' => $visible && $method_data['int_type'] == 'fetch_redirect', 'class' => 'fetch_key')); // deletable

             /* hash fields */
            $return .= $this->ocm->form->get('checkbox', array('name' => 'hash', 'label' => $data['text_hash'], 'attr' => 'int_type', 'visible' => $visible));
            $hash_visible = $method_data['int_type'] != '' && !empty($method_data['hash']);
            $return .= $this->ocm->form->get('select', array('name' => 'hash_type', 'attr' => 'hash', 'visible' => $hash_visible));
            $return .= $this->ocm->form->get('textarea', array('name' => 'hash_str', 'attr' => 'hash', 'visible' => $hash_visible));
            $return .= $this->ocm->form->get('input', array('name' => 'hash_key', 'attr' => 'hash', 'visible' => $hash_visible));
            $return .= $this->ocm->form->get('checkbox', array('name' => 'hash_bin', 'label' => $data['text_hash_bin'], 'attr' => 'hash', 'visible' => $hash_visible));
            $return .= $this->ocm->form->get('checkbox', array('name' => 'hash_base64', 'label' => $data['text_hash_base64'], 'attr' => 'hash', 'visible' => $hash_visible));
            
            $return .= $this->ocm->form->get('select', array('name' => 'success_method_type', 'attr' => 'int_type', 'visible' => $visible));
            $return .= $this->ocm->form->get('textarea', array('name' => 'int_success', 'attr' => 'int_type', 'visible' => $visible));

            if (!empty($method_data['information'])) {
                $method_data['information'] = html_entity_decode($method_data['information'], ENT_QUOTES, 'UTF-8');
                $information = str_replace('{route}', $this->meta['path'], $method_data['information']);
                $information = str_replace('{storeUrl}', $this->ocm->common->getSiteUrl(), $information);
                $class = '';
            } else {
                $information = '';
                $class = 'hide-info info-row'; 
            }
            $element = '<div class="gateway-information">
                            <div class="info-header">' . $data['entry_information'] . '<a class="info-edit">Edit</a></div>
                            <div class="info-body">' . $information . '</div>
                            <textarea class="form-control info-input" name="xpayment[information]">' . $method_data['information'] . '</textarea>
                        </div>';
            $return .= $this->ocm->form->get('bare', array('name' => 'information', 'element' => $element, 'attr' => 'int_type', 'visible' => $visible, 'label_col' => 0, 'class' => $class));

            $return .= '</div>';
            /*End of Integatrion tab */
            $return .= '</div>';
            $return .= '</div>';
        }
        return $return;
    }
    private function getDefaultValues() {
        return array(
            /* array rules */   
            'customer_group'    => array(),
            'geo_zone'          => array(),
            'country'           => array(),
            'product_category'  => array(),
            'product_product'   => array(),
            'product_option'    => array(),
            'product_attribute' => array(),
            'store'             => array(),
            'manufacturer'      => array(),
            'shipping'          => array(),
            'days'              => array(),
            'customers'         => array(),

            /* string rules*/
            'display'            => 'Untitled Item',
            'customer_group_all' => '',
            'country_all'        => '',
            'geo_zone_all'       => '',
            'store_all'          => '',
            'manufacturer_all'   => '',
            'shipping_all'       => '',
            'city_all'           => '',
            'city'               => '',
            'currency_all'       => '',
            'currency'           => array(),
            'email_all'          => '',
            'email'              => '',
            'postal_all'         => '',
            'coupon_all'         => '',
            'voucher_all'        => '',
            'days_all'           => '',
            'customer_all'       => '',
            'postal'             => '',
            'coupon'             => '',
            'voucher'            => '',
            'coupon_rule'        => 'inclusive',
            'voucher_rule'       => 'inclusive',
            'city_rule'          => 'inclusive',
            'postal_rule'        => 'inclusive',
            'customer_rule'      => 'inclusive',
            'time_start'         => '',
            'time_end'           => '',
            'date_start'         => '',
            'date_end'           => '',
            'out_of_stock'       => '',
            'additional_data'    => array(),
            'header_data'        => array(),
            'logo'               => '',
            'total_start'        => 0,
            'total_end'          => 0,
            'weight_start'       => 0,
            'weight_end'         => 0,
            'quantity_start'     => 0,
            'quantity_end'       => 0,
            'free'               => '',
            'sort_order'         => '',
            'status'             => 1,
            'category'           => '',
            'product'            => '',
            'option'             => 1,
            'attribute'          => 1,
            'manufacturer_rule'  => '',
            'int_success'        => '',
            'success_method_type' => '',
            'product_or'         => 1,
            'int_data'           => array(),
            'method_type'        => '',
            'int_url'            => '',
            'int_name'           => '',
            'int_type'           => '',
            'total_type'         => '',
            'order_status_id'    => 1,
            'success'            => '',
            'callback'           => '',
            'inc_order'          => '',
            'admin_only'         => '',
            'inc_email'          => '',
            'hide_title'         => '',
            'additional_email'   => '',
            'custom_css'         => '',
            'custom_js'          => '',
            'js_url'             => '',
            'callback_enable'    => '',
            'callback_data'      => array(),
            'information'        => '',
            'hash'               => '',
            'hash_bin'           => '',
            'hash_base64'        => ''
        );
    }
    private function getParams($type, $params, $data, $autotype = '') {
        $return = '';
        $table_body = '';
        $attr = $autotype ? ' attr="'.$autotype.'"' : '';
        $class = $autotype ? 'ocm-autofill param-autofill' : '';
        foreach ($params as $index => $param) {
            $table_body .= '<tr draggable="true" class="draggable" rel="' . $index .'">' 
                            .'<td draggable="false" class="text-left"><input size="15" type="text" class="form-control param-name" name="xpayment[' . $type . ']['.$index.'][name]" value="' . $param['name'] . '" /></td>'
                            .'<td draggable="false" class="text-left"><input size="15" type="text" class="form-control param-value '.$class.'" ' . $attr . ' name="xpayment[' . $type . ']['.$index.'][value]" value="' . $param['value'] . '" /></td>'
                            .'<td class="text-right"><a class="btn btn-sm btn-default ocm-row-move"><i class="fa fas fa-arrows-alt"></i></a>&nbsp;<a draggable="false" class="btn btn-sm btn-danger ocm-row-remove"><i class="fa fas fa-trash fa-trash-alt"></i></a></td>'
                        .'</tr>';
        }
        if (!$params) $table_body .= '<tr class="no-row"><td colspan="3">'.$data['text_no_params'].'</td></tr>';

        $table_headings = array(
            array(
                'title'  => $data['text_param_name'],
            ),
            array(
                'title'  => $data['text_param_value'],
            ),
            array(
                'title'  => $data['text_param_action']
            )
        );
        $table_footer = '<tfoot>
                           <td colspan="7" class="text-right">&nbsp;';
        $table_footer .= $this->ocm->misc->getButton(array('type' => 'info', 'title' => $data['text_param_add'], 'class' => 'add-param-row ' . $type, 'icon' => 'fa-plus-circle'));
        $table_footer .= '</tr>
                        </tfoot>';
        $class = array(
            'body' => $type . '-tbody draggable-container'
        );
        $return .= $this->ocm->misc->getTableSkeleton($table_headings, $table_body, $table_footer, $class);
        return $return;
    }
    public function importGW() {
        $this->load->model('setting/setting');
        $this->load->model($this->ext_path);
        $json = array();
        $fields = array();
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
            $content = file_get_contents($this->request->files['file']['tmp_name']);
            if ($content) {
                $fields = @json_decode($content, true);
                if (!is_array($fields)) {
                    $fields = array();
                }
                if (!empty($fields['information'])) {
                    $fields['information'] = html_entity_decode($fields['information'], ENT_QUOTES, 'UTF-8');
                    $information = str_replace('{route}', $this->meta['path'], $fields['information']);
                    $information = str_replace('{storeUrl}', $this->ocm->common->getSiteUrl(), $information);
                    $fields['information_formated'] = $information;
                }
                // data
                $params = array();
                if (!empty($fields['int_data']) && is_array($fields['int_data'])) {
                    foreach ($fields['int_data'] as $param) {
                        $params[] = array(
                            'name'  => $param['name'],
                            'value' => $param['value']
                        );
                    }
                }
                $fields['int_data'] = $params;
                // header
                $params = array();
                if (!empty($fields['header_data']) && is_array($fields['header_data'])) {
                    foreach ($fields['header_data'] as $param) {
                        $params[] = array(
                            'name'  => $param['name'],
                            'value' => $param['value']
                        );
                    }
                }
                $fields['header_data'] = $params;
                // additioanl data
                $params = array();
                if (!empty($fields['additional_data']) && is_array($fields['additional_data'])) {
                    foreach ($fields['additional_data'] as $param) {
                        $params[] = array(
                            'name'  => $param['name'],
                            'value' => $param['value']
                        );
                    }
                }
                $fields['additional_data'] = $params;
                $fields['int_url'] = str_replace(array('&amp;', '&quot;'), array('&', '"'), $fields['int_url']);
                $fields['hash_str'] = str_replace(array('&amp;', '&quot;'), array('&', '"'), $fields['hash_str']);
                $fields['int_success'] = str_replace(array('&amp;', '&quot;'), array('&', '"'), $fields['int_success']);
            }
        }
        if ($fields) {
            $json['fields'] = $fields;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 
    }
    private function getFormBuilderOptions($data) {
        $options = array();
        /* All required options */
        $options = array();
        $this->load->model('localisation/geo_zone');
        $geo_zones = $this->model_localisation_geo_zone->getGeoZones();
        $options['geo_zone'] = $this->ocm->form->getOptions($geo_zones, 'geo_zone_id');

        $this->load->model('localisation/country');
        $countries = $this->model_localisation_country->getCountries();
        $options['country'] = $this->ocm->form->getOptions($countries, 'country_id');
        
        $this->load->model('setting/store');
        $stores = $this->model_setting_store->getStores();
        array_unshift($stores, array('store_id' => 0,'name' => $this->language->get('text_store_default')));
        $options['store'] = $this->ocm->form->getOptions($stores, 'store_id');
      
        $cg_path = (VERSION >= '2.1.0.1') ? 'customer' : 'sale';
        $this->load->model($cg_path . '/customer_group');
        $customer_groups = $this->{'model_' . $cg_path . '_customer_group'}->getCustomerGroups();
        $customer_groups[] = array('customer_group_id' => 0, 'name' => $this->language->get('text_guest_checkout'));
        $options['customer_group'] = $this->ocm->form->getOptions($customer_groups, 'customer_group_id');
 
        $this->load->model('localisation/order_status');
        $order_statuses = $this->model_localisation_order_status->getOrderStatuses();
        $options['order_status_id'] = $this->ocm->form->getOptions($order_statuses, 'order_status_id');

        $this->load->model('localisation/currency');
        $currencies = $this->model_localisation_currency->getCurrencies();
        $options['currency'] = $this->ocm->form->getOptions($currencies, 'currency_id');

        $options['shipping'] = $this->ocm->misc->getShippingMethods($data['language_id'], $geo_zones);

        $status_options = array('1' => $data['text_enabled'], '0' => $data['text_disabled']);
        $options['status'] = $options['debug'] = $this->ocm->form->getOptions($status_options, 'none');

        $inc_exc_options = array('inclusive' => $data['text_rule_inclusive'], 'exclusive' => $data['text_rule_exclusive']);
        $inc_exc_options = $this->ocm->form->getOptions($inc_exc_options, 'none');
        $options['city_rule'] = $options['coupon_rule'] = $options['voucher_rule'] = $options['postal_rule'] = $options['customer_rule'] = $options['email_rule'] = $inc_exc_options; 
        $modes = array(
            '0' => $data['text_mode_and'],
            '1' => $data['text_mode_or']
        );
        $options['product_or'] = $this->ocm->form->getOptions($modes, 'none');
        $product_rule_options = array(
            '' => $data['text_any'],
            '6' => $data['text_ones_exact_any'],
            '3' => $data['text_ones_any'],
            '9' => $data['text_ones_any_with_other'],
            '4' => $data['text_ones_exact_must'],
            '2' => $data['text_ones_must'],
            '8' => $data['text_ones_must_with_other'],
            '5' => $data['text_ones_except'],
            '7' => $data['text_ones_except_with_other']
        );
        $product_rule_options = $this->ocm->form->getOptions($product_rule_options, 'none');
        $options['category'] = $options['product'] = $options['option'] = $options['attribute'] = $options['manufacturer_rule'] = $product_rule_options; 

        $total_options = array(
            'sub'            => $data['text_sub_total'],
            'total'          => $data['text_total'],
            'sub_coupon'     => $data['text_sub_without_coupon'],
            'total_coupon'   => $data['text_total_without_coupon'],
            'sub_shipping'   => $data['text_sub_with_shipping'],
            'total_shipping' => $data['text_total_with_shipping'],
            'grand' => $data['text_grand']
        );
        $options['total_type'] = $this->ocm->form->getOptions($total_options, 'none');

        $week_day_optios = array(
            '0' => $data['text_sunday'],
            '1' => $data['text_monday'],
            '2' => $data['text_tuesday'],
            '3' => $data['text_wednesday'],
            '4' => $data['text_thursday'],
            '5' => $data['text_friday'],
            '6' => $data['text_saturday']
        );
        $options['days'] = $this->ocm->form->getOptions($week_day_optios, 'none');

        $integration_options = array(
            ''               => $data['text_integration_none'],
            'redirect'       => $data['text_integration_redirect'],
            'fetch_redirect' => $data['text_integration_fetch_redirect'],
            'api'            => $data['text_integration_api']
        );
        $options['int_type'] = $this->ocm->form->getOptions($integration_options, 'none');

        $method_options = array(
            'post' => $data['text_integration_post'],
            'get'  => $data['text_integration_get'],
            'json' => $data['text_api_json']
        );
        $options['method_type'] = $options['success_method_type'] = $this->ocm->form->getOptions($method_options, 'none');

        $hash_options = array(
            'sha1'   => 'sha1',
            'sha224' => 'sha224',
            'sha256' => 'sha256',
            'sha512' => 'sha512',
            'md5'    => 'md5'
        );
        $options['hash_type'] = $this->ocm->form->getOptions($hash_options, 'none');
        return $options;
    }
    public function load_method() {
       $ext_lang = $this->load->language($this->ext_path);
       /* Some help lang modificaiton */
       $ext_lang['help_time'] = sprintf($this->language->get('help_time'), date('h:i:s A'));
       $ext_lang['help_date'] = sprintf($this->language->get('help_date'), date('Y-m-d'));
       
       $this->load->model($this->ext_path);
       $json = array();
       $data = array();
       $data = array_merge($data, $ext_lang);
       $tab_id = isset($this->request->post['tab_id']) ? $this->request->post['tab_id'] : 0;

       $this->load->model('localisation/language');
       $languages = $this->model_localisation_language->getLanguages();
       $data['languages'] = $this->ocm->url->getLangImage($languages);
       $data['language_id'] = $this->config->get('config_language_id');
       $data['export'] = $this->ocm->url->link($this->ext_path . '/export', '', true);
       $data['export_gw'] = $this->ocm->url->link($this->ext_path . '/exportGW', '', true);
       $data['method_data'] = $this->{$this->ext_key}->getData();
       $data['methods'] = $this->getMethodList($data['method_data']);
       //reset method_data with the  method that need to be loaded
       $data['method_data'] = array($this->{$this->ext_key}->getDataByTabId($tab_id));
       $options = $this->getFormBuilderOptions($data);
       $this->ocm->form->setLangs($ext_lang)->setOptions($options);
       $json['html'] = $tab_id ? $this->getFormData($data) : '';
       $json['tab_id'] = $tab_id;
       $this->response->addHeader('Content-Type: application/json');
       $this->response->setOutput(json_encode($json)); 
    }
    public function export() {
        $this->load->model($this->ext_path);
        $content = '';
        $filename = $this->meta['name'] . '.txt';
        $type = 'text/txt';
        $export = array();
        $export['method_data'] = $this->{$this->ext_key}->getData();
        $setting = $this->ocm->setting->getSetting($this->setting);
        $export = array_merge($export, $setting);
        $content = json_encode($export);
        $this->forceDownload($content, $filename, $type);
    }
    public function import() {
        $this->load->model('setting/setting');
        $this->load->model($this->ext_path);
        $success = false;
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && is_uploaded_file($this->request->files['file_import']['tmp_name']) && file_exists($this->request->files['file_import']['tmp_name'])) {
            $import_data = file_get_contents($this->request->files['file_import']['tmp_name']);
            if ($import_data) {
                $import_data = $this->{$this->ext_key}->getUnCompressedData($import_data);
                if (is_array($import_data) && (isset($import_data['method_data']) || isset($import_data[$this->meta['name']]))) {
                    $method_data = array();
                    if (isset($import_data['method_data']) && is_array($import_data['method_data'])) {
                        $method_data = $import_data['method_data'];
                        unset($import_data['method_data']);
                    }
                    /* Add prefix if imported from OC 2.x */
                    foreach ($import_data as $key => $value) {
                        if ($this->ocm->prefix && strpos($key, $this->ocm->prefix) === false) {
                            $import_data[$this->ocm->prefix . $key] = $value;
                        }
                    }
                    /* Save global */
                    $this->request->post = array_merge($import_data, $this->request->post);
                    $save = $this->ocm->setting->editSetting($this->setting);
                    $this->model_setting_setting->editSetting($save['key'], $save['value']);
                    /* add shipping methods */
                    foreach($method_data as $single) {
                        $single['method_data'] = json_encode($single['method_data']);
                        $this->{$this->ext_key}->addData($single);
                    }
                    $success = true;
                }
            }
        }
        if ($success) {
            $this->session->data['success'] = $this->language->get('text_success');
        } else {
            $this->session->data['warning'] = $this->language->get('error_import');
        }
    }
    public function exportGW() {
        $this->load->model($this->ext_path);
        $content = '';
        $filename = $this->meta['name'] . '.json';
        $type = 'application/json';
        if (isset($this->request->get['no'])) {
            $method_row = $this->{$this->ext_key}->getDataByTabId($this->request->get['no']);
            if ($method_row && !empty($method_row['method_data'])) {
                $method_data = $method_row['method_data'];
                // data
                $params = array();
                if (!empty($method_data['int_data']) && is_array($method_data['int_data'])) {
                    foreach ($method_data['int_data'] as $param) {
                        $params[] = array(
                            'name'  => $param['name'],
                            'value' => $param['value']
                        );
                    }
                }
                $method_data['int_data'] = $params;
                // header
                $params = array();
                if (!empty($method_data['header_data']) && is_array($method_data['header_data'])) {
                    foreach ($method_data['header_data'] as $param) {
                        $params[] = array(
                            'name'  => $param['name'],
                            'value' => $param['value']
                        );
                    }
                }
                $method_data['header_data'] = $params;
                // additioanl data
                $params = array();
                if (!empty($method_data['additional_data']) && is_array($method_data['additional_data'])) {
                    foreach ($method_data['additional_data'] as $param) {
                        $params[] = array(
                            'name'  => $param['name'],
                            'value' => $param['value']
                        );
                    }
                }
                $method_data['additional_data'] = $params;

                $fields = array(
                    "int_type"             => $method_data['int_type'],
                    "int_name"             => $method_data['int_name'],
                    "int_url"              => $method_data['int_url'],
                    "method_type"          => $method_data['method_type'],
                    "int_data"             => $method_data['int_data'],
                    "header_data"          => $method_data['header_data'],
                    "additional_data"      => $method_data['additional_data'],
                    //"fetch_key"            => $method_data['fetch_key'],
                    "hash"                 => !empty($method_data['hash']),
                    "hash_type"            => $method_data['hash_type'],
                    "hash_str"             => $method_data['hash_str'],
                    "hash_key"             => $method_data['hash_key'],
                    "hash_bin"             => !empty($method_data['hash_bin']),
                    "hash_base64"          => !empty($method_data['hash_base64']),
                    "success_method_type"  => $method_data['success_method_type'],
                    "int_success"          => $method_data['int_success'],
                    "js_url"               => $method_data['js_url'],
                    "information"          => $method_data['information']
                );
                $content = json_encode($fields, JSON_PRETTY_PRINT);
                $filename = $method_data['int_name'];
                if (!$filename) {
                    $filename = $this->meta['name'];
                }
                $filename .= '.json';
            }
        }
        $this->forceDownload($content, $filename, $type);
    }
    public function postAction() {
        $json = array();
        $this->load->model($this->ext_path);
        $this->load->language($this->ext_path);
        $order_id = isset($this->request->post['order_id']) ? $this->request->post['order_id'] : 0;
        $payment_id = isset($this->request->post['payment_id']) ? $this->request->post['payment_id'] : 0;
        $tab_id = isset($this->request->post['tab_id']) ? $this->request->post['tab_id'] : 0;
        $addon_name = isset($this->request->post['name']) ? $this->request->post['name'] : '';
        $action = isset($this->request->post['action']) ? $this->request->post['action'] : '';
        if($order_id && $payment_id && $tab_id && $addon_name && $action) {
            $method_data = $this->{$this->ext_key}->getDataByTabId($tab_id);
            $params = array();
            if ($method_data && $method_data['method_data']['additional_data']) {
                if (is_array($method_data['method_data']['additional_data'])) {
                    $params = $this->ocm->common->toCurlData($method_data['method_data']['additional_data']); // in future, only this line is required
                } else {
                    // legacy version 
                    $additional_data = html_entity_decode($method_data['method_data']['additional_data'], ENT_QUOTES, 'UTF-8');
                    $params = @json_decode($additional_data, true);
                    if (!is_array($params)) {
                        $params = array();
                    }
                    // end of legacy
                }
            }
            $path = $this->ocm->common->getExtPath('payment');
            $addon = $this->{$this->ext_key}->getAddon($addon_name, $path);
            if ($addon && (method_exists($addon, $action) || property_exists($addon, $action) || isset($addon->action))) {
                $result = $addon->{$action}($order_id, $payment_id, $params);
                if ($result === false) {
                    $json['message'] = $this->language->get('failed_order_action');
                } else {
                    $json['response'] = $result;
                    $json['message'] = $this->language->get('success_order_action');
                }
            } else {
                $json['message'] = $this->language->get('failed_order_action');
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 
    }
    /* admin events */ 
    public function onOrderView($route, &$data) {
        $tab = $this->_onOrderView($data);
        if ($tab['content']) {
            $xpayment_tab = array(
                'code'    => $this->meta['name'],
                'title'   => $tab['title'],
                'content' => $tab['content']
            );
            array_push($data['tabs'], $xpayment_tab);
        }
    }
    public function _onOrderView($data) {
        $this->load->model($this->ext_path);
        $content = $title = '';
        $tab_id = $this->{$this->ext_key}->getTabIdByOrderId($data['order_id']);
        if ($tab_id) {
            $method_data = $this->{$this->ext_key}->getDataByTabId($tab_id);
            if ($method_data) {
                $path = $this->ocm->common->getExtPath('payment');
                $addon_name = $method_data['method_data']['int_name'];
                $addon = $this->{$this->ext_key}->getAddon($addon_name, $path);
                if ($addon && (method_exists($addon, 'order') || property_exists($addon, 'order') || isset($addon->order))) {
                    $action_url = $this->ocm->url->link($this->ext_path . $this->ocm->divider . 'postAction', '', true);
                    $content = $addon->order($data['order_id'], $tab_id, str_replace('&amp;', '&', $action_url));
                }
                if ($content) {
                    $title = $data['payment_method'];
                }
            }
        }
        return array(
            'title' => $title,
            'content' => $content
        );
    }
}