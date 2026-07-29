<?php
/**
 * Google Merchant XML Feed — catalog controller (the XML generator).
 *
 * Produces a Google Shopping RSS 2.0 feed on the fly at:
 *   index.php?route=extension/feed/webartstudio_google   (or the clean "mask" URL)
 *
 * Site-agnostic: all behaviour is driven by the feed_webartstudio_google_* settings.
 */
class ControllerExtensionFeedWebartstudioGoogle extends Controller {

	// Google product-level availability vocabulary.
	private const AVAILABILITY = ['in_stock', 'out_of_stock', 'preorder', 'backorder'];

	public function index(): void {
		if (!$this->config->get('feed_webartstudio_google_status')) {
			$this->sendNotFound();
			return;
		}

		$this->load->model('extension/feed/webartstudio_google');

		$s = $this->buildSettings();

		$weight_factor = $this->getWeightFactors();

		$buffers = [
			'product'      => $s['buffers']['product'],
			'category'     => $s['buffers']['category'],
			'manufacturer' => $s['buffers']['manufacturer'],
		];
		$general_buffer = ['operation' => $s['buffer_op'], 'value' => (float)$s['buffer_val']];

		$products = $this->model_extension_feed_webartstudio_google->getFeedProducts($s);

		$image_base = (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER) . 'image/';

		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0', 'UTF-8');
		$writer->startElement('rss');
		$writer->writeAttribute('version', '2.0');
		$writer->writeAttribute('xmlns:g', 'http://base.google.com/ns/1.0');
		$writer->startElement('channel');
		$writer->writeElement('title', $this->config->get('config_name') ?: 'Google Merchant Feed');
		$writer->writeElement('link', (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER));
		$writer->writeElement('description', 'Google Merchant Center product feed');

		foreach ($products as $p) {
			$product_id = (int)$p['product_id'];

			// --- availability + quantity from size source -----------------
			$sizes = $this->getSizes($product_id, $s);
			$total_qty = $sizes ? array_sum(array_column($sizes, 'quantity')) : (int)$p['quantity'];
			$available_sizes = array_filter($sizes, static fn($z) => $z['quantity'] > 0);

			$availability = $this->resolveAvailability((int)$total_qty, (int)$p['stock_status_id'], $s);
			if ($availability === null) {
				continue; // out of stock + mapped to "exclude"
			}

			// --- price (regular + optional sale), all with VAT ------------
			$vat = $this->getVatInfo((int)$p['tax_class_id'], $s);

			$regular_net = $this->applyBuffer((float)$p['price'], $product_id, (int)$p['manufacturer_id'], $buffers, $general_buffer);
			$regular_price = $this->priceWithVat($regular_net, $vat, !empty($s['buffer_round']));

			$special = $this->model_extension_feed_webartstudio_google->getSpecialPrice($product_id, (int)$s['customer_group_id']);
			$sale_price = null;
			if ($special !== null && $special < (float)$p['price']) {
				$special_net = $this->applyBuffer($special, $product_id, (int)$p['manufacturer_id'], $buffers, $general_buffer);
				$sale_price  = $this->priceWithVat($special_net, $vat, !empty($s['buffer_round']));
			}

			// --- shared attributes ---------------------------------------
			$brand = trim((string)($p['manufacturer'] ?? ''));
			$mpn   = trim((string)$p[$s['mpn_field']]);
			$gtin  = preg_replace('/\D/', '', (string)$p[$s['ean_field']]);

			$color = $this->getColor($product_id, $s);
			$product_type = $this->model_extension_feed_webartstudio_google->getCategoryPath($product_id, (int)$s['language_id']);

			$desc_raw = $s['desc_source'] === 'meta_description' ? $p['meta_description'] : $p['description'];

			// custom labels (computed once per product, applied to all variants)
			$labels = $this->computeLabels($p, [
				'price'           => $sale_price ?? $regular_price,
				'on_sale'         => $sale_price !== null,
				'availability'    => $availability,
				'manufacturer_id' => (int)$p['manufacturer_id'],
			], $s);

			$weight = $this->convertWeight((float)$p['weight'], (int)$p['weight_class_id'], $weight_factor, $s['weight_unit']);

			$base = [
				'title'         => $this->clean($p['name'], 150, $s),
				'description'   => $this->clean($desc_raw, 5000, $s),
				'link'          => $this->productLink($product_id),
				'image'         => !empty($p['image']) ? $image_base . $p['image'] : '',
				'brand'         => $brand,
				'mpn'           => $mpn,
				'gtin'          => $gtin,
				'color'         => $color,
				'product_type'  => $product_type,
				'weight'        => $weight,
				'regular_price' => $regular_price,
				'sale_price'    => $sale_price,
				'labels'        => $labels,
			];

			$additional = [];
			if (!empty($s['additional_images'])) {
				foreach ($this->model_extension_feed_webartstudio_google->getAdditionalImages($product_id, 10) as $img) {
					$additional[] = $image_base . $img['image'];
				}
			}
			$base['additional'] = $additional;

			// --- emit: variants per size, or a single item ---------------
			if (!empty($s['variations']) && $available_sizes) {
				foreach ($available_sizes as $z) {
					$var_net   = $this->applyBuffer((float)$p['price'] + (float)$z['price_delta'], $product_id, (int)$p['manufacturer_id'], $buffers, $general_buffer);
					$var_price = $this->priceWithVat($var_net, $vat, !empty($s['buffer_round']));

					$var_sale = null;
					if ($special !== null && $special < (float)$p['price']) {
						$vs_net   = $this->applyBuffer($special + (float)$z['price_delta'], $product_id, (int)$p['manufacturer_id'], $buffers, $general_buffer);
						$var_sale = $this->priceWithVat($vs_net, $vat, !empty($s['buffer_round']));
					}

					$item = $base;
					$item['regular_price'] = $var_price;
					$item['sale_price']    = $var_sale;
					$item['id']            = $product_id . '-' . $this->slug($z['name']);
					$item['item_group_id'] = (string)$product_id;
					$item['size']          = $z['name'];
					$item['availability']  = $s['availability_instock'];

					$this->writeItem($writer, $item, $s);
				}
			} else {
				$item = $base;
				$item['id']            = (string)$product_id;
				$item['item_group_id'] = '';
				$item['size']          = $available_sizes ? implode(', ', array_map(static fn($z) => $z['name'], $available_sizes)) : '';
				$item['availability']  = $availability;

				$this->writeItem($writer, $item, $s);
			}
		}

		$writer->endElement(); // channel
		$writer->endElement(); // rss
		$writer->endDocument();

		$this->output($writer->outputMemory());
	}

