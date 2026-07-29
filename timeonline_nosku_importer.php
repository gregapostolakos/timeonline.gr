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

$products = $wp->query("
    SELECT p.ID, p.post_title, p.post_name, p.post_content, p.post_excerpt, p.post_date,
        MAX(CASE WHEN pm.meta_key='_regular_price' THEN pm.meta_value END) as price,
        MAX(CASE WHEN pm.meta_key='_sale_price' THEN pm.meta_value END) as sale_price,
        MAX(CASE WHEN pm.meta_key='_stock' THEN pm.meta_value END) as stock,
        MAX(CASE WHEN pm.meta_key='_stock_status' THEN pm.meta_value END) as stock_status,
        MAX(CASE WHEN pm.meta_key='_weight' THEN pm.meta_value END) as weight,
        MAX(CASE WHEN pm.meta_key='_thumbnail_id' THEN pm.meta_value END) as thumb_id,
        MAX(CASE WHEN pm.meta_key='_yoast_wpseo_title' THEN pm.meta_value END) as seo_title,
        MAX(CASE WHEN pm.meta_key='_yoast_wpseo_metadesc' THEN pm.meta_value END) as seo_desc
    FROM wp_posts p
    LEFT JOIN wp_postmeta pm ON pm.post_id = p.ID
    WHERE p.post_type = 'product' AND p.post_status = 'publish'
    GROUP BY p.ID
    HAVING MAX(CASE WHEN pm.meta_key='_sku' THEN pm.meta_value END) = ''
        OR MAX(CASE WHEN pm.meta_key='_sku' THEN pm.meta_value END) IS NULL
    ORDER BY p.ID ASC
")->fetchAll(PDO::FETCH_ASSOC);

echo "Βρέθηκαν " . count($products) . " προϊόντα χωρίς SKU\n";

$imgPathStmt = $wp->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_wp_attached_file'");
$galleryStmt = $wp->prepare("SELECT meta_value FROM wp_postmeta WHERE post_id = ? AND meta_key = '_product_image_gallery'");
$catStmt     = $wp->prepare("
    SELECT t.name FROM wp_terms t
    JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
    JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
    WHERE tt.taxonomy = 'product_cat' AND tr.object_id = ?
");
$brandStmt = $wp->prepare("
    SELECT t.name FROM wp_terms t
    JOIN wp_term_taxonomy tt ON tt.term_id = t.term_id
    JOIN wp_term_relationships tr ON tr.term_taxonomy_id = tt.term_taxonomy_id
    WHERE tt.taxonomy IN ('brand','product_brand') AND tr.object_id = ?
    LIMIT 1
");

$stats = ['inserted'=>0, 'skipped'=>0, 'errors'=>0];

foreach ($products as $i => $prod) {
    try {
        $name      = html_entity_decode(trim($prod['post_title']), ENT_QUOTES, 'UTF-8');
        $slug      = urldecode(trim($prod['post_name']));
        $slug      = preg_replace('/__trashed$/', '', $slug);

        // Φτιάξε SKU από post_name
        $sku = strtoupper(preg_replace('/[^a-zA-Z0-9\-]/', '-', $slug));
        $sku = trim(preg_replace('/-+/', '-', $sku), '-');
        $sku = substr($sku, 0, 64);

        if (empty($name) || empty($sku)) { $stats['skipped']++; continue; }

        // Έλεγξε αν υπάρχει ήδη
        $chk = $oc->prepare("SELECT product_id FROM oc_product WHERE sku = ? LIMIT 1");
        $chk->execute([$sku]);
        if ($chk->fetchColumn()) { $stats['skipped']++; continue; }

        $chk2 = $oc->prepare("SELECT p.product_id FROM oc_product p
            JOIN oc_product_description pd ON pd.product_id = p.product_id
            WHERE pd.name = ? AND pd.language_id = 1 LIMIT 1");
        $chk2->execute([$name]);
        if ($chk2->fetchColumn()) { $stats['skipped']++; continue; }

        $price       = floatval(str_replace(',', '.', $prod['price'] ?? '0'));
        $salePrice   = floatval(str_replace(',', '.', $prod['sale_price'] ?? '0'));
        $stock       = intval($prod['stock'] ?? 0);
        $stockStatus = $prod['stock_status'] ?? 'instock';
        $weight      = floatval($prod['weight'] ?? '0');
        $description = trim($prod['post_content'] ?? '');
        $shortDesc   = trim($prod['post_excerpt'] ?? '');
        $seoTitle    = html_entity_decode(trim($prod['seo_title'] ?? ''), ENT_QUOTES, 'UTF-8') ?: $name;
        $seoDesc     = trim($prod['seo_desc'] ?? '') ?: substr(strip_tags($shortDesc), 0, 200);

        // Εικόνα
        $mainImage = '';
        if (!empty($prod['thumb_id'])) {
            $imgPathStmt->execute([$prod['thumb_id']]);
            $imgPath = $imgPathStmt->fetchColumn();
            if ($imgPath) {
                $srcPath  = WP_UPLOADS . $imgPath;
                $filename = preg_replace('/[^\w\-.]/', '_', basename($imgPath));
                $dstPath  = OC_IMAGE_DIR . $filename;
                if (!file_exists($dstPath) && file_exists($srcPath)) copy($srcPath, $dstPath);
                if (file_exists($dstPath)) $mainImage = 'catalog/timeonline/' . $filename;
            }
        }

        // Brand
        $brandStmt->execute([$prod['ID']]);
        $brandRow = $brandStmt->fetch(PDO::FETCH_ASSOC);
        $manufacturerId = 0;
        if ($brandRow) {
            $mStmt = $oc->prepare("SELECT manufacturer_id FROM oc_manufacturer WHERE name = ? LIMIT 1");
            $mStmt->execute([trim($brandRow['name'])]);
            $manufacturerId = $mStmt->fetchColumn() ?: 0;
        }

        // Insert product
        $oc->prepare("INSERT INTO oc_product
            (model, sku, price, quantity, weight, image, manufacturer_id, status,
             tax_class_id, date_added, date_modified, minimum, subtract,
             stock_status_id, shipping, points, date_available,
             weight_class_id, length_class_id, sort_order, viewed)
            VALUES (?,?,?,?,?,?,?,1,0,NOW(),NOW(),1,1,?,1,0,NOW(),1,1,0,0)"
        )->execute([
            $sku, $sku, $price, $stock, $weight, $mainImage, $manufacturerId,
            ($stockStatus === 'instock') ? 7 : 5
        ]);
        $productId = (int)$oc->lastInsertId();

        foreach ([1, 2] as $lid) {
            $oc->prepare("INSERT INTO oc_product_description
                (product_id, language_id, name, description, tag, meta_title, meta_description, meta_keyword)
                VALUES (?,?,?,?,'',?,?,'0')"
            )->execute([$productId, $lid, $name, $description ?: $shortDesc, $seoTitle, $seoDesc]);
        }
        $oc->prepare("INSERT INTO oc_product_to_store (product_id, store_id) VALUES (?,?)")
            ->execute([$productId, STORE_ID]);

        // Κατηγορίες
        $catStmt->execute([$prod['ID']]);
        $cats = $catStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($cats as $cat) {
            $catName = trim($cat['name']);
            if (in_array(strtolower($catName), ['uncategorized', 'χωρίς κατηγορία'])) continue;
            $cStmt = $oc->prepare("SELECT cd.category_id FROM oc_category_description cd WHERE cd.name = ? AND cd.language_id = 1 LIMIT 1");
            $cStmt->execute([$catName]);
            $catId = $cStmt->fetchColumn();
            if ($catId) {
                $oc->prepare("INSERT IGNORE INTO oc_product_to_category (product_id, category_id) VALUES (?,?)")
                    ->execute([$productId, $catId]);
            }
        }

        // Sale price
        if ($salePrice > 0 && $salePrice < $price) {
            $oc->prepare("INSERT INTO oc_product_special (product_id, customer_group_id, priority, price, date_start, date_end)
                VALUES (?,1,0,?,'0000-00-00','0000-00-00')")->execute([$productId, $salePrice]);
        }

        // Gallery
        $galleryStmt->execute([$prod['ID']]);
        $galleryIds = array_filter(explode(',', $galleryStmt->fetchColumn() ?: ''));
        foreach ($galleryIds as $idx => $imgId) {
            $imgPathStmt->execute([trim($imgId)]);
            $imgPath = $imgPathStmt->fetchColumn();
            if ($imgPath) {
                $srcPath  = WP_UPLOADS . $imgPath;
                $filename = preg_replace('/[^\w\-.]/', '_', basename($imgPath));
                $dstPath  = OC_IMAGE_DIR . $filename;
                if (!file_exists($dstPath) && file_exists($srcPath)) copy($srcPath, $dstPath);
                if (file_exists($dstPath)) {
                    $oc->prepare("INSERT INTO oc_product_image (product_id, image, sort_order) VALUES (?,?,?)")
                        ->execute([$productId, 'catalog/timeonline/' . $filename, $idx]);
                }
            }
        }

        // SEO URL
        $seoSlug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', urldecode($prod['post_name'])));
        $seoSlug = trim($seoSlug, '-');
        $oc->prepare("INSERT IGNORE INTO oc_seo_url (store_id, language_id, query, keyword) VALUES (0,1,?,?)")
            ->execute(['product_id=' . $productId, $seoSlug]);
        $oc->prepare("INSERT IGNORE INTO oc_seo_url (store_id, language_id, query, keyword) VALUES (0,2,?,?)")
            ->execute(['product_id=' . $productId, $seoSlug]);

        echo "✅ [{$name}] → SKU: {$sku}\n";
        $stats['inserted']++;

    } catch (Exception $e) {
        $stats['errors']++;
        echo "❌ [{$prod['post_title']}]: " . $e->getMessage() . "\n";
    }
}

echo "\n📊 Αποτελέσματα:\n";
echo "✅ Inserted : {$stats['inserted']}\n";
echo "⏭  Skipped  : {$stats['skipped']}\n";
echo "❌ Errors   : {$stats['errors']}\n";
