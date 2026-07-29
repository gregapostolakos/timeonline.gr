<?php
class ControllerExtensionPaymentXpayment extends Controller {
    use OCM\Traits\Front\Equation;
    use OCM\Traits\Front\Shortcode;
    protected $registry;
    private $ocm;
    private $mtype;
    private $ext_path;
    private $ext_key;
    public function __construct($registry) {
        parent::__construct($registry);
        $this->registry = $registry;
        $this->mtype = 'payment';
        $this->ext_path = 'extension/payment/xpayment';
        $this->ext_key = 'model_' . str_replace('/', '_', $this->ext_path);
        $this->ocm = $this->registry->has('ocm_front') ? $this->registry->get('ocm_front') : new OCM\Front($this->registry);
    }
    public function index() {
        $this->language->load($this->ext_path);
        $this->load->model($this->ext_path);
        $this->load->model('checkout/order');

        $data['button_confirm'] = $this->language->get('button_confirm');
        $language_id = $this->config->get('config_language_id');
        $tab_id = (int)str_replace(array('xpayment', '.'), '', $this->session->data['payment_method']['code']);
        $xpayment = $this->{$this->ext_key}->getMethodDataByID($tab_id);
        if (!$xpayment) {
            return 'Something went wrong! X-Payment is not working properly. Please request support.';
        }
        $instruction = $xpayment['instruction'];
        $success = $xpayment['success'];
        $integration = $xpayment['integration'];

        $order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : 0;
        $order_info = $this->model_checkout_order->getOrder($order_id);
        if (isset($this->session->data['payment_method']['code'])) {
            $order_info['payment_code'] = $this->session->data['payment_method']['code']; // it may not be updated so set it.
        }
        $product_placeholder_req = strpos($instruction, '{product') !== false;
        $replacement = $this->{$this->ext_key}->getReplacers($order_info, $product_placeholder_req);
        $addon = $this->{$this->ext_key}->getAddon($integration);

        /* hook - before fetch init_url */
        $data['xerror'] = '';
        if ($addon && (method_exists($addon, '_xRedirect') || property_exists($addon, '_xRedirect') || isset($addon->_xRedirect))) {
            $hook_result = $addon->_xRedirect($replacement['placeholder'], $replacement['replacer']);
            if ($hook_result && is_array($hook_result)) {
                if (isset($hook_result['error']) && $hook_result['error']) {
                    $data['xerror'] = $hook_result['error'];
                } else {
                    /* if URL is found, update redirect URL */
                    if (isset($hook_result['url']) && $hook_result['url']) {
                        $integration['url'] = $hook_result['url'];
                    }
                    foreach ($hook_result as $hkey => $hvalue) {
                        array_push($replacement['placeholder'], '{'.$hkey.'}');
                        array_push($replacement['replacer'], $hvalue);
                    }
                }
            }
        }
        if (!empty($integration['url'])) {
            $integration['url'] = str_replace($replacement['placeholder'], $replacement['replacer'], $integration['url']); 
        }
        /* end of hook*/
        $instruction = str_replace($replacement['placeholder'], $replacement['replacer'], $instruction);
        
        $shortcode_result = $this->applyShortcode($instruction, $replacement);
        $data['xform'] = '';
        if (!empty($shortcode_result['xform'])) {
            $data['xform'] = $shortcode_result['xform'];
        }

        if ($success) $success = str_replace($replacement['placeholder'], $replacement['replacer'], $success);
        $form_data = array();
        if ($integration && $integration['type'] == 'redirect' && $integration['method'] == 'POST' && $integration['data']) {
            $form_data = $this->{$this->ext_key}->getIntegrationData($order_info, $replacement, 'array');
        }
        //$instruction = $this->{$this->ext_key}->parseBBCode($instruction);
        /* hook - before fetch instruction html */
        if ($addon && (method_exists($addon, '_xInstruction') || property_exists($addon, '_xInstruction') || isset($addon->_xInstruction))) {
            $hook_result = $addon->_xInstruction($instruction, $replacement['placeholder'], $replacement['replacer']);
            if ($hook_result !== false) {
                $instruction = $hook_result;
            }
        }
        if ($xpayment['custom_js']) {
            $instruction .= '<script>' . str_replace($replacement['placeholder'], $replacement['replacer'], $xpayment['custom_js']) . '</script>';
        }
        if ($xpayment['custom_css']) $instruction .= '<style>' . $xpayment['custom_css'] . '</style>';
        $data['form_data'] = $form_data;
        $data['redirect'] = ($integration && $integration['type'] == 'redirect' && $integration['method'] == 'POST') ? $integration['url'] : '';
        $data['method_type'] = $integration ? $integration['method'] : 'POST';
        $data['instruction'] = $instruction;
        $data['xpayment_name'] = $xpayment['name'];
        $data['continue'] = $success ? $success : $this->url->link('checkout/success');
        $_action = 'confirm';
        if ($integration) {
            if ($integration['type'] == 'fetch_redirect') {
                $_action = '_confirm';
            } else if ($integration['type'] == 'redirect' && $integration['method'] == 'GET') {
                $_action = '_confirm';
            }
        }
        $data['action'] = 'index.php?route=' . $this->ext_path . $this->ocm->divider . $_action;
        $data['title_status'] = isset($xpayment['hide_title']) && $xpayment['hide_title'] ? false : true;
        /* hook - confirm URL  */
        if ($addon && (method_exists($addon, '_xConfirm') || property_exists($addon, '_xConfirm') || isset($addon->_xConfirm))) {
            $hook_result = $addon->_xConfirm($replacement['placeholder'], $replacement['replacer']);
            if ($hook_result) {
                $data['action'] = $hook_result;
            }
        }
        return $this->ocm->view($this->ext_path, $data);
    }
    public function _confirm() {
        $this->load->model('checkout/order');
        $this->load->model($this->ext_path);
        $this->language->load($this->ext_path);
        $json = array();
        $order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : 0;
        $order_info = $this->model_checkout_order->getOrder($order_id);
        $xpayment = $this->{$this->ext_key}->getMethodDataByOrder($order_info);
        $integration = $xpayment['integration'];
        $addon = $this->{$this->ext_key}->getAddon($integration);
        $replacement = $this->{$this->ext_key}->getReplacers($order_info);
        // hook 
        if ($addon && (method_exists($addon, '_xFetch') || property_exists($addon, '_xFetch') || isset($addon->_xFetch))) {
            $hook_result = $addon->_xFetch($replacement['placeholder'], $replacement['replacer']);
            if ($hook_result && is_array($hook_result)) {
                if (isset($hook_result['error']) && $hook_result['error']) {
                    $json['error'] = $hook_result['error'];
                } else {
                    /* if URL is found, update redirect URL */
                    if (isset($hook_result['url']) && $hook_result['url']) {
                        $json['redirect'] = $hook_result['url'];
                    }
                    foreach ($hook_result as $hkey => $hvalue) {
                        array_push($replacement['placeholder'], '{'.$hkey.'}');
                        array_push($replacement['replacer'], $hvalue);
                    }
                }
            }
            $integration['url'] = str_replace($replacement['placeholder'], $replacement['replacer'], $integration['url']);
        }
        if (!empty($integration['meta']['_key'])
            && !empty($integration['meta']['_key']['_keyUrl'])) {
            $_keyUrl = $integration['meta']['_key']['_keyUrl'];
        } else {
            $_keyUrl = '';
        }
        if (!$json && $integration['type'] == 'fetch_redirect' && $integration['url'] && $_keyUrl) {
            $api_response = $this->{$this->ext_key}->invokeAPI($order_info, $integration, $replacement);
            $redirect = $this->{$this->ext_key}->getIn($_keyUrl, $api_response);
            if ($redirect) {
                $json['redirect'] = $redirect;
            } else {
                $error = $this->{$this->ext_key}->guessErrorMessage($xpayment, $integration, $api_response);
                $json['error'] = $error ? $error : 'Unable to get response from payment gateway';
            }
        } else if (!$json && $integration['type'] == 'redirect' && $integration['method'] == 'GET') {
            $json['redirect'] = $this->{$this->ext_key}->getRequestUrl($order_info, $integration, $replacement);
        } else if (!$json) {
            $json['error'] = $xpayment['error'] ? $xpayment['error'] : 'Please define a gateway URL to fetch payment URL';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    public function confirm() {
        $is_success = true;
        $location = $error = '';
        $form_errors = array();
        $json = array();
        $replacement = array();
        $this->load->model('checkout/order');
        $this->load->model($this->ext_path);
        $this->language->load($this->ext_path);
        if (!isset($this->session->data['order_id'])) {
            $location = $this->url->link('checkout/checkout');
            $this->session->data['error']= $this->language->get('error_payment');
            $this->response->redirect($location);
        }
        $order_id = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : 0;
        $order_info = $this->model_checkout_order->getOrder($order_id);
        /* xform integration */
        $formId = isset($this->request->get['formId'])? $this->request->get['formId'] : 0;
        $is_success = $this->processXForm($formId, $form_errors);
        /* xform end */

        if ($is_success) {
            $result = $this->{$this->ext_key}->settlement($order_info);
            // overwrite redirect type from get/post
            $_xresponse_type = $this->ocm->common->getVar('_xresponse_type');
            $do_redirect = $_xresponse_type == 'redirect' || $result['type'] == 'redirect' || $result['type'] == 'fetch_redirect';
            $is_success = $result['status'];
            $location = $result['redirect'];
            if ($is_success) {
                $replacement = $result['replacement'];
            } else {
                $error = $result['error'] ? $result['error'] : $this->language->get('error_payment');
            }
            if ($do_redirect) {
                if (!$is_success) {
                    $this->session->data['error'] = $error;
                }
                $this->response->redirect($location);
            }
            if ($result['custom_success']) {
                 $this->resetCart();
            }
        }
        if ($is_success) {
            $json['redirect'] = $location;
        } else if($form_errors) {
            $json['errors'] = $form_errors;
        } else {
            $json['error'] = $error;
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    // callback or ipn or offline notify
    public function notify($return = false) {
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/xpayment');
        $_valueIpn = 'done';
        $response = array_merge($this->request->post, $this->request->get); // for ipn, let's consider request to make sure
        $this->request->post = $response; // for ipn, let's consider request to make sure
        $this->request->get = $response; // for ipn, let's consider request to make sure
        //let's fetch order_id from response
        $order_id_keys = $this->{$this->ext_key}->getNotifyParams();
        if (!is_array($order_id_keys)) $order_id_keys = array();
        $order_id_keys[] = 'order_id'; // default one
        foreach($order_id_keys as $order_id_key) {
            $order_id = $this->ocm->common->getVar($order_id_key);
            if ($order_id) break;
        }
        if (!$order_id) {
            $_raw_post = file_get_contents('php://input');
            $_post = @json_decode($_raw_post, true);
            if ($_post) {
                $response = array_merge($response, $_post);
                if (!empty($_post[$order_id_key])) {
                    $order_id = $_post[$order_id_key];
                }
            }
        }
        $order_id = (int)preg_replace('/(\d+)-.*/', '$1', $order_id); // clear suffix e.g. 10-suffix
        if ($order_id) {
            $order_info = $this->model_checkout_order->getOrder($order_id);
            $xpayment = $this->{$this->ext_key}->getMethodDataByOrder($order_info);
            $integration = $xpayment['integration'];
            $replacement = $this->{$this->ext_key}->getReplacers($order_info);
            $addon = $this->{$this->ext_key}->getAddon($integration);
            $order_status_id = $this->{$this->ext_key}->getOrderStatus($xpayment, $integration, $response);
            // hook
            $hook_validate = true;
            if ($addon && (method_exists($addon, '_xNotify') || property_exists($addon, '_xNotify') || isset($addon->_xNotify))) {
                $hook_result = $addon->_xNotify($replacement['placeholder'], $replacement['replacer']);
                if ($hook_result && is_array($hook_result)) {
                    if (isset($hook_result['error']) && $hook_result['error']) {
                        $hook_validate = false;
                    } else {
                        $response = array_merge($response, $hook_result);
                    }
                }
            }
            if ($hook_validate && $order_status_id && (int)$order_status_id !== (int)$order_info['order_status_id']) {
                if ((int)$order_info['order_status_id']) {
                    $comment = $this->ocm->common->getVar('_xcomment');
                    $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true);
                } else {
                    $this->{$this->ext_key}->settlement($order_info);
                }
            }
            if (!empty($integration['meta']['_value'])
                && !empty($integration['meta']['_value']['_valueIpn'])) {
                $_valueIpn = $integration['meta']['_value']['_valueIpn'];
            }
        }
        $xpayment_debug = $this->ocm->getConfig('xpayment_debug', $this->mtype);
        if ($xpayment_debug) {
            $log = 'X-Payment gateway response log (Disable debug mode of the x-payment to turn off)' . PHP_EOL;
            $log .= 'IPN POST:' . PHP_EOL;
            $log .= print_r($this->request->post, true);
            $log .= 'IPN GET:' . PHP_EOL;
            $log .= print_r($this->request->get, true);
            $this->log->write($log);
        }
        if ($return) {
            return 'OK';
        }
        $this->response->addHeader($this->request->server["SERVER_PROTOCOL"]." 200 OK");
        $this->response->setOutput($_valueIpn);
    }
    private function getEquationValue($equation, $placholder, $replacer) {
        $equation = str_replace($placholder, $replacer, $equation);
        /* Removing unwanted placeholder */
        if (strpos($equation, '{') !== false) {
            $equation = preg_replace('/{.*?}/', 0, $equation);
        }
        $condition_status = false;
        $value = (float)$this->calculate_string($equation, $condition_status);
        return array(
            'value'    => $value,
            'status'  => (boolean)$condition_status 
        );
    }
    public function applyShortcode(&$text, $replacement) {
        $return = array();
        $rex = '/\[(\w+)\s?([^]]*)\](.*?)\[\/\w+\]/m';
        if ($shortcodes = $this->ocm->parseShortcode($text)) {
            foreach ($shortcodes as $shortcode) {
                $render = '_' . $shortcode['name'] . 'Render';
                if (method_exists($this, $render) && $_return = $this->{$render}($shortcode, $replacement)) {
                    $text = str_replace($shortcode['full'], $_return['output'], $text);
                    $return[$shortcode['name']] = $shortcode['text'];
                }
            }
        }
        return $return;
    }
    private function processXForm($formId, &$form_errors) {
        $form_status = true;
        if ($formId && $this->ocm->getConfig('xform_status', 'module')) {
            $this->load->language('extension/module/xform');
            $this->load->model('extension/module/xform');
            $xform = new \Xform($this->registry);
            $files = $xform->grabUpload($formId);
            $submitted_data = isset($this->request->post['xform']) ? $this->request->post['xform'] : array();
            $_form_errors = $xform->validateForm($formId, $submitted_data, $files);

            if (!$_form_errors) {
                $formdata = array();
                $formdata['formId'] = $formId;
                $formdata['userIP'] = $this->request->server['REMOTE_ADDR'];
                $formdata['userAgent'] = $this->request->server['HTTP_USER_AGENT'];
                $formdata['submitDate'] = date('Y-m-d H:i:s');
                $formdata['userId'] = ($this->customer->isLogged())? $this->customer->getId(): 0;
                $formdata['storeId'] = $this->config->get('config_store_id');
                $formdata['productId'] = 0;
                $formdata['orderId'] = isset($this->session->data['order_id']) ? $this->session->data['order_id'] : 0 ;
                $recordId = $this->model_extension_module_xform->addFormRecord($formdata);
                $this->model_extension_module_xform->addRecordFields($recordId, $formId, $submitted_data, $files);
                $this->model_extension_module_xform->processFormEmail($recordId);
            } else {
                $fields = $xform->getFormFields($formId);
                $field_types = array();
                foreach ($fields as $field) {
                    $field_types[$field['cid']] = $field['field_type'];
                }
                $default_errors = $xform->getDefaultError();
                $form_texts = $xform->getFormLang($formId);
                $text_errors = $form_texts['errors'];
                foreach ($_form_errors as $cid) {
                    if (!isset($text_errors[$cid])) $text_errors[$cid] = '';
                    $field_type = isset($field_types[$cid]) ? $field_types[$cid] : 'text';
                    $error_message = $text_errors[$cid] ? $text_errors[$cid] : (isset($default_errors[$field_type]) ? $default_errors[$field_type] : '');
                    $form_errors[] = array(
                        'cid' => $cid,
                        'error' => $error_message
                    );
                }
                $form_status = false;
            }
        }
        return $form_status;
    }
    private function resetCart() {
        if (isset($this->session->data['order_id'])) {
            $this->cart->clear();
            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);
        }
    }
    /* Events callback */
    public function onPaymentController(&$route, &$data) {
        $route = trim($route, '0123456789');
    }
    public function onOrderView($route, &$data) {
        $this->load->model($this->ext_path);
        $data['payment_instruction'] = $this->{$this->ext_key}->getInstructionView($data['order_id']);
    }
}