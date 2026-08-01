<?php
/**
 * ============================================================
 *  wp-orders-import.php — import WooCommerce orders into OpenCart
 *
 *  Reads one hex-encoded JSON order per line on stdin (produced by
 *  scripts/wp-orders-migrate.sh) and writes it to oc_order plus
 *  oc_order_product / oc_order_total / oc_order_history.
 *
 *  Every imported order carries `comment = WC#<woocommerce id>`, which is
 *  both the traceability marker and the idempotency key: an order whose
 *  marker is already present is skipped, so the script is safe to re-run.
 *
 *  Line items are matched to the OpenCart catalogue by SKU (variation SKU
 *  first, then the parent product's) against oc_product.sku, falling back
 *  to oc_product.model. Unmatched lines are still imported — they just keep
 *  product_id = 0, exactly like the earlier WooCommerce import did.
 *
 *  Config comes from the environment:
 *    OC_DB_HOST OC_DB_PORT OC_DB_USER OC_DB_PASS OC_DB_NAME OC_DB_PREFIX
 *    OC_LANGUAGE_ID OC_CURRENCY_ID OC_CUSTOMER_GROUP_ID
 *    OC_STORE_NAME OC_STORE_URL OC_INVOICE_PREFIX
 *
 *  Flags: --dry-run
 * ============================================================
 */

if (PHP_SAPI !== 'cli') {
    exit("This script must be run from the command line.\n");
}

$dry_run = in_array('--dry-run', $argv, true);

$host   = getenv('OC_DB_HOST') ?: 'db';
$port   = (int)(getenv('OC_DB_PORT') ?: 3306);
$user   = getenv('OC_DB_USER') ?: '';
$pass   = getenv('OC_DB_PASS') ?: '';
$name   = getenv('OC_DB_NAME') ?: '';
$prefix = getenv('OC_DB_PREFIX') ?: 'oc_';

$language_id        = (int)(getenv('OC_LANGUAGE_ID') ?: 2);
$currency_id        = (int)(getenv('OC_CURRENCY_ID') ?: 3);
$customer_group_id  = (int)(getenv('OC_CUSTOMER_GROUP_ID') ?: 1);
$store_name         = getenv('OC_STORE_NAME') ?: 'TimeOnline';
$store_url          = getenv('OC_STORE_URL') ?: 'https://timeonline.gr';
$invoice_prefix     = getenv('OC_INVOICE_PREFIX') ?: 'INV-';

if ($name === '' || $user === '') {
    exit("Error: OC_DB_NAME and OC_DB_USER must be set.\n");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $user, $pass, $name, $port);
$db->set_charset('utf8mb4');

// WooCommerce status -> OpenCart order_status_id.
$STATUS_MAP = array(
    'wc-completed'  => 5,   // Ολοκληρώθηκε
    'wc-processing' => 2,   // Σε επεξεργασία
    'wc-on-hold'    => 1,   // Σε Αναμονή
    'wc-pending'    => 1,   // Σε Αναμονή
    'wc-cancelled'  => 7,   // Ακυρώθηκε
    'wc-refunded'   => 11,  // Επιστράφηκε
    'wc-failed'     => 10,  // Απέτυχε
);

// The earlier WooCommerce import used these; keeping them makes the new rows
// sort and filter alongside the existing ones.
const PAYMENT_CODE  = 'wc_import';
const SHIPPING_CODE = 'courier';
const SHIPPING_NAME = 'Courier';

function meta($order, $key, $default = '') {
    $v = isset($order['meta'][$key]) ? $order['meta'][$key] : null;
    return ($v === null || $v === '') ? $default : $v;
}

function cut($value, $length) {
    return mb_substr((string)$value, 0, $length, 'UTF-8');
}

// — Parse stdin ——————————————————————————————————————————————
// The export emits two kinds of hex-encoded JSON record, tagged by "type":
// one per order and one per line item. Items are attached to their order here.
$orders = array();
$items = array();
$malformed = 0;
$line_no = 0;