	/* ------------------------------------------------------------------ */
	/* Item writer                                                        */
	/* ------------------------------------------------------------------ */

	private function writeItem(XMLWriter $writer, array $item, array $s): void {
		$writer->startElement('item');

		$writer->writeElement('g:id', (string)$item['id']);
		if ($item['item_group_id'] !== '') {
			$writer->writeElement('g:item_group_id', (string)$item['item_group_id']);
		}

		$this->cdata($writer, 'title', $item['title']);
		$this->cdata($writer, 'description', $item['description']);
		$this->cdata($writer, 'link', $item['link']);

		if ($item['image'] !== '') {
			$this->cdata($writer, 'g:image_link', $item['image']);
		}
		foreach ($item['additional'] as $img) {
			$this->cdata($writer, 'g:additional_image_link', $img);
		}

		$writer->writeElement('g:availability', $item['availability']);
		$writer->writeElement('g:condition', $s['condition']);

		$writer->writeElement('g:price', $this->money($item['regular_price']) . ' ' . $s['currency']);
		if ($item['sale_price'] !== null) {
			$writer->writeElement('g:sale_price', $this->money($item['sale_price']) . ' ' . $s['currency']);
		}

		if ($item['brand'] !== '') {
			$this->cdata($writer, 'g:brand', $item['brand']);
		}
		if ($item['gtin'] !== '') {
			$writer->writeElement('g:gtin', $item['gtin']);
		}
		if ($item['mpn'] !== '') {
			$writer->writeElement('g:mpn', $item['mpn']);
		}
		if ($item['brand'] === '' && $item['gtin'] === '' && $item['mpn'] === '') {
			$writer->writeElement('g:identifier_exists', 'no');
		}

		if ($s['google_category'] !== '') {
			$this->cdata($writer, 'g:google_product_category', $s['google_category']);
		}
		if ($item['product_type'] !== '') {
			$this->cdata($writer, 'g:product_type', $item['product_type']);
		}

		if ($item['color'] !== '') {
			$this->cdata($writer, 'g:color', $item['color']);
		}
		if (($item['size'] ?? '') !== '') {
			$writer->writeElement('g:size', $item['size']);
		}
		if ($s['gender'] !== '') {
			$writer->writeElement('g:gender', $s['gender']);
		}
		if ($s['age_group'] !== '') {
			$writer->writeElement('g:age_group', $s['age_group']);
		}

		if ($item['weight'] !== null) {
			$writer->writeElement('g:shipping_weight', $item['weight']);
		}

		if ($s['shipping'] !== '') {
			$writer->startElement('g:shipping');
			$writer->writeElement('g:country', $s['shipping_country']);
			$writer->writeElement('g:price', $this->money((float)$s['shipping']) . ' ' . $s['currency']);
			$writer->endElement();
		}

		foreach ($item['labels'] as $i => $value) {
			if ($value !== '') {
				$this->cdata($writer, 'g:custom_label_' . $i, $value);
			}
		}

		$writer->endElement(); // item
	}

