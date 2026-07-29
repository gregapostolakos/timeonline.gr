<?php

class ModelExtensionModuleBulkSpecial extends Model
{

    public function getBulkSpecials(): array
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bulk_special ORDER BY date_modified DESC");

        return $query->rows;
    }

    public function getBulkSpecial(int $bulk_special_id): array
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "bulk_special WHERE bulk_special_id = '" . (int)$bulk_special_id . "'");

        return $query->row;
    }

    public function addBulkSpecial(array $data): int
    {
        $this->db->query(
            "INSERT INTO " . DB_PREFIX . "bulk_special
            SET name = '" . $this->db->escape($data['name']) . "',
                customer_group_ids = '" . $this->db->escape(json_encode($data['customer_group_ids'] ?? [])) . "',
                discount_percent = '" . (float)$data['discount_percent'] . "',
                priority = '" . (int)$data['priority'] . "',
                date_start = '" . $this->db->escape($data['date_start']) . "',
                date_end = '" . $this->db->escape($data['date_end']) . "',
                status = '" . (int)$data['status'] . "',
                category_ids = '" . $this->db->escape(json_encode($data['category_ids'] ?? [])) . "',
                ex_category_ids = '" . $this->db->escape(json_encode($data['ex_category_ids'] ?? [])) . "',
                manufacturer_ids = '" . $this->db->escape(json_encode($data['manufacturer_ids'] ?? [])) . "',
                ex_manufacturer_ids = '" . $this->db->escape(json_encode($data['ex_manufacturer_ids'] ?? [])) . "',
                product_ids = '" . $this->db->escape(json_encode($data['product_ids'] ?? [])) . "',
                ex_product_ids = '" . $this->db->escape(json_encode($data['ex_product_ids'] ?? [])) . "',
                date_added = NOW(),
                date_modified = NOW()"
        );

        $bulk_special_id = $this->db->getLastId();

        // Apply bulk special to products
        if ((int)$data['status'] === 1) {
            $this->applyBulkSpecial($bulk_special_id, $data);
        }

        return $bulk_special_id;
    }

    public function editBulkSpecial(int $bulk_special_id, array $data): void
    {
        $this->db->query(
            "UPDATE " . DB_PREFIX . "bulk_special
                SET name = '" . $this->db->escape($data['name']) . "',
                    customer_group_ids = '" . $this->db->escape(json_encode($data['customer_group_ids'] ?? [])) . "',
                    discount_percent = '" . (float)$data['discount_percent'] . "',
                    priority = '" . (int)$data['priority'] . "',
                    date_start = '" . $this->db->escape($data['date_start']) . "',
                    date_end = '" . $this->db->escape($data['date_end']) . "',
                    status = '" . (int)$data['status'] . "',
                    category_ids = '" . $this->db->escape(json_encode($data['category_ids'] ?? [])) . "',
                    ex_category_ids = '" . $this->db->escape(json_encode($data['ex_category_ids'] ?? [])) . "',
                    manufacturer_ids = '" . $this->db->escape(json_encode($data['manufacturer_ids'] ?? [])) . "',
                    ex_manufacturer_ids = '" . $this->db->escape(json_encode($data['ex_manufacturer_ids'] ?? [])) . "',
                    product_ids = '" . $this->db->escape(json_encode($data['product_ids'] ?? [])) . "',
                    ex_product_ids = '" . $this->db->escape(json_encode($data['ex_product_ids'] ?? [])) . "',
                    date_modified = NOW()
                WHERE bulk_special_id = '" . (int)$bulk_special_id . "'"
        );

        // Remove old specials
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "product_special
			 WHERE bulk_special_id = '" . (int)$bulk_special_id . "'"
        );

        // Re-apply bulk special
        if ((int)$data['status'] === 1) {
            $this->applyBulkSpecial($bulk_special_id, $data);
        }
    }

    public function deleteBulkSpecial(int $bulk_special_id): void
    {
        // Delete product specials
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "product_special
			 WHERE bulk_special_id = '" . (int)$bulk_special_id . "'"
        );

        // Delete bulk special
        $this->db->query(
            "DELETE FROM " . DB_PREFIX . "bulk_special
			 WHERE bulk_special_id = '" . (int)$bulk_special_id . "'"
        );
    }

    private function applyBulkSpecial(int $bulk_special_id, array $data): void
    {
        $include_product_ids = [];

        // Categories
        if (!empty($data['category_ids'])) {
            $query = $this->db->query(
                "SELECT DISTINCT product_id
                FROM " . DB_PREFIX . "product_to_category
                WHERE category_id IN (" . implode(',', array_map('intval', $data['category_ids'])) . ")"
            );

            foreach ($query->rows as $row) {
                $include_product_ids[] = (int)$row['product_id'];
            }
        }

        // Manufacturers
        if (!empty($data['manufacturer_ids'])) {
            $query = $this->db->query(
                "SELECT product_id
                FROM " . DB_PREFIX . "product
                WHERE manufacturer_id IN (" . implode(',', array_map('intval', $data['manufacturer_ids'])) . ")"
            );

            foreach ($query->rows as $row) {
                $include_product_ids[] = (int)$row['product_id'];
            }
        }

        // Manual products
        if (!empty($data['product_ids'])) {
            foreach ($data['product_ids'] as $product_id) {
                $include_product_ids[] = (int)$product_id;
            }
        }

        $include_product_ids = array_unique($include_product_ids);

        $exclude_product_ids = [];

        // Excluded categories
        if (!empty($data['ex_category_ids'])) {
            $query = $this->db->query(
                "SELECT DISTINCT product_id
                FROM " . DB_PREFIX . "product_to_category
                WHERE category_id IN (" . implode(',', array_map('intval', $data['ex_category_ids'])) . ")"
            );

            foreach ($query->rows as $row) {
                $exclude_product_ids[] = (int)$row['product_id'];
            }
        }

        // Excluded manufacturers
        if (!empty($data['ex_manufacturer_ids'])) {
            $query = $this->db->query(
                "SELECT product_id
                FROM " . DB_PREFIX . "product
                WHERE manufacturer_id IN (" . implode(',', array_map('intval', $data['ex_manufacturer_ids'])) . ")"
            );

            foreach ($query->rows as $row) {
                $exclude_product_ids[] = (int)$row['product_id'];
            }
        }

        // Excluded manual products
        if (!empty($data['ex_product_ids'])) {
            foreach ($data['ex_product_ids'] as $product_id) {
                $exclude_product_ids[] = (int)$product_id;
            }
        }

        $exclude_product_ids = array_unique($exclude_product_ids);



        $final_product_ids = array_diff($include_product_ids, $exclude_product_ids);

        if (!$final_product_ids) {
            return;
        }

        //return;
        foreach ($final_product_ids as $product_id) {

            // Get product price
            $query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");

            if (!$query->num_rows) {
                continue;
            }

            $price = (float)$query->row['price'];
            $special_price = $price - ($price * (float)$data['discount_percent'] / 100);

            // Normalize customer group ids
            if (is_array($data['customer_group_ids'] ?? null)) {
                $customer_group_ids = $data['customer_group_ids'];
            } else {
                $customer_group_ids = json_decode($data['customer_group_ids'] ?? '[]', true);
            }

            $customer_group_ids = array_map('intval', $customer_group_ids);

            foreach ($customer_group_ids as $customer_group_id) {

                $this->db->query(
                    "INSERT INTO " . DB_PREFIX . "product_special
                    SET product_id = '" . (int)$product_id . "',
                        customer_group_id = '" . (int)$customer_group_id . "',
                        priority = '" . (int)$data['priority'] . "',
                        price = '" . (float)$special_price . "',
                        date_start = '" . $this->db->escape($data['date_start']) . "',
                        date_end = '" . $this->db->escape($data['date_end']) . "',
                        bulk_special_id = '" . (int)$bulk_special_id . "'"
                );
            }
        }
    }

    public function install(): void
    {
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "bulk_special` (
                `bulk_special_id` INT NOT NULL AUTO_INCREMENT,
                `name` VARCHAR(255) NOT NULL,
                `customer_group_ids` TEXT NULL,
                `discount_percent` DECIMAL(5,2) NOT NULL,
                `priority` INT NOT NULL DEFAULT 1,
                `date_start` DATE NOT NULL,
                `date_end` DATE NOT NULL,
                `status` TINYINT(1) NOT NULL DEFAULT 1,
                `category_ids` TEXT NULL,
                `ex_category_ids` TEXT NULL,
                `manufacturer_ids` TEXT NULL,
                `ex_manufacturer_ids` TEXT NULL,
                `product_ids` TEXT NULL,
                `ex_product_ids` TEXT NULL,
                `date_added` DATETIME NOT NULL,
                `date_modified` DATETIME NOT NULL,
                PRIMARY KEY (`bulk_special_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8"
        );

        // Add bulk_special_id column
        $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_special` LIKE 'bulk_special_id'");

        if (!$query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "product_special` ADD COLUMN `bulk_special_id` INT NULL");
        }
    }

    public function uninstall(): void
    {
        // Remove column
        $query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "product_special` LIKE 'bulk_special_id'");

        if ($query->num_rows) {
            $this->db->query("ALTER TABLE `" . DB_PREFIX . "product_special` DROP COLUMN `bulk_special_id`");
        }

        // Drop table
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "bulk_special`");
    }
}
