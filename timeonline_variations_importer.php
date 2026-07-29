<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

$wp = new PDO('mysql:host=localhost;dbname=u419340640_hTfz3;charset=utf8mb4', 'u419340640_xla7L', '/z&^#h>/I5', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
$oc = new PDO('mysql:host=localhost;dbname=u419340640_timeonline_oc;charset=utf8mb4', 'u419340640_timeonline_oc', '79~kD@w:+oL>', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

define('WP_UPLOADS',   '/home/u419340640/domains/timeonline.gr/public_html/wp-content/uploads/');
define('OC_IMAGE_DIR', '/home/u419340640/domains/timeonline.gr/public_html/oc/image/catalog/timeonline/');
define('OC_PREFIX',    'oc_');
define('LANG_ID',      1);
define('STORE_ID',     0);

if (!is_dir(OC_IMAGE_DIR)) mkdir(OC_IMAGE_DIR, 0755, true);

// Τράβα όλα τα variations με στοιχεία parent
$variations = $wp->query("
    SELECT 
        v.ID, v.post_parent,
        MAX(CASE WHEN vm.meta_key='_sku' THEN vm.meta_value END) as sku,
        MAX(CASE WHEN vm.meta_key='_regular_price' THEN vm.meta_value END) as price,
        MAX(CASE WHEN vm.meta_key='_sale_price' THEN vm.meta_value END) as sale_price,
        MAX(CASE WHEN vm.meta_key='_stock' THEN vm.meta_value END) as stock,
        MAX(CASE WHEN vm.meta_key='_stock_status' THEN vm.meta_value END) as stock_status,
        MAX(CASE WHEN vm.meta_key='_thumbnail_id' THEN vm.meta_value END) as thumb_id,
        -- Parent data
        p.post_title as parent_title,
        p.post_content as parent_content,
        p.post_excerpt as parent_excerpt,
        MAX(CASE WHEN pm.meta_key='_thumbnail_id' THEN pm.meta_value END) as parent_thumb_id,
        MAX(CASE WHEN pm.meta_key='_yoast_wpseo_title' THEN pm.meta_value END) as seo_title,
        MAX(CASE WHEN pm.meta_key='_yoast_wpseo_metadesc' THEN pm.meta_value END) as seo_desc
    FROM wp_posts v
    LEFT JOIN wp_postmeta vm ON vm.post_id = v.ID
    JOIN wp_posts p ON p.ID = v.post_parent
    LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
    WHERE v.post_type = 'product_variation' AND v.post_status = 'publish'
    GROUP BY v.ID
    HAVING sku != '' AND sku IS NOT NULL
    ORDER BY v.post_parent, v.ID
")->fetchAll(PDO::FETCH_ASSOC);

echo "Βρέθηκαν " . count($variations) . " variations\n";

$imgPathStmt = $wp->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'");

// Variation attributes
$varAttrStmt = $wp->prepare("
    SELECT meta_key, meta_value FROM wp_postmeta
    WHERE post_id = ? AND meta_key LIKE 'attribute_%'
");

// Parent categories
$catStmt = $oc->prepare("
    SELECT cd.category_id FROM oc_category_description cd
    JOIN oc_product_to_category pc ON pc.category_id = cd.category_id
    JOIN oc_product op ON op.product_id = pc.product_id
    WHERE op.sku = ? AND cd.language_id = 1
");

// Parent manufacturer
$mfrStmt = $oc->prepare("
    SELECT manufacturer_id FROM oc_product WHERE sku = ? LIMIT 1
");

$stats = ['inserted'=>0, 'skipped'=>0, 'errors'=>0];

foreach ($variations as $i => $var) {
    try {
        $sku         = trim($var['sku']);
        $price       = floatval(str_replace(',', '.', $var['price'] ?? '0'));
        $salePrice   = floatval(str_replace(',', '.', $var['sale_price'] ?? '0'));
        $stock       = intval($var['stock'] ?? 0);
        $stockStatus = $var['stock_status'] ?? 'instock';
        $name        = html_entity_decode(trim($var['parent_title']), ENT_QUOTES, 'UTF-8') . ' - ' . $sku;
        $description = trim($var['parent_content'] ?? '');
        $shortDesc   = trim($var['parent_excerpt'] ?? '');
        $seoTitle    = html_entity_decode(trim($var['seo_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?: $name;
        $seoDesc     = trim($var['seo_desc'] ?? '') ?: substr(strip_tags($shortDesc), 0, 200);

        if (empty($sku)) { $stats['skipped']++; continue; }

        // Έλεγξε αν υπάρχει ήδη
        $chk = $oc->prepare("SELECT product_id FROM oc_product WHERE sku = ? LIMIT 1");
        $chk->execute([$sku]);
        if ($chk->fetchColumn()) { $stats['skipped']++; continue; }

        // Εικόνα - πρώτα variation, μετά parent
        $mainImage = '';
        $thumbId = !empty($var['thumb_id']) ? $var['thumb_id'] : $var['parent_thumb_id'];
        if (!empty($thumbId)) {
            $imgPathStmt->execute([$thumbId]);
            $imgPath = $imgPathStmt->fetchColumn();
            if ($imgPath) {
                $srcPath  = WP_UPLOADS . $imgPath;
                $filename = preg_replace('/[^\w\-.]/', '_', basename($imgPath));
                $dstPath  = OC_IMAGE_DIR . $filename;
                if (!file_exists($dstPath) && file_exists($srcPath)) copy($srcPath, $dstPath);
                if (file_exists($dstPath)) $mainImage = 'catalog/timeonline/' . $filename;
            }
        }

        // Βρες parent SKU για κατηγορίες και manufacturer
        $parentSkuStmt = $wp->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_sku' LIMIT 1");
        $parentSkuStmt->execute([$var['post_parent']]);
        $parentSku = $parentSkuStmt->fetchColumn() ?: '';

        // Manufacturer από parent
        $manufacturerId = 0;
        if (!empty($parentSku)) {
            $mfrStmt->execute([$parentSku]);
            $manufacturerId = $mfrStmt->fetchColumn() ?: 0;
        }

        // Insert product
        $oc->prepare("INSERT INTO oc_product
            (model, sku, price, quantity, weight, image, manufacturer_id, status,
             tax_class_id, date_added, date_modified, minimum, subtract,
             stock_status_id, shipping, points, date_available,
             weight_class_id, length_class_id, sort_order, viewed)
            VALUES (?,?,?,?,0,?,?,1,0,NOW(),NOW(),1,1,?,1,0,NOW(),1,1,0,0)"
        )->execute([
            $sku, $sku, $price, $stock, $mainImage, $manufacturerId,
            ($stockStatus === 'instock') ? 7 : 5
        ]);
        $productId = (int)$oc->lastInsertId();

        // Descriptions
        foreach ([1, 2] as $lid) {
            $oc->prepare("INSERT INTO oc_product_description
                (product_id, language_id, name, description, tag, meta_title, meta_description, meta_keyword)
                VALUES (?,?,?,?,'',?,?,'0')"
            )->execute([$productId, $lid, $name, $description ?: $shortDesc, $seoTitle, $seoDesc]);
        }

        $oc->prepare("INSERT INTO oc_product_to_store (product_id, store_id) VALUES (?,?)")
            ->execute([$productId, STORE_ID]);

        // Κατηγορίες από parent
        if (!empty($parentSku)) {
            $catStmt->execute([$parentSku]);
            $catIds = $catStmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($catIds as $catId) {
                $oc->prepare("INSERT IGNORE INTO oc_product_to_category (product_id, category_id) VALUES (?,?)")
                    ->execute([$productId, $catId]);
            }
        }

        // Sale price
        if ($salePrice > 0 && $salePrice < $price) {
            $oc->prepare("INSERT INTO oc_product_special (product_id, customer_group_id, priority, price, date_start, date_end)
                VALUES (?,1,0,?,'0000-00-00','0000-00-00')")->execute([$productId, $salePrice]);
        }

        // Variation attributes ως product attributes
        $varAttrStmt->execute([$var['ID']]);
        $varAttrs = $varAttrStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($varAttrs as $attr) {
            $attrName = ucfirst(str_replace(['attribute_pa_', 'attribute_', '-', '_'], ['', '', ' ', ' '], $attr['meta_key']));
            $attrVal  = trim($attr['meta_value']);
            if (empty($attrVal)) continue;

            // Get or create attribute group
            $agStmt = $oc->prepare("SELECT ag.attribute_group_id FROM oc_attribute_group ag
                JOIN oc_attribute_group_description agd ON agd.attribute_group_id = ag.attribute_group_id
                WHERE agd.name = 'Χαρακτηριστικά' AND agd.language_id = 1");
            $agStmt->execute();
            $agId = $agStmt->fetchColumn();
            if (!$agId) {
                $oc->prepare("INSERT INTO oc_attribute_group (sort_order) VALUES (0)")->execute();
                $agId = (int)$oc->lastInsertId();
                $oc->prepare("INSERT INTO oc_attribute_group_description (attribute_group_id, language_id, name) VALUES (?,1,'Χαρακτηριστικά')")->execute([$agId]);
            }

            $aStmt = $oc->prepare("SELECT a.attribute_id FROM oc_attribute a
                JOIN oc_attribute_description ad ON ad.attribute_id = a.attribute_id
                WHERE ad.name = ? AND ad.language_id = 1 AND a.attribute_group_id = ?");
            $aStmt->execute([$attrName, $agId]);
            $attrId = $aStmt->fetchColumn();
            if (!$attrId) {
                $oc->prepare("INSERT INTO oc_attribute (attribute_group_id, sort_order) VALUES (?,0)")->execute([$agId]);
                $attrId = (int)$oc->lastInsertId();
                $oc->prepare("INSERT INTO oc_attribute_description (attribute_id, language_id, name) VALUES (?,1,?)")->execute([$attrId, $attrName]);
            }

            foreach ([1, 2] as $lid) {
                $oc->prepare("INSERT IGNORE INTO oc_product_attribute (product_id, attribute_id, language_id, text) VALUES (?,?,?,?)")
                    ->execute([$productId, $attrId, $lid, $attrVal]);
            }
        }

        // SEO URL
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $sku));
        $oc->prepare("INSERT IGNORE INTO oc_seo_url (store_id, language_id, query, keyword) VALUES (0,1,?,?)")
            ->execute(['product_id=' . $productId, $slug]);
        $oc->prepare("INSERT IGNORE INTO oc_seo_url (store_id, language_id, query, keyword) VALUES (0,2,?,?)")
            ->execute(['product_id=' . $productId, $slug]);

        $stats['inserted']++;
        if (($i+1) % 50 === 0) echo "Progress: " . ($i+1) . "/" . count($variations) . "\n";

    } catch (Exception $e) {
        $stats['errors']++;
        if ($stats['errors'] <= 5) echo "❌ [SKU:{$var['sku']}]: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 Αποτελέσματα:\n";
echo "✅ Inserted : {$stats['inserted']}\n";
echo "⏭  Skipped  : {$stats['skipped']}\n";
echo "❌ Errors   : {$stats['errors']}\n";