	/* ------------------------------------------------------------------ */
	/* Custom labels (source + rule overrides)                            */
	/* ------------------------------------------------------------------ */

	/** @return string[] five label values (index 0..4). */
	private function computeLabels(array $p, array $ctx, array $s): array {
		$product_id = (int)$p['product_id'];
		$path_ids   = null; // lazy — only fetched if a category rule/source needs it

		$out = ['', '', '', '', ''];

		for ($i = 0; $i < 5; $i++) {
			// 1) rules take precedence (first match wins)
			$value = '';
			foreach ($s['label_rules'] as $rule) {
				if ((int)$rule['label'] !== $i) {
					continue;
				}
				if ($rule['type'] === 'category') {
					if ($path_ids === null) {
						$path_ids = $this->model_extension_feed_webartstudio_google->getProductCategoryPathIds($product_id);
					}
					if (in_array((int)$rule['ref'], $path_ids, true)) {
						$value = $rule['output'];
						break;
					}
				} elseif ($rule['type'] === 'manufacturer') {
					if ($ctx['manufacturer_id'] === (int)$rule['ref']) {
						$value = $rule['output'];
						break;
					}
				} elseif ($rule['type'] === 'price_min') {
					if ($ctx['price'] >= (float)$rule['num']) {
						$value = $rule['output'];
						break;
					}
				} elseif ($rule['type'] === 'price_max') {
					if ($ctx['price'] <= (float)$rule['num']) {
						$value = $rule['output'];
						break;
					}
				} elseif ($rule['type'] === 'on_sale') {
					if ($ctx['on_sale']) {
						$value = $rule['output'];
						break;
					}
				}
			}

			// 2) fall back to the label's base source
			if ($value === '') {
				$value = $this->labelFromSource($s['labels'][$i] ?? 'none', $p, $ctx, $s);
			}

			$out[$i] = trim((string)$value);
		}

		return $out;
	}

	private function labelFromSource(string $source, array $p, array $ctx, array $s): string {
		if ($source === '' || $source === 'none') {
			return '';
		}
		$product_id = (int)$p['product_id'];

		switch ($source) {
			case 'manufacturer':
				return trim((string)($p['manufacturer'] ?? ''));

			case 'category':
				return $this->model_extension_feed_webartstudio_google->getTopCategoryName($product_id, (int)$s['language_id']);

			case 'category_last':
				return $this->model_extension_feed_webartstudio_google->getLastCategoryName($product_id, (int)$s['language_id']);

			case 'category_path':
				return $this->model_extension_feed_webartstudio_google->getCategoryPath($product_id, (int)$s['language_id']);

			case 'on_sale':
				return $ctx['on_sale'] ? 'sale' : 'regular';

			case 'availability':
				return $ctx['availability'];

			case 'price_range':
				return $this->priceBucket((float)$ctx['price'], $s['price_buckets']);
		}

		[$type, $id] = $this->parseSource($source);
		if ($type === 'attribute') {
			$values = $this->model_extension_feed_webartstudio_google->getAttributeValues($product_id, $id, (int)$s['language_id']);
			return $values[0] ?? '';
		}
		if ($type === 'option') {
			$rows = $this->model_extension_feed_webartstudio_google->getOptionSizes($product_id, $id, (int)$s['language_id']);
			return $rows[0]['name'] ?? '';
		}
		return '';
	}

	/** Match a price to a configured bucket label (e.g. "50-100" / "100+"). */
	private function priceBucket(float $price, array $buckets): string {
		foreach ($buckets as $b) {
			$min = $b['min'];
			$max = $b['max'];
			if ($price >= $min && ($max === null || $price < $max)) {
				return $b['label'];
			}
		}
		return '';
	}