while (($line = fgets(STDIN)) !== false) {
    $line_no++;
    $line = trim($line);

    if ($line === '' || $line === 'NULL') {
        continue;
    }

    $json = @hex2bin($line);
    $row = $json === false ? null : json_decode($json, true);

    if (!is_array($row) || !isset($row['type'])) {
        $malformed++;
        fwrite(STDERR, "  ! skipped malformed input on line {$line_no}\n");
        continue;
    }

    if ($row['type'] === 'order' && !empty($row['wc_id'])) {
        $orders[(int)$row['wc_id']] = $row;
    } elseif ($row['type'] === 'item' && !empty($row['order_id'])) {
        $items[(int)$row['order_id']][] = $row;
    } else {
        $malformed++;
        fwrite(STDERR, "  ! skipped unrecognised record on line {$line_no}\n");
    }
}

if (!$orders) {
    exit("Error: no orders on stdin.\n");
}

foreach ($orders as $wc_id => $_) {
    $orders[$wc_id]['items'] = isset($items[$wc_id]) ? $items[$wc_id] : array();

    if (!$orders[$wc_id]['items']) {
        fwrite(STDERR, "  ! WC#{$wc_id}: no line items found\n");
    }
}

printf("Read %d orders / %d line items from WooCommerce%s.\n\n", count($orders), array_sum(array_map('count', $items)), $malformed ? " ({$malformed} malformed)" : '');

// — Country lookup ————————————————————————————————————————————
$countries = array();
$result = $db->query("SELECT country_id, iso_code_2, name FROM `{$prefix}country`");
while ($row = $result->fetch_assoc()) {
    $countries[strtoupper($row['iso_code_2'])] = $row;
}

// — Import ————————————————————————————————————————————————————
$imported = 0;
$skipped = 0;
$unmatched_products = array();
$linked_customers = 0;
$linked_products = 0;
$total_lines = 0;

