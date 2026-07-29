<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(0);

$oc = new PDO('mysql:host=localhost;dbname=u419340640_timeonline_oc;charset=utf8mb4', 'u419340640_timeonline_oc', '79~kD@w:+oL>', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

// ─── Δημιούργησε ή βρες Filter Group ─────────────────────────
function getOrCreateFilterGroup(PDO $oc, string $name): int {
    $stmt = $oc->prepare("SELECT fg.filter_group_id FROM oc_filter_group fg
        JOIN oc_filter_group_description fgd ON fgd.filter_group_id = fg.filter_group_id
        WHERE fgd.name = ? AND fgd.language_id = 1");
    $stmt->execute([$name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int)$row['filter_group_id'];

    $oc->prepare("INSERT INTO oc_filter_group (sort_order) VALUES (0)")->execute();
    $fgId = (int)$oc->lastInsertId();
    foreach ([1, 2] as $lid) {
        $oc->prepare("INSERT INTO oc_filter_group_description (filter_group_id, language_id, name) VALUES (?,?,?)")
            ->execute([$fgId, $lid, $name]);
    }
    return $fgId;
}

// ─── Δημιούργησε ή βρες Filter ───────────────────────────────
function getOrCreateFilter(PDO $oc, string $name, int $groupId): int {
    $stmt = $oc->prepare("SELECT f.filter_id FROM oc_filter f
        JOIN oc_filter_description fd ON fd.filter_id = f.filter_id
        WHERE fd.name = ? AND fd.language_id = 1 AND f.filter_group_id = ?");
    $stmt->execute([$name, $groupId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) return (int)$row['filter_id'];

    $oc->prepare("INSERT INTO oc_filter (filter_group_id, sort_order) VALUES (?,0)")->execute([$groupId]);
    $fId = (int)$oc->lastInsertId();
    foreach ([1, 2] as $lid) {
        $oc->prepare("INSERT INTO oc_filter_description (filter_id, filter_group_id, language_id, name) VALUES (?,?,?,?)")
            ->execute([$fId, $groupId, $lid, $name]);
    }
    return $fId;
}

// ─── 1. BRAND filter group ───────────────────────────────────
echo "Δημιουργία Brand filters...\n";
$brandGroupId = getOrCreateFilterGroup($oc, 'Brand');

$brands = $oc->query("
    SELECT m.manufacturer_id, m.name, COUNT(p.product_id) as total
    FROM oc_manufacturer m
    JOIN oc_product p ON p.manufacturer_id = m.manufacturer_id
    WHERE m.name NOT IN ('OEM', 'Black Friday')
    GROUP BY m.manufacturer_id
    ORDER BY m.name
")->fetchAll(PDO::FETCH_ASSOC);

$brandFilterIds = [];
foreach ($brands as $brand) {
    $filterId = getOrCreateFilter($oc, $brand['name'], $brandGroupId);
    $brandFilterIds[$brand['manufacturer_id']] = $filterId;
    echo "  Brand: {$brand['name']} → filter #{$filterId}\n";
}

// Σύνδεσε Brand filters με προϊόντα
$oc->prepare("DELETE FROM oc_product_filter WHERE filter_id IN (
    SELECT filter_id FROM oc_filter WHERE filter_group_id = ?
)")->execute([$brandGroupId]);

$products = $oc->query("SELECT product_id, manufacturer_id FROM oc_product WHERE manufacturer_id > 0")->fetchAll(PDO::FETCH_ASSOC);
$brandLinked = 0;
foreach ($products as $prod) {
    if (isset($brandFilterIds[$prod['manufacturer_id']])) {
        $oc->prepare("INSERT IGNORE INTO oc_product_filter (product_id, filter_id) VALUES (?,?)")
            ->execute([$prod['product_id'], $brandFilterIds[$prod['manufacturer_id']]]);
        $brandLinked++;
    }
}
echo "✅ Brand: $brandLinked προϊόντα συνδέθηκαν\n\n";

// ─── 2. ΦΥΛΟ filter group ────────────────────────────────────
echo "Δημιουργία Φύλο filters...\n";
$fyloGroupId = getOrCreateFilterGroup($oc, 'Φύλο');

$fyloValues = $oc->query("
    SELECT DISTINCT pa.text
    FROM oc_product_attribute pa
    JOIN oc_attribute_description ad ON ad.attribute_id = pa.attribute_id AND ad.language_id = 1
    WHERE ad.name = 'Φύλο' AND pa.language_id = 1
    ORDER BY pa.text
")->fetchAll(PDO::FETCH_COLUMN);

$fyloFilterIds = [];
foreach ($fyloValues as $val) {
    $filterId = getOrCreateFilter($oc, $val, $fyloGroupId);
    $fyloFilterIds[$val] = $filterId;
    echo "  Φύλο: $val → filter #{$filterId}\n";
}

// Σύνδεσε Φύλο filters με προϊόντα
$oc->prepare("DELETE FROM oc_product_filter WHERE filter_id IN (
    SELECT filter_id FROM oc_filter WHERE filter_group_id = ?
)")->execute([$fyloGroupId]);

$fyloProducts = $oc->query("
    SELECT pa.product_id, pa.text
    FROM oc_product_attribute pa
    JOIN oc_attribute_description ad ON ad.attribute_id = pa.attribute_id AND ad.language_id = 1
    WHERE ad.name = 'Φύλο' AND pa.language_id = 1
")->fetchAll(PDO::FETCH_ASSOC);

$fyloLinked = 0;
foreach ($fyloProducts as $prod) {
    if (isset($fyloFilterIds[$prod['text']])) {
        $oc->prepare("INSERT IGNORE INTO oc_product_filter (product_id, filter_id) VALUES (?,?)")
            ->execute([$prod['product_id'], $fyloFilterIds[$prod['text']]]);
        $fyloLinked++;
    }
}
echo "✅ Φύλο: $fyloLinked προϊόντα συνδέθηκαν\n\n";

// ─── 3. ΑΞΕΣΟΥΑΡ filter group ────────────────────────────────
echo "Δημιουργία Αξεσουάρ filters...\n";
$aksGroupId = getOrCreateFilterGroup($oc, 'Αξεσουάρ');

$aksValues = $oc->query("
    SELECT DISTINCT pa.text, COUNT(*) as total
    FROM oc_product_attribute pa
    JOIN oc_attribute_description ad ON ad.attribute_id = pa.attribute_id AND ad.language_id = 1
    WHERE ad.name = 'Αξεσουάρ' AND pa.language_id = 1
    GROUP BY pa.text
    ORDER BY total DESC
")->fetchAll(PDO::FETCH_ASSOC);

$aksFilterIds = [];
foreach ($aksValues as $val) {
    $filterId = getOrCreateFilter($oc, $val['text'], $aksGroupId);
    $aksFilterIds[$val['text']] = $filterId;
    echo "  Αξεσουάρ: {$val['text']} → filter #{$filterId}\n";
}

// Σύνδεσε Αξεσουάρ filters με προϊόντα
$oc->prepare("DELETE FROM oc_product_filter WHERE filter_id IN (
    SELECT filter_id FROM oc_filter WHERE filter_group_id = ?
)")->execute([$aksGroupId]);

$aksProducts = $oc->query("
    SELECT pa.product_id, pa.text
    FROM oc_product_attribute pa
    JOIN oc_attribute_description ad ON ad.attribute_id = pa.attribute_id AND ad.language_id = 1
    WHERE ad.name = 'Αξεσουάρ' AND pa.language_id = 1
")->fetchAll(PDO::FETCH_ASSOC);

$aksLinked = 0;
foreach ($aksProducts as $prod) {
    if (isset($aksFilterIds[$prod['text']])) {
        $oc->prepare("INSERT IGNORE INTO oc_product_filter (product_id, filter_id) VALUES (?,?)")
            ->execute([$prod['product_id'], $aksFilterIds[$prod['text']]]);
        $aksLinked++;
    }
}
echo "✅ Αξεσουάρ: $aksLinked προϊόντα συνδέθηκαν\n\n";

// ─── Σύνδεσε filters με ΟΛΕΣ τις κατηγορίες ─────────────────
echo "Σύνδεση filters με κατηγορίες...\n";
$categories = $oc->query("SELECT category_id FROM oc_category")->fetchAll(PDO::FETCH_COLUMN);

$oc->prepare("DELETE FROM oc_category_filter WHERE filter_group_id IN (?,?,?)")
    ->execute([$brandGroupId, $fyloGroupId, $aksGroupId]);

foreach ($categories as $catId) {
    foreach ([$brandGroupId, $fyloGroupId, $aksGroupId] as $fgId) {
        $oc->prepare("INSERT IGNORE INTO oc_category_filter (category_id, filter_group_id) VALUES (?,?)")
            ->execute([$catId, $fgId]);
    }
}
echo "✅ Filters συνδέθηκαν με " . count($categories) . " κατηγορίες\n";

echo "\n🎉 Ολοκληρώθηκε!\n";
echo "Filter Groups: Brand (#{$brandGroupId}), Φύλο (#{$fyloGroupId}), Αξεσουάρ (#{$aksGroupId})\n";
