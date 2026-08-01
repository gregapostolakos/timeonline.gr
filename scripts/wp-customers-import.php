<?php
/**
 * ============================================================
 *  wp-customers-import.php — import WordPress customers into OpenCart
 *
 *  Reads one hex-encoded JSON record per line on stdin (produced by
 *  scripts/wp-customers-migrate.sh) and writes them to oc_customer.
 *  Addresses are not migrated.
 *
 *  WordPress hashes cannot be converted to OpenCart's format, so each
 *  customer gets an unusable random OpenCart password and their WordPress
 *  hash is parked in <prefix>customer_wp_password. The WordPress Password
 *  Bridge modification verifies it on the customer's first login and
 *  replaces it with a native OpenCart hash.
 *
 *  Config comes from the environment:
 *    OC_DB_HOST OC_DB_PORT OC_DB_USER OC_DB_PASS OC_DB_NAME OC_DB_PREFIX
 *    OC_CUSTOMER_GROUP_ID OC_LANGUAGE_ID OC_STORE_ID
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

$customer_group_id = (int)(getenv('OC_CUSTOMER_GROUP_ID') ?: 1);
$language_id       = (int)(getenv('OC_LANGUAGE_ID') ?: 1);
$store_id          = (int)(getenv('OC_STORE_ID') ?: 0);

if ($name === '' || $user === '') {
    exit("Error: OC_DB_NAME and OC_DB_USER must be set.\n");
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli($host, $user, $pass, $name, $port);
$db->set_charset('utf8mb4');

/**
 * OpenCart's token() — a random string from the same alphabet, used for the
 * 9 character salt and for the throwaway password.
 */
function oc_token($length) {
    $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    $max = strlen($string) - 1;
    $token = '';

    for ($i = 0; $i < $length; $i++) {
        $token .= $string[random_int(0, $max)];
    }

    return $token;
}

// — Parse stdin ——————————————————————————————————————————————
$records = array();
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

    if (!is_array($row) || empty($row['email'])) {
        $malformed++;
        fwrite(STDERR, "  ! skipped malformed input on line {$line_no}\n");
        continue;
    }

    $records[] = $row;
}

if (!$records) {
    exit("Error: no customer records on stdin.\n");
}

printf("Read %d records from WordPress%s.\n\n", count($records), $malformed ? " ({$malformed} malformed)" : '');

