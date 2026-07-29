<?php
/**
 * Meta Catalog XML Feed — admin controller.
 *
 * Single-instance feed extension. Settings live in oc_setting (group
 * "feed_webartstudio_meta"); per-entity excludes, buffers and custom-label
 * rules live inside the same JSON. Fully plug-and-play: every dropdown is
 * populated from the live DB.
 */
class ControllerExtensionFeedWebartstudioMeta extends Controller {
	private $error = array();

	// Google product-level availability vocabulary.
	private $availability_expressions = array('in stock', 'out of stock', 'available for order', 'preorder', 'discontinued');

	private $conditions   = array('new', 'refurbished', 'used');
	private $genders      = array('', 'male', 'female', 'unisex');
	private $age_groups   = array('', 'newborn', 'infant', 'toddler', 'kids', 'adult');
	private $field_sources = array('model', 'sku', 'mpn', 'ean', 'upc', 'jan', 'isbn', 'location');

	// Rule condition types for the custom-label overrides.
	private $rule_types = array('category', 'manufacturer', 'price_min', 'price_max', 'on_sale');

	public function index(): void {
		$this->load->language('extension/feed/webartstudio_meta');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/feed/webartstudio_meta');
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$post = $this->collectListSettings($this->request->post);
			$this->model_setting_setting->editSetting('feed_webartstudio_meta', $post);

			$this->model_extension_feed_webartstudio_meta->setMask($this->request->post['feed_webartstudio_meta_mask'] ?? '');

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/feed/webartstudio_meta', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getForm();
	}

