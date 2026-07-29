<?php
/**
 * Google Merchant XML Feed — catalog model (front-end queries only).
 *
 * Mirrors the webartstudio_skroutz model. All filtering is driven by the
 * settings array passed in; nothing is site-specific. Whitelisted column
 * names guard the dynamic field mapping.
 */
class ModelExtensionFeedWebartstudioGoogle extends Model {

	private const FIELD_WHITELIST = ['model', 'sku', 'mpn', 'ean', 'upc', 'jan', 'isbn', 'location'];

	/**
	 * Core query: every product that should appear in the feed, with the
	 * fields needed for the flat record. Per-product extras (images,
	 * options, category path) are fetched lazily by the controller.
	 */
	public function getFeedProducts(array $s): array {
		$store_id    = (int)$s['store_id'];
		$language_id = (int)$s['language_id'];

		$sql = "SELECT p.product_id, p.model, p.sku, p.mpn, p.ean, p.upc, p.jan, p.isbn, p.location,
					p.quantity, p.stock_status_id, p.image, p.price, p.weight, p.weight_class_id,
					p.tax_class_id, p.manufacturer_id, p.date_available,
					pd.name, pd.description, pd.meta_description,
					m.name AS manufacturer
				FROM `" . DB_PREFIX . "product` p
				INNER JOIN `" . DB_PREFIX . "product_description` pd
					ON (pd.product_id = p.product_id AND pd.language_id = '" . $language_id . "')
				INNER JOIN `" . DB_PREFIX . "product_to_store` p2s
					ON (p2s.product_id = p.product_id AND p2s.store_id = '" . $store_id . "')
				LEFT JOIN `" . DB_PREFIX . "manufacturer` m
					ON (m.manufacturer_id = p.manufacturer_id)
				WHERE p.status = '1'
					AND p.date_available <= NOW()";

		// --- excludes: products / manufacturers -------------------------
		if (!empty($s['exclude_product'])) {
			$sql .= " AND p.product_id NOT IN (" . implode(',', array_map('intval', $s['exclude_product'])) . ")";
		}
		if (!empty($s['exclude_manufacturer'])) {
			$sql .= " AND p.manufacturer_id NOT IN (" . implode(',', array_map('intval', $s['exclude_manufacturer'])) . ")";
		}

		// --- excludes: categories (incl. all descendants) ---------------
		if (!empty($s['exclude_category'])) {
			$cats = implode(',', array_map('intval', $s['exclude_category']));
			$sql .= " AND p.product_id NOT IN (
				SELECT p2c.product_id FROM `" . DB_PREFIX . "product_to_category` p2c
				INNER JOIN `" . DB_PREFIX . "category_path` cp ON (cp.category_id = p2c.category_id)
				WHERE cp.path_id IN (" . $cats . ")
			)";
		}

		// --- exclude by stock status ------------------------------------
		if (!empty($s['exclude_stock'])) {
			$sql .= " AND p.stock_status_id NOT IN (" . implode(',', array_map('intval', $s['exclude_stock'])) . ")";
		}

		// --- exclude by empty value -------------------------------------
		$empty = (array)($s['exclude_empty'] ?? []);
		if (in_array('image', $empty, true)) {
			$sql .= " AND p.image <> '' AND p.image IS NOT NULL";
		}
		if (in_array('manufacturer', $empty, true)) {
			$sql .= " AND p.manufacturer_id > 0";
		}
		if (in_array('price', $empty, true)) {
			$sql .= " AND p.price > 0";
		}
		if (in_array('ean', $empty, true)) {
			$col = $this->safeField($s['ean_field']);
			$sql .= " AND TRIM(COALESCE(p." . $col . ", '')) <> ''";
		}
		if (in_array('mpn', $empty, true)) {
			$col = $this->safeField($s['mpn_field']);
			$sql .= " AND TRIM(COALESCE(p." . $col . ", '')) <> ''";
		}

