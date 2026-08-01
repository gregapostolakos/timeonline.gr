<?php
/**
 * Eurobank Payment Gateway (Eurobank e-Commerce / Cardlink) - 3DSecure v2
 * Catalog controller - Webartstudio
 *
 * Ροη:
 *  index()    -> εμφανιζει το κουμπι επιβεβαιωσης (και δοσεις) στο checkout
 *  send()     -> φτιαχνει τη φορμα με το digest και κανει redirect στην τραπεζα
 *  callback() -> ελεγχει το digest της απαντησης και ενημερωνει την παραγγελια
 */
class ControllerExtensionPaymentWebartstudioEurobank extends Controller {

    private $code = 'webartstudio_eurobank';
    private $key  = 'payment_webartstudio_eurobank';

    // ----------------------------------------------------------------
    // Βημα checkout: κουμπι επιβεβαιωσης + (προαιρετικα) δοσεις
    // ----------------------------------------------------------------
    public function index() {
        $this->load->language('extension/payment/webartstudio_eurobank');
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        $data['button_confirm'] = $this->language->get('button_confirm');
        $data['text_loading']   = $this->language->get('text_loading');
        $data['action']         = $this->url->link('extension/payment/webartstudio_eurobank/send', '', true);

        // Δοσεις
        $data['installments_status'] = (bool)$this->config->get($this->key . '_installments_status');
        $data['installments']        = array();

        if ($data['installments_status'] && !empty($order_info)) {
            $tiers = $this->config->get($this->key . '_installments');
            $order_total = (float)$order_info['total'];
            $max = 0;
            $interest = 0;

            if (is_array($tiers)) {
                foreach ($tiers as $tier) {
                    $tier_amount = isset($tier['amount']) ? (float)$tier['amount'] : 0;
                    if ($order_total >= $tier_amount && (int)$tier['max'] >= $max) {
                        $max = (int)$tier['max'];
                        $interest = isset($tier['interest']) ? (float)$tier['interest'] : 0;
                    }
                }
            }

            // Λιστα διαθεσιμων δοσεων (2 εως max)
            for ($i = 2; $i <= $max; $i++) {
                $data['installments'][] = array(
                    'value'    => $i . '|' . $interest,
                    'label'    => $i . ' x' . ($interest > 0 ? (' (' . $interest . '%)') : '')
                );
            }
        }

        $data['text_installments'] = $this->language->get('text_installments');
        $data['text_no_inst']      = $this->language->get('text_no_inst');

        // Τυπος εξοδου ωστε το twig να εμφανισει το κουμπι επιβεβαιωσης
        $data['output_type'] = 'checkout';

        return $this->load->view('extension/payment/webartstudio_eurobank', $data);
    }

    // ----------------------------------------------------------------
    // Δημιουργια φορμας προς την τραπεζα
    // ----------------------------------------------------------------
    public function send() {
        $this->load->language('extension/payment/webartstudio_eurobank');
        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if (empty($order_info)) {
            $this->log_debug('Empty order on send().');
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
            return;
        }

        // --- Endpoint ---
        $environment = (int)$this->config->get($this->key . '_environment');
        $test_url = trim($this->config->get($this->key . '_test_url'));
        $live_url = trim($this->config->get($this->key . '_live_url'));

        if ($environment == 1) {
            $action = !empty($live_url) ? $live_url : 'https://vpos.eurocommerce.gr/vpos/shophandlermpi';
        } else {
            $action = !empty($test_url) ? $test_url : 'https://eurocommerce-test.cardlink.gr/vpos/shophandlermpi';
        }

        // --- Δοσεις απο επιλογη πελατη ---
        $installments = 0;
        $interest = 0;
        if (isset($this->request->post['inst_selection']) && $this->request->post['inst_selection'] != '0' && $this->request->post['inst_selection'] != '') {
            $parts = explode('|', $this->request->post['inst_selection']);
            $installments = (int)$parts[0];
            $interest = isset($parts[1]) ? (float)$parts[1] : 0;
        }

        // --- Ποσο / νομισμα ---
        if ($this->config->get($this->key . '_only_accept_eur') && $order_info['currency_code'] !== 'EUR') {
            $amount = $this->currency->format($order_info['total'], 'EUR', false, false);
            $currency = 'EUR';
        } else {
            $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
            $currency = $order_info['currency_code'];
        }

        // Προσθηκη επιτοκιου στις δοσεις
        if ($installments > 1 && $interest > 0) {
            $amount = $amount * (1 + ($interest / 100));
        }
        $amount = number_format(round($amount, 2), 2, '.', '');

        // --- Καθαρισμος διευθυνσεων ---
        $clean = function ($value) {
            return preg_replace('/[^\p{L}\p{N}\s-]/u', '', (string)$value);
        };

        // --- Μοναδικη αναφορα παραγγελιας ---
        // Προσοχη: το Nexi/Cardlink δεχεται ΜΟΝΟ αλφαριθμητικους χαρακτηρες (οχι - η _)
        $rand = substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 5);
        $reference = $order_info['order_id'] . 'WAS' . time() . $rand;
        $reference = mb_substr($reference, 0, 50, 'UTF-8');

