<?php
/**
 * Skroutz XML Feed — catalog controller (the XML generator).
 *
 * Produces a Skroutz-compliant feed on the fly at:
 *   index.php?route=extension/feed/webartstudio_skroutz   (or the clean "mask" URL)
 *
 * Site-agnostic: all behaviour is driven by the feed_webartstudio_skroutz_* settings.
 */
class ControllerExtensionFeedWebartstudioSkroutz extends Controller {

	public function index(): void {
		if (!$this->config->get('feed_webartstudio_skroutz_status')) {
			$this->sendNotFound();
			return;
		}

		$this->load->model('extension/feed/webartstudio_skroutz');

		$s = $this->buildSettings();

		// Weight-class conversion factors (units per 1 kg) for grams output.
		$weight_factor = $this->getWeightFactors();

		// Buffer maps (reference_id => [operation, value]) from JSON settings.
		$buffers = [
			'product'      => $s['buffers']['product'],
			'category'     => $s['buffers']['category'],
			'manufacturer' => $s['buffers']['manufacturer'],
		];
		$general_buffer = ['operation' => $s['buffer_op'], 'value' => (float)$s['buffer_val']];

		$products = $this->model_extension_feed_webartstudio_skroutz->getFeedProducts($s);

		$image_base = (defined('HTTPS_SERVER') ? HTTPS_SERVER : HTTP_SERVER) . 'image/';

		$writer = new XMLWriter();
		$writer->openMemory();
		$writer->setIndent(true);
		$writer->startDocument('1.0', 'UTF-8');
		$writer->startElement('mywebstore');
		$writer->writeElement('created_at', date('Y-m-d H:i'));
		$writer->startElement('products');

		foreach ($products as $p) {
			$product_id = (int)$p['product_id'];

			// --- availability + quantity from size source -----------------
			$sizes = $this->getSizes($product_id, $s);          // [['name','quantity','price_delta'], ...]
			$total_qty = $sizes ? array_sum(array_column($sizes, 'quantity')) : (int)$p['quantity'];
			$available_sizes = array_filter($sizes, static fn($z) => $z['quantity'] > 0);

			$availability = $this->resolveAvailability((int)$total_qty, (int)$p['stock_status_id'], $s);
			if ($availability === null) {
				continue; // out of stock + mapped to "exclude"
			}

			// --- price (special → buffer → VAT) ---------------------------
			$net = $this->model_extension_feed_webartstudio_skroutz->getSpecialPrice($product_id, (int)$s['customer_group_id']);
			$net = ($net !== null) ? $net : (float)$p['price'];
			$net = $this->applyBuffer($net, $product_id, (int)$p['manufacturer_id'], $buffers, $general_buffer);

			$vat = $this->getVatInfo((int)$p['tax_class_id'], $s);   // [calc_rate, report_rate, inclusive]
			$price_with_vat = $this->priceWithVat($net, $vat, !empty($s['buffer_round']));
			$vat_rate = $vat['report'];

			// --- write product -------------------------------------------
			$writer->startElement('product');

			$writer->writeElement('id', (string)$product_id);
			$this->cdata($writer, 'name', $this->clean($p['name'], 300, $s));
			$this->cdata($writer, 'link', $this->productLink($product_id));

			if (!empty($p['image'])) {
				$this->cdata($writer, 'image', $image_base . $p['image']);
			}

			if (!empty($s['additional_images'])) {
				foreach ($this->model_extension_feed_webartstudio_skroutz->getAdditionalImages($product_id, 15) as $img) {
					$this->cdata($writer, 'additionalimage', $image_base . $img['image']);
				}
			}

			$this->cdata($writer, 'category', $this->model_extension_feed_webartstudio_skroutz->getCategoryPath($product_id, (int)$s['language_id']));
			$writer->writeElement('price_with_vat', $this->money($price_with_vat));
			$writer->writeElement('vat', number_format($vat_rate, 2, '.', ''));

			$this->cdata($writer, 'manufacturer', $p['manufacturer'] ?? '');

			$mpn = trim((string)$p[$s['mpn_field']]);
			if ($mpn !== '') {
				$writer->writeElement('mpn', $mpn);
			}

			$ean = preg_replace('/\D/', '', (string)$p[$s['ean_field']]);
			if ($ean !== '') {
				$writer->writeElement('ean', $ean);
			}

			$writer->writeElement('availability', $availability);

			// flat size list = available sizes
			if ($available_sizes) {
				$writer->writeElement('size', implode(',', array_map(static fn($z) => $z['name'], $available_sizes)));
			}

			// weight → grams or kg
			$weight = $this->convertWeight((float)$p['weight'], (int)$p['weight_class_id'], $weight_factor, $s['weight_unit']);
			if ($weight !== null) {
				$writer->writeElement('weight', $weight);
			}

			$color = $this->getColor($product_id, $p['name'], $s);
			if ($color !== '') {
				$this->cdata($writer, 'color', $color);
			}

			$desc = $s['desc_source'] === 'meta_description' ? $p['meta_description'] : $p['description'];
			$this->cdata($writer, 'description', $this->clean($desc, 10000, $s));

			$writer->writeElement('quantity', (string)max(0, (int)$total_qty));

			if ($s['shipping'] !== '') {
				$writer->writeElement('shipping', $this->money((float)$s['shipping']));
			}

			// --- size variations -----------------------------------------
			if (!empty($s['variations']) && $sizes) {
				$this->writeVariations($writer, $p, $sizes, $net, $vat, $s);
			}

			$writer->endElement(); // product
		}

		$writer->endElement(); // products
		$writer->endElement(); // mywebstore
		$writer->endDocument();

		$this->output($writer->outputMemory());
	}

