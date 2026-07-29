<?php
class ModelExtensionModuleProductImportExport extends Model {

        private $attribute_columns = array(
                'Φύλο',
                'Αξεσουάρ',
                'Υλικό',
                'Σειρά',
                'Χρώμα',
                'Διάσταση',
                'Αδιάβροχο',
                'Διάμετρος Κάσας',
                'Κάντραν',
                'Εγγύηση',
                'Κρύσταλο',
                'Μηχανισμός',
                'Υλικό Δεσίματος',
                'Κάσα',
                'Δέσιμο',
                'Σχήμα Πλαισίου',
                'Χρώμα Δεσίματος',
                'Μέγεθος Δαχτυλιδιού',
                'Χρώμα Πλαισίου',
        );

        public function install() {
                $this->load->model('setting/event');
                $this->model_setting_event->deleteEventByCode('product_import_export');
                $this->model_setting_event->addEvent('product_import_export', 'admin/view/catalog/product_list/after', 'extension/module/product_import_export/eventProductListAfter');
                $this->load->model('setting/setting');
                $this->model_setting_setting->editSetting('module_product_import_export', array('module_product_import_export_status' => 1));
        }

        public function uninstall() {
                $this->load->model('setting/event');
                $this->model_setting_event->deleteEventByCode('product_import_export');
        }

        public function import($file) {
                $this->load->language('extension/module/product_import_export');

                $handle = fopen($file, 'r');

                if (!$handle) {
                        return array('error' => 'error_upload');
                }

                $first_line = fgets($handle);

                if ($first_line === false) {
                        fclose($handle);
                        return array('error' => 'error_empty');
                }

                if (strncmp($first_line, "\xEF\xBB\xBF", 3) === 0) {
                        $first_line = substr($first_line, 3);
                }

                $delimiter = (substr_count($first_line, ';') >= substr_count($first_line, ',')) ? ';' : ',';

                $header = array_map(function ($column) {
                        return strtolower(trim($column));
                }, str_getcsv(trim($first_line), $delimiter));

                if (!in_array('product_id', $header) && !in_array('name', $header)) {
                        fclose($handle);
                        return array('error' => 'error_header');
                }

                // Βρες attribute columns στο header (με prefix attr_)
                $attr_header_map = array(); // header_index -> attr_name
                foreach ($header as $index => $col) {
                        if (strpos($col, 'attr_') === 0) {
                                $attr_name = substr($col, 5); // αφαίρεσε το attr_
                                $attr_header_map[$index] = $attr_name;
                        }
                }

                $updated = 0;
                $created = 0;
                $skipped = 0;
                $errors = array();
                $row_number = 1;

                while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $row_number++;

                        if (($row === array(null)) || (implode('', array_map('trim', $row)) === '')) {
                                continue;
                        }

                        $values = array();

                        foreach ($header as $index => $column) {
                                $values[$column] = isset($row[$index]) ? trim($row[$index]) : '';
                        }

                        $product_id = !empty($values['product_id']) ? (int)$values['product_id'] : 0;

                        if ($product_id) {
                                $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

                                if (!$query->num_rows) {
                                        $skipped++;
                                        if (count($errors) < 20) {
                                                $errors[] = sprintf($this->language->get('text_row_not_found'), $row_number, $product_id);
                                        }
                                        continue;
                                }

                                $this->updateProduct($product_id, $values, $header);

                                // Update attributes αν υπάρχουν στο CSV
                                if (!empty($attr_header_map)) {
                                        $this->updateProductAttributes($product_id, $row, $attr_header_map);
                                }

                                $updated++;
                        } else {
                                if (empty($values['name'])) {
                                        $skipped++;
                                        if (count($errors) < 20) {
                                                $errors[] = sprintf($this->language->get('text_row_no_name'), $row_number);
                                        }
                                        continue;
                                }

                                $product_id = $this->createProduct($values);

                                // Create attributes
                                if ($product_id && !empty($attr_header_map)) {
                                        $this->updateProductAttributes($product_id, $row, $attr_header_map);
                                }

                                $created++;
                        }
                }

                fclose($handle);