foreach ($orders as $order) {
    $wc_id = (int)$order['wc_id'];
    $marker = 'WC#' . $wc_id;

    // Idempotency: never import the same WooCommerce order twice.
    $stmt = $db->prepare("SELECT order_id FROM `{$prefix}order` WHERE comment = ?");
    $stmt->bind_param('s', $marker);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($existing) {
        printf("  skip  %s — already imported as order #%d\n", $marker, $existing['order_id']);
        $skipped++;
        continue;
    }

    $status = isset($STATUS_MAP[$order['status']]) ? $STATUS_MAP[$order['status']] : 1;

    if (!isset($STATUS_MAP[$order['status']])) {
        fwrite(STDERR, "  ! {$marker}: unknown status '{$order['status']}', defaulting to Σε Αναμονή\n");
    }

    $email = meta($order, '_billing_email');
    $bill_country = strtoupper(meta($order, '_billing_country', 'GR'));
    $ship_country = strtoupper(meta($order, '_shipping_country', $bill_country));

    // Link to a customer account by e-mail; guest checkouts stay at 0.
    $customer_id = 0;
    if ($email !== '') {
        $stmt = $db->prepare("SELECT customer_id FROM `{$prefix}customer` WHERE LOWER(email) = LOWER(?) ORDER BY customer_id LIMIT 1");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $c = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($c) {
            $customer_id = (int)$c['customer_id'];
            $linked_customers++;
        }
    }

    // Shipping falls back to billing when WooCommerce recorded none.
    $ship_first = meta($order, '_shipping_first_name', meta($order, '_billing_first_name'));
    $ship_last  = meta($order, '_shipping_last_name', meta($order, '_billing_last_name'));

    $sub_total = 0.0;
    $lines = array();

    foreach ((isset($order['items']) && is_array($order['items']) ? $order['items'] : array()) as $item) {
        $total_lines++;
        $qty = max(1, (int)$item['qty']);
        $line_total = (float)$item['line_total'];
        $sub_total += $line_total;

        $sku = trim((string)$item['sku']);
        $product_id = 0;
        $model = '';

        if ($sku !== '') {
            // Exact SKU wins; model is the fallback the old catalogue used.
            $stmt = $db->prepare("SELECT product_id, model FROM `{$prefix}product` WHERE sku = ? ORDER BY product_id LIMIT 1");
            $stmt->bind_param('s', $sku);
            $stmt->execute();
            $p = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$p) {
                $stmt = $db->prepare("SELECT product_id, model FROM `{$prefix}product` WHERE model = ? ORDER BY product_id LIMIT 1");
                $stmt->bind_param('s', $sku);
                $stmt->execute();
                $p = $stmt->get_result()->fetch_assoc();
                $stmt->close();
            }

            if ($p) {
                $product_id = (int)$p['product_id'];
                $model = (string)$p['model'];
                $linked_products++;
            }
        }

        if ($product_id === 0) {
            $unmatched_products[] = $marker . ' — ' . $item['name'] . ' (SKU: ' . ($sku !== '' ? $sku : 'κενό') . ')';
        }

        $lines[] = array(
            'product_id' => $product_id,
            'name'       => cut($item['name'], 255),
            'model'      => cut($model, 64),
            'quantity'   => $qty,
            'price'      => $line_total / $qty,
            'total'      => $line_total,
        );
    }

    $shipping = (float)meta($order, '_order_shipping', 0);
    $tax = (float)meta($order, '_order_tax', 0) + (float)meta($order, '_order_shipping_tax', 0);
    $discount = (float)meta($order, '_cart_discount', 0);
    $grand_total = (float)meta($order, '_order_total', 0);

    if ($dry_run) {
        printf(
            "  [dry-run] %s -> %s | %s | %s | %d είδη | σύνολο %.2f | πελάτης %s\n",
            $marker,
            $order['status'],
            $order['date_added'],
            $email,
            count($lines),
            $grand_total,
            $customer_id ? '#' . $customer_id : 'guest'
        );
        $imported++;
        continue;
    }

    // — oc_order ———————————————————————————————————————————————
    $sql = "INSERT INTO `{$prefix}order` SET
        invoice_no = 0, invoice_prefix = ?, store_id = 0, store_name = ?, store_url = ?,
        customer_id = ?, customer_group_id = ?, firstname = ?, lastname = ?, email = ?, telephone = ?,
        fax = '', custom_field = '',
        payment_firstname = ?, payment_lastname = ?, payment_company = ?, payment_address_1 = ?,
        payment_address_2 = ?, payment_city = ?, payment_postcode = ?, payment_country = ?,
        payment_country_id = ?, payment_zone = '', payment_zone_id = 0, payment_address_format = '',
        payment_custom_field = '', payment_method = ?, payment_code = ?,
        shipping_firstname = ?, shipping_lastname = ?, shipping_company = ?, shipping_address_1 = ?,
        shipping_address_2 = ?, shipping_city = ?, shipping_postcode = ?, shipping_country = ?,
        shipping_country_id = ?, shipping_zone = '', shipping_zone_id = 0, shipping_address_format = '',
        shipping_custom_field = '', shipping_method = ?, shipping_code = ?,
        comment = ?, total = ?, order_status_id = ?,
        affiliate_id = 0, commission = 0, marketing_id = 0, tracking = '',
        language_id = ?, currency_id = ?, currency_code = ?, currency_value = 1.00000000,
        ip = ?, forwarded_ip = '', user_agent = ?, accept_language = '',
        date_added = ?, date_modified = ?";

    $bill_country_name = isset($countries[$bill_country]) ? $countries[$bill_country]['name'] : $bill_country;
    $bill_country_id   = isset($countries[$bill_country]) ? (int)$countries[$bill_country]['country_id'] : 0;
    $ship_country_name = isset($countries[$ship_country]) ? $countries[$ship_country]['name'] : $ship_country;
    $ship_country_id   = isset($countries[$ship_country]) ? (int)$countries[$ship_country]['country_id'] : 0;

    // Each entry is [bind type, value] so the type string is derived from the
    // list itself — it can never drift out of sync with the placeholders.
    $bind = array(
        array('s', $invoice_prefix),
        array('s', $store_name),
        array('s', $store_url),
        array('i', $customer_id),
        array('i', $customer_group_id),
        array('s', cut(meta($order, '_billing_first_name'), 32)),
        array('s', cut(meta($order, '_billing_last_name'), 32)),
        array('s', cut($email, 96)),
        array('s', cut(meta($order, '_billing_phone'), 32)),
        array('s', cut(meta($order, '_billing_first_name'), 32)),
        array('s', cut(meta($order, '_billing_last_name'), 32)),
        array('s', cut(meta($order, '_billing_company'), 60)),
        array('s', cut(meta($order, '_billing_address_1'), 128)),
        array('s', cut(meta($order, '_billing_address_2'), 128)),
        array('s', cut(meta($order, '_billing_city'), 128)),
        array('s', cut(meta($order, '_billing_postcode'), 10)),
        array('s', cut($bill_country_name, 128)),
        array('i', $bill_country_id),
        array('s', cut(meta($order, '_payment_method_title'), 128)),
        array('s', PAYMENT_CODE),
        array('s', cut($ship_first, 32)),
        array('s', cut($ship_last, 32)),
        array('s', cut(meta($order, '_shipping_company'), 40)),
        array('s', cut(meta($order, '_shipping_address_1', meta($order, '_billing_address_1')), 128)),
        array('s', cut(meta($order, '_shipping_address_2', meta($order, '_billing_address_2')), 128)),
        array('s', cut(meta($order, '_shipping_city', meta($order, '_billing_city')), 128)),
        array('s', cut(meta($order, '_shipping_postcode', meta($order, '_billing_postcode')), 10)),
        array('s', cut($ship_country_name, 128)),
        array('i', $ship_country_id),
        array('s', SHIPPING_NAME),
        array('s', SHIPPING_CODE),
        array('s', $marker),
        array('d', $grand_total),
        array('i', $status),
        array('i', $language_id),
        array('i', $currency_id),
        array('s', cut(meta($order, '_order_currency', 'EUR'), 3)),
        array('s', cut(meta($order, '_customer_ip_address'), 40)),
        array('s', cut(meta($order, '_customer_user_agent'), 255)),
        array('s', $order['date_added']),
        array('s', $order['date_modified']),
    );

    $placeholders = substr_count($sql, '?');

    if (count($bind) !== $placeholders) {
        fwrite(STDERR, sprintf("Error: %d bind values for %d placeholders — aborting.\n", count($bind), $placeholders));
        exit(1);
    }

    $types = implode('', array_column($bind, 0));
    $args = array_column($bind, 1);

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$args);
    $stmt->execute();
    $order_id = (int)$stmt->insert_id;
    $stmt->close();

    // — oc_order_product ———————————————————————————————————————
    $stmt = $db->prepare("INSERT INTO `{$prefix}order_product` SET order_id = ?, product_id = ?, name = ?, model = ?, quantity = ?, price = ?, total = ?, tax = 0, reward = 0");
    foreach ($lines as $l) {
        $stmt->bind_param('iissidd', $order_id, $l['product_id'], $l['name'], $l['model'], $l['quantity'], $l['price'], $l['total']);
        $stmt->execute();
    }
    $stmt->close();

    // — oc_order_total ——————————————————————————————————————————
    // Same codes, titles and sort order the earlier import used.
    $totals = array(
        array('sub_total', 'Sub-Total', $sub_total, 1),
    );

    if (abs($discount) > 0.001) {
        $totals[] = array('coupon', 'Coupon', -abs($discount), 2);
    }

    $totals[] = array('shipping', 'Shipping', $shipping, 3);
    $totals[] = array('tax', 'VAT', $tax, 4);
    $totals[] = array('total', 'Total', $grand_total, 9);

    $stmt = $db->prepare("INSERT INTO `{$prefix}order_total` SET order_id = ?, code = ?, title = ?, value = ?, sort_order = ?");
    foreach ($totals as $t) {
        $stmt->bind_param('issdi', $order_id, $t[0], $t[1], $t[2], $t[3]);
        $stmt->execute();
    }
    $stmt->close();

    // — oc_order_history ————————————————————————————————————————
    $history_comment = 'Imported from WooCommerce';
    $stmt = $db->prepare("INSERT INTO `{$prefix}order_history` SET order_id = ?, order_status_id = ?, notify = 0, comment = ?, date_added = ?");
    $stmt->bind_param('iiss', $order_id, $status, $history_comment, $order['date_added']);
    $stmt->execute();
    $stmt->close();

    printf(
        "  import %s -> OC #%d | %s | %d είδη | σύνολο %.2f | πελάτης %s\n",
        $marker, $order_id, $order['date_added'], count($lines), $grand_total,
        $customer_id ? '#' . $customer_id : 'guest'
    );

    $imported++;
}

$db->close();

echo "\n=================================================\n";
printf("  %s\n", $dry_run ? 'DRY RUN — nothing was written' : 'Import complete');
printf("  Orders imported        : %d\n", $imported);
printf("  Orders skipped (dupes) : %d\n", $skipped);
printf("  Line items             : %d (%d matched to catalogue)\n", $total_lines, $linked_products);
printf("  Orders linked to account: %d\n", $linked_customers);

if ($unmatched_products) {
    printf("\n  Γραμμές χωρίς αντιστοίχιση προϊόντος (product_id = 0):\n");
    foreach ($unmatched_products as $u) {
        echo "    - {$u}\n";
    }
}

echo "=================================================\n";