		$sql .= " GROUP BY p.product_id ORDER BY p.product_id ASC";

		return $this->db->query($sql)->rows;
	}

	private function safeField(string $field): string {
		return in_array($field, self::FIELD_WHITELIST, true) ? $field : 'ean';
	}

	/* ------------------------------------------------------------------ */
	/* Pricing                                                            */
	/* ------------------------------------------------------------------ */

	/** Lowest active special price for the customer group, or null. */
	public function getSpecialPrice(int $product_id, int $customer_group_id): ?float {
		$query = $this->db->query("
			SELECT price FROM `" . DB_PREFIX . "product_special`
			WHERE product_id = '" . (int)$product_id . "'
				AND customer_group_id = '" . (int)$customer_group_id . "'
				AND ((date_start = '0000-00-00' OR date_start < NOW())
					AND (date_end = '0000-00-00' OR date_end > NOW()))
			ORDER BY priority ASC, price ASC LIMIT 1
		");

		return $query->num_rows ? (float)$query->row['price'] : null;
	}

	/* ------------------------------------------------------------------ */
	/* Per-product extras                                                 */
	/* ------------------------------------------------------------------ */

	/** Deepest (most specific) category path as "A > B > C". */
	public function getCategoryPath(int $product_id, int $language_id): string {
		$category = $this->db->query("
			SELECT p2c.category_id, MAX(cp.level) AS depth
			FROM `" . DB_PREFIX . "product_to_category` p2c
			INNER JOIN `" . DB_PREFIX . "category_path` cp ON (cp.category_id = p2c.category_id)
			INNER JOIN `" . DB_PREFIX . "category` c ON (c.category_id = p2c.category_id AND c.status = '1')
			WHERE p2c.product_id = '" . (int)$product_id . "'
			GROUP BY p2c.category_id ORDER BY depth DESC LIMIT 1
		");

		if (!$category->num_rows) {
			return '';
		}

		$path = $this->db->query("
			SELECT GROUP_CONCAT(cd.name ORDER BY cp.level SEPARATOR ' > ') AS path
			FROM `" . DB_PREFIX . "category_path` cp
			INNER JOIN `" . DB_PREFIX . "category_description` cd
				ON (cd.category_id = cp.path_id AND cd.language_id = '" . (int)$language_id . "')
			WHERE cp.category_id = '" . (int)$category->row['category_id'] . "'
		");

		return $path->num_rows ? (string)$path->row['path'] : '';
	}

	/** Top-level (level 1) category name of the deepest assigned path, or ''. */
	public function getTopCategoryName(int $product_id, int $language_id): string {
		$category = $this->db->query("
			SELECT p2c.category_id, MAX(cp.level) AS depth
			FROM `" . DB_PREFIX . "product_to_category` p2c
			INNER JOIN `" . DB_PREFIX . "category_path` cp ON (cp.category_id = p2c.category_id)
			INNER JOIN `" . DB_PREFIX . "category` c ON (c.category_id = p2c.category_id AND c.status = '1')
			WHERE p2c.product_id = '" . (int)$product_id . "'
			GROUP BY p2c.category_id ORDER BY depth DESC LIMIT 1
		");

		if (!$category->num_rows) {
			return '';
		}

		$top = $this->db->query("
			SELECT cd.name
			FROM `" . DB_PREFIX . "category_path` cp
			INNER JOIN `" . DB_PREFIX . "category_description` cd
				ON (cd.category_id = cp.path_id AND cd.language_id = '" . (int)$language_id . "')
			WHERE cp.category_id = '" . (int)$category->row['category_id'] . "'
			ORDER BY cp.level ASC LIMIT 1
		");

		return $top->num_rows ? (string)$top->row['name'] : '';
	}

	/** Deepest (most specific) assigned category's own name, or ''. */
	public function getLastCategoryName(int $product_id, int $language_id): string {
		$category = $this->db->query("
			SELECT p2c.category_id, MAX(cp.level) AS depth
			FROM `" . DB_PREFIX . "product_to_category` p2c
			INNER JOIN `" . DB_PREFIX . "category_path` cp ON (cp.category_id = p2c.category_id)
			INNER JOIN `" . DB_PREFIX . "category` c ON (c.category_id = p2c.category_id AND c.status = '1')
			WHERE p2c.product_id = '" . (int)$product_id . "'
			GROUP BY p2c.category_id ORDER BY depth DESC LIMIT 1
		");

		if (!$category->num_rows) {
			return '';
		}

		$name = $this->db->query("
			SELECT name FROM `" . DB_PREFIX . "category_description`
			WHERE category_id = '" . (int)$category->row['category_id'] . "' AND language_id = '" . (int)$language_id . "'
		");

		return $name->num_rows ? (string)$name->row['name'] : '';
	}

	/** Additional product images (excluding the main image). */
	public function getAdditionalImages(int $product_id, int $limit): array {
		return $this->db->query("
			SELECT image FROM `" . DB_PREFIX . "product_image`
			WHERE product_id = '" . (int)$product_id . "' AND image <> ''
			ORDER BY sort_order ASC LIMIT " . (int)$limit . "
		")->rows;
	}

	/**
	 * Size values from a product OPTION, with per-size stock and price delta.
	 * Returns rows: [name, quantity, price, price_prefix, product_option_value_id].
	 */
	public function getOptionSizes(int $product_id, int $option_id, int $language_id): array {
		return $this->db->query("
			SELECT pov.product_option_value_id, ovd.name, pov.quantity, pov.subtract,
				pov.price, pov.price_prefix
			FROM `" . DB_PREFIX . "product_option_value` pov
			INNER JOIN `" . DB_PREFIX . "option_value_description` ovd
				ON (ovd.option_value_id = pov.option_value_id AND ovd.language_id = '" . (int)$language_id . "')
			WHERE pov.product_id = '" . (int)$product_id . "'
				AND pov.option_id = '" . (int)$option_id . "'
			ORDER BY ovd.name ASC
		")->rows;
	}

	/* ------------------------------------------------------------------ */
	/* Product relations                                                  */
	/* ------------------------------------------------------------------ */

	/** Category ids a product belongs to (direct assignments). @return int[] */
	public function getProductCategories(int $product_id): array {
		$query = $this->db->query("
			SELECT category_id FROM `" . DB_PREFIX . "product_to_category`
			WHERE product_id = '" . (int)$product_id . "'
		");
		return array_map(static fn($r) => (int)$r['category_id'], $query->rows);
	}

	/**
	 * All category ids in a product's tree (direct assignments + every
	 * ancestor). Lets "in category X (incl. descendants)" rules match by a
	 * simple intersection. @return int[]
	 */
	public function getProductCategoryPathIds(int $product_id): array {
		$query = $this->db->query("
			SELECT DISTINCT cp.path_id
			FROM `" . DB_PREFIX . "product_to_category` p2c
			INNER JOIN `" . DB_PREFIX . "category_path` cp ON (cp.category_id = p2c.category_id)
			WHERE p2c.product_id = '" . (int)$product_id . "'
		");
		return array_map(static fn($r) => (int)$r['path_id'], $query->rows);
	}

	/** Distinct text values of an ATTRIBUTE for a product (e.g. sizes/color). */
	public function getAttributeValues(int $product_id, int $attribute_id, int $language_id): array {
		$rows = $this->db->query("
			SELECT text FROM `" . DB_PREFIX . "product_attribute`
			WHERE product_id = '" . (int)$product_id . "'
				AND attribute_id = '" . (int)$attribute_id . "'
				AND language_id = '" . (int)$language_id . "'
		")->rows;

		return array_values(array_filter(array_map(static fn($r) => trim($r['text']), $rows)));
	}
}
