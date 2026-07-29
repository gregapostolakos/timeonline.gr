<?php
/**
 * Eurobank Payment Gateway (Eurobank e-Commerce / Cardlink)
 * Admin model - Webartstudio
 */
class ModelExtensionPaymentWebartstudioEurobank extends Model {

    // Δημιουργια του πινακα log συναλλαγων κατα την εγκατασταση
    public function install() {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "webartstudio_eurobank` (
                `id`               INT(11)        NOT NULL AUTO_INCREMENT,
                `order_id`         INT(11)        NOT NULL DEFAULT 0,
                `reference`        VARCHAR(64)    NOT NULL,
                `amount`           DECIMAL(15,2)  NOT NULL DEFAULT 0.00,
                `currency`         VARCHAR(3)     NOT NULL DEFAULT '',
                `installments`     INT(3)         NOT NULL DEFAULT 0,
                `interest`         DECIMAL(6,2)   NOT NULL DEFAULT 0.00,
                `status`           VARCHAR(20)    NOT NULL DEFAULT 'PENDING',
                `tx_id`            VARCHAR(64)    NOT NULL DEFAULT '',
                `payment_ref`      VARCHAR(64)    NOT NULL DEFAULT '',
                `pay_method`       VARCHAR(32)    NOT NULL DEFAULT '',
                `message`          VARCHAR(255)   NOT NULL DEFAULT '',
                `date_added`       DATETIME       NULL DEFAULT NULL,
                `date_modified`    DATETIME       NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `reference` (`reference`),
                KEY `order_id`  (`order_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }

    // Διατηρουμε τον πινακα στο uninstall ωστε να μη χανονται ιστορικα δεδομενα
    public function uninstall() {
        // Σκοπιμα κενο. Αν θες πληρη διαγραφη, ξεσχολιασε την επομενη γραμμη.
        // $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "webartstudio_eurobank`;");
    }
}
