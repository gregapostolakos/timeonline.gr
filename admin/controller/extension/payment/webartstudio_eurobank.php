<?php
/**
 * Eurobank Payment Gateway (Eurobank e-Commerce / Cardlink) - 3DSecure v2
 * Admin controller - Webartstudio
 *
 * Καθαρη υλοποιηση χωρις εξωτερικες κλησεις. Ολες οι ρυθμισεις
 * αποθηκευονται στον πινακα `setting` του OpenCart.
 */
class ControllerExtensionPaymentWebartstudioEurobank extends Controller {

    private $error = array();

    // Κωδικος/διαδρομη extension
    private $code = 'webartstudio_eurobank';
    private $path = 'extension/payment/webartstudio_eurobank';
    private $key  = 'payment_webartstudio_eurobank';

    public function index() {
        $this->load->language($this->path);

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');

        // --- Αποθηκευση ρυθμισεων (AJAX POST) ---
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting($this->key, $this->request->post);

            $json = array();
            $json['success'] = $this->language->get('text_success');

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && isset($this->error['warning'])) {
            $json = array();
            $json['error'] = $this->error['warning'];

            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode($json));
            return;
        }

        // --- Δεδομενα προβολης ---
        $data = $this->getFormData();

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view($this->path, $data));
    }

    private function getFormData() {
        $data = array();

        // Φορτωση του κοινου εικαστικου Webartstudio στο <head> (οχι inline,
        // ωστε να μη σπαει η γειτνιαση #column-left + #content του admin theme)
        // Το ?v= αναγκαζει τον browser να φορτωσει το νεο CSS (cache-busting)
        $this->document->addStyle('view/stylesheet/webartstudio/was-admin.css?v=1.0.1');

        // Breadcrumbs
        $data['breadcrumbs'] = array();
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_extension'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true)
        );
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], true)
        );

        // URLs ενεργειων
        $data['action']        = $this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel']        = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);
        $data['log_download']  = $this->url->link($this->path . '/download', 'user_token=' . $this->session->data['user_token'], true);
        $data['log_clear']     = $this->url->link($this->path . '/clear', 'user_token=' . $this->session->data['user_token'], true);
        $data['user_token']    = $this->session->data['user_token'];

        // URL επιβεβαιωσης (confirm/cancel) που πρεπει να δηλωθει στην τραπεζα
        $data['callback_url']  = HTTPS_CATALOG . 'index.php?route=' . $this->path . '/callback';

        // Λιστα γλωσσων (για τιτλο ανα γλωσσα)
        $this->load->model('localisation/language');
        $data['languages'] = $this->model_localisation_language->getLanguages();

        // Order statuses
        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // Geo zones
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // --- Πεδια ρυθμισεων (post -> αλλιως απο τη βαση) ---
        $fields = array(
            'status', 'merchant_id', 'secret', 'environment', 'test_url', 'live_url',
            'transaction_type', 'order_status_id', 'order_status_preauth_id',
            'geo_zone_id', 'total_min', 'total_max', 'only_accept_eur', 'send_state',
            'css_url', 'clear_cart', 'installments_status', 'installment_offset',
            'sort_order', 'debug'
        );

        foreach ($fields as $field) {
            $name = $this->key . '_' . $field;
            if (isset($this->request->post[$name])) {
                $data[$name] = $this->request->post[$name];
            } else {
                $data[$name] = $this->config->get($name);
            }
        }

        // Τιτλος ανα γλωσσα (πινακας)
        $title_key = $this->key . '_title';
        if (isset($this->request->post[$title_key])) {
            $data[$title_key] = $this->request->post[$title_key];
        } else {
            $data[$title_key] = $this->config->get($title_key);
        }
        if (!is_array($data[$title_key])) {
            $data[$title_key] = array();
        }

        // Πινακας δοσεων
        $inst_key = $this->key . '_installments';
        if (isset($this->request->post[$inst_key])) {
            $data[$inst_key] = $this->request->post[$inst_key];
        } else {
            $config_inst = $this->config->get($inst_key);
            $data[$inst_key] = is_array($config_inst) ? $config_inst : array();
        }

        // Default transaction type
        if ($data[$this->key . '_transaction_type'] === null || $data[$this->key . '_transaction_type'] === '') {
            $data[$this->key . '_transaction_type'] = '1';
        }

        // Περιεχομενο debug log
        $data['log_content'] = '';
        $file = DIR_LOGS . $this->code . '.log';
        if (is_file($file)) {
            $size = filesize($file);
            if ($size > 2097152) {
                $data['log_content'] = '--- Το αρχειο log ειναι πολυ μεγαλο για προβολη εδω. Κατεβασε το τοπικα. ---';
            } else {
                $data['log_content'] = file_get_contents($file);
            }
        }

        $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

        return $data;
    }

    // Κατεβασμα debug log
    public function download() {
        $file = DIR_LOGS . $this->code . '.log';

        if (is_file($file) && filesize($file) > 0) {
            $this->response->addheader('Pragma: public');
            $this->response->addheader('Expires: 0');
            $this->response->addheader('Content-Type: application/octet-stream');
            $this->response->addheader('Content-Disposition: attachment; filename="' . $this->code . '_' . date('Y-m-d_H-i-s') . '.log"');
            $this->response->addheader('Content-Transfer-Encoding: binary');
            $this->response->setOutput(file_get_contents($file));
        } else {
            $this->response->redirect($this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], true));
        }
    }

    // Καθαρισμα debug log
    public function clear() {
        $this->load->language($this->path);

        if ($this->user->hasPermission('modify', $this->path)) {
            $file = DIR_LOGS . $this->code . '.log';
            if (is_file($file)) {
                $handle = fopen($file, 'w+');
                fclose($handle);
            }
        }

        $this->response->redirect($this->url->link($this->path, 'user_token=' . $this->session->data['user_token'], true));
    }

    // Εγκατασταση (δημιουργια πινακα log)
    public function install() {
        $this->load->model($this->path);
        $this->model_extension_payment_webartstudio_eurobank->install();
    }

    // Απεγκατασταση
    public function uninstall() {
        $this->load->model($this->path);
        $this->model_extension_payment_webartstudio_eurobank->uninstall();
    }

    private function validate() {
        if (!$this->user->hasPermission('modify', $this->path)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post[$this->key . '_merchant_id'])) {
            $this->error['warning'] = $this->language->get('error_merchant_id');
        }

        if (empty($this->request->post[$this->key . '_secret'])) {
            $this->error['warning'] = $this->language->get('error_secret');
        }

        return !$this->error;
    }
}