                return array(
                        'updated' => $updated,
                        'created' => $created,
                        'skipped' => $skipped,
                        'errors'  => $errors
                );
        }

        private function updateProduct($product_id, $values, $header) {
                $set = array();

                if (in_array('model', $header)) {
                        $set[] = "model = '" . $this->db->escape($values['model']) . "'";
                }
                if (in_array('sku', $header)) {
                        $set[] = "sku = '" . $this->db->escape($values['sku']) . "'";
                }
                if (in_array('ean', $header)) {
                        $set[] = "ean = '" . $this->db->escape($values['ean']) . "'";
                }
                if (in_array('quantity', $header) && ($values['quantity'] !== '')) {
                        $set[] = "quantity = '" . (int)$values['quantity'] . "'";
                }
                if (in_array('price', $header) && ($values['price'] !== '')) {
                        $set[] = "price = '" . $this->toDecimal($values['price']) . "'";
                }
                if (in_array('status', $header) && ($values['status'] !== '')) {
                        $set[] = "status = '" . (int)$values['status'] . "'";
                }
                if (in_array('weight', $header) && ($values['weight'] !== '')) {
                        $set[] = "weight = '" . $this->toDecimal($values['weight']) . "'";
                }

                if ($set) {
                        $set[] = "date_modified = NOW()";
                        $this->db->query("UPDATE " . DB_PREFIX . "product SET " . implode(', ', $set) . " WHERE product_id = '" . (int)$product_id . "'");
                }

                if (in_array('name', $header) && ($values['name'] !== '')) {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_description SET name = '" . $this->db->escape($values['name']) . "' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "'");
                }

                // Αφαίρεσε special price (δεν χρησιμοποιείται πλέον)
                // Αν θέλεις να καθαρίσεις υπάρχοντα special prices:
                // $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
        }

        private function updateProductAttributes($product_id, $row, $attr_header_map) {
                foreach ($attr_header_map as $index => $attr_name) {
                        $value = isset($row[$index]) ? trim($row[$index]) : '';
                        if ($value === '') continue;

                        // Βρες attribute_id
                        $attr_query = $this->db->query("
                                SELECT a.attribute_id FROM " . DB_PREFIX . "attribute a
                                JOIN " . DB_PREFIX . "attribute_description ad ON ad.attribute_id = a.attribute_id
                                WHERE ad.name = '" . $this->db->escape($attr_name) . "' AND ad.language_id = 1
                                LIMIT 1
                        ");

                        if (!$attr_query->num_rows) continue;

                        $attribute_id = $attr_query->row['attribute_id'];

                        // Update για language_id 1 και 2
                        foreach (array(1, 2) as $language_id) {
                                $existing = $this->db->query("
                                        SELECT * FROM " . DB_PREFIX . "product_attribute
                                        WHERE product_id = '" . (int)$product_id . "'
                                        AND attribute_id = '" . (int)$attribute_id . "'
                                        AND language_id = '" . (int)$language_id . "'
                                ");

                                if ($existing->num_rows) {
                                        $this->db->query("
                                                UPDATE " . DB_PREFIX . "product_attribute
                                                SET text = '" . $this->db->escape($value) . "'
                                                WHERE product_id = '" . (int)$product_id . "'
                                                AND attribute_id = '" . (int)$attribute_id . "'
                                                AND language_id = '" . (int)$language_id . "'
                                        ");
                                } else {
                                        $this->db->query("
                                                INSERT INTO " . DB_PREFIX . "product_attribute (product_id, attribute_id, language_id, text)
                                                VALUES ('" . (int)$product_id . "', '" . (int)$attribute_id . "', '" . (int)$language_id . "', '" . $this->db->escape($value) . "')
                                        ");
                                }
                        }
                }
        }

        private function createProduct($values) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product SET model = '" . $this->db->escape(isset($values['model']) ? $values['model'] : '') . "', sku = '" . $this->db->escape(isset($values['sku']) ? $values['sku'] : '') . "', upc = '', ean = '" . $this->db->escape(isset($values['ean']) ? $values['ean'] : '') . "', jan = '', isbn = '', mpn = '', location = '', quantity = '" . (int)(isset($values['quantity']) ? $values['quantity'] : 0) . "', minimum = '1', subtract = '1', stock_status_id = '" . (int)$this->config->get('config_stock_status_id') . "', date_available = NOW(), manufacturer_id = '0', shipping = '1', price = '" . $this->toDecimal(isset($values['price']) ? $values['price'] : '0') . "', points = '0', weight = '" . $this->toDecimal(isset($values['weight']) ? $values['weight'] : '0') . "', weight_class_id = '1', length = '0', width = '0', height = '0', length_class_id = '1', status = '" . (int)(isset($values['status']) ? $values['status'] : 0) . "', tax_class_id = '0', sort_order = '0', date_added = NOW(), date_modified = NOW()");

                $product_id = $this->db->getLastId();

                $language_query = $this->db->query("SELECT language_id FROM " . DB_PREFIX . "language");

                foreach ($language_query->rows as $language) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language['language_id'] . "', name = '" . $this->db->escape($values['name']) . "', description = '', tag = '', meta_title = '" . $this->db->escape($values['name']) . "', meta_description = '', meta_keyword = ''");
                }

                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '0'");

                return $product_id;
        }

        private function toDecimal($value) {
                $value = trim((string)$value);
                if ((strpos($value, ',') !== false) && (strpos($value, '.') !== false)) {
                        $value = str_replace('.', '', $value);
                }
                $value = str_replace(',', '.', $value);
                return (float)$value;
        }
}
