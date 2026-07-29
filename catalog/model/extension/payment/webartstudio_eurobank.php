<?php
/**
 * Eurobank Payment Gateway (Eurobank e-Commerce / Cardlink)
 * Catalog model - Webartstudio
 */
class ModelExtensionPaymentWebartstudioEurobank extends Model {

    private $key = 'payment_webartstudio_eurobank';

    public function getMethod($address, $total) {
        $this->load->language('extension/payment/webartstudio_eurobank');

        // Ελεγχος ευρους συνολου
        $total_min = (float)$this->config->get($this->key . '_total_min');
        $total_max = (float)$this->config->get($this->key . '_total_max');

        if ($total < $total_min) {
            return array();
        }
        if ($total_max > 0 && $total > $total_max) {
            return array();
        }
        if ($total <= 0) {
            return array();
        }

        // Ελεγχος γεωγραφικης ζωνης
        $geo_zone_id = (int)$this->config->get($this->key . '_geo_zone_id');

        if ($geo_zone_id) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone
                WHERE geo_zone_id = '" . $geo_zone_id . "'
                AND country_id = '" . (int)$address['country_id'] . "'
                AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

            if (!$query->num_rows) {
                return array();
            }
        }

        // Ελεγχος EUR
        if ($this->config->get($this->key . '_only_accept_eur')) {
            $eur = $this->db->query("SELECT `code` FROM `" . DB_PREFIX . "currency` WHERE `code` = 'EUR'");
            if (!$eur->num_rows) {
                return array();
            }
        }

        // Τιτλος ανα γλωσσα
        $titles = $this->config->get($this->key . '_title');
        $language_id = (int)$this->config->get('config_language_id');

        if (is_array($titles) && !empty($titles[$language_id])) {
            $title = $titles[$language_id];
        } else {
            $title = $this->language->get('text_title');
        }

        return array(
            'code'       => 'webartstudio_eurobank',
            'title'      => $title,
            'terms'      => '',
            'sort_order' => $this->config->get($this->key . '_sort_order')
        );
    }
}