        // --- Πεδια αιτηματος ---
        $data = array();
        $data['version']     = '2';
        $data['mid']         = trim($this->config->get($this->key . '_merchant_id'));

        $lang = strtolower($this->session->data['language']);
        $data['lang']        = (strpos($lang, 'el') === 0 || strpos($lang, 'gr') === 0) ? 'el' : 'en';

        $order_desc = sprintf($this->language->get('text_order_desc'), $order_info['order_id']);
        $data['orderid']     = $reference;
        $data['orderDesc']   = $order_desc;
        $data['orderAmount'] = $amount;
        $data['currency']    = $currency;
        $data['payerEmail']  = isset($order_info['email']) ? $order_info['email'] : '';
        $data['payerPhone']  = preg_replace('~\D~', '', isset($order_info['telephone']) ? $order_info['telephone'] : '');

        // Billing
        $data['billCountry'] = isset($order_info['payment_iso_code_2']) ? $order_info['payment_iso_code_2'] : '';
        if ($data['billCountry'] === 'GR' || !$this->config->get($this->key . '_send_state')) {
            $data['billState'] = '';
        } else {
            $data['billState'] = isset($order_info['payment_zone_code']) ? $order_info['payment_zone_code'] : '';
        }
        $data['billZip']     = $clean(isset($order_info['payment_postcode']) ? $order_info['payment_postcode'] : '');
        $data['billCity']    = $clean(isset($order_info['payment_city']) ? $order_info['payment_city'] : '');
        $data['billAddress'] = $clean(trim((isset($order_info['payment_address_1']) ? $order_info['payment_address_1'] : '') . ' ' . (isset($order_info['payment_address_2']) ? $order_info['payment_address_2'] : '')));

        // Shipping
        $data['shipCountry'] = isset($order_info['shipping_iso_code_2']) ? $order_info['shipping_iso_code_2'] : '';
        if ($data['shipCountry'] === 'GR' || !$this->config->get($this->key . '_send_state')) {
            $data['shipState'] = '';
        } else {
            $data['shipState'] = isset($order_info['shipping_zone_code']) ? $order_info['shipping_zone_code'] : '';
        }
        $data['shipZip']     = $clean(isset($order_info['shipping_postcode']) ? $order_info['shipping_postcode'] : '');
        $data['shipCity']    = $clean(isset($order_info['shipping_city']) ? $order_info['shipping_city'] : '');
        $data['shipAddress'] = $clean(trim((isset($order_info['shipping_address_1']) ? $order_info['shipping_address_1'] : '') . ' ' . (isset($order_info['shipping_address_2']) ? $order_info['shipping_address_2'] : '')));

        $data['payMethod']             = '';
        $data['trType']                = trim($this->config->get($this->key . '_transaction_type'));
        $data['extInstallmentoffset']  = '';
        $data['extInstallmentperiod']  = '';

