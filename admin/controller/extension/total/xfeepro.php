<?php
class ControllerExtensionTotalXfeepro extends Controller {
     use OCM\Traits\Back\Controller\Common;
     use OCM\Traits\Back\Controller\Crud;
     use OCM\Traits\Back\Controller\Product;
     use OCM\Traits\Back\Controller\Util;
     private $ext_path;
     private $ext_key;
     private $error = array();
     private $ocm;
     private $meta = array(
        'id'       => '14078',
        'type'     => 'total',
        'name'     => 'xfeepro',
        'path'     => 'extension/total/',
        'title'    => 'X-Feepro',
        'version'  => '4.0.1',
        'ocmod'    => false,
        'event'    => true
    );
    /* Config with default values  Special keyword __LANG__ denotes array of languages e.g 'name' => array('__LANG__' => 'xyz') */
    private $setting = array(
        'xfeepro_status'     => '',
        'xfeepro_sort_order' => '',
        'xfeepro_map_api'    => '',
        'xfeepro_address'    => 'p',
        'xfeepro_disable_cache' => '',
        'xfeepro_admin_menu' => '',
        'xfeepro_group'      => array(),
        'xfeepro_group_name' => array(),
        'xfeepro_debug'      => ''
    );
    private $events = array();
    private $tables = array(
        'xfeepro' => array (
            array('name'=> 'sort_order', 'option' => 'int(8) NULL')
        )
    );
    public function __construct($registry) {
        parent::__construct($registry);
        $this->ocm = new OCM\Back($registry, $this->meta);
        $this->ext_path = $this->meta['path'] . $this->meta['name'];
        $this->ext_key = 'model_' . str_replace('/', '_', $this->ext_path);
    }
    