	/* ------------------------------------------------------------------ */
	/* Variations                                                         */
	/* ------------------------------------------------------------------ */

	private function writeVariations(XMLWriter $writer, array $p, array $sizes, float $net, array $vat, array $s): void {
		$product_id = (int)$p['product_id'];
		$stock_status_id = (int)$p['stock_status_id'];
		$mpn = trim((string)$p[$s['mpn_field']]);
		$ean = preg_replace('/\D/', '', (string)$p[$s['ean_field']]);

		$opened = false;
		foreach ($sizes as $z) {
			$qty = (int)$z['quantity'];

			$avail = $this->resolveAvailability($qty, $stock_status_id, $s);
			if ($avail === null) {
				continue; // out-of-stock size mapped to "exclude"
			}

			// variation price = (net + option delta) then VAT
			$var_net = $net + (float)$z['price_delta'];
			$var_price = $this->priceWithVat($var_net, $vat, !empty($s['buffer_round']));

			if (!$opened) {
				$writer->startElement('variations');
				$opened = true;
			}

			$writer->startElement('variation');
			$writer->writeElement('variationid', $product_id . '-' . $this->slug($z['name']));
			$this->cdata($writer, 'link', $this->productLink($product_id));
			$writer->writeElement('availability', $avail);
			if ($mpn !== '') {
				$writer->writeElement('manufacturersku', $mpn);
			}
			if ($ean !== '') {
				$writer->writeElement('ean', $ean);
			}
			$writer->writeElement('price_with_vat', $this->money($var_price));
			$writer->writeElement('size', $z['name']);
			$writer->writeElement('quantity', (string)max(0, $qty));
			$writer->endElement(); // variation
		}

		if ($opened) {
			$writer->endElement(); // variations
		}
	}

	/* ------------------------------------------------------------------ */
	/* Size / color resolution                                            */
	/* ------------------------------------------------------------------ */