// — Make sure the bridge table exists ————————————————————————
// The foreign key means deleting a customer takes their parked hash with it,
// so a recycled customer_id can never inherit someone else's password.
$create_table = "CREATE TABLE IF NOT EXISTS `{$prefix}customer_wp_password` (
  `customer_id` int(11) NOT NULL,
  `hash` varchar(255) NOT NULL,
  `date_added` datetime NOT NULL,
  PRIMARY KEY (`customer_id`),
  CONSTRAINT `fk_{$prefix}customer_wp_password` FOREIGN KEY (`customer_id`)
    REFERENCES `{$prefix}customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";

if ($dry_run) {
    echo "[dry-run] would create table {$prefix}customer_wp_password if missing\n\n";
} else {
    $db->query($create_table);
}

// — Existing customers, keyed by lowercase e-mail ——————————————
$existing = array();
$result = $db->query("SELECT customer_id, LOWER(email) AS email, firstname, lastname, telephone FROM `{$prefix}customer`");

while ($row = $result->fetch_assoc()) {
    $existing[$row['email']] = $row;
}

printf("OpenCart already has %d customers.\n\n", count($existing));

// — Import ————————————————————————————————————————————————————
$inserted = 0;
$linked = 0;
$filled = 0;
$skipped = 0;

$insert_customer = "INSERT INTO `{$prefix}customer` SET
    customer_group_id = ?, store_id = ?, language_id = ?,
    firstname = ?, lastname = ?, email = ?, telephone = '', fax = '',
    password = ?, salt = ?, cart = '', wishlist = '', newsletter = 0,
    address_id = 0, custom_field = '', ip = '', status = 1, safe = 0,
    token = '', code = '', date_added = ?";

$link_hash = "INSERT INTO `{$prefix}customer_wp_password` (customer_id, hash, date_added)
    VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE hash = VALUES(hash), date_added = VALUES(date_added)";

foreach ($records as $row) {
    $email      = trim($row['email']);
    $key        = mb_strtolower($email, 'UTF-8');
    $firstname  = mb_substr(trim((string)($row['firstname'] ?? '')), 0, 32, 'UTF-8');
    $lastname   = mb_substr(trim((string)($row['lastname'] ?? '')), 0, 32, 'UTF-8');
    $hash       = (string)($row['hash'] ?? '');
    $date_added = $row['date_added'] ?: date('Y-m-d H:i:s');

    // WordPress lets a customer register without filling in a name; OpenCart
    // shows firstname all over the account area, so fall back to the local
    // part of the e-mail rather than leaving it blank.
    if ($firstname === '') {
        $firstname = mb_substr(strstr($email, '@', true) ?: $email, 0, 32, 'UTF-8');
    }

    if ($hash === '') {
        fwrite(STDERR, "  ! {$email}: no password hash, skipped\n");
        $skipped++;
        continue;
    }

    if (isset($existing[$key])) {
        $customer = $existing[$key];
        $customer_id = (int)$customer['customer_id'];

        // Keep the OpenCart account as it is; only fill in what is missing and
        // accept the WordPress password as a second way in.
        $sets = array();

        if (trim($customer['firstname']) === '' || strcasecmp(trim($customer['firstname']), 'Customer') === 0) {
            $sets['firstname'] = $firstname;
        }

        if (trim($customer['lastname']) === '' && $lastname !== '') {
            $sets['lastname'] = $lastname;
        }

        if ($sets) {
            if ($dry_run) {
                printf("[dry-run] fill %s -> %s\n", $email, json_encode($sets, JSON_UNESCAPED_UNICODE));
            } else {
                $assignments = array();
                $types = '';
                $values = array();

                foreach ($sets as $column => $value) {
                    $assignments[] = "`{$column}` = ?";
                    $types .= 's';
                    $values[] = $value;
                }

                $stmt = $db->prepare("UPDATE `{$prefix}customer` SET " . implode(', ', $assignments) . " WHERE customer_id = ?");
                $types .= 'i';
                $values[] = $customer_id;
                $stmt->bind_param($types, ...$values);
                $stmt->execute();
                $stmt->close();
            }

            $filled++;
        }

        if ($dry_run) {
            printf("[dry-run] link WordPress password for existing customer %s (#%d)\n", $email, $customer_id);
        } else {
            $stmt = $db->prepare($link_hash);
            $stmt->bind_param('is', $customer_id, $hash);
            $stmt->execute();
            $stmt->close();
        }

        $linked++;
        continue;
    }

    if ($dry_run) {
        printf("[dry-run] insert %s (%s %s) registered %s\n", $email, $firstname, $lastname, $date_added);
        $inserted++;
        continue;
    }

    // A random 40 hex password that no plaintext maps to — the customer can
    // only get in via the WordPress hash or OpenCart's password reset.
    $unusable = bin2hex(random_bytes(20));
    $salt = oc_token(9);

    $stmt = $db->prepare($insert_customer);
    $stmt->bind_param('iiissssss', $customer_group_id, $store_id, $language_id, $firstname, $lastname, $email, $unusable, $salt, $date_added);
    $stmt->execute();
    $customer_id = (int)$stmt->insert_id;
    $stmt->close();

    $stmt = $db->prepare($link_hash);
    $stmt->bind_param('is', $customer_id, $hash);
    $stmt->execute();
    $stmt->close();

    // Remember it so a duplicate e-mail later in the file is treated as existing.
    $existing[$key] = array('customer_id' => $customer_id, 'firstname' => $firstname, 'lastname' => $lastname, 'telephone' => '');

    $inserted++;
}

$db->close();

echo "\n=================================================\n";
printf("  %s\n", $dry_run ? 'DRY RUN — nothing was written' : 'Import complete');
printf("  Inserted new customers : %d\n", $inserted);
printf("  Existing customers     : %d (WordPress password linked)\n", $linked);
printf("  Names filled in        : %d\n", $filled);
printf("  Skipped (no hash)      : %d\n", $skipped);
echo "=================================================\n";