	/* ------------------------------------------------------------------ */
	/* Size / color resolution                                            */
	/* ------------------------------------------------------------------ */

	/** @return array<array{name:string, quantity:int, price_delta:float}> */
	private function getSizes(int $product_id, array $s): array {
		[$type, $id] = $this->parseSource($s['size_source']);
		$sizes = [];

		if ($type === 'option') {
			foreach ($this->model_extension_feed_webartstudio_google->getOptionSizes($product_id, $id, (int)$s['language_id']) as $row) {
				$delta = (float)$row['price'];
				if ($row['price_prefix'] === '-') {
					$delta = -$delta;
				}
				$sizes[] = [
					'name'        => trim($row['name']),
					'quantity'    => (int)$row['quantity'],
					'price_delta' => $delta,
				];
			}
		} elseif ($type === 'attribute') {
			foreach ($this->model_extension_feed_webartstudio_google->getAttributeValues($product_id, $id, (int)$s['language_id']) as $value) {
				foreach (preg_split('/\s*,\s*/', $value) as $piece) {
					if ($piece !== '') {
						$sizes[] = ['name' => $piece, 'quantity' => 1, 'price_delta' => 0.0];
					}
				}
			}
		}

		return $sizes;
	}

	private function getColor(int $product_id, array $s): string {
		$src = $s['color_source'];
		if ($src === 'none' || $src === '' || $src === 'name') {
			return '';
		}
		[$type, $id] = $this->parseSource($src);
		if ($type === 'attribute') {
			$values = $this->model_extension_feed_webartstudio_google->getAttributeValues($product_id, $id, (int)$s['language_id']);
			return $values[0] ?? '';
		}
		if ($type === 'option') {
			$rows = $this->model_extension_feed_webartstudio_google->getOptionSizes($product_id, $id, (int)$s['language_id']);
			return $rows[0]['name'] ?? '';
		}
		return '';
	}

	/** "option:11" → ['option', 11]; "none" → ['none', 0]. */
	private function parseSource(string $value): array {
		if (strpos($value, ':') !== false) {
			[$type, $id] = explode(':', $value, 2);
			return [$type, (int)$id];
		}
		return [$value, 0];
	}

	/* ------------------------------------------------------------------ */
	/* Pricing helpers                                                    */
	/* ------------------------------------------------------------------ */

	private function applyBuffer(float $net, int $product_id, int $manufacturer_id, array $buffers, array $general): float {
		if (isset($buffers['product'][$product_id])) {
			return $this->buffer($net, $buffers['product'][$product_id]);
		}
		if ($buffers['category']) {
			foreach ($this->model_extension_feed_webartstudio_google->getProductCategories($product_id) as $cat) {
				if (isset($buffers['category'][$cat])) {
					return $this->buffer($net, $buffers['category'][$cat]);
				}
			}
		}
		if ($manufacturer_id && isset($buffers['manufacturer'][$manufacturer_id])) {
			return $this->buffer($net, $buffers['manufacturer'][$manufacturer_id]);
		}
		if ((float)$general['value'] !== 0.0) {
			return $this->buffer($net, $general);
		}
		return $net;
	}

	private function buffer(float $net, array $rule): float {
		$value = (float)$rule['value'];
		if ($rule['operation'] === 'fixed') {
			return max(0.0, $net + $value);
		}
		return max(0.0, $net * (1 + $value / 100));
	}

	/**
	 * @return array{calc:float, report:float, inclusive:bool}
	 */
	private function getVatInfo(int $tax_class_id, array $s): array {
		$rate = 0.0;
		if ($tax_class_id > 0) {
			$rate = round((float)$this->tax->getTax(100, $tax_class_id), 2);
		}

		if ($rate > 0) {
			return ['calc' => $rate, 'report' => $rate, 'inclusive' => false];
		}

		return ['calc' => 0.0, 'report' => (float)$s['vat_default'], 'inclusive' => true];
	}

	private function priceWithVat(float $net, array $vat, bool $round_99): float {
		$price = $vat['inclusive'] ? $net : $net * (1 + $vat['calc'] / 100);
		if ($round_99) {
			$price = floor($price) + 0.99;
		}
		return $price;
	}

	private function productLink(int $product_id): string {
		return html_entity_decode(
			$this->url->link('product/product', 'product_id=' . $product_id, true),
			ENT_QUOTES,
			'UTF-8'
		);
	}

