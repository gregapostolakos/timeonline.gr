<?php
/**
 * Skroutz XML Feed — admin controller.
 *
 * Single-instance feed extension. Settings live in oc_setting (group
 * "feed_webartstudio_skroutz"); per-entity excludes & buffers live in their own tables.
 * Fully plug-and-play: every dropdown is populated from the live DB.
 */
class ControllerExtensionFeedWebartstudioSkroutz extends Controller {
	private $error = array();

	// Skroutz's fixed product-level availability vocabulary (cross-referenced
	// by Skroutz — NOT site values, so it is safe to keep canonical here).
	private $availability_expressions = array(
		'Άμεσα διαθέσιμο',
		'Διαθέσιμο από 1 έως 3 ημέρες',
		'Διαθέσιμο από 4 έως 6 ημέρες',
		'Διαθέσιμο από 7 έως 12 ημέρες',
	);

	private $field_sources = array('model', 'sku', 'mpn', 'ean', 'upc', 'jan', 'isbn', 'location');

	public function index(): void {
		$this->load->language('extension/feed/webartstudio_skroutz');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/feed/webartstudio_skroutz');
		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			// Fold the exclude/buffer lists into prefixed setting keys so
			// editSetting() stores them as JSON alongside everything else.
			$post = $this->collectListSettings($this->request->post);
			$this->model_setting_setting->editSetting('feed_webartstudio_skroutz', $post);

			// The "mask" is stored as an SEO keyword pointing at the feed route.
			$this->model_extension_feed_webartstudio_skroutz->setMask($this->request->post['feed_webartstudio_skroutz_mask'] ?? '');

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/feed/webartstudio_skroutz', 'user_token=' . $this->session->data['user_token'], true));
		}

