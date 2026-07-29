<?php
/**
 * Google Merchant XML Feed — admin model.
 *
 * Plug-and-play: no site-specific IDs are hardcoded and the module owns no
 * tables. All choices are resolved at runtime from the DB; every setting
 * (including the exclude/buffer/label lists) is stored as JSON in oc_setting.
 */
class ModelExtensionFeedWebartstudioGoogle extends Model {

	/* ------------------------------------------------------------------ */
	/* Install / Uninstall                                                */
	/* ------------------------------------------------------------------ */

	public function install(): void {
		// No schema to create — all state lives in oc_setting / oc_seo_url.
	}

	public function uninstall(): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE `code` = 'feed_webartstudio_google'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'extension/feed/webartstudio_google'");
	}

	/* ------------------------------------------------------------------ */
	/* Feed mask  →  SEO keyword pointing at the feed route               */
	/* ------------------------------------------------------------------ */

	public function setMask(string $keyword): void {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_url` WHERE `query` = 'extension/feed/webartstudio_google'");

		$keyword = trim($keyword, "/ \t\n\r\0\x0B");

		if ($keyword === '') {
			return;
		}

		foreach ($this->getLanguages() as $language) {
			$this->db->query("
				INSERT INTO `" . DB_PREFIX . "seo_url`
				SET `store_id`    = '0',
					`language_id` = '" . (int)$language['language_id'] . "',
					`query`       = 'extension/feed/webartstudio_google',
					`keyword`     = '" . $this->db->escape($keyword) . "'
			");
		}
	}

	public function getMask(): string {
		$query = $this->db->query("
			SELECT `keyword` FROM `" . DB_PREFIX . "seo_url`
			WHERE `query` = 'extension/feed/webartstudio_google' LIMIT 1
		");

		return $query->num_rows ? $query->row['keyword'] : '';
	}

	/* ------------------------------------------------------------------ */
	/* Dynamic lookups for the settings form (everything site-agnostic)   */
	/* ------------------------------------------------------------------ */

	public function getOptions(int $language_id): array {
		return $this->db->query("
			SELECT o.option_id, od.name, o.type
			FROM `" . DB_PREFIX . "option` o
			LEFT JOIN `" . DB_PREFIX . "option_description` od
				ON (od.option_id = o.option_id AND od.language_id = '" . (int)$language_id . "')
			ORDER BY od.name ASC
		")->rows;
	}

	public function getAttributes(int $language_id): array {
		return $this->db->query("
			SELECT a.attribute_id, ad.name
			FROM `" . DB_PREFIX . "attribute` a
			LEFT JOIN `" . DB_PREFIX . "attribute_description` ad
				ON (ad.attribute_id = a.attribute_id AND ad.language_id = '" . (int)$language_id . "')
			ORDER BY ad.name ASC
		")->rows;
	}

	public function getStockStatuses(int $language_id): array {
		return $this->db->query("
			SELECT stock_status_id, name
			FROM `" . DB_PREFIX . "stock_status`
			WHERE language_id = '" . (int)$language_id . "'
			ORDER BY name ASC
		")->rows;
	}

	public function getWeightClasses(int $language_id): array {
		return $this->db->query("
			SELECT wc.weight_class_id, wcd.title, wcd.unit, wc.value
			FROM `" . DB_PREFIX . "weight_class` wc
			LEFT JOIN `" . DB_PREFIX . "weight_class_description` wcd
				ON (wcd.weight_class_id = wc.weight_class_id AND wcd.language_id = '" . (int)$language_id . "')
			ORDER BY wcd.title ASC
		")->rows;
	}

	public function getStores(): array {
		$stores = [['store_id' => 0, 'name' => $this->config->get('config_name') . ' (Default)']];

		foreach ($this->db->query("SELECT store_id, name FROM `" . DB_PREFIX . "store` ORDER BY name ASC")->rows as $row) {
			$stores[] = $row;
		}

		return $stores;
	}

	public function getLanguages(): array {
		return $this->db->query("
			SELECT language_id, name, code FROM `" . DB_PREFIX . "language`
			WHERE status = '1' ORDER BY sort_order, name
		")->rows;
	}

	public function getCustomerGroups(int $language_id): array {
		return $this->db->query("
			SELECT cg.customer_group_id, cgd.name
			FROM `" . DB_PREFIX . "customer_group` cg
			LEFT JOIN `" . DB_PREFIX . "customer_group_description` cgd
				ON (cgd.customer_group_id = cg.customer_group_id AND cgd.language_id = '" . (int)$language_id . "')
			ORDER BY cg.sort_order, cgd.name
		")->rows;
	}

	public function getManufacturers(): array {
		return $this->db->query("
			SELECT manufacturer_id, name FROM `" . DB_PREFIX . "manufacturer` ORDER BY name ASC
		")->rows;
	}

	/* ------------------------------------------------------------------ */
	/* Autocomplete (admin) + name resolvers for saved rows               */
	/* ------------------------------------------------------------------ */

	public function getProductsByName(string $name, int $language_id, int $limit = 10): array {
		return $this->db->query("
			SELECT p.product_id, pd.name, p.model
			FROM `" . DB_PREFIX . "product` p
			LEFT JOIN `" . DB_PREFIX . "product_description` pd
				ON (pd.product_id = p.product_id AND pd.language_id = '" . (int)$language_id . "')
			WHERE pd.name LIKE '" . $this->db->escape('%' . $name . '%') . "'
			ORDER BY pd.name ASC
			LIMIT " . (int)$limit . "
		")->rows;
	}

	public function getProductName(int $product_id, int $language_id): string {
		$query = $this->db->query("
			SELECT name FROM `" . DB_PREFIX . "product_description`
			WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$language_id . "'
		");

		return $query->num_rows ? $query->row['name'] : ('#' . $product_id);
	}

	public function getCategoriesByName(string $name, int $language_id, int $limit = 10): array {
		$rows = $this->db->query("
			SELECT c.category_id, cd.name,
				(SELECT GROUP_CONCAT(cd2.name ORDER BY cp.level SEPARATOR ' > ')
					FROM `" . DB_PREFIX . "category_path` cp
					LEFT JOIN `" . DB_PREFIX . "category_description` cd2
						ON (cd2.category_id = cp.path_id AND cd2.language_id = '" . (int)$language_id . "')
					WHERE cp.category_id = c.category_id) AS path
			FROM `" . DB_PREFIX . "category` c
			LEFT JOIN `" . DB_PREFIX . "category_description` cd
				ON (cd.category_id = c.category_id AND cd.language_id = '" . (int)$language_id . "')
			WHERE cd.name LIKE '" . $this->db->escape('%' . $name . '%') . "'
			ORDER BY path ASC
			LIMIT " . (int)$limit . "
		")->rows;

		return $rows;
	}

	public function getCategoryName(int $category_id, int $language_id): string {
		$query = $this->db->query("
			SELECT GROUP_CONCAT(cd.name ORDER BY cp.level SEPARATOR ' > ') AS path
			FROM `" . DB_PREFIX . "category_path` cp
			LEFT JOIN `" . DB_PREFIX . "category_description` cd
				ON (cd.category_id = cp.path_id AND cd.language_id = '" . (int)$language_id . "')
			WHERE cp.category_id = '" . (int)$category_id . "'
		");

		return ($query->num_rows && $query->row['path']) ? $query->row['path'] : ('#' . $category_id);
	}

	public function getManufacturerName(int $manufacturer_id): string {
		$query = $this->db->query("
			SELECT name FROM `" . DB_PREFIX . "manufacturer` WHERE manufacturer_id = '" . (int)$manufacturer_id . "'
		");

		return $query->num_rows ? $query->row['name'] : ('#' . $manufacturer_id);
	}

	public function getManufacturersByName(string $name, int $limit = 10): array {
		return $this->db->query("
			SELECT manufacturer_id, name FROM `" . DB_PREFIX . "manufacturer`
			WHERE name LIKE '" . $this->db->escape('%' . $name . '%') . "'
			ORDER BY name ASC LIMIT " . (int)$limit . "
		")->rows;
	}

	/**
	 * Every category as a flat list with full-path name + parent_id
	 * (for the exclude checkbox list and descendant auto-checking).
	 */
	public function getCategories(int $language_id): array {
		return $this->db->query("
			SELECT c.category_id, c.parent_id,
				(SELECT GROUP_CONCAT(cd2.name ORDER BY cp.level SEPARATOR ' > ')
					FROM `" . DB_PREFIX . "category_path` cp
					LEFT JOIN `" . DB_PREFIX . "category_description` cd2
						ON (cd2.category_id = cp.path_id AND cd2.language_id = '" . (int)$language_id . "')
					WHERE cp.category_id = c.category_id) AS name
			FROM `" . DB_PREFIX . "category` c
			ORDER BY name ASC
		")->rows;
	}
}