	private function money(float $value): string {
		return number_format($value, 2, '.', '');
	}

	/* ------------------------------------------------------------------ */
	/* Availability / weight                                              */
	/* ------------------------------------------------------------------ */

	private function resolveAvailability(int $quantity, int $stock_status_id, array $s): ?string {
		if ($quantity > 0) {
			return $s['availability_instock'];
		}
		$map = (array)$s['availability_map'];
		$expr = $map[$stock_status_id] ?? '';
		return $expr !== '' ? $expr : null;
	}

	private function convertWeight(float $weight, int $weight_class_id, array $factors, string $unit): ?string {
		if ($weight <= 0 || !isset($factors[$weight_class_id]) || (float)$factors[$weight_class_id] == 0.0) {
			return null;
		}
		$kg = $weight / (float)$factors[$weight_class_id];
		if ($unit === 'kg') {
			return rtrim(rtrim(number_format($kg, 3, '.', ''), '0'), '.') . ' kg';
		}
		return (string)(int)round($kg * 1000) . ' g'; // grams
	}

	private function getWeightFactors(): array {
		$factors = [];
		foreach ($this->db->query("SELECT weight_class_id, value FROM `" . DB_PREFIX . "weight_class`")->rows as $row) {
			$factors[(int)$row['weight_class_id']] = (float)$row['value'];
		}
		return $factors;
	}

	/* ------------------------------------------------------------------ */
	/* Text / output helpers                                              */
	/* ------------------------------------------------------------------ */

	private function clean(?string $text, int $max, array $s): string {
		$text = (string)$text;
		if (!empty($s['strip_html'])) {
			$text = strip_tags(html_entity_decode($text, ENT_QUOTES, 'UTF-8'));
		}
		$text = trim(preg_replace('/\s+/u', ' ', $text));
		if (mb_strlen($text) > $max) {
			$text = mb_substr($text, 0, $max);
		}
		return $text;
	}

	private function slug(string $value): string {
		$value = preg_replace('/\s+/u', '', $value);
		$value = str_replace(['/', '\\'], '-', $value);
		return $value !== '' ? $value : '0';
	}

	private function cdata(XMLWriter $writer, string $name, string $value): void {
		$writer->startElement($name);
		$writer->writeCdata($value);
		$writer->endElement();
	}

	private function output(string $xml): void {
		// Discard anything already emitted (e.g. PHP deprecation notices from
		// core libraries when display_errors is on) so the XML is served clean,
		// then buffer a fresh, uncompressed response.
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		ob_start();

		$this->response->addHeader('Content-Type: application/xml; charset=UTF-8');
		$this->response->setOutput($xml);
	}