		$this->getForm();
	}

	protected function getForm(): void {
		// Webartstudio brand stylesheet (scoped under .was-ui).
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
				'href' => $this->url->link('extension/feed/webartstudio_skroutz', 'user_token=' . $this->session->data['user_token'], true),
			),
		);

		$data['action']     = $this->url->link('extension/feed/webartstudio_skroutz', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel']     = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=feed', true);
		$data['user_token'] = $this->session->data['user_token'];

		$data['autocomplete_product']  = $this->url->link('extension/feed/webartstudio_skroutz/autocomplete', 'user_token=' . $this->session->data['user_token'] . '&type=product', true);
		$data['autocomplete_category'] = $this->url->link('extension/feed/webartstudio_skroutz/autocomplete', 'user_token=' . $this->session->data['user_token'] . '&type=category', true);

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

		$data['feed_webartstudio_skroutz_mask'] = $this->request->post['feed_webartstudio_skroutz_mask'] ?? $this->model_extension_feed_webartstudio_skroutz->getMask();

		// ---- dynamic lookups (site-agnostic) ----------------------------
		$language_id = (int)($data['feed_webartstudio_skroutz_language_id'] ?: $this->getDefaultLanguageId());

		$data['languages']       = $this->model_extension_feed_webartstudio_skroutz->getLanguages();
		$data['stores']          = $this->model_extension_feed_webartstudio_skroutz->getStores();
		$data['customer_groups'] = $this->model_extension_feed_webartstudio_skroutz->getCustomerGroups($language_id);
		$data['options']         = $this->model_extension_feed_webartstudio_skroutz->getOptions($language_id);
		$data['attributes']      = $this->model_extension_feed_webartstudio_skroutz->getAttributes($language_id);
		$data['stock_statuses']  = $this->model_extension_feed_webartstudio_skroutz->getStockStatuses($language_id);
		$data['weight_classes']  = $this->model_extension_feed_webartstudio_skroutz->getWeightClasses($language_id);
		$data['manufacturers']   = $this->model_extension_feed_webartstudio_skroutz->getManufacturers();
		$data['categories']      = $this->model_extension_feed_webartstudio_skroutz->getCategories($language_id);
		$data['field_sources']   = $this->field_sources;
		$data['availability_expressions'] = $this->availability_expressions;

		// ---- the live feed URL (clean mask if set, otherwise the route) --
		$mask = $data['feed_webartstudio_skroutz_mask'];
		$data['feed_url'] = $mask
			? rtrim(HTTP_CATALOG, '/') . '/' . ltrim($mask, '/')
			: HTTP_CATALOG . 'index.php?route=extension/feed/webartstudio_skroutz';

		// ---- saved excludes --------------------------------------------
		// Products: resolved pills (too many to list). Categories/manufacturers:
		// selected-id arrays to pre-check the full checkbox lists.
		$data['exclude_products']             = $this->resolveExcludes('product', $language_id);
		$data['exclude_selected_category']    = $this->excludeSelectedIds('category');
		$data['exclude_selected_manufacturer'] = $this->excludeSelectedIds('manufacturer');

		// ---- saved buffers (resolved to names for display) --------------
		$data['buffers'] = array(
			'product'      => $this->resolveBuffers('product', $language_id),
			'category'     => $this->resolveBuffers('category', $language_id),
			'manufacturer' => $this->resolveBuffers('manufacturer', $language_id),
		);

		$data['header']      = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']      = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/feed/webartstudio_skroutz', $data));
	}

	/* ------------------------------------------------------------------ */
	/* Defaults                                                           */
	/* ------------------------------------------------------------------ */

	private function getDefaults(): array {
		return array(
			'feed_webartstudio_skroutz_status'                 => 0,
			'feed_webartstudio_skroutz_store_id'               => 0,
			'feed_webartstudio_skroutz_language_id'            => $this->getDefaultLanguageId(),
			'feed_webartstudio_skroutz_customer_group_id'      => (int)$this->config->get('config_customer_group_id'),
			'feed_webartstudio_skroutz_weight_unit'            => 'g',
			'feed_webartstudio_skroutz_mpn_field'              => 'mpn',
			'feed_webartstudio_skroutz_ean_field'              => 'ean',
			'feed_webartstudio_skroutz_desc_source'            => 'description',
			'feed_webartstudio_skroutz_vat_default'            => 24,
			'feed_webartstudio_skroutz_strip_html'             => 1,
			'feed_webartstudio_skroutz_variations'             => 1,
			'feed_webartstudio_skroutz_size_source'            => 'none',  // "option:ID" | "attribute:ID" | "none"
			'feed_webartstudio_skroutz_color_source'           => 'none',  // "option:ID" | "attribute:ID" | "name" | "none"
			'feed_webartstudio_skroutz_additional_images'      => 1,
			'feed_webartstudio_skroutz_availability_instock'   => 'Διαθέσιμο από 1 έως 3 ημέρες',
			'feed_webartstudio_skroutz_availability_map'       => array(),
			'feed_webartstudio_skroutz_exclude_stock'          => array(),
			'feed_webartstudio_skroutz_exclude_empty'          => array(),
			'feed_webartstudio_skroutz_buffer_op'              => 'percent',
			'feed_webartstudio_skroutz_buffer_val'             => 0,
			'feed_webartstudio_skroutz_buffer_round'           => 0,
			'feed_webartstudio_skroutz_shipping'               => '',
		);
	}

	private function getDefaultLanguageId(): int {
		// Skroutz prefers a single language, ideally Greek.
		$this->load->model('extension/feed/webartstudio_skroutz');
		foreach ($this->model_extension_feed_webartstudio_skroutz->getLanguages() as $language) {
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

	/**
	 * Convert the posted exclude_* / buffer_* list inputs into prefixed
	 * setting keys (arrays), which editSetting() stores as JSON. The raw
	 * un-prefixed inputs are ignored by editSetting().
	 */
	private function collectListSettings(array $post): array {
		foreach (array('product', 'category', 'manufacturer') as $type) {
			// excludes → array of unique positive ids
			$ids = array_values(array_unique(array_filter(
				array_map('intval', (array)($post['exclude_' . $type] ?? array())),
				static fn($v) => $v > 0
			)));
			$post['feed_webartstudio_skroutz_exclude_' . $type] = $ids;

			// buffers → array of {id, operation, value}
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
			$post['feed_webartstudio_skroutz_buffer_' . $type] = $rules;
		}

		return $post;
	}

	private function resolveExcludes(string $type, int $language_id): array {
		if (isset($this->request->post['exclude_' . $type])) {
			$ids = array_map('intval', (array)$this->request->post['exclude_' . $type]);
		} else {
			$ids = (array)$this->config->get('feed_webartstudio_skroutz_exclude_' . $type);
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

		foreach ((array)$this->config->get('feed_webartstudio_skroutz_buffer_' . $type) as $rule) {
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

	/** Selected exclude ids for a type (POST > saved config). @return int[] */
	private function excludeSelectedIds(string $type): array {
		if (isset($this->request->post['exclude_' . $type])) {
			$ids = (array)$this->request->post['exclude_' . $type];
		} else {
			$ids = (array)$this->config->get('feed_webartstudio_skroutz_exclude_' . $type);
		}
		return array_values(array_filter(array_map('intval', $ids), static fn($v) => $v > 0));
	}

	private function resolveName(string $type, int $reference_id, int $language_id): string {
		switch ($type) {
			case 'product':      return $this->model_extension_feed_webartstudio_skroutz->getProductName($reference_id, $language_id);
			case 'category':     return $this->model_extension_feed_webartstudio_skroutz->getCategoryName($reference_id, $language_id);
			case 'manufacturer': return $this->model_extension_feed_webartstudio_skroutz->getManufacturerName($reference_id);
		}
		return '#' . $reference_id;
	}

	/* ------------------------------------------------------------------ */
	/* Autocomplete (products / categories)                               */
	/* ------------------------------------------------------------------ */

	public function autocomplete(): void {
		$json = array();
		$type = $this->request->get['type'] ?? '';
		$filter_name = html_entity_decode($this->request->get['filter_name'] ?? '', ENT_QUOTES, 'UTF-8');

		if ($filter_name !== '') {
			$this->load->model('extension/feed/webartstudio_skroutz');
			$language_id = $this->getDefaultLanguageId();

			if ($type === 'product') {
				foreach ($this->model_extension_feed_webartstudio_skroutz->getProductsByName($filter_name, $language_id) as $r) {
					$json[] = array(
						'reference_id' => (int)$r['product_id'],
						'name'         => strip_tags(html_entity_decode($r['name'], ENT_QUOTES, 'UTF-8')) . ' (' . $r['model'] . ')',
					);
				}
			} elseif ($type === 'category') {
				foreach ($this->model_extension_feed_webartstudio_skroutz->getCategoriesByName($filter_name, $language_id) as $r) {
					$json[] = array(
						'reference_id' => (int)$r['category_id'],
						'name'         => strip_tags(html_entity_decode($r['path'] ?: $r['name'], ENT_QUOTES, 'UTF-8')),
					);
				}
			} elseif ($type === 'manufacturer') {
				foreach ($this->model_extension_feed_webartstudio_skroutz->getManufacturersByName($filter_name) as $r) {
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
		if (!$this->user->hasPermission('modify', 'extension/feed/webartstudio_skroutz')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}

	public function install(): void {
		$this->load->model('extension/feed/webartstudio_skroutz');
		$this->model_extension_feed_webartstudio_skroutz->install();
	}

	public function uninstall(): void {
		$this->load->model('extension/feed/webartstudio_skroutz');
		$this->model_extension_feed_webartstudio_skroutz->uninstall();
	}
}
