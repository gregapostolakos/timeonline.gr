<?php
namespace Cart;

/**
 * WordPress password bridge.
 *
 * OpenCart stores passwords as SHA1(salt + SHA1(salt + SHA1(password))) and
 * WordPress uses bcrypt/phpass, so hashes cannot be converted between the two.
 * Instead the WordPress hash of every migrated customer is kept in
 * DB_PREFIX . 'customer_wp_password'. The first time such a customer signs in
 * with their old password the hash is verified here, the password is re-hashed
 * into OpenCart's native format and the WordPress hash is dropped.
 *
 * verify() mirrors wp_check_password() from wp-includes/pluggable.php and
 * covers the three formats WordPress 6.8+ can hold:
 *
 *   $wp$2y$..   bcrypt over a base64 sha384 HMAC pre-hash   (current)
 *   $P$ / $H$   portable phpass MD5                         (legacy)
 *   32 chars    bare md5                                    (ancient)
 */
class WpPassword {
	// Key WordPress feeds to hash_hmac() before bcrypt — see wp_hash_password().
	const PREHASH_KEY = 'wp-sha384';

	// WordPress refuses to hash anything longer than this.
	const MAX_PASSWORD_LENGTH = 4096;

	private static $itoa64 = './0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';

	private static $available = null;

	/**
	 * Checks a plaintext password against a WordPress hash.
	 */
	public static function verify($password, $hash) {
		if ($password === '' || $hash === '' || strlen($password) > self::MAX_PASSWORD_LENGTH) {
			return false;
		}

		if (strlen($hash) <= 32) {
			return hash_equals($hash, md5($password));
		}

		if (strpos($hash, '$wp') === 0) {
			return password_verify(base64_encode(hash_hmac('sha384', $password, self::PREHASH_KEY, true)), substr($hash, 3));
		}

		if (strpos($hash, '$P$') === 0 || strpos($hash, '$H$') === 0) {
			return hash_equals($hash, self::cryptPrivate($password, $hash));
		}

		return password_verify($password, $hash);
	}

	/**
	 * Verifies the stored WordPress hash for this e-mail and, on a match,
	 * converts the password to OpenCart's native format.
	 *
	 * Returns the customer row query on success, false otherwise.
	 */
	public static function migrate($db, $email, $password) {
		if (!self::isAvailable($db)) {
			return false;
		}

		$query = $db->query("SELECT c.customer_id, w.hash FROM " . DB_PREFIX . "customer c INNER JOIN " . DB_PREFIX . "customer_wp_password w ON (w.customer_id = c.customer_id) WHERE LOWER(c.email) = '" . $db->escape(utf8_strtolower($email)) . "' AND c.status = '1'");

		if (!$query->num_rows || !self::verify($password, $query->row['hash'])) {
			return false;
		}

		$customer_id = (int)$query->row['customer_id'];
		$salt = token(9);

		$db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $db->escape($salt) . "', password = '" . $db->escape(sha1($salt . sha1($salt . sha1($password)))) . "' WHERE customer_id = '" . $customer_id . "'");

		self::forget($db, $customer_id);

		return $db->query("SELECT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . $customer_id . "' AND status = '1'");
	}

	/**
	 * Drops the WordPress hash so an old password stops working once the
	 * customer (or an admin) sets a new one through OpenCart.
	 */
	public static function forget($db, $customer_id) {
		if (self::isAvailable($db)) {
			$db->query("DELETE FROM " . DB_PREFIX . "customer_wp_password WHERE customer_id = '" . (int)$customer_id . "'");
		}
	}

	public static function forgetByEmail($db, $email) {
		if (self::isAvailable($db)) {
			$db->query("DELETE w FROM " . DB_PREFIX . "customer_wp_password w INNER JOIN " . DB_PREFIX . "customer c ON (c.customer_id = w.customer_id) WHERE LOWER(c.email) = '" . $db->escape(utf8_strtolower($email)) . "'");
		}
	}

	/**
	 * The bridge stays inert until the migration has created its table, so the
	 * modification is safe to deploy before (or without) running the import.
	 */
	private static function isAvailable($db) {
		if (self::$available === null) {
			self::$available = (bool)$db->query("SHOW TABLES LIKE '" . DB_PREFIX . "customer_wp_password'")->num_rows;
		}

		return self::$available;
	}

	/**
	 * Port of PasswordHash::crypt_private() from wp-includes/class-phpass.php.
	 */
	private static function cryptPrivate($password, $setting) {
		if (strlen($setting) < 12) {
			return '*0';
		}

		$count_log2 = strpos(self::$itoa64, $setting[3]);

		if ($count_log2 === false || $count_log2 < 7 || $count_log2 > 30) {
			return '*0';
		}

		$salt = substr($setting, 4, 8);

		if (strlen($salt) != 8) {
			return '*0';
		}

		$count = 1 << $count_log2;
		$hash = md5($salt . $password, true);

		do {
			$hash = md5($hash . $password, true);
		} while (--$count);

		return substr($setting, 0, 12) . self::encode64($hash, 16);
	}

	/**
	 * Port of PasswordHash::encode64().
	 */
	private static function encode64($input, $count) {
		$output = '';
		$i = 0;

		do {
			$value = ord($input[$i++]);
			$output .= self::$itoa64[$value & 0x3f];

			if ($i < $count) {
				$value |= ord($input[$i]) << 8;
			}

			$output .= self::$itoa64[($value >> 6) & 0x3f];

			if ($i++ >= $count) {
				break;
			}

			if ($i < $count) {
				$value |= ord($input[$i]) << 16;
			}

			$output .= self::$itoa64[($value >> 12) & 0x3f];

			if ($i++ >= $count) {
				break;
			}

			$output .= self::$itoa64[($value >> 18) & 0x3f];
		} while ($i < $count);

		return $output;
	}
}