	/** @return array<array{name:string, quantity:int, price_delta:float}> */
	private function getSizes(int $product_id, array $s): array {
		[$type, $id] = $this->parseSource($s['size_source']);
		$sizes = [];

		if ($type === 'option') {
			foreach ($this->model_extension_feed_webartstudio_skroutz->getOptionSizes($product_id, $id, (int)$s['language_id']) as $row) {
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
			// Attributes carry no stock; treat each listed value as available.
			foreach ($this->model_extension_feed_webartstudio_skroutz->getAttributeValues($product_id, $id, (int)$s['language_id']) as $value) {
				foreach (preg_split('/\s*,\s*/', $value) as $piece) {
					if ($piece !== '') {
						$sizes[] = ['name' => $piece, 'quantity' => 1, 'price_delta' => 0.0];
					}
				}
			}
		}

		return $sizes;
	}

	private function getColor(int $product_id, string $name, array $s): string {
		$src = $s['color_source'];
		if ($src === 'none' || $src === '') {
			return '';
		}
		if ($src === 'name') {
			return ''; // name-parsing is intentionally conservative; left for a curated map
		}
		[$type, $id] = $this->parseSource($src);
		if ($type === 'attribute') {
			$values = $this->model_extension_feed_webartstudio_skroutz->getAttributeValues($product_id, $id, (int)$s['language_id']);
			return $values[0] ?? '';
		}
		if ($type === 'option') {
			$rows = $this->model_extension_feed_webartstudio_skroutz->getOptionSizes($product_id, $id, (int)$s['language_id']);
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
		// precedence: product > category > manufacturer > general
		if (isset($buffers['product'][$product_id])) {
			return $this->buffer($net, $buffers['product'][$product_id]);
		}
		if ($buffers['category']) {
			foreach ($this->model_extension_feed_webartstudio_skroutz->getProductCategories($product_id) as $cat) {
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
	 * Resolve how VAT applies to a product.
	 * @return array{calc:float, report:float, inclusive:bool}
	 *   calc      — rate to add on top of the net price (0 when inclusive)
	 *   report    — the <vat> value to emit
	 *   inclusive — true when the stored price already contains VAT
	 */
	private function getVatInfo(int $tax_class_id, array $s): array {
		$rate = 0.0;
		if ($tax_class_id > 0) {
			// tax library is configured for the store's default geo zone.
			$rate = round((float)$this->tax->getTax(100, $tax_class_id), 2);
		}

		if ($rate > 0) {
			return ['calc' => $rate, 'report' => $rate, 'inclusive' => false];
		}

		// No tax class / zero rate → assume the stored price already includes
		// VAT; report the configured default rate without re-adding it.
		return ['calc' => 0.0, 'report' => (float)$s['vat_default'], 'inclusive' => true];
	}

	private function priceWithVat(float $net, array $vat, bool $round_99): float {
		$price = $vat['inclusive'] ? $net : $net * (1 + $vat['calc'] / 100);
		if ($round_99) {
			$price = floor($price) + 0.99;
		}
		return $price;
	}

	/** Product URL, with entity-decoded ampersands (raw URL for CDATA). */
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

	/** Returns the expression, or null when the product should be skipped. */
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
		return (string)(int)round($kg * 1000); // grams
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
		$accepts_gzip = strpos($this->request->server['HTTP_ACCEPT_ENCODING'] ?? '', 'gzip') !== false;

		$this->response->addHeader('Content-Type: application/xml; charset=UTF-8');

		if ($accepts_gzip && function_exists('gzencode')) {
			$this->response->addHeader('Content-Encoding: gzip');
			$this->response->setOutput(gzencode($xml, 6));
		} else {
			$this->response->setOutput($xml);
		}
	}

	private function sendNotFound(): void {
		$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
		$this->response->setOutput('');
	}

	/* ------------------------------------------------------------------ */
	/* Settings                                                           */
	/* ------------------------------------------------------------------ */

	private function buildSettings(): array {
		return [
			'store_id'             => (int)$this->config->get('feed_webartstudio_skroutz_store_id'),
			'language_id'          => (int)($this->config->get('feed_webartstudio_skroutz_language_id') ?: $this->config->get('config_language_id')),
			'customer_group_id'    => (int)($this->config->get('feed_webartstudio_skroutz_customer_group_id') ?: $this->config->get('config_customer_group_id')),
			'weight_unit'          => $this->config->get('feed_webartstudio_skroutz_weight_unit') ?: 'g',
			'mpn_field'            => $this->config->get('feed_webartstudio_skroutz_mpn_field') ?: 'mpn',
			'ean_field'            => $this->config->get('feed_webartstudio_skroutz_ean_field') ?: 'ean',
			'desc_source'          => $this->config->get('feed_webartstudio_skroutz_desc_source') ?: 'description',
			'vat_default'          => $this->config->get('feed_webartstudio_skroutz_vat_default') !== null ? (float)$this->config->get('feed_webartstudio_skroutz_vat_default') : 24.0,
			'strip_html'           => $this->config->get('feed_webartstudio_skroutz_strip_html'),
			'variations'           => $this->config->get('feed_webartstudio_skroutz_variations'),
			'size_source'          => $this->config->get('feed_webartstudio_skroutz_size_source') ?: 'none',
			'color_source'         => $this->config->get('feed_webartstudio_skroutz_color_source') ?: 'none',
			'additional_images'    => $this->config->get('feed_webartstudio_skroutz_additional_images'),
			'availability_instock' => $this->config->get('feed_webartstudio_skroutz_availability_instock') ?: 'Διαθέσιμο από 1 έως 3 ημέρες',
			'availability_map'     => (array)$this->config->get('feed_webartstudio_skroutz_availability_map'),
			'exclude_stock'        => (array)$this->config->get('feed_webartstudio_skroutz_exclude_stock'),
			'exclude_empty'        => (array)$this->config->get('feed_webartstudio_skroutz_exclude_empty'),
			'buffer_op'            => $this->config->get('feed_webartstudio_skroutz_buffer_op') ?: 'percent',
			'buffer_val'           => (float)$this->config->get('feed_webartstudio_skroutz_buffer_val'),
			'buffer_round'         => $this->config->get('feed_webartstudio_skroutz_buffer_round'),
			'shipping'             => (string)$this->config->get('feed_webartstudio_skroutz_shipping'),
			'exclude_product'      => $this->excludeIds('product'),
			'exclude_category'     => $this->excludeIds('category'),
			'exclude_manufacturer' => $this->excludeIds('manufacturer'),
			'buffers'              => [
				'product'      => $this->bufferMap('product'),
				'category'     => $this->bufferMap('category'),
				'manufacturer' => $this->bufferMap('manufacturer'),
			],
		];
	}

	/** Exclude ids for a type, from the JSON setting. @return int[] */
	private function excludeIds(string $type): array {
		$ids = (array)$this->config->get('feed_webartstudio_skroutz_exclude_' . $type);
		return array_values(array_filter(array_map('intval', $ids), static fn($v) => $v > 0));
	}

	/**
	 * Buffer rules for a type keyed by reference id.
	 * @return array<int, array{operation:string, value:float}>
	 */
	private function bufferMap(string $type): array {
		$map = [];
		foreach ((array)$this->config->get('feed_webartstudio_skroutz_buffer_' . $type) as $rule) {
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