        if ($installments > 1) {
            $offset = $this->config->get($this->key . '_installment_offset');
            $data['extInstallmentoffset'] = ($offset === '' || $offset === null) ? '0' : $offset;
            $data['extInstallmentperiod'] = $installments;
        }

        $data['extRecurringfrequency'] = '';
        $data['extRecurringenddate']   = '';
        $data['cssUrl']     = $this->config->get($this->key . '_css_url');
        $callback_url       = $this->url->link('extension/payment/webartstudio_eurobank/callback', '', true);
        $data['confirmUrl'] = $callback_url;
        $data['cancelUrl']  = $callback_url;
        $data['var1'] = '';
        $data['var2'] = '';
        $data['var3'] = '';
        $data['var4'] = '';
        $data['var5'] = '';

        // --- Ορια μηκους πεδιων (οπως οριζει το πρωτοκολλο) ---
        $data['lang']        = mb_substr($data['lang'], 0, 2, 'UTF-8');
        $data['orderid']     = mb_substr($data['orderid'], 0, 50, 'UTF-8');
        $data['orderDesc']   = mb_substr($data['orderDesc'], 0, 128, 'UTF-8');
        $data['currency']    = mb_substr($data['currency'], 0, 3, 'UTF-8');
        $data['payerEmail']  = mb_substr($data['payerEmail'], 0, 64, 'UTF-8');
        $data['payerPhone']  = mb_substr($data['payerPhone'], 0, 30, 'UTF-8');
        $data['billCountry'] = mb_substr($data['billCountry'], 0, 2, 'UTF-8');
        $data['billState']   = mb_substr($data['billState'], 0, 50, 'UTF-8');
        $data['billZip']     = mb_substr($data['billZip'], 0, 16, 'UTF-8');
        $data['billCity']    = mb_substr($data['billCity'], 0, 64, 'UTF-8');
        $data['billAddress'] = mb_substr($data['billAddress'], 0, 100, 'UTF-8');
        $data['shipCountry'] = mb_substr($data['shipCountry'], 0, 2, 'UTF-8');
        $data['shipState']   = mb_substr($data['shipState'], 0, 50, 'UTF-8');
        $data['shipZip']     = mb_substr($data['shipZip'], 0, 16, 'UTF-8');
        $data['shipCity']    = mb_substr($data['shipCity'], 0, 64, 'UTF-8');
        $data['shipAddress'] = mb_substr($data['shipAddress'], 0, 100, 'UTF-8');

        // --- Υπολογισμος digest ---
        $secret = trim($this->config->get($this->key . '_secret'));
        $digest_string =
            $data['version'] . $data['mid'] . $data['lang'] . $data['orderid'] . $data['orderDesc'] .
            $data['orderAmount'] . $data['currency'] . $data['payerEmail'] . $data['payerPhone'] .
            $data['billCountry'] . $data['billState'] . $data['billZip'] . $data['billCity'] . $data['billAddress'] .
            $data['shipCountry'] . $data['shipState'] . $data['shipZip'] . $data['shipCity'] . $data['shipAddress'] .
            $data['payMethod'] . $data['trType'] . $data['extInstallmentoffset'] . $data['extInstallmentperiod'] .
            $data['extRecurringfrequency'] . $data['extRecurringenddate'] .
            $data['cssUrl'] . $data['confirmUrl'] . $data['cancelUrl'] . $secret;

        $data['digest'] = base64_encode(hash('sha256', $digest_string, true));