	private function sendNotFound(): void {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
		$this->response->setOutput('');
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                           */
	/* ------------------------------------------------------------------ */

	private function buildSettings(): array {
		$instock = $this->config->get('feed_webartstudio_google_availability_instock') ?: 'in_stock';
		if (!in_array($instock, self::AVAILABILITY, true)) {
			$instock = 'in_stock';
		}

		return [
			'store_id'             => (int)$this->config->get('feed_webartstudio_google_store_id'),
			'language_id'          => (int)($this->config->get('feed_webartstudio_google_language_id') ?: $this->config->get('config_language_id')),
			'customer_group_id'    => (int)($this->config->get('feed_webartstudio_google_customer_group_id') ?: $this->config->get('config_customer_group_id')),
			'currency'             => strtoupper($this->config->get('feed_webartstudio_google_currency') ?: 'EUR'),
			'weight_unit'          => $this->config->get('feed_webartstudio_google_weight_unit') ?: 'g',
			'mpn_field'            => $this->config->get('feed_webartstudio_google_mpn_field') ?: 'mpn',
			'ean_field'            => $this->config->get('feed_webartstudio_google_ean_field') ?: 'ean',
			'desc_source'          => $this->config->get('feed_webartstudio_google_desc_source') ?: 'description',
			'vat_default'          => $this->config->get('feed_webartstudio_google_vat_default') !== null ? (float)$this->config->get('feed_webartstudio_google_vat_default') : 24.0,
			'strip_html'           => $this->config->get('feed_webartstudio_google_strip_html'),
			'variations'           => $this->config->get('feed_webartstudio_google_variations'),
			'size_source'          => $this->config->get('feed_webartstudio_google_size_source') ?: 'none',
			'color_source'         => $this->config->get('feed_webartstudio_google_color_source') ?: 'none',
			'additional_images'    => $this->config->get('feed_webartstudio_google_additional_images'),
			'condition'            => $this->config->get('feed_webartstudio_google_condition') ?: 'new',
			'gender'               => (string)$this->config->get('feed_webartstudio_google_gender'),
			'age_group'            => (string)$this->config->get('feed_webartstudio_google_age_group'),
			'google_category'      => (string)$this->config->get('feed_webartstudio_google_google_category'),
			'availability_instock' => $instock,
			'availability_map'     => (array)$this->config->get('feed_webartstudio_google_availability_map'),
			'exclude_stock'        => (array)$this->config->get('feed_webartstudio_google_exclude_stock'),
			'exclude_empty'        => (array)$this->config->get('feed_webartstudio_google_exclude_empty'),
			'buffer_op'            => $this->config->get('feed_webartstudio_google_buffer_op') ?: 'percent',
			'buffer_val'           => (float)$this->config->get('feed_webartstudio_google_buffer_val'),
			'buffer_round'         => $this->config->get('feed_webartstudio_google_buffer_round'),
			'shipping'             => (string)$this->config->get('feed_webartstudio_google_shipping'),
			'shipping_country'     => $this->config->get('feed_webartstudio_google_shipping_country') ?: 'GR',
			'exclude_product'      => $this->excludeIds('product'),
			'exclude_category'     => $this->excludeIds('category'),
			'exclude_manufacturer' => $this->excludeIds('manufacturer'),
			'buffers'              => [
				'product'      => $this->bufferMap('product'),
				'category'     => $this->bufferMap('category'),
				'manufacturer' => $this->bufferMap('manufacturer'),
			],
			'labels'               => $this->labelSources(),
			'label_rules'          => $this->labelRules(),
			'price_buckets'        => $this->priceBuckets(),
		];
	}

	/** @return string[] five label source strings (index 0..4). */
	private function labelSources(): array {
		$out = [];
		for ($i = 0; $i < 5; $i++) {
			$out[$i] = (string)($this->config->get('feed_webartstudio_google_label_' . $i . '_source') ?: 'none');
		}
		return $out;
	}

	/** @return array<array{label:int, type:string, ref:int, num:float, output:string}> */
	private function labelRules(): array {
		$rules = [];
		foreach ((array)$this->config->get('feed_webartstudio_google_label_rules') as $rule) {
			$type = (string)($rule['type'] ?? '');
			if ($type === '') {
				continue;
			}
			$rules[] = [
				'label'  => max(0, min(4, (int)($rule['label'] ?? 0))),
				'type'   => $type,
				'ref'    => (int)($rule['ref'] ?? 0),
				'num'    => (float)($rule['num'] ?? 0),
				'output' => trim((string)($rule['output'] ?? '')),
			];
		}
		return $rules;
	}

	/** Parse the "0-30,30-60,60-100,100-" bucket string. */
	private function priceBuckets(): array {
		$raw = (string)$this->config->get('feed_webartstudio_google_price_buckets');
		$buckets = [];
		foreach (preg_split('/\s*,\s*/', trim($raw)) as $piece) {
			if ($piece === '') {
				continue;
			}
			$parts = explode('-', $piece, 2);
			$min = (float)$parts[0];
			$max = (isset($parts[1]) && $parts[1] !== '') ? (float)$parts[1] : null;
			$buckets[] = [
				'min'   => $min,
				'max'   => $max,
				'label' => $max === null ? ((string)(int)$min . '+') : ((string)(int)$min . '-' . (string)(int)$max),
			];
		}
		return $buckets;
	}

	/** Exclude ids for a type, from the JSON setting. @return int[] */
	private function excludeIds(string $type): array {
		$ids = (array)$this->config->get('feed_webartstudio_google_exclude_' . $type);
		return array_values(array_filter(array_map('intval', $ids), static fn($v) => $v > 0));
	}

	/** @return array<int, array{operation:string, value:float}> */
	private function bufferMap(string $type): array {
		$map = [];
		foreach ((array)$this->config->get('feed_webartstudio_google_buffer_' . $type) as $rule) {
			$id = (int)($rule['id'] ?? 0);
			if ($id > 0) {
				$map[$id] = [
					'operation' => ($rule['operation'] ?? 'percent') === 'fixed' ? 'fixed' : 'percent',
					'value'     => (float)($rule['value'] ?? 0),
				];
			}
		}
		return $map;
	}
}