    public function index() {
        $this->load->language($this->ext_path);
        $ext_lang = $this->load->language($this->ext_path);
        $this->load->model($this->ext_path);
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/setting');

        /* Some help lang modificaiton */
        $ext_lang['help_time'] = sprintf($this->language->get('help_time'), date('h:i:s A'));
        $ext_lang['help_date'] = sprintf($this->language->get('help_date'), date('Y-m-d'));

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

        $this->load->model('localisation/language');
        $languages = $this->model_localisation_language->getLanguages();
        $data['languages'] = $this->ocm->url->getLangImage($languages);
        $data['language_id'] = $this->config->get('config_language_id');

        $data['groups_count'] = 10;

        $data['method_data'] = $this->{$this->ext_key}->getData();

        /* All required options */
        $options = array();
        
        $this->load->model('localisation/tax_class'); 
        $tax_classes = $this->model_localisation_tax_class->getTaxClasses();
        array_unshift($tax_classes, array('tax_class_id' => -1,'name' => $this->language->get('text_tax_products_wise')));
        array_unshift($tax_classes, array('tax_class_id' => 0,'name' => $this->language->get('text_none')));
        $options['tax_class_id'] = $this->ocm->form->getOptions($tax_classes, 'tax_class_id');

        $this->load->model('localisation/geo_zone');
        $geo_zones = $this->model_localisation_geo_zone->getGeoZones();
        $options['geo_zone'] = $this->ocm->form->getOptions($geo_zones, 'geo_zone_id');

        $this->load->model('setting/store');
        $stores = $this->model_setting_store->getStores();
        array_unshift($stores, array('store_id' => 0,'name' => $this->language->get('text_store_default')));
        $options['store'] = $options['estimator_store'] = $this->ocm->form->getOptions($stores, 'store_id');

        $this->load->model('localisation/currency');
        $currencies = $this->model_localisation_currency->getCurrencies();
        $options['currency'] = $this->ocm->form->getOptions($currencies, 'currency_id');
      
        $cg_path = (VERSION >= '2.1.0.1') ? 'customer' : 'sale';
        $this->load->model($cg_path . '/customer_group');
        $customer_groups = $this->{'model_' . $cg_path . '_customer_group'}->getCustomerGroups();
        $customer_groups[] = array('customer_group_id' => 0, 'name' => $this->language->get('text_guest_checkout'));
        $options['customer_group'] = $this->ocm->form->getOptions($customer_groups, 'customer_group_id');

        $this->load->model('localisation/country');
        $countries = $this->model_localisation_country->getCountries();
        $options['country'] = $this->ocm->form->getOptions($countries, 'country_id');

        $options['payment'] = $this->ocm->misc->getPaymentMethods($data['language_id']);
        $options['shipping'] = $this->ocm->misc->getShippingMethods($data['language_id'], $geo_zones);

        $status_options = array('1' => $data['text_enabled'], '0' => $data['text_disabled']);
        $options['status'] = $options['debug'] = $this->ocm->form->getOptions($status_options, 'none');

        $address_options = array('p' => $data['text_address_billing'], 's' => $data['text_address_shipping']);
        $options['address'] = $this->ocm->form->getOptions($address_options, 'none');

        $inc_exc_options = array('inclusive' => $data['text_rule_inclusive'], 'exclusive' => $data['text_rule_exclusive']);
        $inc_exc_options = $this->ocm->form->getOptions($inc_exc_options, 'none');
        $options['city_rule'] = $options['coupon_rule'] = $options['postal_rule'] = $options['customer_rule'] = $inc_exc_options; 

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
        $options['category'] = $options['product'] = $options['option'] = $options['attribute'] = $options['manufacturer_rule'] = $options['location_rule'] = $product_rule_options; 

        $rate_type_options = array(
            'flat'            => $data['text_rate_flat'],
            'quantity'        => $data['text_rate_quantity'],
            'weight'          => $data['text_rate_weight'],
            'volume'          => $data['text_rate_volume'],
            'dimensional'     => $data['text_dimensional_weight'],
            'volumetric'      => $data['text_volumetric_weight'],
            'sub'             => $data['text_rate_sub_total'],
            'total'           => $data['text_rate_total'],
            'sub_coupon'      => $data['text_rate_sub_coupon'],
            'total_coupon'    => $data['text_rate_total_coupon'],
            'grand'           => $data['text_grand_total'],
            'grand_wtax'      => $data['text_percent_grand_wo_tax'],
            'no_product'      => $data['text_no_of_product'],
            'no_category'     => $data['text_no_of_category'],
            'no_manufacturer' => $data['text_no_of_manufacturers'],
            'no_location'     => $data['text_no_of_location'],
            'distance'        => $data['text_rate_type_distance'],
            'equation'        => $data['text_type_equation']
        );
        $options['rate_type'] = $this->ocm->form->getOptions($rate_type_options, 'none');

        $percent_options = array(
            'sub'             => $data['text_percent_sub_total'],
            'total'           => $data['text_percent_total'],
            'grand'           => $data['text_grand_total'],
            'grand_wtax'      => $data['text_percent_grand_wo_tax'],
            'sub_shipping'    => $data['text_percent_sub_total_shipping'],
            'total_shipping'  => $data['text_percent_total_shipping'],
            'sub_special'     => $data['text_percent_sub_special'],
            'total_special'   => $data['text_total_special'],
            'sub_shipping_plus'    => $data['text_percent_sub_total_shipping_plus'],
            'total_shipping_plus'  => $data['text_percent_total_shipping_plus'],
            'total_special'    => $data['text_percent_total_special'],
            'special'          => $data['text_percent_special'],
            'shipping'         => $data['text_percent_shipping'],
            'vouchers'         => $data['text_percent_vouchers'],
            'price_self'       => $data['text_self_sub'],
            'price_self_tax'   => $data['text_self_total'],
            'original'         => $data['text_original_total']
        );
        $options['rate_percent'] = $this->ocm->form->getOptions($percent_options, 'none');

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

        $final_cost = array(
            'single' => $data['text_final_single'],
            'cumulative' => $data['text_final_cumulative']
        );
        $options['rate_final'] = $this->ocm->form->getOptions($final_cost, 'none');

        $modes = array(
            '0' => $data['text_mode_and'],
            '1' => $data['text_mode_or']
        );
        $options['product_or'] = $this->ocm->form->getOptions($modes, 'none');

        $custom_fields = $this->getCustomFields();
        $options['custom'] = $this->ocm->form->getOptions($custom_fields, 'custom_field_value_id');

        $group = array();
        $group[] = array(
            'name' => 'None',
            'value' => 0
        );
        for ($i=1; $i <= $data['groups_count']; $i++) {
            $group[] = array(
                'name' => 'Group' . $i,
                'value' => $i
            );
        }
        $options['group'] = $group;
        /* set form data */
        $this->ocm->form->setLangs($ext_lang)->setOptions($options);

        $group_modes = array(
            'no_group' => $this->language->get('text_no_grouping'),
            'lowest'   => $this->language->get('text_lowest'),
            'highest'  => $this->language->get('text_highest'),
            'average'  => $this->language->get('text_average'),
            'sum'      => $this->language->get('text_sum')
        );
        $data['group_modes'] = $group_modes;

        $placeholders = array(
            '{subTotal}'                => $this->language->get('text_eq_cart_total'),
            '{subTotalWithTax}'         => $this->language->get('text_eq_cart_total_tax'),
            '{weight}'                  => $this->language->get('text_eq_cart_weight'),
            '{quantity}'                => $this->language->get('text_eq_cart_qnty'),
            '{volume}'                  => $this->language->get('text_eq_cart_vol'),
            '{dimensional}'             => $this->language->get('text_eq_dimension'),
            '{volumetric}'              => $this->language->get('text_eq_volumetric'),
            '{subTotalAsPerProductRule}' => $this->language->get('text_eq_method_total'),
            '{subTotalWithTaxAsPerProductRule}' => $this->language->get('text_eq_method_total_tax'),
            '{weightAsPerProductRule}'  => $this->language->get('text_eq_method_weight'),
            '{quantityAsPerProductRule}'=> $this->language->get('text_eq_method_qnty'),
            '{volumeAsPerProductRule}'  => $this->language->get('text_eq_method_vol'),
            '{xfeepro}'                 => $this->language->get('text_eq_xfeepro'),
            '{shippingCost}'            => $this->language->get('text_eq_shipping'),
            '{vouchers}'                => $this->language->get('text_percent_vouchers'),
            '{special}'                 => $this->language->get('text_percent_special'),
            '{modifier}'                => $this->language->get('text_eq_modifier'),
            '{noOfProduct}'             => $this->language->get('text_eq_no_product'),
            '{noOfCategory}'            => $this->language->get('text_eq_no_cat'),
            '{noOfCategoryAsPerProductRule}' => $this->language->get('text_eq_no_cat_per_rule'),
            '{noOfCategorySpecified}'      => $this->language->get('text_eq_no_cat_method'),
            '{noOfLocation}'            => $this->language->get('text_eq_no_loc'),
            '{noOfManufacturer}'        => $this->language->get('text_eq_no_man'),
            '{noOfProductAsPerProductRule}' => $this->language->get('text_eq_no_product_method'),
            '{noOfFreeProduct}'         => $this->language->get('text_eq_no_free_product'),
            '{noOfBlock}'               => $this->language->get('text_eq_no_block'),
            '{blockPriceAsc}'           => $this->language->get('text_eq_block_asc'),
            '{blockPriceDesc}'          => $this->language->get('text_eq_block_desc'),
            '{priceAscByNoOfBlock}'     => $this->language->get('text_eq_no_block_asc'),
            '{priceDescByNoOfBlock}'    => $this->language->get('text_eq_no_block_desc'),
            '{couponValue}'             => $this->language->get('text_eq_coupon'),
            '{rewardValue}'             => $this->language->get('text_eq_reward'),
            '{grandTotal}'              => $this->language->get('text_grand_total'),
            '{grandWithoutTax}'         => $this->language->get('text_eq_grand_wo_tax'),
            '{roundedGrandTotal}'       => $this->language->get('text_eq_grand_round'),
            '{highest}'                 => $this->language->get('text_eq_highest'),
            '{lowest}'                  => $this->language->get('text_eq_lowest'),
            '{highestQnty}'             => $this->language->get('text_eq_highest_qnty'),
            '{lowestQnty}'              => $this->language->get('text_eq_lowest_qnty'),
            '{average}'                 => $this->language->get('text_eq_average'),
            '{distance}'                => $this->language->get('text_rate_type_distance'),
            '{alreadyAppliedXfeepro}'   => $this->language->get('text_applied_sub'),
            '{nonMethodSub}'            => $this->language->get('text_eq_non_method_sub'),
            '{nonMethodQnty}'           => $this->language->get('text_eq_non_method_qnty'),
            '{actualSubTotal}'          => $this->language->get('text_original_total'),
            '{xlevel}'                  => $this->language->get('text_xlevel_discount'),
            '{xdiscount}'               => $this->language->get('text_xdiscount_discount'),
            '{minHeight}, {maxHeight}, {sumHeight}' => $this->language->get('text_eq_height'),
            '{minWidth}, {maxWidth}, {sumWidth}'    => $this->language->get('text_eq_width'),
            '{minLength}, {maxLength}, {sumLength}' => $this->language->get('text_eq_length'),
            '{productWidth}, {productHeight}, {productLength}, {productWeight}, {productQuantity}, {productPrice}, {productVolume}'    => $this->language->get('text_eq_all'),
            '{anyProductWidth}, {anyProductHeight}, {anyProductLength}, {anyProductWeight}, {anyProductQuantity}, {anyProductPrice}, {anyProductSpecialPrice}, {anyProductVolume}' => $this->language->get('text_eq_any')
        );

        $more_help = array();
        $eq_placeholders = $this->getPlaceholderList($placeholders);
        $more_help['equation'] = $eq_placeholders . $this->ocm->misc->getHelpTag($data['more_equation']);
        $more_help['zone'] = $data['more_zone'];
        $more_help['dimensional_factor'] = $data['more_dimensional_factor'];
        $data['more_help'] = json_encode($more_help);
        
        $data['cg_path'] = $cg_path;
        $data['oc_4'] = VERSION > '4.0.0.0';
        $data['divider'] = $this->ocm->divider;
        $data['ocm_base_url'] = $this->ocm->common->getOcmBaseUrl('admin', $this->meta['name']);
        $data['global'] = $this->getConfigForm($data);
        $data['tpl'] = json_encode(array(
            'method'     =>  $this->getFormData($data, true)
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
    public function export() {
        $this->load->model($this->ext_path);
        $content = '';
        $filename = $this->meta['name'] . '.txt';
        $type = 'text/x-csv';
        if (isset($this->request->get['no'])) {
            $method_row = $this->{$this->ext_key}->getDataByTabId($this->request->get['no']);
            if ($method_row) {
                $fields = array('{start}','{end}','{cost}','{block}','{partial}');
                if (empty($method_row['method_data']['ranges'])) {
                    $method_row['method_data']['ranges'] = array();
                }
                $content = $this->ocm->misc->getCSV($method_row['method_data']['ranges'], $fields);
                $filename = $method_row['method_data']['display'];
                if (!$filename) {
                    $filename = $this->getLangField($method_data, 'name');
                }
                $filename .= '.csv';
            }
        } else {
            $export = array();
            $export['method_data'] = $this->{$this->ext_key}->getData();
            $setting = $this->ocm->setting->getSetting($this->setting);
            $export = array_merge($export, $setting);
            $content = json_encode($export);
            $type = 'text/txt';
        }
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

                    /* add methods */
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
    public function importCSV(){
        ini_set('auto_detect_line_endings', true);
        $this->load->language($this->ext_path);
        $json = array();
        if (!empty($this->request->files['file']['name'])) {
            $filename = basename(preg_replace('/[^a-zA-Z0-9\.\-\s+]/', '', html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8')));
            $allowed =  array('csv');
            if (!in_array(substr(strrchr($filename, '.'), 1), $allowed)) {
                $json['error'] = $this->language->get('error_filetype');
            }
            if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
                $json['error'] = $this->language->get('error_partial');
            }
        } else {
            $json['error']=$this->language->get('error_upload');  
        }

        $heading = array('start' => 0,'end' => 0, 'cost' => 0,'block' => 0, 'partial' => 0);
        $heading_found = false;

        if (!$json && is_uploaded_file($this->request->files['file']['tmp_name']) && file_exists($this->request->files['file']['tmp_name'])) {
            $isFound = false;
            $json['data']=array();
            if (($handle = fopen($this->request->files['file']['tmp_name'], "r")) !== FALSE) {
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    if (!$heading_found && strpos($data[0], '{') !== false && strpos($data[0], '}') !== false) {
                        foreach ($data as $index => $field) {
                            foreach ($heading as $key => $value) {
                                if ('{'.$key.'}' == trim($field)) {
                                    $heading[$key] = $index;
                                    $heading_found = true;
                                }
                            }
                        }
                        continue;
                    }
                    if (!$heading_found) continue;
                    $row = array();
                    foreach ($heading as $key => $index) {
                        $row[$key] = isset($data[$index]) ? $data[$index] : '';
                    }
                    if ($row) {
                        $json['data'][] = $row; 
                        $isFound = true;
                    }
                }
                fclose($handle);
            }
            if (!$isFound)$json['error'] = $this->language->get('error_no_data');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json)); 
    }
    private function getConfigForm($data) {
        /* Set base name for form inputs */
        $setting = $this->ocm->setting->getSetting($this->setting, $data['languages']);
        $this->ocm->form->setBasename($this->ocm->prefix . 'xfeepro_', 'prefix');
        $this->ocm->form->setPreset($setting)->setIDPostfix('');

        $return = '';
        $tabs = array(
            'global-general' => $data['tab_global_general'],
            'global-group' => $data['tab_group_option'],
            'global-export' => $data['tab_import_export'],
            'global-help' => $data['tab_help']
        );
        $return .= $this->ocm->misc->getTabs('global-tab', $tabs);
        $return .= '<div class="tab-content">';

        $return .= '<div class="tab-pane active" id="global-general">';
        $return .= $this->ocm->form->get('input', array('name' => 'sort_order', 'help' => $data['help_module_sort_order']));
        $return .= $this->ocm->form->get('input', 'map_api');
        $return .= $this->ocm->form->get('select', 'address');
        $return .= $this->ocm->form->get('checkbox', array('name' => 'admin_menu', 'label' => $data['text_admin_menu']));
        $return .= $this->ocm->form->get('checkbox', array('name' => 'disable_cache', 'label' => $data['text_disable_cache']));
        $return .= $this->ocm->form->get('select', array('name' => 'status', 'title' => $data['entry_module_status'], 'help' => $data['help_module_status']));
        $return .= $this->ocm->form->get('select', 'debug');
        $return .= '</div>';

        $return .= '<div class="tab-pane" id="global-group">';
        $table_body = $this->getGroups($data, $setting);
        $table_headings = array(
            array(
                'title'   => $data['text_group_id']
            ),
            array(
                'title'   => $data['text_group_type']
            ),
            array(
                'title'   => $data['entry_group_name'],
                'help'  => $data['help_group_name']
            )
        );
        $element = $this->ocm->misc->getTableSkeleton($table_headings, $table_body);
        $return .= $this->ocm->form->get('bare', array('name' => 'method_group', 'element' => $element, 'label_col' => 12));
        $return .= '</div>';

        $return .= '<div class="tab-pane" id="global-export">';
        $element = '<input type="file" class="form-control ocm-import" id="input-import" accept="text/txt" name="file_import" />&nbsp<button type="submit" form="form-ocm" data-toggle="tooltip" name="action" value="import" class="btn btn-primary">'.$data['text_import'].'</button>';
        $return .= $this->ocm->form->get('bare', array('name' => 'import', 'element' => $element));
        $element = '<a href="' . $data['export'] . '" target="_blank" class="btn btn-primary">' . $data['text_export'] . '</a>';
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
        $this->load->model($data['cg_path'] . '/customer');
        if ($new_tab) {
            $data['method_data'] = array(
                array('tab_id' => '__INDEX__', 'method_data' => array())
            );
        }
        /* Set base name for form inputs */
        $this->ocm->form->setBasename($this->meta['name']);
        $range_types = array('quantity', 'weight', 'volume', 'dimensional', 'volumetric', 'sub', 'total', 'sub_coupon', 'total_coupon', 'grand', 'grand_wtax', 'no_product', 'no_category', 'no_manufacturer', 'no_location', 'distance', 'equation');
        $non_total_types = array('flat','quantity', 'weight', 'volume', 'dimensional', 'volumetric', 'no_product', 'no_category', 'no_manufacturer', 'no_location', 'distance', 'equation');

        $fields_lang = array(
            'name'  => 'Untitled Item'
        );
        $fields_all = array(
            'store'          => 'store_all',
            'geo_zone'       => 'geo_zone_all',
            'zone'           => 'zone_all',
            'country'        => 'country_all', 
            'currency'       => 'currency_all',
            'customer_group' => 'customer_group_all',
            'payment'        => 'payment_all',
            'shipping'       => 'shipping_all',
            'city'           => 'city_all',
            'postal'         => 'postal_all',
            'coupon'         => 'coupon_all',
            'days'           => 'days_all',
            'customers'      => 'customer_all',
            'custom'         => 'custom_all'
        );
        $default_values = $this->getDefaultValues();
        $return = '';
        foreach($data['method_data'] as $single_method) {
            $no_of_tab   = $single_method['tab_id'];
            $method_data = $single_method['method_data'];
            $method_data = $this->resetEmptyAll($method_data, $fields_all);
            $method_data = array_merge($default_values, $method_data);
            $method_data = $this->setDefaultByLangs($method_data, $data['languages'], $fields_lang);
            if (!$method_data['display']) {
                $method_data['display'] = $this->getLangField($method_data, 'name');
            }
            if ($no_of_tab == '__INDEX__') {
                $method_data['method_specific'] = 1;
            }

            $this->ocm->form->setPreset($method_data)->setIDPostfix($no_of_tab);

            $return .= '<div id="ocm-method-'.$no_of_tab.'" class="tab-pane xfeepro ocm-method">';
            $return .= '<div class="ocm-action-btn">';
            $return .= $this->ocm->misc->getButton(array('type' => 'success', 'help'=> $data['text_method_save'], 'class' => 'btn-ocm-save btn-sm', 'icon' => 'fa-save'));
            $return .= $this->ocm->misc->getButton(array('type' => 'warning', 'help'=> $data['text_method_copy'], 'class' => 'btn-ocm-copy btn-sm', 'icon' => 'fa-copy'));
            $return .= $this->ocm->misc->getButton(array('type' => 'danger', 'help'=> $data['text_method_remove'], 'class' => 'btn-ocm-delete btn-sm', 'icon' => 'fa-trash fa-trash-alt'));
            $return .= '</div>';

            $return .= $this->ocm->form->get('input', 'display');

            $return .= $this->ocm->misc->getLangTabs('language_' . $no_of_tab, $data['languages']);

            $active = ' active';
            $return .= '<div class="tab-content ocm-common-lang">';
            foreach ($data['languages'] as $language) { 
                $language_id = $language['language_id'];
                $return .= '<div class="tab-pane' . $active . '" id="language_' . $no_of_tab . '-' . $language_id . '">';
                $param = array(
                    'name'  => 'name[' . $language_id . ']',
                    'required' => true
                );
                $return .= $this->ocm->form->get('input', $param);
                $return .= '</div>';
                $active = '';
            }
            $return .= '</div>';

            $tabs = array(
                'common_' . $no_of_tab      => $data['tab_general'],
                'criteria_' . $no_of_tab    => $data['tab_criteria_setting'],
                'catprod_' . $no_of_tab     => $data['tab_category_product'],
                'price_' . $no_of_tab       => $data['tab_price_setting'],
                'event_' . $no_of_tab => $data['tab_condition_event']
            );
            $return .= $this->ocm->misc->getTabs('method-tab' . $no_of_tab, $tabs);

            $return .= '<div class="tab-content">';
            $return .= '<div class="tab-pane active" id="common_' . $no_of_tab . '">';
            $element = '<div class="ocm-id">' . $no_of_tab . '</div>';
            $return .= $this->ocm->form->get('bare', array('name' => 'id', 'element' => $element));
            $return .= $this->ocm->form->get('checkbox', 'inc_vat');
            $return .= $this->ocm->form->get('checkbox', array('name' => 'fake', 'label' => $data['text_fake']));
            $return .= $this->ocm->form->get('checkbox', array('name' => 'hidden', 'label' => $data['text_hidden']));
            $return .= $this->ocm->form->get('select', 'tax_class_id');
            $return .= $this->ocm->form->get('input', 'sort_order');
            $return .= $this->ocm->form->get('select', 'status');
            $return .= $this->ocm->form->get('select', 'group');
            $return .= '</div>';
            $return .= '<div class="tab-pane" id="criteria_'.$no_of_tab.'">';

            $zones = $this->getZones($method_data['country']);
            $zones = $this->ocm->form->getOptions($zones, 'zone_id');

            $return .= $this->ocm->form->get('checkgroup', 'store[]');
            $return .= $this->ocm->form->get('checkgroup', array('name' => 'geo_zone[]', 'search' => true));
            $return .= $this->ocm->form->get('checkgroup', 'customer_group[]');
            $return .= $this->ocm->form->get('checkgroup', array('name' => 'country[]', 'search' => true));
            $return .= $this->ocm->form->get('checkgroup', array('name' => 'zone[]', 'options' => $zones, 'search' => true));
            $return .= $this->ocm->form->get('checkgroup', 'currency[]');
            $return .= $this->ocm->form->get('checkgroup', 'payment[]');
            $return .= $this->ocm->form->get('checkgroup', 'shipping[]');
            $return .= $this->ocm->form->get('checkgroup', array('name' => 'custom[]', 'search' => true));

            /* cunstomers */
            $customers = array();
            foreach ($method_data['customers'] as $customer_id) {
                $customer_info = $this->{'model_' . $data['cg_path'] . '_customer'}->getCustomer($customer_id);
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
            /* end of postal */
            $return .= $this->ocm->form->get('checkbox', array('name' => 'logged', 'label' => $data['text_logged']));
            $return .= $this->ocm->form->get('checkbox', array('name' => 'first', 'label' => $data['text_first']));

            $return .= $this->ocm->form->get('checkgroup', 'days[]');
            $return .= $this->ocm->form->get('range', array('name' => 'time', 'type' => 'time'));
            $return .= $this->ocm->form->get('range', array('name' => 'date', 'type' => 'date'));
            $return .= $this->hookRules($method_data, $data);

            $return .= '</div>';
            $return .= '<div class="tab-pane" id="catprod_'.$no_of_tab.'">';
            $return .=$this->ocm->misc->getHelpTag($data['text_product_rules']); 
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
                        'name'            => $manufacturer_info['name']
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

            /* Locations  */
            $locations = array();
            foreach ($method_data['location'] as $location) {
                if ($location) {
                    $locations[] = array(
                        'value'  => $location,
                        'name'   => $location
                    );
                }
            }

            $visible = (int)$method_data['location_rule'] > 1;
            $return .= $this->ocm->form->get('select', 'location_rule');
            $param = array(
                'name'  => 'location[]',
                'options' => $locations,
                'attr'  => 'location_rule',
                'visible' => $visible
            );
            $return .= $this->ocm->form->get('autofill', $param);

            $return .=$this->ocm->misc->getHelpTag($data['text_product_rules_advanced']);
            $return .= $this->ocm->form->get('select', 'product_or');

            $return .= $this->ocm->form->get('checkbox', array('name' => 'ingore_product_rule', 'label' => $data['text_ingore_product']));
            $return .= '</div>';

            $return .= '<div class="tab-pane" id="price_'.$no_of_tab.'">';

            $return .= $this->ocm->form->get('select', 'rate_type');
            $visible = $method_data['rate_type']=='dimensional' || $method_data['rate_type']=='volumetric';
            $return .= $this->ocm->form->get('input', array('name' =>'dimensional_factor', 'visible' => $visible, 'class' => 'rate_type ocm-hide volumetric dimensional'));
            $return .= $this->ocm->form->get('checkbox', array('name' =>'dimensional_overfule', 'visible' => $visible, 'class' => 'rate_type ocm-hide volumetric dimensional'));

            $return .= $this->ocm->form->get('checkbox', array('name' => 'method_specific', 'label' => $data['text_method_specific']));

            $return .= $this->ocm->form->get('input', array('name' => 'cost', 'visible' => $method_data['rate_type']=='flat', 'class' => 'rate_type ocm-hide flat'));

            $element = $this->getRanges($method_data, $data, $no_of_tab);
            $return .= $this->ocm->form->get('bare', array('name' => 'unit_range', 'label_col' => 0, 'element' => $element, 'visible' => $method_data['rate_type'] != 'flat', 'class' => 'rate_type ocm-hide ' . implode(' ', $range_types)));

            $splits = array('additional', 'additional_per', 'additional_limit');
            $element = $this->ocm->misc->getSplittedInput($splits, $method_data, $data, $this->meta['name']);
            $return .= $this->ocm->form->get('bare', array('name' => 'additional', 'element' => $element, 'visible' => $method_data['rate_type'] != 'flat', 'class' => 'rate_type ocm-hide ' . implode(' ', $range_types)));

            $visible =  in_array($method_data['rate_type'], $non_total_types); 
            $return .= $this->ocm->form->get('range', array('name' =>'order_total', 'visible' => $visible, 'class' => 'rate_type ocm-hide ' . implode(' ', $non_total_types)));
            
            $visible = $method_data['rate_type'] != 'weight' && $method_data['rate_type'] != 'product';
            $return .= $this->ocm->form->get('range', array('name' =>'weight', 'visible' => $visible, 'class' => 'rate_type ocm-hide flat ' . implode(' ', array_diff($range_types, array('weight')))));
            
            $visible = $method_data['rate_type'] != 'quantity' && $method_data['rate_type'] != 'product';
            $return .= $this->ocm->form->get('range', array('name' =>'quantity', 'visible' => $visible, 'class' => 'rate_type ocm-hide flat ' . implode(' ', array_diff($range_types, array('quantity')))));

            $return .= $this->ocm->form->get('input', array('name' => 'cart_adjust', 'visible' => $method_data['rate_type'] != 'flat', 'class' => 'rate_type ocm-hide  ' . implode(' ', $range_types)));

            $visible = $method_data['rate_type'] != 'flat' && $method_data['rate_type'] != 'product';
            $return .= $this->ocm->form->get('select', array('name' => 'rate_final', 'visible' => $visible, 'class' => 'rate_type ocm-hide ' . implode(' ', $range_types)));

            $return .= $this->ocm->form->get('select', 'rate_percent');
            
            $splits = array('rate_min', 'rate_max', 'rate_add');
            $element = $this->ocm->misc->getSplittedInput($splits, $method_data, $data, $this->meta['name']);
            $return .= $this->ocm->form->get('bare', array('name' => 'price_adjustment', 'element' => $element, 'visible' => $method_data['rate_type'] != 'flat', 'class' => 'rate_type ocm-hide ' . implode(' ', $range_types)));

            $return .= $this->ocm->form->get('textarea', 'equation');
            $return .= '</div>';

            $return .= '<div class="tab-pane" id="event_'.$no_of_tab.'">';
            
            $return .= $this->ocm->form->get('checkbox', array('name' => 'disable', 'label' => $data['text_disable']));
            $return .= $this->ocm->form->get('checkbox', array('name' => 'disable_other', 'label' => $data['text_disable_other']));
            // Event On Active
            $hides = array();
            foreach ($method_data['hide'] as $hide_tab_id) {
                if (isset($data['methods'][$hide_tab_id])) {
                    $hides[] = array(
                        'value' => $hide_tab_id,
                        'name'  => $data['methods'][$hide_tab_id]
                    );
                }
            }
            $param = array(
                'name'  => 'hide[]',
                'attr' => 'hide',
                'options' => $hides,
                'class' => 'ocm-visible'
            );
            $return .= $this->ocm->form->get('autofill', $param);

            // Event On Inactive
            $hides = array();
            foreach ($method_data['hide_inactive'] as $hide_tab_id) {
                if (isset($data['methods'][$hide_tab_id])) {
                    $hides[] = array(
                        'value' => $hide_tab_id,
                        'name'  => $data['methods'][$hide_tab_id]
                    );
                }
            }
            $param = array(
                'name'  => 'hide_inactive[]',
                'attr' => 'hide',
                'options' => $hides,
                'class' => 'ocm-visible'
            );
            $return .= $this->ocm->form->get('autofill', $param);
            $return .= '</div>';
            /* End of event tab */
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
            'product_category'  => array(),
            'product_product'   => array(),
            'store'             => array(),
            'currency'          => array(),
            'payment'           => array(),
            'shipping'          => array(),
            'manufacturer'      => array(),
            'days'              => array(),
            'ranges'            => array(),
            'products'          => array(),
            'country'           => array(),
            'zone'              => array(),
            'name'              => array(),
            'product_option'    => array(),
            'product_attribute' => array(),
            'hide'              => array(),
            'hide_inactive'     => array(),
            'location'          => array(),
            'customers'         => array(),
            'custom'            => array(),
            /* string/numberic rules*/
            'dimensional_factor'   => '500',
            'dimensional_overfule' => '',
            'customer_group_all'   => '',
            'geo_zone_all'         => '',
            'country_all'          => '',
            'zone_all'             => '',
            'store_all'            => '',
            'manufacturer_all'     => '',
            'postal_all'           => '',
            'coupon_all'           => '',
            'currency_all'         => '',
            'payment_all'          => '',
            'shipping_all'         => '',
            'city_all'             => '',
            'days_all'             => '',
            'customer_all'         => '',
            'custom_all'           => '',
            'city'                 => '',
            'postal'               => '',
            'coupon'               => '',
            'city_rule'           => 'inclusive',
            'postal_rule'         => 'inclusive',
            'coupon_rule'         => 'inclusive',
            'customer_rule'       => 'inclusive',
            'time_start'          => '',
            'time_end'            => '',
            'rate_final'          => 'single',
            'rate_percent'        => 'sub',
            'rate_min'            => '',
            'rate_max'            => '',
            'rate_add'            => '',
            'location_rule'       => '',
            'manufacturer_rule'   => '',
            'additional'          => '',
            'additional_per'      => '',
            'additional_limit'    => '',
            'group'               => 'no_group',
            'order_total_start'   => '',
            'order_total_end'     => '',
            'weight_start'        => '',
            'weight_end'          => '',
            'quantity_start'    => '',
            'quantity_end'      => '',
            'equation'          => '',
            'tax_class_id'      => '',
            'option'            => 1,
            'attribute'         => 1,
            'sort_order'        => '',
            'status'            => 1,
            'category'          => '',
            'product'           => '',
            'rate_type'         => 'flat',
            'cost'              => '',
            'display'           => 'Untitled Item',
            'ingore_product_rule' => '',
            'product_or'        => 1,
            'method_specific'   => '',
            'date_start'        => '',
            'date_end'          => '',
            'inc_vat'           => '',
            'fake'              => '',
            'hidden'            => '',
            'first'             => '',
            'logged'             => '',
            'disable'           => '',
            'disable_other'     => '',
            'cart_adjust'       => 0
        );
    }
    private function getRanges($method_data, $data, $no_of_tab) {
        $fields = array('start', 'end', 'cost', 'block', 'partial');
        $return = '';
        $return .='<div class="ocm-range-container">
                    <div class="ocm-range-option">';
        $return .= '   <div class="price-range">'.$data['entry_unit_range'].'</div>';
        $return .= '   <a target="_blank" href="'.$data['export'].'&no='.$no_of_tab.'" class="btn btn-info export-btn btn-sm range-btn" role="button">'.$data['text_export'].'</a>';
        $return .=     $this->ocm->misc->getButton(array('type' => 'danger', 'title'=> $data['text_delete_all'], 'class' => 'ocm-row-remove-all btn-sm range-btn'));
        $return .=     $this->ocm->misc->getButton(array('type' => 'primary', 'title'=> $data['text_csv_import'], 'class' => 'range-import-btn btn-sm range-btn'));
        $return .= '</div>';

        $ranges = array();
        foreach ($method_data['ranges'] as $counter => $range) {
            foreach ($fields as $field) {
                if (!isset($range[$field])) {
                    $range[$field] = '';
                }
            }
            $ranges[] = $range;
        }
        $table_body = '';
        foreach ($ranges as $counter => $range) {
            $table_body .= '<tr rel="'.$counter.'">' 
                            .'<td class="text-left"><input size="15" type="text" class="form-control" name="xfeepro[ranges]['.$counter.'][start]" value="' . $range['start'] . '" /></td>'
                            .'<td class="text-left"><input size="15" type="text" class="form-control" name="xfeepro[ranges]['.$counter.'][end]" value="' . $range['end'] . '" /></td>'
                            .'<td class="text-left"><input size="15" type="text" class="form-control" name="xfeepro[ranges]['.$counter.'][cost]" value="' . $range['cost'] . '" /></td>'
                            .'<td class="text-left"><input size="6" type="text" class="form-control" name="xfeepro[ranges]['.$counter.'][block]" value="' . $range['block'] . '" /></td>'
                            .'<td class="text-left">
                                <select class="form-control" name="xfeepro[ranges]['.$counter.'][partial]">
                                    <option '.(($range['partial']=='0') ? 'selected': '' ) . ' value="0">' . $data['text_up'] . '</option>
                                    <option '.(($range['partial']=='2') ? 'selected': '' ) . ' value="2">' . $data['text_down'] . '</option>
                                    <option '.(($range['partial']=='1') ? 'selected': '' ) .' value="1">' . $data['text_fraction'] . '</option>
                                </select>
                            </td>'
                            .'<td class="text-right"><a class="btn btn-sm btn-danger ocm-row-remove">'.$data['text_remove'].'</a></td>'
                        .'</tr>';
        }
        if (!$method_data['ranges']) $table_body .= '<tr class="no-row"><td colspan="6">'.$data['text_no_unit_row'].'</td></tr>';

        $table_headings = array(
            array(
                'title'  => $data['text_start'],
                'help'   => $data['help_unit_start']
            ),
            array(
                'title'  => $data['text_end'],
                'help'   => $data['help_unit_end']
            ),
            array(
                'title' => $data['text_cost'],
                'help'  => $data['help_unit_price']
            ),
            array(
                'title' => $data['text_qnty_block'],
                'help'  => $data['help_unit_ppu']
            ),
            array(
                'title' => $data['text_partial'],
                'help'  => $data['help_partial']
            ),
            array(
                'title' => $data['text_action']
            )
        );
        $table_footer = '<tfoot>
                           <td colspan="7" class="text-right">&nbsp;';
        $table_footer .= $this->ocm->misc->getButton(array('type' => 'primary', 'title'=> $data['text_add_new'], 'class' => 'add-ocm-row', 'icon' => 'fa-plus-circle'));
        $table_footer .= '</tr>
                        </tfoot>';
        $return .= $this->ocm->misc->getTableSkeleton($table_headings, $table_body, $table_footer);
        $return .=  '</div>';
        return $return;
    }
    private function getGroups($data, $setting) {
        $prefix = $this->ocm->prefix;
        $return = '';
        for ($i=1; $i <= $data['groups_count']; $i++) {
            $current_method_mode = 'no_group';
            $current_method_name =  isset($setting[$prefix . 'xfeepro_group_name'][$i]) ? $setting[$prefix . 'xfeepro_group_name'][$i]:'';
            $return .='<tr>
                <td class="text-left">Group'.$i.'</td>
                <td class="text-left">
                <select class="form-control xfeepro_group'.$i.'" name="' . $prefix . 'xfeepro_group['.$i.']">';
            foreach ($data['group_modes'] as $type => $name) {
                $selected = (isset($setting[$prefix . 'xfeepro_group'][$i]) && $setting[$prefix . 'xfeepro_group'][$i]==$type) ? 'selected':'';
                $current_method_mode = (isset($setting[$prefix . 'xfeepro_group'][$i]) && $setting[$prefix . 'xfeepro_group'][$i]==$type)? $type: $current_method_mode;
                $return .='<option value="'.$type.'" '.$selected.'>'.$name.'</option>';
            }
            $return .='. </select>';
            $return .= '</td>
                        <td class="text-left"> 
                            <input type="text" name="' . $prefix . 'xfeepro_group_name['.$i.']" value="'.$current_method_name.'" placeholder="'.$data['placeholder_group_name'].'" class="form-control" />
                        </td>
                    </tr>';
        }
        return $return;
    }
    // validate hook for sort_order value
    private function onValidateGeneral(&$save) {
        $key = VERSION >= '3.0.0.0' ? 'total_' . $this->meta['name'] . '_sort_order' : $this->meta['name'] . '_sort_order';
        $min_value = (int)$this->ocm->common->getConfig('sub_total_sort_order', 'total') + 1;
        $max_value = (int)$this->ocm->common->getConfig('total_sort_order', 'total') - 1;
        if ((int)$save['value'][$key] < (int)$min_value) {
            $save['value'][$key] = $min_value;
        }
        if ((int)$save['value'][$key] > (int)$max_value) {
            $save['value'][$key] = $max_value;
        }
    }
    private function hookRules($method_data, $data) {
        $hook = '';
        /* HOOK RULES HERE */
        /* name must be in hook array e.g hook['name'] */
        //$hook .= $this->ocm->form->get('input', array('name' => 'hook[custom_field]', 'title' => 'Enter Custom Fields'));
        return $hook;
    }
 }