        // --- Καταγραφη συναλλαγης στη βαση ---
        $this->db->query("INSERT INTO `" . DB_PREFIX . "webartstudio_eurobank` SET
            `order_id`     = '" . (int)$order_info['order_id'] . "',
            `reference`    = '" . $this->db->escape($reference) . "',
            `amount`       = '" . $this->db->escape($amount) . "',
            `currency`     = '" . $this->db->escape($currency) . "',
            `installments` = '" . (int)$installments . "',
            `interest`     = '" . (float)$interest . "',
            `status`       = 'PENDING',
            `date_added`   = NOW()");

        if ($this->config->get($this->key . '_debug')) {
            $this->log_debug('SEND digest_string: ' . $digest_string);
            $this->log_debug('SEND data: ' . print_r($data, true));
        }

        // --- Εξοδος: φορμα auto-submit ---
        $output  = '<form action="' . $action . '" method="POST" id="was-eurobank-form" name="wasEurobankForm" accept-charset="UTF-8">';
        foreach ($data as $field_name => $field_value) {
            $output .= '<input type="hidden" name="' . $field_name . '" value="' . htmlspecialchars($field_value, ENT_QUOTES, 'UTF-8') . '"/>';
        }
        $output .= '</form>';

        $view = array();
        $view['output_type']       = 'submit';
        $view['submitform_output'] = $output;
        $view['text_redirecting']  = $this->language->get('text_redirecting');
        $view['header']            = $this->load->controller('common/header');
        $view['footer']            = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/webartstudio_eurobank', $view));
    }