	protected function getForm(): void {
		$this->document->addStyle('view/stylesheet/webartstudio/was-admin.css');

		$data['error_warning'] = $this->error['warning'] ?? '';

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$data['breadcrumbs'] = array(
			array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
			),
			array(
				'text' => $this->language->get('text_extension'),
				'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true),
			),
			array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('extension/feed/webartstudio_meta', 'user_token=' . $this->session->data['user_token'], true),
			),
		);

		$data['action']     = $this->url->link('extension/feed/webartstudio_meta', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel']     = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);
		$data['user_token'] = $this->session->data['user_token'];

		$data['autocomplete_product']  = $this->url->link('extension/feed/webartstudio_meta/autocomplete', 'user_token=' . $this->session->data['user_token'] . '&type=product', true);
		$data['autocomplete_category'] = $this->url->link('extension/feed/webartstudio_meta/autocomplete', 'user_token=' . $this->session->data['user_token'] . '&type=category', true);

		// ---- saved settings (POST > config > default) -------------------
		$defaults = $this->getDefaults();
		foreach ($defaults as $key => $default) {
			if (isset($this->request->post[$key])) {
				$data[$key] = $this->request->post[$key];
			} elseif ($this->config->get($key) !== null) {
				$data[$key] = $this->config->get($key);
			} else {
				$data[$key] = $default;
			}
		}

		$data['feed_webartstudio_meta_mask'] = $this->request->post['feed_webartstudio_meta_mask'] ?? $this->model_extension_feed_webartstudio_meta->getMask();

		// ---- dynamic lookups (site-agnostic) ----------------------------
		$language_id = (int)($data['feed_webartstudio_meta_language_id'] ?: $this->getDefaultLanguageId());

		$data['languages']       = $this->model_extension_feed_webartstudio_meta->getLanguages();
		$data['stores']          = $this->model_extension_feed_webartstudio_meta->getStores();
		$data['customer_groups'] = $this->model_extension_feed_webartstudio_meta->getCustomerGroups($language_id);
		$data['options']         = $this->model_extension_feed_webartstudio_meta->getOptions($language_id);
		$data['attributes']      = $this->model_extension_feed_webartstudio_meta->getAttributes($language_id);
		$data['stock_statuses']  = $this->model_extension_feed_webartstudio_meta->getStockStatuses($language_id);
		$data['weight_classes']  = $this->model_extension_feed_webartstudio_meta->getWeightClasses($language_id);
		$data['manufacturers']   = $this->model_extension_feed_webartstudio_meta->getManufacturers();
		$data['categories']      = $this->model_extension_feed_webartstudio_meta->getCategories($language_id);
		$data['field_sources']   = $this->field_sources;
		$data['availability_expressions'] = $this->availability_expressions;
		$data['conditions']      = $this->conditions;
		$data['genders']         = $this->genders;
		$data['age_groups']      = $this->age_groups;
		$data['rule_types']      = $this->rule_types;
		$data['label_indexes']   = array(0, 1, 2, 3, 4);

		// ---- the live feed URL (clean mask if set, otherwise the route) --
		$mask = $data['feed_webartstudio_meta_mask'];
		$data['feed_url'] = $mask
			? rtrim(HTTP_CATALOG, '/') . '/' . ltrim($mask, '/')
			: HTTP_CATALOG . 'index.php?route=extension/feed/webartstudio_meta';

		// ---- saved excludes --------------------------------------------
		$data['exclude_products']              = $this->resolveExcludes('product', $language_id);
		$data['exclude_selected_category']     = $this->excludeSelectedIds('category');
		$data['exclude_selected_manufacturer'] = $this->excludeSelectedIds('manufacturer');

		// ---- saved buffers (resolved to names for display) --------------
		$data['buffers'] = array(
			'product'      => $this->resolveBuffers('product', $language_id),
			'category'     => $this->resolveBuffers('category', $language_id),
			'manufacturer' => $this->resolveBuffers('manufacturer', $language_id),
		);

		// ---- saved custom-label sources + rules -------------------------
		$data['label_sources'] = array();
		for ($i = 0; $i < 5; $i++) {
			$key = 'feed_webartstudio_meta_label_' . $i . '_source';
			$data['label_sources'][$i] = isset($this->request->post[$key]) ? $this->request->post[$key] : ($this->config->get($key) ?: 'none');
		}
		$data['label_rules'] = $this->resolveLabelRules($language_id);

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/webartstudio_meta', $data));
	}

	/* ------------------------------------------------------------------ */
	/* Defaults                                                           */
	/* ------------------------------------------------------------------ */

	private function getDefaults(): array {
		return array(
			'feed_webartstudio_meta_status'               => 0,
			'feed_webartstudio_meta_store_id'             => 0,
			'feed_webartstudio_meta_language_id'          => $this->getDefaultLanguageId(),
			'feed_webartstudio_meta_customer_group_id'    => (int)$this->config->get('config_customer_group_id'),
			'feed_webartstudio_meta_currency'             => 'EUR',
			'feed_webartstudio_meta_weight_unit'          => 'g',
			'feed_webartstudio_meta_mpn_field'            => 'mpn',
			'feed_webartstudio_meta_ean_field'            => 'ean',
			'feed_webartstudio_meta_desc_source'          => 'description',
			'feed_webartstudio_meta_vat_default'          => 24,
			'feed_webartstudio_meta_strip_html'           => 1,
			'feed_webartstudio_meta_variations'           => 1,
			'feed_webartstudio_meta_size_source'          => 'none',
			'feed_webartstudio_meta_color_source'         => 'none',
			'feed_webartstudio_meta_additional_images'    => 1,
			'feed_webartstudio_meta_condition'            => 'new',
			'feed_webartstudio_meta_gender'               => '',
			'feed_webartstudio_meta_age_group'            => '',
			'feed_webartstudio_meta_google_category'      => '',
			'feed_webartstudio_meta_availability_instock' => 'in stock',
			'feed_webartstudio_meta_availability_map'     => array(),
			'feed_webartstudio_meta_exclude_stock'        => array(),
			'feed_webartstudio_meta_exclude_empty'        => array(),
			'feed_webartstudio_meta_buffer_op'            => 'percent',
			'feed_webartstudio_meta_buffer_val'           => 0,
			'feed_webartstudio_meta_buffer_round'         => 0,
			'feed_webartstudio_meta_shipping'             => '',
			'feed_webartstudio_meta_shipping_country'     => 'GR',
			'feed_webartstudio_meta_price_buckets'        => '0-30,30-60,60-100,100-',
			'feed_webartstudio_meta_label_0_source'       => 'none',
			'feed_webartstudio_meta_label_1_source'       => 'none',
			'feed_webartstudio_meta_label_2_source'       => 'none',
			'feed_webartstudio_meta_label_3_source'       => 'none',
			'feed_webartstudio_meta_label_4_source'       => 'none',
		);
	}

	private function getDefaultLanguageId(): int {
		$this->load->model('extension/feed/webartstudio_meta');
		foreach ($this->model_extension_feed_webartstudio_meta->getLanguages() as $language) {
			if ($language['code'] === 'el-gr') {
				return (int)$language['language_id'];
			}
		}
		$config_id = (int)$this->config->get('config_language_id');
		return $config_id ?: 1;
	}

	/* ------------------------------------------------------------------ */
	/* Save helpers — fold list inputs into JSON setting keys             */
	/* ------------------------------------------------------------------ */

	private function collectListSettings(array $post): array {
		foreach (array('product', 'category', 'manufacturer') as $type) {
			$ids = array_values(array_unique(array_filter(
				array_map('intval', (array)($post['exclude_' . $type] ?? array())),
				static fn($v) => $v > 0
			)));
			$post['feed_webartstudio_meta_exclude_' . $type] = $ids;

			$bids  = (array)($post['buffer_' . $type . '_id']  ?? array());
			$bops  = (array)($post['buffer_' . $type . '_op']  ?? array());
			$bvals = (array)($post['buffer_' . $type . '_val'] ?? array());
			$rules = array();
			foreach ($bids as $i => $rid) {
				$rid = (int)$rid;
				if ($rid > 0) {
					$rules[] = array(
						'id'        => $rid,
						'operation' => (($bops[$i] ?? 'percent') === 'fixed') ? 'fixed' : 'percent',
						'value'     => (float)($bvals[$i] ?? 0),
					);
				}
			}
			$post['feed_webartstudio_meta_buffer_' . $type] = $rules;
		}

		// custom-label override rules
		$labels  = (array)($post['label_rule_label']  ?? array());
		$types   = (array)($post['label_rule_type']   ?? array());
		$refs    = (array)($post['label_rule_ref']    ?? array());
		$nums    = (array)($post['label_rule_num']    ?? array());
		$outputs = (array)($post['label_rule_output'] ?? array());
		$label_rules = array();
		foreach ($types as $i => $type) {
			$type = (string)$type;
			if (!in_array($type, $this->rule_types, true)) {
				continue;
			}
			$output = trim((string)($outputs[$i] ?? ''));
			if ($output === '') {
				continue;
			}
			$label_rules[] = array(
				'label'  => max(0, min(4, (int)($labels[$i] ?? 0))),
				'type'   => $type,
				'ref'    => (int)($refs[$i] ?? 0),
				'num'    => (float)($nums[$i] ?? 0),
				'output' => $output,
			);
		}
		$post['feed_webartstudio_meta_label_rules'] = $label_rules;

		return $post;
	}

	private function resolveExcludes(string $type, int $language_id): array {
		if (isset($this->request->post['exclude_' . $type])) {
			$ids = array_map('intval', (array)$this->request->post['exclude_' . $type]);
		} else {
			$ids = (array)$this->config->get('feed_webartstudio_meta_exclude_' . $type);
		}

		$rows = array();
		foreach ($ids as $reference_id) {
			$reference_id = (int)$reference_id;
			if ($reference_id > 0) {
				$rows[] = array(
					'reference_id' => $reference_id,
					'name'         => $this->resolveName($type, $reference_id, $language_id),
				);
			}
		}
		return $rows;
	}

	private function resolveBuffers(string $type, int $language_id): array {
		$rows = array();

		if (isset($this->request->post['buffer_' . $type . '_id'])) {
			$ids  = (array)$this->request->post['buffer_' . $type . '_id'];
			$ops  = (array)($this->request->post['buffer_' . $type . '_op']  ?? array());
			$vals = (array)($this->request->post['buffer_' . $type . '_val'] ?? array());
			foreach ($ids as $i => $rid) {
				$rid = (int)$rid;
				if ($rid > 0) {
					$rows[] = array(
						'reference_id' => $rid,
						'operation'    => $ops[$i] ?? 'percent',
						'value'        => $vals[$i] ?? 0,
						'name'         => $this->resolveName($type, $rid, $language_id),
					);
				}
			}
			return $rows;
		}

		foreach ((array)$this->config->get('feed_webartstudio_meta_buffer_' . $type) as $rule) {
			$rid = (int)($rule['id'] ?? 0);
			if ($rid > 0) {
				$rows[] = array(
					'reference_id' => $rid,
					'operation'    => $rule['operation'] ?? 'percent',
					'value'        => $rule['value'] ?? 0,
					'name'         => $this->resolveName($type, $rid, $language_id),
				);
			}
		}
		return $rows;
	}

	/** Saved custom-label rules, resolved to names for category/manufacturer refs. */
	private function resolveLabelRules(int $language_id): array {
		if (isset($this->request->post['label_rule_type'])) {
			$labels  = (array)($this->request->post['label_rule_label']  ?? array());
			$types   = (array)($this->request->post['label_rule_type']   ?? array());
			$refs    = (array)($this->request->post['label_rule_ref']    ?? array());
			$nums    = (array)($this->request->post['label_rule_num']    ?? array());
			$outputs = (array)($this->request->post['label_rule_output'] ?? array());
			$rules = array();
			foreach ($types as $i => $type) {
				$rules[] = array(
					'label'    => (int)($labels[$i] ?? 0),
					'type'     => (string)$type,
					'ref'      => (int)($refs[$i] ?? 0),
					'num'      => $nums[$i] ?? 0,
					'output'   => (string)($outputs[$i] ?? ''),
					'ref_name' => $this->ruleRefName((string)$type, (int)($refs[$i] ?? 0), $language_id),
				);
			}
			return $rules;
		}

		$rules = array();
		foreach ((array)$this->config->get('feed_webartstudio_meta_label_rules') as $rule) {
			$type = (string)($rule['type'] ?? '');
			$rules[] = array(
				'label'    => (int)($rule['label'] ?? 0),
				'type'     => $type,
				'ref'      => (int)($rule['ref'] ?? 0),
				'num'      => $rule['num'] ?? 0,
				'output'   => (string)($rule['output'] ?? ''),
				'ref_name' => $this->ruleRefName($type, (int)($rule['ref'] ?? 0), $language_id),
			);
		}
		return $rules;
	}

	private function ruleRefName(string $type, int $ref, int $language_id): string {
		if ($ref <= 0) {
			return '';
		}
		if ($type === 'category') {
			return $this->model_extension_feed_webartstudio_meta->getCategoryName($ref, $language_id);
		}
		if ($type === 'manufacturer') {
			return $this->model_extension_feed_webartstudio_meta->getManufacturerName($ref);
		}
		return '';
	}

	private function excludeSelectedIds(string $type): array {
		if (isset($this->request->post['exclude_' . $type])) {
			$ids = (array)$this->request->post['exclude_' . $type];
		} else {
			$ids = (array)$this->config->get('feed_webartstudio_meta_exclude_' . $type);
		}
		return array_values(array_filter(array_map('intval', $ids), static fn($v) => $v > 0));
	}

	private function resolveName(string $type, int $reference_id, int $language_id): string {
		switch ($type) {
			case 'product':      return $this->model_extension_feed_webartstudio_meta->getProductName($reference_id, $language_id);
			case 'category':     return $this->model_extension_feed_webartstudio_meta->getCategoryName($reference_id, $language_id);
			case 'manufacturer': return $this->model_extension_feed_webartstudio_meta->getManufacturerName($reference_id);
		}
		return '#' . $reference_id;
	}

	/* ------------------------------------------------------------------ */
	/* Autocomplete (products / categories / manufacturers)               */
	/* ------------------------------------------------------------------ */

	public function autocomplete(): void {
		$json = array();
		$type = $this->request->get['type'] ?? '';
		$filter_name = html_entity_decode($this->request->get['filter_name'] ?? '', ENT_QUOTES, 'UTF-8');

		if ($filter_name !== '') {
			$this->load->model('extension/feed/webartstudio_meta');
			$language_id = $this->getDefaultLanguageId();

			if ($type === 'product') {
				foreach ($this->model_extension_feed_webartstudio_meta->getProductsByName($filter_name, $language_id) as $r) {
					$json[] = array(
						'reference_id' => (int)$r['product_id'],
						'name'         => strip_tags(html_entity_decode($r['name'], ENT_QUOTES, 'UTF-8')) . ' (' . $r['model'] . ')',
					);
				}
			} elseif ($type === 'category') {
				foreach ($this->model_extension_feed_webartstudio_meta->getCategoriesByName($filter_name, $language_id) as $r) {
					$json[] = array(
						'reference_id' => (int)$r['category_id'],
						'name'         => strip_tags(html_entity_decode($r['path'] ?: $r['name'], ENT_QUOTES, 'UTF-8')),
					);
				}
			} elseif ($type === 'manufacturer') {
				foreach ($this->model_extension_feed_webartstudio_meta->getManufacturersByName($filter_name) as $r) {
					$json[] = array(
						'reference_id' => (int)$r['manufacturer_id'],
						'name'         => strip_tags(html_entity_decode($r['name'], ENT_QUOTES, 'UTF-8')),
					);
				}
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	/* ------------------------------------------------------------------ */
	/* Validate / install / uninstall                                     */
	/* ------------------------------------------------------------------ */

	protected function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/feed/webartstudio_meta')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	public function install(): void {
		$this->load->model('extension/feed/webartstudio_meta');
		$this->model_extension_feed_webartstudio_meta->install();
	}

	public function uninstall(): void {
		$this->load->model('extension/feed/webartstudio_meta');
		$this->model_extension_feed_webartstudio_meta->uninstall();
	}
}