    // ----------------------------------------------------------------
    // Απαντηση τραπεζας
    // ----------------------------------------------------------------
    public function callback() {
        $this->load->language('extension/payment/webartstudio_eurobank');
        $this->load->model('checkout/order');

        // Background (server-to-server) confirmation: η Cardlink ποσταρει απευθειας,
        // χωρις browser, με το header Modirum-VPOS. Η παραγγελια ενημερωνεται κανονικα
        // αλλα δεν κανουμε redirect ουτε αγγιζουμε το session — δεν υπαρχει πελατης.
        $is_background = isset($_SERVER['HTTP_MODIRUM_VPOS']);

        $fields = array('version', 'mid', 'orderid', 'status', 'orderAmount', 'currency',
            'paymentTotal', 'message', 'riskScore', 'payMethod', 'txId', 'paymentRef', 'digest');

        $p = array();
        foreach ($fields as $f) {
            $p[$f] = isset($_POST[$f]) ? $_POST[$f] : '';
        }

        if ($this->config->get($this->key . '_debug')) {
            $this->log_debug('CALLBACK POST: ' . print_r($_POST, true));
        }

        // --- Επαληθευση digest απαντησης ---
        $secret = trim($this->config->get($this->key . '_secret'));
        $check_string =
            $p['version'] . $p['mid'] . $p['orderid'] . $p['status'] . $p['orderAmount'] .
            $p['currency'] . $p['paymentTotal'] . $p['message'] . $p['riskScore'] .
            $p['payMethod'] . $p['txId'] . $p['paymentRef'] . $secret;

        $check_digest = base64_encode(hash('sha256', $check_string, true));

        if ($check_digest !== $p['digest']) {
            $this->log_debug('CALLBACK invalid digest. Expected: ' . $check_digest . ' Got: ' . $p['digest']);
            if ($is_background) { $this->background_ok('invalid digest'); return; }
            $this->session->data['error'] = $this->language->get('error_general');
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
            return;
        }

        // --- Ανακτηση εγγραφης απο τη βαση βασει reference ---
        $row = $this->db->query("SELECT * FROM `" . DB_PREFIX . "webartstudio_eurobank`
            WHERE `reference` = '" . $this->db->escape($p['orderid']) . "' LIMIT 1")->row;

        if (empty($row)) {
            $this->log_debug('CALLBACK reference not found: ' . $p['orderid']);
            if ($is_background) { $this->background_ok('reference not found: ' . $p['orderid']); return; }
            $this->session->data['error'] = $this->language->get('error_general');
            $this->response->redirect($this->url->link('checkout/checkout', '', true));
            return;
        }

        $oc_order_id = (int)$row['order_id'];
        $status = strtoupper($p['status']);

        // Ενημερωση εγγραφης log
        $this->db->query("UPDATE `" . DB_PREFIX . "webartstudio_eurobank` SET
            `status`        = '" . $this->db->escape($status) . "',
            `tx_id`         = '" . $this->db->escape($p['txId']) . "',
            `payment_ref`   = '" . $this->db->escape($p['paymentRef']) . "',
            `pay_method`    = '" . $this->db->escape($p['payMethod']) . "',
            `message`       = '" . $this->db->escape(mb_substr($p['message'], 0, 255, 'UTF-8')) . "',
            `date_modified` = NOW()
            WHERE `id` = '" . (int)$row['id'] . "'");

        // --- Επιτυχια ---
        if ($status === 'AUTHORIZED' || $status === 'CAPTURED') {

            $comment  = '<b>Eurobank Payment Gateway</b><br/>';
            $comment .= 'Status: ' . $p['status'] . '<br/>';
            $comment .= 'Amount: ' . number_format((float)$p['orderAmount'], 2, '.', '') . ' ' . $p['currency'] . '<br/>';
            if ((int)$row['installments'] > 1) {
                $comment .= 'Installments: ' . (int)$row['installments'] . ' (' . (float)$row['interest'] . '%)<br/>';
            }
            $comment .= 'Payment Method: ' . $p['payMethod'] . '<br/>';
            $comment .= 'Transaction ID: ' . $p['txId'] . '<br/>';
            $comment .= 'Payment Ref: ' . $p['paymentRef'] . '<br/>';
            $comment .= 'Reference: ' . $p['orderid'];

            // Επιλογη κατασταστης αναλογα με τον τυπο
            if ($status === 'CAPTURED') {
                $order_status_id = (int)$this->config->get($this->key . '_order_status_id');
            } else {
                $order_status_id = (int)$this->config->get($this->key . '_order_status_preauth_id');
            }

            // Ενημερωση μονο αν δεν εχει ηδη οριστικοποιηθει
            $order = $this->model_checkout_order->getOrder($oc_order_id);
            if (!empty($order) && (int)$order['order_status_id'] < 1) {
                $this->model_checkout_order->addOrderHistory($oc_order_id, $order_status_id, $comment, false, false);
            }

            // Το clear_cart() και το redirect αφορουν μονο τον πελατη στον browser.
            if ($is_background) { $this->background_ok('order ' . $oc_order_id . ' -> ' . $status); return; }

            if ($this->config->get($this->key . '_clear_cart')) {
                $this->clear_cart();
            }

            $this->response->redirect($this->url->link('checkout/success', '', true));
            return;
        }

        // --- Αποτυχια / ακυρωση ---
        if ($is_background) { $this->background_ok('order ' . $oc_order_id . ' -> ' . $status); return; }

        if ($status === 'CANCELED' || $status === 'CANCELLED') {
            $this->session->data['error'] = $this->language->get('error_canceled');
        } elseif ($status === 'REFUSED') {
            $this->session->data['error'] = $this->language->get('error_refused');
        } else {
            $this->session->data['error'] = $this->language->get('error_failed');
        }

        $this->response->redirect($this->url->link('checkout/checkout', '', true));
    }

    // ----------------------------------------------------------------
    // Βοηθητικα
    // ----------------------------------------------------------------
    private function clear_cart() {
        $this->cart->clear();
        unset($this->session->data['shipping_method']);
        unset($this->session->data['shipping_methods']);
        unset($this->session->data['payment_method']);
        unset($this->session->data['payment_methods']);
        unset($this->session->data['comment']);
        unset($this->session->data['order_id']);
        unset($this->session->data['coupon']);
        unset($this->session->data['reward']);
        unset($this->session->data['voucher']);
        unset($this->session->data['vouchers']);
        unset($this->session->data['totals']);
    }

    /**
     * Τερματισμος background κλησης. Η Cardlink περιμενει σκετο HTTP 200 —
     * αγνοει body και redirects, οποτε δεν στελνουμε τιποτα αλλο.
     */
    private function background_ok($note) {
        $this->log_debug('CALLBACK background: ' . $note);
        $this->response->addHeader('HTTP/1.1 200 OK');
        $this->response->setOutput('');
    }

    private function log_debug($message) {
        if ($this->config->get($this->key . '_debug')) {
            $log = new Log($this->code . '.log');
            $log->write($message);
        }
    }
}
