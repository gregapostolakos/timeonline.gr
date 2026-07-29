# OpenCart 3.x — Complete Developer Reference

## Stack Overview

| Layer | Detail |
|-------|--------|
| Version | OpenCart 3.x |
| Architecture | MVC-L (Model-View-Controller-Language) |
| Template engine | Twig |
| DB | MariaDB 10.11 / MySQL 5.7+ |
| PHP | 7.3 / 7.4 / 8.2 / 8.3 |
| DI | Registry pattern (no container) |
| Extensions | OCMOD (XML), Events, native PHP |

---

## 1. Request Lifecycle

```
index.php
  └─ system/startup.php          load engine classes + helper functions
       └─ system/framework.php   instantiate & register core services:
            Config → Log → Event → Loader → Request → Response
            → DB → Session → Cache → URL → Language → Document
            → Customer → Cart → Currency → Tax → Weight → Length
                 └─ Router → Action → Controller::method()
                                           ↓
                                   $this->load->model()
                                   $this->load->language()
                                   $this->model_*->method()  (DB queries)
                                   $this->load->view()       (Twig render)
                                           ↓
                              Response::output()  →  browser
```

**Key constants set at boot:**

```php
DB_PREFIX        // 'oc_'
DIR_APPLICATION  // catalog/ or admin/
DIR_SYSTEM       // system/
DIR_LANGUAGE     // language directory
DIR_CONFIG       // config directory
DIR_IMAGE        // image/
DIR_LOGS         // system/storage/logs/
DIR_STORAGE      // system/storage/
DIR_MODIFICATION // system/storage/modification/
DIR_CATALOG      // catalog/
DIR_TEMPLATE     // catalog/view/theme/
```

---

## 2. MVC-L File Naming & Directory Structure

### Naming Conventions

| Layer | File path | Class name |
|-------|-----------|------------|
| Controller | `catalog/controller/product/product.php` | `ControllerProductProduct` |
| Model | `catalog/model/catalog/product.php` | `ModelCatalogProduct` |
| View | `catalog/view/theme/default/template/product/product.twig` | — |
| Language | `catalog/language/en-gb/product/product.php` | — |
| Admin Controller | `admin/controller/catalog/product.php` | `ControllerCatalogProduct` |
| Admin Model | `admin/model/catalog/product.php` | `ModelCatalogProduct` |
| Admin View | `admin/view/template/catalog/product_form.twig` | — |
| Admin Language | `admin/language/en-gb/catalog/product.php` | — |

### Full Module File Set

```
admin/
  controller/extension/module/<name>.php   → ControllerExtensionModule<Name>
  model/extension/module/<name>.php        → ModelExtensionModule<Name> (optional)
  view/template/extension/module/<name>.twig
  language/en-gb/extension/module/<name>.php

catalog/
  controller/extension/module/<name>.php   → ControllerExtensionModule<Name>
  model/extension/module/<name>.php        → ModelExtensionModule<Name> (optional)
  view/theme/default/template/extension/module/<name>.twig
  language/en-gb/extension/module/<name>.php (optional)
```

### Model Registration Key

After `$this->load->model('catalog/product')`, the model is accessible as:
```php
$this->model_catalog_product  // slashes → underscores, prefixed with model_
```

---

## 3. Registry & Dependency Injection

**File:** `system/engine/registry.php`

```php
// Set
$registry->set('db', $db);

// Get
$db = $registry->get('db');

// Magic access in Controller/Model (via __get/__set)
$this->db      // same as $registry->get('db')
$this->load    // same as $registry->get('load')
```

Both `Controller` and `Model` base classes proxy `$this->*` to the Registry.

### All Registry Objects

| Key | Class | Description |
|-----|-------|-------------|
| `config` | `Config` | Configuration settings |
| `db` | `DB` | Database adapter |
| `load` | `Loader` | MVC-L loader |
| `request` | `Request` | HTTP request data |
| `response` | `Response` | HTTP response |
| `session` | `Session` | Session storage |
| `cache` | `Cache` | Cache adapter |
| `url` | `Url` | URL generator |
| `language` | `Language` | Translations |
| `document` | `Document` | Page head manager |
| `event` | `Event` | Event system |
| `log` | `Log` | File logger |
| `customer` | `Cart\Customer` | Customer auth & profile |
| `cart` | `Cart\Cart` | Shopping cart |
| `currency` | `Cart\Currency` | Currency format & convert |
| `tax` | `Cart\Tax` | Tax calculation |
| `weight` | `Cart\Weight` | Weight unit conversion |
| `length` | `Cart\Length` | Length unit conversion |
| `user` | `Cart\User` | Admin user (admin only) |
| `encryption` | `Encryption` | Encrypt/decrypt |

---

## 4. Loader API (`$this->load`)

**File:** `system/engine/loader.php`

### `$this->load->model($route)`

```php
$this->load->model('catalog/product');
// Registers as: $this->model_catalog_product
// Fires: model/catalog/product/{method}/before|after for every method call
```

### `$this->load->view($route, $data = []): string`

```php
$data['products'] = [...];
return $this->load->view('catalog/product', $data);
// Renders: catalog/view/theme/{theme}/template/catalog/product.twig
// Fires: view/catalog/product/before|after
```

### `$this->load->language($route, $key = ''): array`

```php
$this->load->language('product/product');
$text = $this->language->get('text_home');

// Namespaced load
$this->load->language('product/product', 'product');
$text = $this->language->get('product')->get('text_home');
// Fires: language/product/product/before|after
```

### `$this->load->controller($route, $data = []): mixed`

```php
$data['header'] = $this->load->controller('common/header');
$data['footer'] = $this->load->controller('common/footer');
// Fires: controller/{route}/before|after
```

### `$this->load->library($route)`

```php
$this->load->library('payment/paypal');
// Loads: system/library/payment/paypal.php
// Registers with basename key in registry
```

### `$this->load->helper($route)`

```php
$this->load->helper('general');
// Includes: system/helper/general.php (functions available globally)
```

### `$this->load->config($route)`

```php
$this->load->config('catalog');
// Merges: system/config/catalog.php into $this->config
```

---

## 5. Controller Patterns

### Basic Structure

```php
class ControllerProductProduct extends Controller {
    public function index() {
        $this->load->language('product/product');
        $this->load->model('catalog/product');

        // Read params
        $product_id = isset($this->request->get['product_id'])
            ? (int)$this->request->get['product_id'] : 0;

        // Handle POST
        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validate()) {
                $this->model_catalog_product->addProduct($this->request->post);
                $this->session->data['success'] = $this->language->get('text_success');
                $this->response->redirect($this->url->link('catalog/product'));
                return;
            }
        }

        // Prepare data
        $data['heading_title'] = $this->language->get('heading_title');
        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')],
        ];
        $data['product'] = $this->model_catalog_product->getProduct($product_id);

        // Sub-controllers
        $data['header'] = $this->load->controller('common/header');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('product/product', $data));
    }

    protected function validate() {
        if (!$this->request->post['name']) {
            $this->error['name'] = $this->language->get('error_name');
        }
        return !$this->error;
    }
}
```

### Reading Request Data

```php
// GET
$id    = (int)($this->request->get['id'] ?? 0);
$path  = $this->request->get['path'] ?? '';

// POST
$name  = $this->request->post['name'] ?? '';
$price = (float)($this->request->post['price'] ?? 0);

// Method check
if ($this->request->server['REQUEST_METHOD'] == 'POST') { }

// HTTPS check
if ($this->request->server['HTTPS']) { }
```

### Response Patterns

```php
// HTML page
$this->response->setOutput($this->load->view('route', $data));

// JSON
$this->response->addHeader('Content-Type: application/json');
$this->response->setOutput(json_encode(['success' => true]));

// Redirect
$this->response->redirect($this->url->link('catalog/product'));

// 404
$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
$this->response->setOutput($this->load->view('error/not_found', $data));
```

### Session Flash Messages

```php
// Set
$this->session->data['success'] = 'Saved successfully!';
$this->session->data['error']   = 'Something went wrong.';

// Read & clear (in next request)
if (isset($this->session->data['success'])) {
    $data['success'] = $this->session->data['success'];
    unset($this->session->data['success']);
}
```

### URL Generation

```php
// Basic
$this->url->link('product/product', 'product_id=5')
// → http://localhost/index.php?route=product/product&product_id=5

// With array args
$this->url->link('product/product', ['product_id' => 5, 'path' => '20'])

// Secure (HTTPS)
$this->url->link('account/login', '', true)

// Admin with user_token
$this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'])
```

---

## 6. Model Patterns

### Basic Structure

```php
class ModelCatalogProduct extends Model {
    public function getProduct(int $product_id): array|false {
        $query = $this->db->query("
            SELECT p.*, pd.name, pd.description, pd.meta_title
            FROM " . DB_PREFIX . "product p
            LEFT JOIN " . DB_PREFIX . "product_description pd
                ON (p.product_id = pd.product_id AND pd.language_id = '" .
                    (int)$this->config->get('config_language_id') . "')
            WHERE p.product_id = '" . (int)$product_id . "'
            AND p.status = '1'
        ");

        return $query->num_rows ? $query->row : false;
    }

    public function getProducts(array $data = []): array {
        $sql = "SELECT * FROM " . DB_PREFIX . "product WHERE status = '1'";

        if (!empty($data['category_id'])) {
            $sql .= " AND p.product_id IN (
                SELECT product_id FROM " . DB_PREFIX . "product_to_category
                WHERE category_id = '" . (int)$data['category_id'] . "'
            )";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            $data['start'] = max(0, (int)($data['start'] ?? 0));
            $data['limit'] = max(1, (int)($data['limit'] ?? 20));
            $sql .= " LIMIT " . $data['start'] . "," . $data['limit'];
        }

        $query = $this->db->query($sql);
        return $query->rows;
    }

    public function addProduct(array $data): int {
        $this->db->query("
            INSERT INTO " . DB_PREFIX . "product SET
            model      = '" . $this->db->escape($data['model']) . "',
            price      = '" . (float)$data['price'] . "',
            status     = '" . (int)$data['status'] . "',
            date_added = NOW()
        ");

        $product_id = $this->db->getLastId();

        $this->db->query("
            INSERT INTO " . DB_PREFIX . "product_description SET
            product_id  = '" . (int)$product_id . "',
            language_id = '" . (int)$this->config->get('config_language_id') . "',
            name        = '" . $this->db->escape($data['name']) . "',
            description = '" . $this->db->escape($data['description']) . "'
        ");

        return $product_id;
    }

    public function editProduct(int $product_id, array $data): void {
        $this->db->query("
            UPDATE " . DB_PREFIX . "product SET
            price  = '" . (float)$data['price'] . "',
            status = '" . (int)$data['status'] . "',
            date_modified = NOW()
            WHERE product_id = '" . (int)$product_id . "'
        ");
    }

    public function deleteProduct(int $product_id): void {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product
            WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description
            WHERE product_id = '" . (int)$product_id . "'");
    }

    public function install(): void {
        $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "my_table` (
            `id`    int(11) NOT NULL AUTO_INCREMENT,
            `value` varchar(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8");
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "my_table`");
    }
}
```

### Escaping Rules

```php
// String — always escape
"name = '" . $this->db->escape($string) . "'"

// Integer — always cast
"id = '" . (int)$id . "'"

// Float — cast and quote
"price = '" . (float)$price . "'"

// Current timestamp
"date_added = NOW()"

// NULL
"field = NULL"
```

### Query Result Object

```php
$result = $this->db->query("SELECT ...");

$result->num_rows   // int — number of rows
$result->row        // array — first row (associative)
$result->rows       // array[] — all rows

$this->db->getLastId()       // int — last INSERT id
$this->db->countAffected()   // int — rows affected by UPDATE/DELETE
```

---

## 7. View (Twig) Patterns

### Rendering in Controller

```php
// All array keys become Twig variables
$data['title']      = 'My Page';
$data['breadcrumbs'] = [...];
$data['products']   = $this->model_catalog_product->getProducts();
$data['header']     = $this->load->controller('common/header');
$data['footer']     = $this->load->controller('common/footer');

$this->response->setOutput($this->load->view('catalog/product', $data));
```

### Twig Template Basics

```twig
{# Variables #}
<h1>{{ title }}</h1>

{# Loops #}
{% for product in products %}
  <div>{{ product.name }} — {{ product.price }}</div>
{% endfor %}

{# Conditionals #}
{% if customer_logged %}
  <p>Welcome, {{ customer_name }}!</p>
{% else %}
  <p><a href="{{ login_link }}">Login</a></p>
{% endif %}

{# Include sub-template #}
{{ header|raw }}
{{ footer|raw }}

{# Filters #}
{{ price|number_format(2) }}
{{ text|escape }}
{{ html_content|raw }}

{# Default value #}
{{ variable|default('fallback') }}
```

### Template Location Resolution

```
DIR_MODIFICATION/catalog/view/theme/{theme}/template/{route}.twig  (checked first)
DIR_APPLICATION/view/theme/{theme}/template/{route}.twig            (fallback)
DIR_APPLICATION/view/theme/default/template/{route}.twig            (default theme fallback)
```

---

## 8. Language Patterns

### Language File Format

```php
<?php
// catalog/language/en-gb/product/product.php
$_['heading_title']   = 'Products';
$_['text_home']       = 'Home';
$_['text_add_to_cart'] = 'Add to Cart';
$_['text_wishlist']   = 'Wish List (%s)';
$_['error_not_found'] = 'Product not found!';
```

### Usage in Controller

```php
// Load
$this->load->language('product/product');

// Get string
$title = $this->language->get('heading_title');

// With sprintf
$wishlist = sprintf($this->language->get('text_wishlist'), 5);
// → "Wish List (5)"

// Pass to template
$data['text_add_to_cart'] = $this->language->get('text_add_to_cart');
```

### Fallback Chain

1. `en-gb` (default language) — always loaded first
2. Current language code — overlays on top
3. Missing key → returns the key itself (never throws)

---

## 9. Core Library Classes

### DB

```php
$this->db->query($sql): object        // rows, row, num_rows
$this->db->escape($value): string     // XSS + SQL injection safe
$this->db->getLastId(): int           // last INSERT auto_increment id
$this->db->countAffected(): int       // rows affected by last query
$this->db->connected(): bool
```

### Cache

```php
$this->cache->get($key): mixed        // false if not found
$this->cache->set($key, $value)       // default expiry from config
$this->cache->delete($key)
$this->cache->delete('*')             // clear all (File driver)
```

Drivers: `File` (default), `Redis`, `Memcached`, `APC`

Cache key pattern used in OC core: `product.{product_id}`, `category.{store_id}`

### Session

```php
$this->session->data['key'] = 'value';   // write
$val = $this->session->data['key'];       // read
unset($this->session->data['key']);       // delete

$this->session->getId(): string
$this->session->start($id = ''): string
$this->session->destroy()
```

Common session keys: `customer_id`, `cart`, `wishlist`, `user_token`, `success`, `error`, `api_id`

### Request

```php
$this->request->get['key']      // $_GET (HTML-escaped)
$this->request->post['key']     // $_POST (HTML-escaped)
$this->request->cookie['key']   // $_COOKIE
$this->request->files['key']    // $_FILES
$this->request->server['key']   // $_SERVER

// Common server keys
$this->request->server['REQUEST_METHOD']   // GET|POST
$this->request->server['HTTPS']            // bool
$this->request->server['REMOTE_ADDR']      // client IP
$this->request->server['HTTP_USER_AGENT']
```

> All values are `htmlspecialchars()` cleaned — safe for HTML output, but **not** for SQL (use `$this->db->escape()` additionally).

### Response

```php
$this->response->setOutput($html)
$this->response->getOutput(): string
$this->response->addHeader('Content-Type: application/json')
$this->response->redirect($url, $status = 302)   // exits
$this->response->setCompression($level)           // 0-9 gzip
$this->response->output()                         // called by framework
```

### URL

```php
$this->url->link($route, $args = '', $secure = false): string
$this->url->addRewrite($handler)   // register SEO rewriter
```

### Language

```php
$this->language->get($key): string    // returns key if not found
$this->language->set($key, $value)
$this->language->load($file, $ns = ''): array
$this->language->all(): array
$this->language->data            // full array
```

### Document

```php
$this->document->setTitle($title)
$this->document->getTitle(): string
$this->document->setDescription($desc)
$this->document->setKeywords($kw)
$this->document->addLink($href, $rel)              // canonical, alternate, etc.
$this->document->addStyle($href, $rel = 'stylesheet', $media = 'screen', $pos = 'header')
$this->document->addScript($href, $pos = 'header') // pos: 'header'|'footer'
$this->document->getStyles($pos = 'header'): array
$this->document->getScripts($pos = 'header'): array
$this->document->getLinks(): array
```

### Config

```php
$this->config->get($key): mixed    // null if not found
$this->config->set($key, $value)
$this->config->has($key): bool
$this->config->load($filename)     // loads system/config/{filename}.php

// Common config keys
$this->config->get('config_store_id')
$this->config->get('config_language_id')
$this->config->get('config_currency')
$this->config->get('config_customer_group_id')
$this->config->get('config_name')        // store name
$this->config->get('config_email')       // store email
$this->config->get('config_url')         // HTTP URL
$this->config->get('config_ssl')         // HTTPS URL
$this->config->get('config_tax')         // bool
$this->config->get('config_compression') // gzip level
```

### Log

```php
$this->log->write($message)
// Appends to DIR_LOGS/{filename} with timestamp
// Format: "2026-04-06 10:15:30 - {message}"
// Arrays/objects use print_r
```

### Mail

```php
$mail = new Mail('mail');        // adaptor: 'mail'|'smtp'
$mail->setTo('to@example.com'); // string or array
$mail->setFrom('from@shop.com');
$mail->setSender('Shop Name');
$mail->setReplyTo('reply@shop.com');
$mail->setSubject('Subject');
$mail->setText('Plain text body');
$mail->setHtml('<p>HTML body</p>');
$mail->addAttachment(DIR_DOWNLOAD . 'file.pdf');
$mail->send();                   // throws Exception if validation fails
```

Requires: `to`, `from`, `sender`, `subject`, and at least `text` or `html`.

### Image

```php
$image = new Image('image/catalog/product.jpg');

$image->getWidth(): int
$image->getHeight(): int
$image->getMime(): string        // 'image/jpeg', 'image/png', etc.

// Resize (proportional — fills canvas, centers, preserves alpha)
$image->resize(300, 300);
$image->resize(300, 0);          // width only, proportional height
$image->save('image/cache/thumb.jpg', 90);  // quality 0-100

// Crop
$image->crop($top_x, $top_y, $bottom_x, $bottom_y);

// Rotate
$image->rotate(90, 'FFFFFF');   // degrees, bg color

// Watermark
$image->watermark($watermark_image, 'bottomright');
// positions: topleft|topcenter|topright|middleleft|middlecenter|middleright|bottomleft|bottomcenter|bottomright
```

### Customer (`$this->customer`)

```php
$this->customer->isLogged(): bool
$this->customer->login($email, $password, $override = false): bool
$this->customer->logout(): void

$this->customer->getId(): int
$this->customer->getFirstName(): string
$this->customer->getLastName(): string
$this->customer->getEmail(): string
$this->customer->getTelephone(): string
$this->customer->getGroupId(): int
$this->customer->getAddressId(): int
$this->customer->getNewsletter(): bool
$this->customer->getBalance(): float        // account credit
$this->customer->getRewardPoints(): int
```

### Cart (`$this->cart`)

```php
$this->cart->add($product_id, $qty = 1, $option = [], $recurring_id = 0)
$this->cart->update($cart_id, $quantity)
$this->cart->remove($cart_id)
$this->cart->clear()

$this->cart->getProducts(): array    // full cart items with prices/tax/weight
$this->cart->getTotal(): float       // total with tax
$this->cart->getSubTotal(): float    // pre-tax
$this->cart->getTaxes(): array       // tax breakdown
$this->cart->getWeight(): float
$this->cart->countProducts(): int    // sum of quantities
$this->cart->hasProducts(): bool
$this->cart->hasStock(): bool
$this->cart->hasShipping(): bool
$this->cart->hasDownload(): bool
$this->cart->hasRecurringProducts(): bool
```

**Product array keys:** `cart_id`, `product_id`, `name`, `model`, `image`, `option`, `quantity`, `minimum`, `stock`, `price`, `total`, `tax_class_id`, `weight`, `weight_class_id`, `subtract`, `shipping`, `download`, `reward`, `points`, `recurring`

### Currency (`$this->currency`)

```php
$this->currency->format($number, $currency, $value = '', $format = true): string
// e.g.: $this->currency->format(19.99, 'USD') → '$19.99'

$this->currency->convert($value, $from, $to): float
$this->currency->getValue($currency): float    // exchange rate
$this->currency->getDecimalPlace($currency): int
$this->currency->getSymbolLeft($currency): string
$this->currency->getSymbolRight($currency): string
$this->currency->getId($currency): int
$this->currency->has($currency): bool
```

### Tax (`$this->tax`)

```php
$this->tax->setShippingAddress($country_id, $zone_id)
$this->tax->setPaymentAddress($country_id, $zone_id)
$this->tax->setStoreAddress($country_id, $zone_id)

$this->tax->calculate($value, $tax_class_id, $calculate = true): float
// $calculate: true=all, 'P'=percent only, 'F'=fixed only
$this->tax->getTax($value, $tax_class_id): float     // tax amount only
$this->tax->getRates($value, $tax_class_id): array   // breakdown: tax_rate_id, name, rate, type, amount
$this->tax->unsetRates()
```

### Weight & Length

```php
$this->weight->convert($value, $from_class_id, $to_class_id): float
$this->weight->getUnit($weight_class_id): string   // 'kg', 'lb', etc.

$this->length->convert($value, $from_class_id, $to_class_id): float
$this->length->getUnit($length_class_id): string   // 'cm', 'inch', etc.
```

---

## 10. Event System

### Architecture

**File:** `system/engine/event.php`

```php
// Register handler
$event->register($trigger, new Action($route), $priority = 0);

// Trigger (called by framework automatically)
$result = $event->trigger('controller/product/product/before', [&$route, &$data]);

// Unregister
$event->unregister($trigger, $route);
$event->clear($trigger);
```

- **Priority:** lower = earlier execution
- **Wildcard:** `*` matches any segment, `?` matches single char
- **Propagation stops** on first non-null return value from a handler

### Native Trigger Points

| Trigger | When | Args |
|---------|------|------|
| `controller/{route}/before` | Before any controller method | `&$route, &$data` |
| `controller/{route}/after` | After any controller method | `&$route, &$data, &$output` |
| `model/{route}/{method}/before` | Before any model method | `&$route, &$args` |
| `model/{route}/{method}/after` | After any model method | `&$route, &$output` |
| `view/{route}/before` | Before Twig render | varies |
| `view/{route}/after` | After Twig render | varies |
| `language/{route}/before` | Before language load | — |
| `language/{route}/after` | After language load | — |
| `config/{route}/before` | Before config load | — |
| `config/{route}/after` | After config load | — |

### Event Handler Structure

```php
class ControllerEventMyHandler extends Controller {
    // Before controller — can modify $route or $data, or return early
    public function beforeController(&$route, &$data) {
        // Modify $data here
        return null; // null = continue; non-null = stop propagation & return
    }

    // After controller — can modify $output (the HTML/JSON response)
    public function afterController(&$route, &$data, &$output) {
        $output = str_replace('old', 'new', $output);
    }

    // After model method — can modify return value
    public function afterGetProduct(&$route, &$output) {
        if ($output) {
            $output['custom_field'] = 'value';
        }
    }
}
```

### Registering Events in DB

Events stored in `oc_event` table. Register via model:

```php
$this->load->model('setting/event');
$this->model_setting_event->addEvent(
    'my_module',                             // code
    'catalog/controller/product/product/before',  // trigger
    'extension/module/my_module/event',      // action route
    1,                                        // status
    0                                         // sort_order / priority
);
```

Delete on uninstall:
```php
$this->model_setting_event->deleteEventByCode('my_module');
```

### Config-level Events (System)

Pre-registered in `system/config/catalog.php`:
```php
$_['action_event'] = [
    'controller/*/before' => ['event/language/before'],
    'controller/*/after'  => ['event/language/after'],
    'view/*/before'       => [500 => 'event/theme', 998 => 'event/language'],
    'language/*/after'    => ['event/translation'],
];
```

---

## 11. OCMOD System

### XML Structure

```xml
<?xml version="1.0" encoding="UTF-8"?>
<modification>
    <name>Display Name</name>
    <code>unique_code</code>
    <version>1.0.0</version>
    <author>Author</author>
    <link>https://example.com</link>

    <file path="catalog/controller/product/product.php">
        <operation error="log|abort|skip">
            <ignoreif regex="false">already_patched_string</ignoreif>
            <search regex="false" trim="true" index="0" limit="-1">
                <![CDATA[// code to find]]>
            </search>
            <add position="before|after|replace" offset="0" trim="false">
                <![CDATA[// code to inject]]>
            </add>
        </operation>
    </file>
</modification>
```

### Attributes Reference

| Attribute | Values | Description |
|-----------|--------|-------------|
| `file path` | glob pattern, pipe `\|` for multiple | Files to patch |
| `operation error` | `log` (default), `abort`, `skip` | On search failure |
| `search regex` | `false` (default), `true` | Treat search as regex |
| `search trim` | `true` (default), `false` | Trim whitespace |
| `search index` | `0,1,2` | Match Nth occurrence (0-based) |
| `search limit` | `-1` (default = unlimited) | Max replacements (regex only) |
| `add position` | `replace` (default), `before`, `after` | Where to add code |
| `add offset` | int | Line offset for before/after |
| `ignoreif regex` | `false` (default), `true` | Skip if pattern found |

### Multiple Files

```xml
<file path="catalog/controller/product/product.php|catalog/controller/product/category.php">
```

Wildcard:
```xml
<file path="catalog/controller/extension/module/*.php">
```

### How OCMOD Works

1. Admin → Extensions → Modifications → **Refresh** button
2. All OCMODs processed in order, files written to `system/storage/modification/`
3. `modification()` function (in `startup.php`) intercepts every `require`/`include` and checks this folder first
4. Twig templates checked: `DIR_MODIFICATION/catalog/view/theme/...` before original

### When to Refresh

- After installing/enabling/disabling any OCMOD
- Changes don't take effect until refresh

---

## 12. Extension Types

| Type | Directory | Key method |
|------|-----------|------------|
| `module` | `extension/module/` | `index($setting)` |
| `payment` | `extension/payment/` | `index()`, `confirm()`, `callback()` |
| `shipping` | `extension/shipping/` | `getQuote($address)` (model) |
| `total` | `extension/total/` | `getTotal(&$totals, &$taxes, &$total)` (model) |
| `feed` | `extension/feed/` | Feed generation |
| `fraud` | `extension/fraud/` | Fraud detection |
| `captcha` | `extension/captcha/` | Captcha render |
| `report` | `extension/report/` | Report data |
| `theme` | `extension/theme/` | Theme assets |
| `analytics` | `extension/analytics/` | Analytics snippets |
| `currency` | `extension/currency/` | Currency rate update |
| `dashboard` | `extension/dashboard/` | Admin dashboard widget |
| `language` | `extension/language/` | Language pack |

### Registration in DB

```php
// oc_extension table
$this->load->model('setting/extension');
$this->model_setting_extension->install('module', 'my_module');   // install
$this->model_setting_extension->uninstall('module', 'my_module'); // uninstall
```

### Settings Storage

Settings saved in `oc_setting`:
```php
// Key format: {type}_{code}_{option}
// e.g.: module_my_module_status, payment_paypal_mode

$this->load->model('setting/setting');
$this->model_setting_setting->editSetting('module_my_module', $this->request->post);
$settings = $this->model_setting_setting->getSetting('module_my_module');
```

### Payment Extension Methods

```php
class ControllerExtensionPaymentMyGateway extends Controller {
    public function index()    { /* HTML snippet in checkout */ }
    public function confirm()  { /* Process payment, return JSON */ }
    public function callback() { /* Webhook handler */ }
}
```

### Shipping Model

```php
class ModelExtensionShippingMyShipping extends Model {
    public function getQuote($address): array {
        return [
            'code'  => 'my_shipping',
            'title' => 'My Shipping',
            'quote' => [
                'standard' => [
                    'code'         => 'my_shipping.standard',
                    'title'        => 'Standard Delivery',
                    'cost'         => 5.00,
                    'tax_class_id' => 0,
                    'text'         => $this->currency->format(5.00, $this->session->data['currency']),
                ],
            ],
            'sort_order' => $this->config->get('shipping_my_shipping_sort_order'),
            'error'      => false,
        ];
    }
}
```

### Total Extension Model

```php
class ModelExtensionTotalCoupon extends Model {
    public function getTotal(&$totals, &$taxes, &$total) {
        $discount = -5.00;
        $totals[] = [
            'code'       => 'coupon',
            'title'      => 'Coupon',
            'value'      => $discount,
            'sort_order' => $this->config->get('total_coupon_sort_order'),
        ];
        $total += $discount;
    }
}
```

---

## 13. Admin Permissions

### Permission Structure (stored in `oc_user_group.permission` as JSON)

```json
{
    "access": ["common/dashboard", "catalog/product", "sale/order"],
    "modify": ["catalog/product", "sale/order"]
}
```

**`access`** — can view/read  
**`modify`** — can edit/create/delete (requires access too)

### Checking in Controllers

```php
// Check modify permission
if (!$this->user->hasPermission('modify', 'extension/module/my_module')) {
    $this->error['warning'] = $this->language->get('error_permission');
}

// Check access
if (!$this->user->hasPermission('access', 'catalog/product')) {
    $this->response->redirect($this->url->link('error/permission'));
}
```

### Managing Permissions

```php
$this->load->model('user/user_group');
$this->model_user_user_group->addPermission($group_id, 'access', 'extension/module/my_module');
$this->model_user_user_group->addPermission($group_id, 'modify', 'extension/module/my_module');
$this->model_user_user_group->removePermissions('extension/module/my_module'); // all groups
```

### Admin User Object (`$this->user`)

```php
$this->user->isLogged(): bool
$this->user->getId(): int
$this->user->getUserName(): string
$this->user->getGroupId(): int
$this->user->hasPermission($type, $route): bool
```

`$this->user` only available in admin (not catalog).

---

## 14. Core Database Tables

### Catalog

| Table | Purpose |
|-------|---------|
| `oc_product` | Core product data (model, price, weight, stock, status, dates) |
| `oc_product_description` | Multi-language name, description, meta (PK: product_id + language_id) |
| `oc_product_image` | Additional product images (sort_order) |
| `oc_product_to_category` | Product↔category mapping |
| `oc_product_to_store` | Product availability per store |
| `oc_product_to_layout` | Product layout override per store |
| `oc_product_to_download` | Product↔download file mapping |
| `oc_product_option` | Product option assignments (required, default value) |
| `oc_product_option_value` | Option value variants (price/weight modifier, subtract stock) |
| `oc_product_attribute` | Product attribute values (text, per language) |
| `oc_product_filter` | Product↔filter mapping |
| `oc_product_discount` | Quantity discounts per customer group |
| `oc_product_special` | Sale prices per customer group (date range, priority) |
| `oc_product_reward` | Reward points per product per customer group |
| `oc_product_recurring` | Subscription products per customer group |
| `oc_product_related` | Related products |
| `oc_category` | Category (parent_id, top, column, sort_order, status) |
| `oc_category_description` | Multi-language name, description, meta |
| `oc_category_path` | Hierarchy paths (for breadcrumbs, nested queries) |
| `oc_category_filter` | Category↔filter mapping |
| `oc_category_to_store` | Category availability per store |
| `oc_category_to_layout` | Category layout override per store |
| `oc_manufacturer` | Brands (name, image, sort_order) |
| `oc_manufacturer_to_store` | Brand availability per store |
| `oc_attribute` | Attribute (group, sort_order) |
| `oc_attribute_description` | Multi-language attribute name |
| `oc_attribute_group` | Attribute group |
| `oc_attribute_group_description` | Multi-language group name |
| `oc_option` | Option type (select, radio, checkbox, text, textarea, file, date) |
| `oc_option_description` | Multi-language option name |
| `oc_option_value` | Option values (image, sort_order) |
| `oc_option_value_description` | Multi-language option value name |
| `oc_filter` | Filters (group, sort_order) |
| `oc_filter_description` | Multi-language filter name |
| `oc_filter_group` | Filter groups |
| `oc_filter_group_description` | Multi-language filter group name |
| `oc_review` | Customer reviews (product_id, rating, status, date) |
| `oc_recurring` | Subscription profiles (price, frequency, cycle, duration, trial) |
| `oc_recurring_description` | Multi-language recurring name |
| `oc_download` | Downloadable files (filename, mask) |
| `oc_download_description` | Multi-language download name |

### Customer

| Table | Purpose |
|-------|---------|
| `oc_customer` | Accounts (email, password, salt, group, newsletter, ip, status, code) |
| `oc_customer_group` | Groups (approval, sort_order) |
| `oc_customer_group_description` | Multi-language group name |
| `oc_address` | Shipping/billing addresses (firstname, lastname, city, country_id, zone_id) |
| `oc_customer_wishlist` | Wishlist (customer_id + product_id, date_added) |
| `oc_customer_transaction` | Account credit/debit transactions |
| `oc_customer_reward` | Reward points log (order_id, points, date) |
| `oc_customer_history` | Admin notes on customer |
| `oc_customer_activity` | Activity log (key, data, ip) |
| `oc_customer_login` | Failed login attempts (email, ip, total) |
| `oc_customer_ip` | IP addresses used by customer |
| `oc_customer_online` | Currently online visitors |
| `oc_customer_approval` | Pending customer approvals |
| `oc_customer_affiliate` | Affiliate program participation |
| `oc_customer_search` | Search history |
| `oc_custom_field` | Custom registration/checkout fields |
| `oc_custom_field_description` | Multi-language field labels |
| `oc_custom_field_value` | Predefined values (select, radio) |
| `oc_custom_field_value_description` | Multi-language value labels |
| `oc_custom_field_customer_group` | Field requirements per customer group |

### Orders

| Table | Purpose |
|-------|---------|
| `oc_order` | Full order (customer, billing, shipping, payment, totals, currency, ip, status) |
| `oc_order_product` | Line items (name, model, quantity, price, total, tax) |
| `oc_order_option` | Selected options per line item |
| `oc_order_total` | Totals breakdown (code, title, value, sort_order) |
| `oc_order_history` | Status change log (status, notify, comment, date) |
| `oc_order_status` | Status names (multi-language) |
| `oc_order_recurring` | Subscription orders |
| `oc_order_recurring_transaction` | Subscription payment transactions |
| `oc_order_shipment` | Shipment tracking (courier, tracking_number) |
| `oc_order_voucher` | Vouchers issued in orders |
| `oc_return` | Return requests (order, product, reason, action, status) |
| `oc_return_reason` | Return reasons (multi-language) |
| `oc_return_action` | Return actions (refund, credit, replacement — multi-language) |
| `oc_return_status` | Return statuses (multi-language) |
| `oc_return_history` | Return status change log |
| `oc_shipping_courier` | Shipping carriers (code, name) |

**`oc_order.order_status_id`** common values: 1=Pending, 2=Processing, 3=Shipped, 5=Complete, 7=Canceled, 8=Denied, 9=Canceled Reversal, 10=Failed, 11=Refunded, 12=Reversed, 13=Chargeback, 14=Expired, 15=Processed, 16=Voided

### CMS

| Table | Purpose |
|-------|---------|
| `oc_information` | Pages (bottom, sort_order, status) |
| `oc_information_description` | Multi-language title, description, meta |
| `oc_information_to_store` | Page availability per store |
| `oc_information_to_layout` | Page layout override |
| `oc_banner` | Banner rotators (name, status) |
| `oc_banner_image` | Banner images (title, link, image, sort_order, language) |
| `oc_layout` | Layout templates (name) |
| `oc_layout_module` | Module placements (layout_id, code, position, sort_order) |
| `oc_layout_route` | Layout assignments to routes/pages per store |
| `oc_module` | Module instances (name, code, setting JSON) |

### Settings & Localization

| Table | Purpose |
|-------|---------|
| `oc_setting` | Key-value config (store_id, code, key, value, serialized) |
| `oc_store` | Multi-store (name, url, ssl) |
| `oc_language` | Languages (code, locale, directory, status) |
| `oc_currency` | Currencies (code, symbol_left, symbol_right, decimal_place, value, status) |
| `oc_country` | Countries (name, iso_code_2, iso_code_3, postcode_required) |
| `oc_zone` | States/regions (country_id, name, code) |
| `oc_geo_zone` | Geographic zones for tax/shipping rules |
| `oc_zone_to_geo_zone` | Zone membership in geo_zones |
| `oc_tax_class` | Tax classifications (title, description) |
| `oc_tax_rate` | Tax rates (geo_zone, name, rate, type P/F) |
| `oc_tax_rate_to_customer_group` | Tax rate applicability per customer group |
| `oc_tax_rule` | Links tax_class to tax_rate (based: shipping/billing, priority) |
| `oc_weight_class` | Weight units (kg, g, lb, oz — conversion values) |
| `oc_weight_class_description` | Multi-language unit labels |
| `oc_length_class` | Length units (cm, mm, inch — conversion values) |
| `oc_length_class_description` | Multi-language unit labels |
| `oc_stock_status` | Stock labels (in stock, pre-order, out of stock) |
| `oc_location` | Store locations (address, geocode, hours) |

### SEO & Users

| Table | Purpose |
|-------|---------|
| `oc_seo_url` | SEO URL mappings (query → keyword, per store + language) |
| `oc_user` | Admin users (username, password, salt, group, email, status) |
| `oc_user_group` | Admin groups + permission JSON (access[], modify[]) |
| `oc_api` | API credentials (username, key, status) |
| `oc_api_ip` | Whitelisted IPs per API key |
| `oc_api_session` | API session tokens |

### Extensions & Events

| Table | Purpose |
|-------|---------|
| `oc_event` | Event handlers (code, trigger, action, sort_order, status) |
| `oc_extension` | Installed extensions (type, code) |
| `oc_extension_install` | Installation history (filename) |
| `oc_extension_path` | Files created by extension |
| `oc_modification` | OCMOD XML (code, xml, status, date_added) |

### Marketing & System

| Table | Purpose |
|-------|---------|
| `oc_coupon` | Coupons (code, type P/F, discount, shipping, total, uses, date range) |
| `oc_coupon_product` | Product restrictions per coupon |
| `oc_coupon_category` | Category restrictions per coupon |
| `oc_coupon_history` | Coupon usage log |
| `oc_voucher` | Gift vouchers (code, amount, from/to, theme, status) |
| `oc_voucher_history` | Voucher redemption log |
| `oc_voucher_theme` | Voucher visual themes |
| `oc_marketing` | Marketing campaigns (name, code, clicks) |
| `oc_session` | Session storage (session_id, data, expire) |
| `oc_cart` | Cart items (customer_id or session_id, product_id, option, quantity) |
| `oc_theme` | Theme customizations per page/store |
| `oc_translation` | Translation overrides (store, language, route, key, value) |
| `oc_upload` | Uploaded files (name, filename, code) |
| `oc_statistics` | Counters (code, value) |

---

## 15. Common Config Keys

```php
// Store
config_name                  // Store name
config_owner                 // Owner name
config_address               // Address
config_email                 // Contact email
config_telephone             // Telephone
config_url                   // HTTP base URL
config_ssl                   // HTTPS base URL
config_store_id              // Current store ID

// Locale
config_language              // Language code ('en-gb')
config_language_id           // Language ID (int)
config_currency              // Currency code ('USD')
config_currency_auto         // Auto-detect currency
config_country_id            // Default country
config_zone_id               // Default zone

// Customer
config_customer_group_id     // Default customer group
config_customer_online       // Track online visitors
config_customer_approval     // Require approval
config_customer_email_activation // Require email activation
config_guest_checkout        // Allow guest checkout
config_checkout_address      // Pre-select address

// Tax
config_tax                   // Display with tax
config_tax_default           // Default tax class
config_tax_customer          // Tax based on: shipping|payment|store

// Products
config_product_limit         // Default products per page
config_product_description_length  // Truncate description
config_review_status         // Enable reviews
config_review_guest          // Allow guest reviews

// Upload / Images
config_image_thumb_width / _height
config_image_popup_width / _height
config_image_product_width / _height
config_image_category_width / _height
config_image_manufacturer_width / _height
config_upload_max_size       // Max file upload (bytes)
config_allowed_upload

// System
config_maintenance           // Maintenance mode
config_compression           // Gzip level (0=off)
config_error_display         // Show errors in browser
config_error_log             // Log errors to file
config_error_filename        // Log file name
config_pagination_limit      // Admin results per page
```

---

## 16. Core Catalog Model Methods

### ModelCatalogProduct

```php
// Single product (full data with price, special, discount, rating, etc.)
getProduct(int $product_id): array|false

// List with filters
getProducts(array $data = []): array
// $data keys: filter_category_id, filter_sub_category, filter_filter (comma-separated IDs),
//             filter_name, filter_tag, filter_description (bool), filter_manufacturer_id,
//             sort ('pd.name'|'p.model'|'p.quantity'|'p.price'|'rating'|'p.sort_order'|'p.date_added'),
//             order ('ASC'|'DESC'), start (int), limit (int, default 20)

getTotalProducts(array $data = []): int    // same filter keys as getProducts()

getProductSpecials(array $data = []): array
// $data keys: sort, order, start, limit
getTotalProductSpecials(): int

getLatestProducts(int $limit): array       // cached: product.latest.{lang}.{store}.{group}.{limit}
getPopularProducts(int $limit): array      // cached: product.popular.{lang}.{store}.{group}.{limit}
getBestSellerProducts(int $limit): array   // cached: product.bestseller.{lang}.{store}.{group}.{limit}

getProductAttributes(int $product_id): array
// Returns: [{attribute_group_id, name, attribute: [{attribute_id, name, text}]}]

getProductOptions(int $product_id): array
// Returns: [{product_option_id, option_id, name, type, value, required,
//            product_option_value: [{product_option_value_id, option_value_id, name, image,
//                                    quantity, subtract, price, price_prefix, weight, weight_prefix}]}]

getProductDiscounts(int $product_id): array   // filtered by customer_group_id, date range
getProductImages(int $product_id): array      // sort_order ASC
getProductRelated(int $product_id): array     // full product data for each related
getCategories(int $product_id): array         // rows from product_to_category
getProductLayoutId(int $product_id): int

getProfile(int $product_id, int $recurring_id): array|false
getProfiles(int $product_id): array

checkProductCategory(int $product_id, array $category_ids): array|false

updateViewed(int $product_id): void
```

**Constant filter patterns applied automatically:**
- `language_id = config_language_id`
- `store_id = config_store_id`
- `customer_group_id = config_customer_group_id` (for pricing/discounts)
- `status = '1'`
- `date_available <= NOW()`
- Date ranges for discounts/specials validated: `start <= NOW() AND end >= NOW()`

**Cache keys:**
```
product.latest.{language_id}.{store_id}.{customer_group_id}.{limit}
product.popular.{language_id}.{store_id}.{customer_group_id}.{limit}
product.bestseller.{language_id}.{store_id}.{customer_group_id}.{limit}
```

---

### ModelCatalogCategory

```php
getCategory(int $category_id): array|false
// Joins: category_description, category_to_store
// Filters: language_id, store_id, status=1

getCategories(int $parent_id = 0): array
// Orders by: sort_order, LCASE(name)

getCategoryFilters(int $category_id): array
// Returns: [{filter_group_id, name, filter: [{filter_id, name}]}]

getCategoryLayoutId(int $category_id): int

getTotalCategoriesByCategoryId(int $parent_id = 0): int
```

---

### ModelCatalogManufacturer

```php
getManufacturer(int $manufacturer_id): array|false

getManufacturers(array $data = []): array
// If $data empty → cached as manufacturer.{store_id}
// $data keys: sort ('name'|'sort_order'), order, start, limit
```

---

### ModelCatalogReview

```php
addReview(int $product_id, array $data): void
// $data keys: name, text, rating — also sends email alert if configured

getReviewsByProductId(int $product_id, int $start = 0, int $limit = 20): array
getTotalReviewsByProductId(int $product_id): int
```

---

### ModelCatalogInformation

```php
getInformation(int $information_id): array|false
getInformations(): array            // ordered by sort_order, LCASE(title)
getInformationLayoutId(int $information_id): int
```

> **Note:** No separate `ModelCatalogAttribute`, `ModelCatalogOption`, `ModelCatalogFilter`, or `ModelCatalogSearch` — these are integrated into the product/category models.

---

## 17. Pagination

**File:** `system/library/pagination.php`

### Properties

| Property | Default | Description |
|----------|---------|-------------|
| `$total` | `0` | Total items |
| `$page` | `1` | Current page (1-indexed) |
| `$limit` | `20` | Items per page |
| `$num_links` | `8` | Page number links around current page |
| `$url` | `''` | URL with `{page}` placeholder |
| `$text_first` | `'\|<'` | First page button text |
| `$text_last` | `'>|'` | Last page button text |
| `$text_next` | `'>'` | Next page button text |
| `$text_prev` | `'<'` | Previous page button text |

### render(): string

Returns empty string if only one page. Otherwise returns Bootstrap `<ul class="pagination">`.

- Current page: `<li class="active"><span>N</span></li>` (no link)
- Other pages: `<li><a href="...">N</a></li>`
- Page 1 link strips `{page}` from URL: removes `&page={page}`, `?page={page}`, `&amp;page={page}`

### Usage Pattern

```php
// Controller
$page  = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;
$limit = $this->config->get('config_limit_admin'); // or hardcoded e.g. 20

$total   = $this->model_catalog_product->getTotalProducts($filter_data);
$results = $this->model_catalog_product->getProducts(array_merge($filter_data, [
    'start' => ($page - 1) * $limit,
    'limit' => $limit,
]));

$pagination        = new Pagination();
$pagination->total = $total;
$pagination->page  = $page;
$pagination->limit = $limit;
$pagination->url   = $this->url->link('catalog/product',
    'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

$data['pagination'] = $pagination->render();

// Results text: "Showing X to Y of Z (N Pages)"
$data['results'] = sprintf(
    $this->language->get('text_pagination'),
    $total ? ($page - 1) * $limit + 1 : 0,
    min($page * $limit, $total),
    $total,
    ceil($total / $limit)
);
```

```twig
{# Twig template #}
<div class="row">
  <div class="col-sm-6 text-left">{{ pagination }}</div>
  <div class="col-sm-6 text-right">{{ results }}</div>
</div>
```

---

## 18. Admin CRUD Pattern

### user_token

Every admin URL and form must include `user_token`:

```php
// In URLs
$this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)

// In template data
$data['user_token'] = $this->session->data['user_token'];

// In Twig (for JS)
location = 'index.php?route=catalog/product&user_token={{ user_token }}' + params;
```

### List Action (index + getList)

```php
public function index() {
    $this->load->language('catalog/product');
    $this->document->setTitle($this->language->get('heading_title'));
    $this->load->model('catalog/product');
    $this->getList();
}

protected function getList() {
    // 1. Extract GET params with defaults
    $sort  = $this->request->get['sort']  ?? 'pd.name';
    $order = $this->request->get['order'] ?? 'ASC';
    $page  = (int)($this->request->get['page'] ?? 1);

    // 2. Build persistent URL string (all filters + sort + page)
    $url = '';
    if (isset($this->request->get['filter_name'])) {
        $url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
    }
    // ... repeat for every filter param

    // 3. Breadcrumbs
    $data['breadcrumbs'] = [
        ['text' => $this->language->get('text_home'),
         'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)],
        ['text' => $this->language->get('heading_title'),
         'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)],
    ];

    // 4. Action URLs
    $data['add']    = $this->url->link('catalog/product/add',    'user_token=' . $this->session->data['user_token'] . $url, true);
    $data['delete'] = $this->url->link('catalog/product/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

    // 5. DB query
    $limit       = $this->config->get('config_limit_admin');
    $filter_data = ['filter_name' => $filter_name, 'sort' => $sort, 'order' => $order,
                    'start' => ($page - 1) * $limit, 'limit' => $limit];
    $total   = $this->model_catalog_product->getTotalProducts($filter_data);
    $results = $this->model_catalog_product->getProducts($filter_data);

    // 6. Format rows
    $data['products'] = [];
    foreach ($results as $result) {
        $data['products'][] = [
            'product_id' => $result['product_id'],
            'name'       => $result['name'],
            'edit'       => $this->url->link('catalog/product/edit',
                               'user_token=' . $this->session->data['user_token'] . '&product_id=' . $result['product_id'] . $url, true),
        ];
    }

    // 7. Messages
    $data['error_warning'] = $this->error['warning'] ?? '';
    if (isset($this->session->data['success'])) {
        $data['success'] = $this->session->data['success'];
        unset($this->session->data['success']);
    } else {
        $data['success'] = '';
    }

    // 8. Selected checkboxes
    $data['selected'] = (array)($this->request->post['selected'] ?? []);

    // 9. Sortable column links (toggle ASC/DESC)
    $sort_url = '&order=' . ($order === 'ASC' ? 'DESC' : 'ASC');
    $data['sort_name']  = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.name'  . $sort_url, true);
    $data['sort_model'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&sort=p.model' . $sort_url, true);
    $data['sort']  = $sort;
    $data['order'] = $order;

    // 10. Pagination
    $pagination        = new Pagination();
    $pagination->total = $total;
    $pagination->page  = $page;
    $pagination->limit = $limit;
    $pagination->url   = $this->url->link('catalog/product',
        'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);
    $data['pagination'] = $pagination->render();
    $data['results']    = sprintf($this->language->get('text_pagination'),
        $total ? ($page-1)*$limit+1 : 0, min($page*$limit, $total), $total, ceil($total/$limit));

    // 11. Layout
    $data['header']      = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer']      = $this->load->controller('common/footer');
    $data['user_token']  = $this->session->data['user_token'];

    $this->response->setOutput($this->load->view('catalog/product_list', $data));
}
```

### Form Action (add + edit + getForm)

```php
public function add() {
    $this->load->language('catalog/product');
    $this->load->model('catalog/product');

    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
        $this->model_catalog_product->addProduct($this->request->post);
        $this->session->data['success'] = $this->language->get('text_success');
        $this->response->redirect($this->url->link('catalog/product',
            'user_token=' . $this->session->data['user_token'] . $url, true));
    }
    $this->getForm();
}

public function edit() {
    $this->load->language('catalog/product');
    $this->load->model('catalog/product');

    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
        $this->model_catalog_product->editProduct($this->request->get['product_id'], $this->request->post);
        $this->session->data['success'] = $this->language->get('text_success');
        $this->response->redirect($this->url->link('catalog/product',
            'user_token=' . $this->session->data['user_token'] . $url, true));
    }
    $this->getForm();
}

protected function getForm() {
    // Action URL
    if (!isset($this->request->get['product_id'])) {
        $data['action'] = $this->url->link('catalog/product/add',  'user_token=' . $this->session->data['user_token'] . $url, true);
    } else {
        $data['action'] = $this->url->link('catalog/product/edit', 'user_token=' . $this->session->data['user_token'] . '&product_id=' . $this->request->get['product_id'] . $url, true);
    }
    $data['cancel'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true);

    // Errors
    $data['error_warning'] = $this->error['warning'] ?? '';
    $data['error_name']    = $this->error['name']    ?? [];   // keyed by language_id
    $data['error_model']   = $this->error['model']   ?? '';

    // Load existing (edit) or empty (add) data
    $product_info = (isset($this->request->get['product_id']) && $this->request->server['REQUEST_METHOD'] != 'POST')
        ? $this->model_catalog_product->getProduct($this->request->get['product_id']) : [];

    // Field population: POST data > DB data > default
    $data['model'] = $this->request->post['model'] ?? $product_info['model'] ?? '';

    // Multi-language fields
    $data['product_description'] = $this->request->post['product_description']
        ?? ($this->request->get['product_id']
            ? $this->model_catalog_product->getProductDescriptions($this->request->get['product_id'])
            : []);

    // Language list for tabs
    $this->load->model('localisation/language');
    $data['languages'] = $this->model_localisation_language->getLanguages();

    $data['header']      = $this->load->controller('common/header');
    $data['column_left'] = $this->load->controller('common/column_left');
    $data['footer']      = $this->load->controller('common/footer');
    $data['user_token']  = $this->session->data['user_token'];

    $this->response->setOutput($this->load->view('catalog/product_form', $data));
}
```

### Delete (bulk via checkboxes)

```php
public function delete() {
    $this->load->model('catalog/product');

    if (isset($this->request->post['selected']) && $this->validateDelete()) {
        foreach ($this->request->post['selected'] as $product_id) {
            $this->model_catalog_product->deleteProduct($product_id);
        }
        $this->session->data['success'] = $this->language->get('text_success');
        $this->response->redirect($this->url->link('catalog/product',
            'user_token=' . $this->session->data['user_token'] . $url, true));
    }
    $this->getList();
}
```

### Validate Methods

```php
protected function validateForm(): bool {
    if (!$this->user->hasPermission('modify', 'catalog/product')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }

    // Multi-language field
    foreach ($this->request->post['product_description'] as $language_id => $value) {
        if (utf8_strlen($value['name']) < 1 || utf8_strlen($value['name']) > 255) {
            $this->error['name'][$language_id] = $this->language->get('error_name');
        }
    }

    // Single field
    if (utf8_strlen($this->request->post['model']) < 1 || utf8_strlen($this->request->post['model']) > 64) {
        $this->error['model'] = $this->language->get('error_model');
    }

    // Generic banner if other errors
    if ($this->error && !isset($this->error['warning'])) {
        $this->error['warning'] = $this->language->get('error_warning');
    }

    return !$this->error;
}

protected function validateDelete(): bool {
    if (!$this->user->hasPermission('modify', 'catalog/product')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }
    return !$this->error;
}
```

### Breadcrumbs Array

```php
$data['breadcrumbs'] = [
    ['text' => $this->language->get('text_home'),
     'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)],
    ['text' => $this->language->get('heading_title'),
     'href' => $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . $url, true)],
];
```

```twig
<ul class="breadcrumb">
  {% for breadcrumb in breadcrumbs %}
    <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
  {% endfor %}
</ul>
```

### Admin Twig Layout Skeleton

```twig
{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">{% for b in breadcrumbs %}<li><a href="{{ b.href }}">{{ b.text }}</a></li>{% endfor %}</ul>
    </div>
  </div>
  <div class="container-fluid">
    {% if error_warning %}<div class="alert alert-danger">{{ error_warning }}</div>{% endif %}
    {% if success %}<div class="alert alert-success">{{ success }}</div>{% endif %}
    {# list or form content #}
  </div>
</div>
{{ footer }}
```

### List Twig: Sortable Column Header

```twig
{% if sort == 'pd.name' %}
  <a href="{{ sort_name }}" class="{{ order|lower }}">{{ column_name }}</a>
{% else %}
  <a href="{{ sort_name }}">{{ column_name }}</a>
{% endif %}
```

### List Twig: Bulk Delete Checkboxes

```twig
<form action="{{ delete }}" method="post" id="form-product">
  <table class="table table-bordered table-hover">
    <thead><tr>
      <td><input type="checkbox" onclick="$('input[name*=\'selected\']').prop('checked', this.checked);"/></td>
      <td>{{ column_name }}</td>
    </tr></thead>
    <tbody>
      {% for product in products %}
      <tr>
        <td><input type="checkbox" name="selected[]" value="{{ product.product_id }}"
          {{ product.product_id in selected ? 'checked' : '' }}/></td>
        <td><a href="{{ product.edit }}">{{ product.name }}</a></td>
      </tr>
      {% endfor %}
    </tbody>
  </table>
</form>
<button type="button" form="form-product" formaction="{{ delete }}" class="btn btn-danger"
  onclick="confirm('{{ text_confirm }}') ? $('#form-product').submit() : false;">
  <i class="fa fa-trash-o"></i> {{ button_delete }}
</button>
```

---

## 19. Image Resize Model

### `model/tool/image.php → resize()`

```php
$this->load->model('tool/image');
$url = $this->model_tool_image->resize($filename, $width, $height): string
```

**Parameters:**
- `$filename` — relative path from `DIR_IMAGE` (e.g. `catalog/product/image.jpg`, `placeholder.png`)
- `$width`, `$height` — target dimensions in pixels

**Returns:** Full URL to cached resized image:
- HTTP: `{config_url}image/cache/catalog/product/image-200x150.jpg`
- HTTPS: `{config_ssl}image/cache/catalog/product/image-200x150.jpg`

**Cache path:** `image/cache/{original_path_without_ext}-{width}x{height}.{ext}`

**Cache logic:**
- Serves cached version if it exists AND original hasn't been modified since
- Regenerates if cache missing OR `filemtime(original) > filemtime(cache)`
- Creates subdirectories automatically

**Supported formats:** PNG, JPEG, GIF, WebP — others return original URL

**Resize behaviour (via `Image::resize()`):**
- Default (`''`): Fit within bounds, centered on white/transparent canvas
- `'w'`: Scale to width only
- `'h'`: Scale to height only

**Standard patterns:**

```php
// With theme config dimensions
$data['thumb'] = $this->model_tool_image->resize(
    $product_info['image'],
    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
    $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
);

// With placeholder fallback (always use)
$image = $product_info['image'] ?: 'placeholder.png';
$data['thumb'] = $this->model_tool_image->resize($image, 200, 150);

// Fixed size (e.g. option value icons)
'image' => $this->model_tool_image->resize($option_value['image'], 50, 50)
```

**Theme config keys for dimensions:**
```
theme_{theme}_image_popup_width / _height
theme_{theme}_image_thumb_width / _height
theme_{theme}_image_product_width / _height
theme_{theme}_image_related_width / _height
theme_{theme}_image_cart_width / _height
theme_{theme}_image_category_width / _height
theme_{theme}_image_additional_width / _height
```

**Admin version** (`admin/model/tool/image.php`): Same logic, uses `HTTPS_CATALOG`/`HTTP_CATALOG` constants instead of config values.

---

## 20. SEO URL System

### Architecture

```
.htaccess:  RewriteRule ^([^?]*) index.php?_route_=$1
                 ↓
catalog/controller/startup/seo_url.php
  → resolves _route_ → sets $request->get params + route
  → registers self as URL rewriter (addRewrite($this))
                 ↓
system/library/url.php link()
  → applies rewrite() to every generated URL
```

**Enable/disable:** `config_seo_url` = 1/0 in `oc_setting`

### Database Table

```sql
oc_seo_url (seo_url_id, store_id, language_id, query, keyword)
-- query examples: 'product_id=50', 'category_id=20', 'manufacturer_id=8',
--                 'information_id=4', 'custom/my_module'
-- keyword: 'canon-eos', 'cameras', 'my-custom-page'
```

### Incoming URL Resolution

```
/canon-eos  →  _route_=canon-eos
     ↓
SELECT * FROM oc_seo_url WHERE keyword='canon-eos' AND store_id=0
     ↓
query = 'product_id=50'  →  $request->get['product_id'] = 50
                          →  $request->get['route'] = 'product/product'
```

**Query → route mapping:**

| query prefix | Sets GET param | Sets route |
|-------------|---------------|------------|
| `product_id=N` | `product_id` | `product/product` |
| `category_id=N` | `path` (appended) | `product/category` |
| `manufacturer_id=N` | `manufacturer_id` | `product/manufacturer/info` |
| `information_id=N` | `information_id` | `information/information` |
| any other | — | `{query}` directly |

**Category chains:** `/cameras/dslr` resolves as two separate keywords, `path` becomes `20_30`

### Outgoing URL Generation

When `$this->url->link('product/product', 'product_id=50')` is called, the rewriter:

1. Queries: `SELECT * FROM oc_seo_url WHERE query='product_id=50' AND store_id=0 AND language_id=1`
2. If found: replaces route with `/keyword`, removes matched params
3. Remaining params appended as query string

### Assigning SEO URLs to Entities

In model save methods (e.g. `addProduct`, `editProduct`):

```php
// POST format: product_seo_url[$store_id][$language_id] = 'keyword'
foreach ($data['product_seo_url'] as $store_id => $language) {
    foreach ($language as $language_id => $keyword) {
        if (!empty($keyword)) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET
                store_id    = '" . (int)$store_id . "',
                language_id = '" . (int)$language_id . "',
                query       = 'product_id=" . (int)$product_id . "',
                keyword     = '" . $this->db->escape($keyword) . "'");
        }
    }
}
// On edit: DELETE WHERE query='product_id=N' first, then re-insert
// On delete: DELETE WHERE query='product_id=N'
```

**Same pattern for:** `category_id=N`, `manufacturer_id=N`, `information_id=N`

### Custom Route SEO URL

```php
// Via admin: Design → SEO URLs
// query = 'custom/my_module', keyword = 'my-custom-page'

// Programmatic:
$this->load->model('design/seo_url');
$this->model_design_seo_url->addSeoUrl([
    'store_id'    => 0,
    'language_id' => 1,
    'query'       => 'custom/my_module',
    'keyword'     => 'my-custom-page',
]);
```

### Conflict Rules

- Within same store: one keyword → only one query (no duplicate destinations)
- Within same store: one query → only one keyword (no duplicate slugs)
- Across stores: same keyword allowed for different queries

### Validation in Admin

```php
$seo_urls = $this->model_design_seo_url->getSeoUrlsByKeyword($keyword);
foreach ($seo_urls as $seo_url) {
    if ($seo_url['store_id'] == $store_id && $seo_url['query'] != $current_query) {
        $this->error['keyword'] = $this->language->get('error_keyword'); // conflict
    }
}
```

### `addRewrite()` Contract

Any object passed to `$url->addRewrite($obj)` must implement:
```php
public function rewrite(string $link): string {
    // Parse URL, query DB for keyword, rebuild URL if found
    // Return original $link if no match
}
```

Multiple rewriters can be chained — each transforms the output of the previous.

---

## Section 21 — Startup Sequence (Complete Boot Flow)

### Entry Point: index.php

```php
define('VERSION', '3.0.4.1');
require_once('config.php');       // Defines DIR_*, DB_*, HTTP_SERVER constants
if (!defined('DIR_APPLICATION')) { header('Location: install/index.php'); exit; }
require_once(DIR_SYSTEM . 'startup.php');
start('catalog');                  // 'catalog' or 'admin'
```

### system/startup.php — Phase 1

Executes before framework, sets up PHP environment:

1. `error_reporting(E_ALL)` + PHP 7.3 version check
2. Timezone to UTC if not set in php.ini
3. IIS/Windows `$_SERVER` compatibility fixes (`DOCUMENT_ROOT`, `REQUEST_URI`, `HTTP_HOST`)
4. HTTPS detection — checks `$_SERVER['HTTPS']`, port 443, and `HTTP_X_FORWARDED_PROTO` headers
5. Defines `modification($filename)` — looks for file in `DIR_MODIFICATION` first, falls back to original
6. Loads Composer autoloader if `DIR_STORAGE/vendor/autoload.php` exists
7. Registers SPL autoloader `library()` → maps `Cart\Customer` to `system/library/cart/customer.php`
8. Manually `require_once` engine classes: `Action`, `Controller`, `Event`, `Router`, `Loader`, `Model`, `Registry`, `Proxy`
9. Requires `system/helper/general.php` and `system/helper/utf8.php`
10. Defines `start($application_config)` which requires `system/framework.php`

### system/framework.php — Phase 2 (Service Registration Order)

| # | Registry Key | Class | Notes |
|---|---|---|---|
| 1 | `config` | `Config` | Loads `system/config/default.php` + `system/config/catalog.php` |
| 2 | `log` | `Log` | Filename from config |
| 3 | — | — | `date_default_timezone_set()` from config |
| 4 | — | — | Global `set_error_handler()` (log + display based on config) |
| 5 | `event` | `Event` | Registers config-level events with priorities |
| 6 | `load` | `Loader` | Dynamic resource loader |
| 7 | `request` | `Request` | Wraps `$_GET`, `$_POST`, `$_SERVER`, `$_COOKIE`, `$_FILES` |
| 8 | `response` | `Response` | Sets no-cache headers immediately |
| 9 | `db` | `DB` | Only if `db_autostart=true`; syncs MySQL timezone |
| 10 | `session` | `Session` | Only if `session_autostart=true`; sets cookie |
| 11 | `cache` | `Cache` | Engine from config |
| 12 | `url` | `Url` | Only if `url_autostart=true` |
| 13 | `language` | `Language` | Language directory from config |
| 14 | `document` | `Document` | Page head manager |
| 15 | — | — | Autoload: configs, languages, libraries, models |
| 16 | `router` | `Router` | Created, pre-actions registered |
| 17 | — | — | `$route->dispatch($action, $error)` → execution begins |
| 18 | — | — | `$response->output()` → sends buffered output |

### Router Dispatch Loop

```php
// Pre-actions run first (in order)
foreach ($this->pre_action as $pre_action) {
    $result = $this->execute($pre_action);
    if ($result instanceof Action) { $action = $result; break; }
}

// Main loop
while ($action instanceof Action) {
    $action = $this->execute($action);
}
// If execute() returns null → stop. Returns Action → loop. Returns Exception → error action.
```

### Action Class — Route Resolution

Route `product/category` resolves:
1. Try `catalog/controller/product/category.php` → NOT FOUND
2. Pop last segment → method = `category`, try `catalog/controller/product.php` → FOUND
3. Class: `ControllerProduct`, method: `category()`

Routes are sanitized: only `[a-zA-Z0-9_/]` allowed. Magic methods (`__*`) blocked.

### Catalog Pre-Actions (run in this order before main route)

| Controller | What It Does |
|---|---|
| `startup/error` | Replaces global error handler with store-aware version; uses `config_error_log`, `config_error_display` |
| `startup/event` | Loads DB-stored events from `oc_event` into Event system |
| `startup/maintenance` | If `config_maintenance=1` and not admin user → return `common/maintenance` Action |
| `startup/seo_url` | Decodes `_route_` into `$_GET` params; registers URL rewriter |
| `startup/session` | API token session OR cookie session; handles `api_token` GET param for API routes |
| `startup/startup` | **Main startup** — see below |

### startup/startup — Store Initialization Detail

```
1. Match HTTP_HOST against oc_store.url / oc_store.ssl → set config_store_id
2. Load all settings: store_id=0 first, then current store (ORDER BY store_id ASC → store overrides global)
3. Sync DB timezone with PHP timezone
4. Re-create URL object with store-specific config_url / config_ssl
5. Detect language: session → cookie → Accept-Language header → config_language
6. Create Language object, load language file, set config_language_id
7. Init Customer (Cart\Customer)
8. Resolve config_customer_group_id: session customer → logged-in customer → guest → config default
9. Handle ?tracking= cookie (affiliate tracking + increment oc_marketing.clicks)
10. Detect & set currency (same pattern as language)
11. Register: currency, tax, weight, length, cart, encryption
```

### Controller Base Class

```php
abstract class Controller {
    protected $registry;
    public function __construct($registry) { $this->registry = $registry; }
    public function __get($key) { return $this->registry->get($key); }
    public function __set($key, $value) { $this->registry->set($key, $value); }
}
// Access any service as $this->db, $this->config, $this->load, etc.
```

### Loader — Key Behaviors

- All methods fire `before`/`after` events (e.g., `view/product/product/before`)
- Models loaded once, cached in registry as `model_extension_module_foo`
- Models wrapped in **Proxy** class for method-level event interception
- `load->view()` uses Template engine, passes `$data` vars into Twig scope
- Events can short-circuit by returning non-null non-Exception from before event

### Constants Defined by config.php

`VERSION`, `HTTP_SERVER`, `HTTPS_SERVER`, `DIR_APPLICATION`, `DIR_SYSTEM`, `DIR_IMAGE`, `DIR_STORAGE`, `DIR_LANGUAGE`, `DIR_TEMPLATE`, `DIR_CONFIG`, `DIR_CACHE`, `DIR_DOWNLOAD`, `DIR_LOGS`, `DIR_MODIFICATION`, `DIR_SESSION`, `DIR_UPLOAD`, `DB_DRIVER`, `DB_HOSTNAME`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`, `DB_PORT`, `DB_PREFIX`

---

## Section 22 — Multi-Store Management

### How Stores Are Identified

On every request, `startup/startup` matches the current domain to `oc_store`:

```php
// HTTP:
SELECT * FROM oc_store WHERE REPLACE(`url`, 'www.', '') = 'http://example.com/'
// HTTPS:
SELECT * FROM oc_store WHERE REPLACE(`ssl`, 'www.', '') = 'https://example.com/'

// Result:
if ($query->num_rows)   { config_store_id = $query->row['store_id']; }
else                    { config_store_id = 0; }  // Default store
// Override: ?store_id=X in GET overrides everything
```

### Settings Scope — How Store Overrides Work

```sql
-- Settings loaded in ASC order: global first, then store-specific
SELECT * FROM oc_setting
WHERE store_id = '0' OR store_id = '{current_store_id}'
ORDER BY store_id ASC
```

Store-specific values silently overwrite global values. This means every `config_*` key can differ per store: `config_name`, `config_currency`, `config_language`, `config_layout_id`, etc.

### oc_store Table

```sql
store_id  INT AUTO_INCREMENT  -- 0 = default (cannot be deleted)
name      VARCHAR(64)         -- Display name in admin header
url       VARCHAR(255)        -- HTTP URL: 'http://example.com/'  (trailing slash required)
ssl       VARCHAR(255)        -- HTTPS URL: 'https://example.com/'
```

### Admin Store Management

**ModelSettingStore::addStore($data):**
1. INSERT into `oc_store` (name, url, ssl)
2. Copy all `oc_layout_route` entries from `store_id=0` to new store
3. Clear `store` cache

**ModelSettingSetting::editSetting($code, $data, $store_id = 0):**
- DELETE all keys for `(store_id, code)` then re-insert
- Keys filtered by `substr($key, 0, strlen($code)) == $code`
- Arrays are JSON-encoded with `serialized=1`

**ModelSettingSetting::getSetting($code, $store_id = 0):**  
Returns all keys for a code+store as associative array.

### Multi-Store Tables

| Table | Purpose |
|---|---|
| `oc_store` | Store URLs and names |
| `oc_setting` | All settings scoped by `store_id` |
| `oc_layout_route` | Route→layout assignment per `store_id` |
| `oc_product_to_store` | Which products appear in which stores |
| `oc_category_to_store` | Which categories appear in which stores |
| `oc_seo_url` | SEO keywords scoped by `store_id` + `language_id` |

### Product/Category Store Assignment

```php
// Model save:
DELETE FROM oc_product_to_store WHERE product_id = ?
foreach ($data['product_store'] as $store_id) {
    INSERT INTO oc_product_to_store SET product_id=?, store_id=?
}

// Model query always filters by current store:
LEFT JOIN oc_product_to_store p2s ON p.product_id = p2s.product_id
WHERE p2s.store_id = '{config_store_id}'
```

### Programmatic Store-Specific Settings in Extensions

```php
// Read (current store, already loaded into $this->config):
$this->config->get('my_extension_setting');

// Save to specific store:
$this->model_setting_setting->editSetting('my_extension', $data, $store_id);

// Save to all stores:
$stores = $this->model_setting_store->getStores();
foreach ($stores as $store) {
    $this->model_setting_setting->editSetting('my_extension', $data, $store['store_id']);
}
```

### Admin Store Selector

The admin header shows all store URLs as links (for viewing storefronts). Admin itself is NOT store-scoped — all stores share one admin panel. Store selection only affects which storefront URL you open.

---

## Section 23 — Payment Extensions (Complete)

### File Structure

```
catalog/controller/extension/payment/{code}.php  — index(), confirm(), [callback(), webhook()]
catalog/model/extension/payment/{code}.php        — getMethod($address, $total)
admin/controller/extension/payment/{code}.php     — index(), validate()
admin/model/extension/payment/{code}.php          — [install(), uninstall()]
admin/language/en-gb/extension/payment/{code}.php
catalog/view/theme/default/template/extension/payment/{code}.twig
admin/view/template/extension/payment/{code}.twig
```

### catalog/model — getMethod($address, $total)

```php
public function getMethod($address, $total) {
    // 1. Check minimum order total
    if ($this->config->get('payment_cod_total') > 0 && $this->config->get('payment_cod_total') > $total) {
        return array();
    }

    // 2. Check geo-zone (0 = all zones allowed)
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone
        WHERE geo_zone_id = '" . (int)$this->config->get('payment_cod_geo_zone_id') . "'
        AND country_id = '" . (int)$address['country_id'] . "'
        AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

    if ($this->config->get('payment_cod_geo_zone_id') && !$query->num_rows) {
        return array();
    }

    return array(
        'code'       => 'cod',
        'title'      => $this->language->get('text_title'),
        'terms'      => '',
        'sort_order' => $this->config->get('payment_cod_sort_order')
    );
}

// For recurring/subscription support:
public function recurringPayments() { return true; }
```

### catalog/controller — index() and confirm()

```php
public function index() {
    // Renders payment form on checkout confirmation page
    return $this->load->view('extension/payment/cod');
}

public function confirm() {
    $json = array();
    if ($this->session->data['payment_method']['code'] == 'cod') {
        $this->load->model('checkout/order');
        $this->model_checkout_order->addOrderHistory(
            $this->session->data['order_id'],
            $this->config->get('payment_cod_order_status_id')
        );
        $json['redirect'] = $this->url->link('checkout/success');
    }
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
```

### Gateway Callback Pattern

```php
// Callback URL: /index.php?route=extension/payment/paypal&callback_token={token}
public function callback() {
    if (!empty($this->request->get['callback_token'])) {
        if (hash_equals($stored_token, $this->request->get['callback_token'])) {
            // Verify payment with gateway API
            // Update order:
            $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, $notify);
            $this->response->redirect($this->url->link('checkout/success'));
        }
    }
}

// Webhook URL: /index.php?route=extension/payment/paypal&webhook_token={token}
public function webhook() {
    $payload = json_decode(file_get_contents('php://input'), true);
    if (hash_equals($stored_token, $this->request->get['webhook_token'])) {
        // Map event_type to order_status_id:
        // 'PAYMENT.CAPTURE.COMPLETED' → completed status
        // 'PAYMENT.CAPTURE.REFUNDED'  → refunded status
        // 'PAYMENT.CAPTURE.DENIED'    → denied status
        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, $comment, true);
    }
}
```

### admin/controller — index() and validate()

```php
public function index() {
    if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
        $this->model_setting_setting->editSetting('payment_cod', $this->request->post);
        $this->session->data['success'] = $this->language->get('text_success');
        $this->response->redirect($this->url->link('marketplace/extension',
            'user_token=' . $this->session->data['user_token'] . '&type=payment', true));
    }
    $data['payment_cod_status']          = $this->config->get('payment_cod_status');
    $data['payment_cod_order_status_id'] = $this->config->get('payment_cod_order_status_id');
    $data['payment_cod_geo_zone_id']     = $this->config->get('payment_cod_geo_zone_id');
    $data['payment_cod_total']           = $this->config->get('payment_cod_total');
    $data['payment_cod_sort_order']      = $this->config->get('payment_cod_sort_order');
    $this->response->setOutput($this->load->view('extension/payment/cod', $data));
}
protected function validate() {
    if (!$this->user->hasPermission('modify', 'extension/payment/cod')) {
        $this->error['warning'] = $this->language->get('error_permission');
    }
    return !$this->error;
}
```

### Settings Key Pattern

```
payment_{code}_status           → 0/1 enabled
payment_{code}_order_status_id  → order status when payment confirmed
payment_{code}_total            → minimum order total (0 = no minimum)
payment_{code}_sort_order       → display order
payment_{code}_geo_zone_id      → 0 = all zones, else restrict
```

### Checkout Flow — How Payment Extensions Are Called

```
1. checkout/payment_method → loads all payment extensions, calls getMethod($address, $total) on each
   → stores results in session['payment_methods']
2. checkout/confirm → loads payment controller, calls index() → displays payment form
3. Customer submits → payment controller confirm() called via AJAX
4. confirm() calls addOrderHistory($order_id, $status_id) → success redirect or error
```

### addOrderHistory — Side Effects

When order moves to `config_processing_status` or `config_complete_status`:
1. Fraud extensions checked
2. Total extensions `confirm()` called (vouchers, coupons, reward points)
3. Product inventory deducted (`quantity - X` where `subtract=1`)
4. Affiliate commission transaction added (if `config_affiliate_auto`)

### Recurring Profiles

```php
// oc_recurring table: price, frequency (day/week/semi_month/month/year), cycle, duration, trial_*
// Cart detects: $this->cart->hasRecurringProducts()
// Only payment extensions returning recurringPayments()=true are shown when cart has recurring
// Payment extension must create subscription with gateway and store in extension-specific table
// Subsequent billing handled by gateway webhooks → update order status per cycle
```

### admin/model install()/uninstall()

```php
// Only needed if extension creates custom tables
public function install() {
    $this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "my_payment_table` (...)");
}
public function uninstall() {
    $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "my_payment_table`");
}
```

---

## Section 24 — Shipping Extensions (Complete)

### File Structure

```
catalog/model/extension/shipping/{code}.php   — getQuote($address)
admin/controller/extension/shipping/{code}.php — index(), validate()
admin/language/en-gb/extension/shipping/{code}.php
admin/view/template/extension/shipping/{code}.twig
```

No catalog controller needed — shipping is purely data (quotes), no UI.

### catalog/model — getQuote($address)

```php
public function getQuote($address) {
    // $address keys: country_id, zone_id, postcode, city, ...

    // 1. Geo-zone check
    $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone
        WHERE geo_zone_id = '" . (int)$this->config->get('shipping_flat_geo_zone_id') . "'
        AND country_id = '" . (int)$address['country_id'] . "'
        AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

    $status = !$this->config->get('shipping_flat_geo_zone_id') || $query->num_rows;

    if (!$status) return array();  // Not available for this address

    // 2. Build quotes array (can have multiple options)
    $quote_data['flat'] = array(
        'code'         => 'flat.flat',     // {method_code}.{quote_code}
        'title'        => 'Flat Rate',
        'cost'         => (float)$this->config->get('shipping_flat_cost'),
        'tax_class_id' => (int)$this->config->get('shipping_flat_tax_class_id'),
        'text'         => $this->currency->format(
            $this->tax->getRate($cost, $tax_class_id), $this->session->data['currency']
        )
    );

    return array(
        'code'       => 'flat',
        'title'      => $this->language->get('text_title'),
        'quote'      => $quote_data,
        'sort_order' => $this->config->get('shipping_flat_sort_order'),
        'error'      => false
    );
}
```

### Quote Code Format

`{method_code}.{quote_code}` — e.g., `flat.flat`, `standard.domestic`, `standard.international`

When customer selects shipping:
```php
$shipping = explode('.', $post['shipping_method']);
// $shipping[0] = method code, $shipping[1] = quote code
$selected = $session['shipping_methods'][$shipping[0]]['quote'][$shipping[1]];
$session['shipping_method'] = $selected;
// Stored: code, title, cost, tax_class_id, text
```

### Settings Key Pattern

```
shipping_{code}_status        → 0/1 enabled
shipping_{code}_cost          → base cost
shipping_{code}_tax_class_id  → tax applied (0 = no tax)
shipping_{code}_sort_order    → display order
shipping_{code}_geo_zone_id   → 0 = all zones
```

### Checkout Flow — How Shipping Extensions Are Called

```
1. checkout/shipping_method → loads all enabled shipping extensions
2. Calls getQuote($session['shipping_address']) on each
3. Non-empty returns stored in session['shipping_methods']
4. Customer selects → session['shipping_method'] = selected quote data
5. Order creation uses session['shipping_method']['title'] and ['code']
```

---

## Section 25 — Customer Groups, Discounts & Specials

### Customer Group Assignment

```php
// On addCustomer():
$customer_group_id = in_array($data['customer_group_id'], $config_customer_group_display)
    ? $data['customer_group_id']
    : $this->config->get('config_customer_group_id');  // Default group

// config_customer_group_display = array of group IDs customers can self-select
// config_customer_group_id = default group ID

// If group requires approval (oc_customer_group.approval=1):
INSERT INTO oc_customer_approval SET customer_id=?, type='customer'
```

### Price Priority in Cart

The Cart applies pricing in this priority order (highest wins):

```
1. Product Special (oc_product_special) — limited time sale
2. Product Discount (oc_product_discount, quantity=1) — group-specific price
3. Base price (oc_product.price)
```

Discounts for quantity > 1 are tiered:
```sql
-- Highest matching tier wins:
SELECT price FROM oc_product_discount
WHERE product_id = ? AND customer_group_id = ? AND quantity <= {cart_qty}
ORDER BY quantity DESC, priority ASC, price ASC LIMIT 1
```

Specials override discounts for quantity 1:
```sql
SELECT price FROM oc_product_special
WHERE product_id = ? AND customer_group_id = ?
AND (date_start = '0000-00-00' OR date_start < NOW())
AND (date_end = '0000-00-00' OR date_end > NOW())
ORDER BY priority ASC, price ASC LIMIT 1
```

### oc_product_discount Table

```
product_id, customer_group_id, quantity, priority, price, date_start, date_end
-- quantity=1 → group-specific base price
-- quantity>1 → tiered bulk discount
-- priority breaks ties (lower = higher priority)
```

### oc_product_special Table

```
product_id, customer_group_id, priority, price, date_start, date_end
-- date fields '0000-00-00' = no limit
```

### oc_product_reward Table

```
product_id, customer_group_id, points
-- Reward points earned when buying this product (per group)
```

### Saving Discounts/Specials (Admin Model)

```php
// admin/model/catalog/product.php
$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id=?");
foreach ($data['product_discount'] as $d) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET
        product_id=?, customer_group_id=?, quantity=?, priority=?,
        price=?, date_start=?, date_end=?");
}

$this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id=?");
foreach ($data['product_special'] as $s) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET
        product_id=?, customer_group_id=?, priority=?, price=?, date_start=?, date_end=?");
}
```

### config_customer_group_id Flow

- Set during startup from: logged-in customer group → session guest group → config default
- All catalog model queries use `$this->config->get('config_customer_group_id')` for pricing

---

## Section 26 — Affiliate System

### Registration Flow

```php
// catalog/model/account/customer.php::addAffiliate($customer_id, $data)
INSERT INTO oc_customer_affiliate SET
    customer_id = ?,
    company = ?,
    website = ?,
    tracking = token(64),                          // Unique 64-char tracking token
    commission = config_affiliate_commission,       // Default % from config
    tax = ?,
    payment = ?,                                   // 'cheque', 'paypal', or 'bank'
    cheque = ?, paypal = ?,
    bank_name = ?, bank_branch_number = ?, bank_swift_code = ?,
    bank_account_name = ?, bank_account_number = ?,
    custom_field = json_encode([]),
    status = !config_affiliate_approval            // 1 if auto-approve, 0 if needs review

// If approval required:
INSERT INTO oc_customer_approval SET customer_id=?, type='affiliate'
```

### Tracking Flow

```php
// ?tracking=TOKEN in URL:
setcookie('tracking', $token, time() + 3600*24*1000, '/');
UPDATE oc_marketing SET clicks = clicks+1 WHERE code = ?

// On addOrder():
$affiliate = getAffiliateByTracking($cookie['tracking']);
$order_data['affiliate_id'] = $affiliate['customer_id'];
$order_data['commission']   = $order_total * ($affiliate['commission'] / 100);
$order_data['tracking']     = $token;
```

### Commission Recording

```php
// catalog/model/checkout/order.php::addOrderHistory()
// When status moves to config_processing_status or config_complete_status:
if ($order_info['affiliate_id'] && $this->config->get('config_affiliate_auto')) {
    if (!getTotalTransactionsByOrderId($order_id)) {  // Prevent double-credit
        $this->model_account_customer->addTransaction(
            $order_info['affiliate_id'],
            'Order #' . $order_id,
            $order_info['commission'],
            $order_id
        );
    }
}

// If order status reverts from processing/complete:
$this->model_account_customer->deleteTransactionByOrderId($order_id);
```

### oc_customer_affiliate Fields

`customer_id`, `company`, `website`, `tracking` (64-char unique token), `commission` (%), `tax`, `payment` (cheque/paypal/bank), `cheque`, `paypal`, `bank_name`, `bank_branch_number`, `bank_swift_code`, `bank_account_name`, `bank_account_number`, `custom_field`, `status`

### Key Model Methods

```php
getAffiliate($customer_id)          // → affiliate row or empty
getAffiliateByTracking($tracking)   // → affiliate row or empty
addTransaction($customer_id, $desc, $amount, $order_id)
deleteTransactionByOrderId($order_id)
getTotalTransactionsByOrderId($order_id)
getTransactionTotal($customer_id)   // → SUM(amount) — current balance
```

### Config Keys

```
config_affiliate_commission  → default commission % for new affiliates
config_affiliate_approval    → 1 = require admin approval
config_affiliate_auto        → 1 = auto-record commission on order processing
```

---

## Section 27 — Reward Points System

### How Points Are Earned

Points are per-product per-customer-group (`oc_product_reward`). Fetched in Cart:

```php
SELECT points FROM oc_product_reward
WHERE product_id = ? AND customer_group_id = '{config_customer_group_id}'
```

Cart item includes `'reward' => $points * $quantity` and `'points' => $product_points * $quantity`.

On order creation, reward is stored in `oc_order_product.reward`.

### How Points Are Added to Customer Balance

`addOrderHistory()` triggers reward point credit when reaching `config_complete_status`:

```php
// Total extensions (total/reward_points) call confirm() on order status change
// This inserts into oc_customer_reward:
INSERT INTO oc_customer_reward SET
    customer_id=?, order_id=?, description=?, points=?, date_added=NOW()
```

### Redeeming Points at Checkout

Customer can apply points during checkout (requires `config_customer_reward_status=1`):
- Points value = points × `config_reward_point_exchange`
- Applied as order total discount

### Model Methods

```php
// catalog/model/account/reward.php
getRewards($data)      // Paginated history, sortable by points/date
getTotalRewards()      // COUNT for logged-in customer
getTotalPoints()       // SUM(points) — current balance

// catalog/model/account/customer.php
getRewardTotal($customer_id)  // SUM from oc_customer_reward
```

### oc_customer_reward

`customer_reward_id`, `customer_id`, `order_id`, `description`, `points` (positive=earned, negative=spent), `date_added`

### Config Keys

```
config_customer_reward_status   → 1 = enable reward points
config_reward_point_exchange    → points-to-currency rate (e.g., 1 point = $0.01)
config_complete_status          → array of status IDs that trigger point credit
config_processing_status        → array of status IDs (processing)
```

---

## Section 28 — Returns / RMA Workflow

### Database Tables

**oc_return:**
`return_id`, `order_id`, `product_id`, `customer_id`, `firstname`, `lastname`, `email`, `telephone`, `product` (name snapshot), `model`, `quantity`, `opened` (bool), `return_reason_id`, `return_action_id`, `return_status_id`, `comment`, `date_ordered`, `date_added`, `date_modified`

**oc_return_status:** `return_status_id`, `language_id`, `name`  
Default: 1=Pending, 2=Awaiting Products, 3=Complete

**oc_return_reason:** `return_reason_id`, `language_id`, `name`  
Examples: Defective, Not As Described, Wrong Item

**oc_return_action:** `return_action_id`, `language_id`, `name`  
Default: 1=Refunded, 2=Credit Issued, 3=Replacement Sent

**oc_return_history:** `return_history_id`, `return_id`, `return_status_id`, `notify`, `comment`, `date_added`

### Customer Workflow

```
1. GET  /account/return/add → form with return reasons, pre-populated order_id/product_id from GET
2. POST validates: order_id, name(1-32), email, telephone(3-32), product(1-255), model(1-64), reason
3. ModelAccountReturn::addReturn() → INSERT with status=config_return_status_id (usually Pending)
4. /account/return → paginated list of own returns
5. /account/return/info?return_id=X → detail + history (customer_id filter enforced)
```

### Admin Workflow

```
1. Admin /sale/return → filtered list (by return_id, order_id, customer, product, model, status, date)
2. Admin can add/edit/delete returns
3. Status change: ModelSaleReturn::addReturnHistory($return_id, $status_id, $comment, $notify)
   → UPDATE oc_return SET return_status_id=? + INSERT oc_return_history
   → If notify=1: customer receives email
4. No automatic refund/credit — admin handles manually in payment gateway + order adjustment
```

### Status Change is Immutable Audit Trail

Every status change creates a new `oc_return_history` row. The `oc_return.return_status_id` column is always updated to the latest status. History is chronological.

### Key Admin Model Methods

```php
addReturn($data)                           // INSERT
editReturn($return_id, $data)              // UPDATE (does not add history)
deleteReturn($return_id)                   // DELETE return + all history
getReturn($return_id)                      // Single return with status/reason/action names
getReturns($data)                          // Filtered, sorted, paginated list
getTotalReturns($data)                     // COUNT with same filters
addReturnHistory($return_id, $status_id, $comment, $notify)  // Status change
getReturnHistories($return_id, $start, $limit)
getTotalReturnHistoriesByReturnStatusId($status_id)
getTotalReturnsByReturnStatusId($status_id)
getTotalReturnsByReturnReasonId($reason_id)
```

### Config Key

```
config_return_status_id  → initial return status when customer submits (usually 1=Pending)
config_return_id         → information page ID for RMA agreement terms
```

---

## Section 29 — API System (External / Mobile Integration)

### Database Tables

**oc_api:** `api_id`, `username`, `key` (long secret), `status` (0/1), `date_added`, `date_modified`

**oc_api_ip:** `api_ip_id`, `api_id`, `ip` — IP whitelist per API key

**oc_api_session:** `api_session_id`, `api_id`, `session_id` (the api_token), `ip`, `date_added`, `date_modified`

### Authentication Flow

```
POST /index.php?route=api/login
Body: { "username": "Default", "key": "secret_key" }

1. SELECT FROM oc_api WHERE username=? AND key=? AND status=1
2. SELECT FROM oc_api_ip WHERE api_id=?
3. Check REMOTE_ADDR in whitelist → error "IP not allowed" if not found
4. Create session, INSERT oc_api_session (api_id, session_id, ip, timestamps)
5. Set session->data['api_id'] = api_id
6. Return: {"success": "...", "api_token": "{session_id}"}
```

All subsequent requests must include `api_token` (POST or GET).

Session startup detects API route:
```php
if (isset($this->request->get['api_token']) && substr($route, 0, 4) == 'api/') {
    // Look up api_token in oc_api_session, validate IP, start that session
    // Sessions expire after 1 hour of inactivity (TIMESTAMPADD(HOUR, 1, date_modified) < NOW())
}
```

### Available Endpoints

| Route | Method | Description |
|---|---|---|
| `api/login` | POST | Authenticate, get api_token |
| `api/customer` | POST | Set customer info for session |
| `api/cart/add` | POST | Add product(s) to cart |
| `api/cart/edit` | POST | Update cart item quantity |
| `api/cart/remove` | POST | Remove cart item |
| `api/cart/products` | GET | Get cart contents with totals |
| `api/shipping/address` | POST | Set shipping address |
| `api/shipping/methods` | GET | Get available shipping methods |
| `api/shipping/method` | POST | Select shipping method |
| `api/payment/address` | POST | Set billing address |
| `api/payment/methods` | GET | Get available payment methods |
| `api/payment/method` | POST | Select payment method |
| `api/order/add` | POST | Create order from session data |

### cart/products Response Format

```json
{
  "products": [{
    "cart_id": "...", "product_id": 123,
    "name": "...", "model": "...",
    "option": [{"name": "Color", "value": "Red", "type": "select"}],
    "quantity": 2, "stock": true, "shipping": true,
    "price": "$9.99", "total": "$19.98", "reward": 100
  }],
  "vouchers": [...],
  "totals": [
    {"title": "Sub-Total", "text": "$19.98"},
    {"title": "Shipping", "text": "$5.00"},
    {"title": "Total", "text": "$24.98"}
  ]
}
```

### shipping/methods Response Format

```json
{
  "shipping_methods": {
    "flat": {
      "title": "Flat Rate",
      "quote": {
        "flat": {"code": "flat.flat", "title": "Flat Rate", "cost": 5.00, "tax_class_id": 0}
      },
      "sort_order": 1
    }
  }
}
```

### Complete Order Creation Sequence

```
1. POST api/login              → get api_token
2. POST api/cart/add           → add products
3. POST api/customer           → set customer (or customer_id for existing)
4. POST api/shipping/address   → set shipping address
5. GET  api/shipping/methods   → get available methods
6. POST api/shipping/method    → select method (e.g., "flat.flat")
7. POST api/payment/address    → set billing address
8. GET  api/payment/methods    → get available methods
9. POST api/payment/method     → select method (e.g., "cod")
10. POST api/order/add         → create order → returns {"order_id": 123}
```

### api/order/add Validation

Before creating order, all of these must be set in session:
- `api_id` (from login)
- `customer` (from api/customer)
- `payment_address` + `payment_method`
- `shipping_address` + `shipping_method` (if cart has shipping items)
- Cart must have products with stock

### Admin API Management

**admin/model/user/api.php:**
```php
addApi($data)           // username, key, status, api_ip[] array
editApi($api_id, $data) // UPDATE + delete old IPs + insert new IPs
deleteApi($api_id)
getApi($api_id)
getApis($data)          // Paginated, sorted by username
getApiIps($api_id)
addApiSession($api_id, $session_id, $ip)   // Also auto-whitelists IP if not already
deleteApiSession($api_session_id)
deleteApiSessionBySessionId($session_id)
```

### Security Notes

- API key must be kept secret (stored in oc_api)
- IP whitelist enforced on every login attempt
- Sessions expire after 1 hour idle (cleaned up on next login)
- All inputs sanitized via `$this->db->escape()` + `utf8_strlen()`
- Cart is session-scoped — isolated from frontend cart if different session

---

## Section 30 — Product Options System

### Database Schema

| Table | Purpose |
|---|---|
| `oc_option` | Base option definition: `option_id`, `type`, `sort_order` |
| `oc_option_description` | Multilingual name: `option_id`, `language_id`, `name` |
| `oc_option_value` | Pre-defined values (for select/radio/checkbox): `option_value_id`, `option_id`, `image`, `sort_order` |
| `oc_option_value_description` | Multilingual value names: `option_value_id`, `language_id`, `option_id`, `name` |
| `oc_product_option` | Maps options to products: `product_option_id`, `product_id`, `option_id`, `value` (text types), `required` |
| `oc_product_option_value` | Option value modifiers per product: `product_option_value_id`, `product_option_id`, `product_id`, `option_id`, `option_value_id`, `quantity`, `subtract`, `price`, `price_prefix` (+/-), `points`, `points_prefix`, `weight`, `weight_prefix` |
| `oc_order_option` | Snapshot of selected options at order time: `name`, `value`, `type` |
| `oc_upload` | File uploads: `upload_id`, `name`, `filename`, `code` (SHA1), `date_added` |

### Option Types

| Type | Input | Values in DB | Price Modifier |
|---|---|---|---|
| `select` | Dropdown | oc_option_value | Yes |
| `radio` | Radio buttons | oc_option_value | Yes |
| `checkbox` | Multiple checkboxes | oc_option_value | Yes (each) |
| `image` | Visual selector | oc_option_value | Yes |
| `text` | Single-line input | `oc_product_option.value` (default) | No |
| `textarea` | Multi-line input | `oc_product_option.value` (default) | No |
| `file` | File upload | SHA1 code stored | No |
| `date` | Date picker (YYYY-MM-DD) | `oc_product_option.value` | No |
| `time` | Time picker (HH:mm) | `oc_product_option.value` | No |
| `datetime` | Date+time (YYYY-MM-DD HH:mm) | `oc_product_option.value` | No |

### Saving Product Options (Admin Model)

```php
// admin/model/catalog/product.php
// For select/radio/checkbox/image:
INSERT INTO oc_product_option (product_id, option_id, required)
foreach product_option_value:
    INSERT INTO oc_product_option_value (
        product_option_id, product_id, option_id, option_value_id,
        quantity, subtract, price, price_prefix,
        points, points_prefix, weight, weight_prefix
    )

// For text/textarea/file/date/datetime/time:
INSERT INTO oc_product_option (product_id, option_id, value, required)
```

### POST Data Structure

```php
$data['product_option'][] = array(
    'product_option_id'    => int,        // For edit
    'option_id'            => int,
    'type'                 => 'select',
    'required'             => 1,
    'value'                => '',         // For text types only
    'product_option_value' => array(      // For select/radio/checkbox/image only
        array(
            'product_option_value_id' => int,
            'option_value_id'         => int,
            'quantity'                => 0,
            'subtract'                => 0,
            'price'                   => 10.00,
            'price_prefix'            => '+',
            'points'                  => 5,
            'points_prefix'           => '+',
            'weight'                  => 0.5,
            'weight_prefix'           => '+'
        )
    )
);
```

### Cart Price/Weight Calculation

```php
// system/library/cart/cart.php getProducts()
// For each selected option value:
if ($price_prefix == '+') { $option_price += $price; }
if ($price_prefix == '-') { $option_price -= $price; }
// Same for weight and points

// final_price = base_price + sum(option_prices)
// final_weight = (base_weight + sum(option_weights)) * quantity
```

### Required Option Validation

```php
// catalog/controller/checkout/cart.php
foreach ($product_options as $option) {
    if ($option['required'] && empty($option_post[$option['product_option_id']])) {
        $json['error']['option'][$option['product_option_id']] = 'Required!';
    }
}
// No cart entry created until all required options are filled
```

### Option Value in Cart (Session)

Options stored as JSON in `oc_cart.option`:
- select/radio: `{"5": "23"}`  (product_option_id => option_value_id)
- checkbox: `{"5": ["23","24"]}`
- text/date: `{"6": "user text"}`
- file: `{"7": "sha1uploadcode"}`

### File Upload Flow

```
1. User selects file → POST to catalog/model/tool/upload
2. File saved to DIR_UPLOAD with hashed filename
3. INSERT INTO oc_upload (name, filename, code=sha1(uniqid()))
4. SHA1 code returned to cart as option value
5. At order: code stored in oc_order_option.value
6. File retrieved via getUploadByCode($code)
```

### Stock Check per Option

```php
// In cart, if option subtract=1:
if ($option_value['subtract'] && $option_value['quantity'] < $cart_quantity) {
    $stock = false;  // Product marked out of stock
}
// quantity=0 means unlimited stock for that option
```

---

## Section 31 — Coupon System

### oc_coupon Table

```
coupon_id, name, code (varchar 20 unique),
type: 'F' (fixed amount) | 'P' (percentage),
discount, logged (requires login), shipping (apply to shipping),
total (minimum order), date_start, date_end,
uses_total (0=unlimited), uses_customer (0=unlimited),
status, date_added
```

**Related tables:** `oc_coupon_product` (product restrictions), `oc_coupon_category` (category restrictions), `oc_coupon_history` (usage log: coupon_id, order_id, customer_id, amount, date_added)

### Coupon Validation (getCoupon)

`catalog/model/extension/total/coupon.php::getCoupon($code)` checks:
1. Code exists, `status=1`, within date range
2. Minimum order total (`total` field vs cart subtotal)
3. Global usage limit (`uses_total` vs COUNT(oc_coupon_history))
4. Login requirement (`logged=1` → must be logged in)
5. Per-customer limit (`uses_customer` vs customer's usage count)
6. Product/category restrictions (if set, at least one cart item must match)

Returns validated coupon array or null.

### Discount Calculation

```php
// getTotal($total) — modifies $total by reference
// Fixed type ('F'): distributes discount proportionally per product
//   discount_per_product = coupon_discount * (product_total / applicable_subtotal)
//   Fixed discount capped at applicable subtotal
// Percentage type ('P'):
//   discount_per_product = product_total * (discount% / 100)
// Shipping discount: adds shipping cost to discount_total if coupon.shipping=1
// Tax adjustment: reduces tax amounts when discount applied
// Appends to $total['totals']: code='coupon', value=-discount_total (NEGATIVE)
```

### confirm() — Called at Order Finalization

```php
// Re-validates coupon (race condition protection)
// If still valid: INSERT INTO oc_coupon_history (coupon_id, order_id, customer_id, amount)
// If invalid: return config_fraud_status_id (marks order as fraud)
```

### unconfirm() — Called on Order Cancellation

```php
DELETE FROM oc_coupon_history WHERE order_id = ?
```

### Session Storage

```php
$this->session->data['coupon'] = 'COUPON_CODE';  // Set when code applied
// Removed from session at checkout/success
```

### Admin Model Methods

`addCoupon($data)`, `editCoupon($id, $data)`, `deleteCoupon($id)` — all handle `oc_coupon_product` and `oc_coupon_category` cascade.  
`getCouponHistories($coupon_id, $start, $limit)` — usage history.

---

## Section 32 — Voucher System

### oc_voucher Table

```
voucher_id, order_id (0 if admin-created), code (varchar 10 unique),
from_name, from_email, to_name, to_email,
voucher_theme_id, message, amount, status, date_added
```

**Related tables:** `oc_voucher_history` (redemptions: voucher_id, order_id, amount **negative**, date_added), `oc_voucher_theme` + `oc_voucher_theme_description`, `oc_order_voucher` (denormalized copy per order)

### Voucher vs Coupon

| Feature | Coupon | Voucher |
|---|---|---|
| Code length | varchar 20 | varchar 10 |
| Product restrictions | Yes | No |
| Minimum order | Yes | No |
| Balance | Single use | Partial redemption (running balance) |
| Purchase flow | Admin creates | Customer buys OR admin creates |
| History amount | Positive (discount given) | Negative (balance deducted) |

### Balance Calculation

```php
// Remaining balance = original amount + SUM(negative history amounts)
SELECT SUM(amount) FROM oc_voucher_history WHERE voucher_id = ?
// Histories are negative, so: $balance = $original + $sum_of_negatives
```

### Voucher Validation (getVoucher)

1. Code exists, `status=1`
2. If linked to order (`order_id > 0`): that order must be in `config_complete_status`
3. Remaining balance > 0

### getTotal() Behavior

```php
// Uses min(voucher_balance, order_total) — never exceeds what's owed
// Appends: code='voucher', value=-amount (NEGATIVE)
// Unsets session['voucher'] if voucher expired/exhausted
```

### confirm() — Record Redemption

```php
INSERT INTO oc_voucher_history (voucher_id, order_id, amount=NEGATIVE, date_added)
// Amount is the negative deduction (reduces future balance)
```

### Gift Voucher Purchase Flow

```
1. Customer goes to /account/voucher → fills form (to/from, theme, message, amount)
2. Validates: amount between config_voucher_min and config_voucher_max
3. Stores in session['vouchers'][$random_key] = {...}
4. Appears as line item in cart
5. On order completion, addVoucher() creates oc_voucher with unique 10-char code
6. Email sent to recipient with code
```

---

## Section 33 — Total Extensions

### Architecture

All total extensions implement `getTotal(&$total)` which receives arrays **by reference**:

```php
$total_data = array(
    'totals' => &$totals,   // array of total line items
    'taxes'  => &$taxes,    // array indexed by tax_rate_id
    'total'  => &$total     // running grand total
);
```

Each extension modifies these in-place. Execution order = `total_{code}_sort_order` config value.

### Total Item Format

```php
$total['totals'][] = array(
    'code'       => 'sub_total',
    'title'      => 'Sub-Total',
    'value'      => 100.00,    // positive=charge, negative=discount
    'sort_order' => 10
);
$total['total'] += 100.00;
```

### Standard Extensions & Sort Orders

| Extension | Code | Sort Order | Notes |
|---|---|---|---|
| Sub-Total | `sub_total` | 10 | Cart subtotal; adds voucher amounts |
| Shipping | `shipping` | 20 | From session['shipping_method']; adds shipping tax |
| Coupon | `coupon` | 30 | Negative value; adjusts taxes |
| Voucher | `voucher` | 40 | Negative; capped at running total |
| Reward | `reward` | 45 | Negative; deducts proportionally |
| Tax | `tax` | 50 | Iterates $taxes[] accumulated by others |
| Total | `total` | 60 | Grand total line; must run last; `max(0, total)` |

### confirm() Method — Post-Order Finalization

Some extensions have `confirm($order_info, $order_total)`, called per total line when order status moves to processing/complete:

| Extension | confirm() Action |
|---|---|
| `coupon` | INSERT oc_coupon_history; return fraud_status if invalid |
| `voucher` | INSERT oc_voucher_history (negative amount); return fraud_status if invalid |
| `reward` | INSERT oc_customer_reward (negative points); return fraud_status if insufficient |

### How Totals Are Collected (in checkout/confirm.php)

```php
// 1. Sort extension list by sort_order
array_multisort($sort_order, SORT_ASC, $results);

// 2. Call getTotal() on each enabled extension
foreach ($results as $result) {
    if ($this->config->get('total_' . $result['code'] . '_status')) {
        $this->load->model('extension/total/' . $result['code']);
        $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
    }
}

// 3. Re-sort $totals by individual sort_order for display
array_multisort($sort_order, SORT_ASC, $totals);
```

### Settings Pattern

```
total_{code}_status      → 0/1 enabled
total_{code}_sort_order  → execution and display order
```

---

## Section 34 — Checkout Multi-Step Flow

### Overview

```
Cart → Checkout entry → Account (login/register/guest) →
Payment Address → [Shipping Address] → [Shipping Method] →
Payment Method → Confirm → Success
```

Shipping steps only shown if `$this->cart->hasShipping()` is true.

### Step-by-Step Session State

#### 1. Cart (`/checkout/cart`)
- Reads: `session['vouchers']`, `session['success']`
- Writes: `session['coupon']`, `session['voucher']`, `session['reward']` (via AJAX calls to total extension controllers)
- Renders total extension forms (coupon input, voucher input, etc.)

#### 2. Checkout Entry (`/checkout/checkout`)
- Validates: cart has products + stock, minimum quantities met
- If not → redirect to cart
- Routes frontend JavaScript to show appropriate steps

#### 3. Register/Guest Step
- **register**: Creates customer account, auto-login
- **guest**: Sets `session['guest']` + `session['payment_address']`
- Validation: name(1-32), email(valid), telephone(3-32), address_1(3-128), city(2-128), postcode(if country requires)

#### 4. Payment Address (`/checkout/payment_address`)
- Requires logged-in customer
- Writes: `session['payment_address']`
- Unsets: `session['payment_method']`, `session['payment_methods']`

#### 5. Shipping Address (`/checkout/shipping_address`)
- Requires logged-in + `cart->hasShipping()`
- Writes: `session['shipping_address']`
- Unsets: `session['shipping_method']`, `session['shipping_methods']`

#### 6. Shipping Method (`/checkout/shipping_method`)
- Loads all enabled shipping extensions, calls `getQuote($shipping_address)` on each
- Writes: `session['shipping_methods']`, `session['shipping_method']`, `session['comment']`

#### 7. Payment Method (`/checkout/payment_method`)
- Recalculates totals to get correct `$total` for `getMethod($address, $total)`
- Loads all enabled payment extensions, calls `getMethod($payment_address, $total)` on each
- Validates Terms & Conditions agreement (`config_checkout_id`)
- Writes: `session['payment_methods']`, `session['payment_method']`, `session['comment']`, `session['agree']`

#### 8. Confirm (`/checkout/confirm`)
- Validates all required session data; redirects if anything missing
- Recalculates totals one final time
- Assembles complete `$order_data` array
- Calls `model_checkout_order->addOrder($order_data)` → `session['order_id']`
- Loads payment controller `extension/payment/{code}` → renders payment form

#### 9. Success (`/checkout/success`)
- Calls `$this->cart->clear()`
- Unsets: ALL checkout session keys (order_id, shipping_method/methods, payment_method/methods, guest, comment, coupon, reward, voucher, vouchers, totals)

### Complete order_data Assembly

```php
$order_data = [
    // Store
    'invoice_prefix', 'store_id', 'store_name', 'store_url',
    // Customer
    'customer_id', 'customer_group_id', 'firstname', 'lastname',
    'email', 'telephone', 'custom_field',
    // Payment address (all address fields + custom_field)
    'payment_firstname', 'payment_lastname', ..., 'payment_custom_field',
    'payment_method', 'payment_code',
    // Shipping address + method
    'shipping_firstname', ..., 'shipping_custom_field',
    'shipping_method', 'shipping_code',
    // Products array (with options, download, price, tax, reward)
    'products' => [...],
    // Gift vouchers being purchased
    'vouchers' => [...],
    // Totals (from total extensions)
    'totals' => [...],
    // Metadata
    'comment', 'total', 'affiliate_id', 'commission', 'tracking',
    'language_id', 'currency_id', 'currency_code', 'currency_value',
    'ip', 'forwarded_ip', 'user_agent', 'accept_language'
];
```

---

## Section 35 — Mail System

### Mail Class API (`system/library/mail.php`)

```php
$mail = new Mail($this->config->get('config_mail_engine')); // 'mail' or 'smtp'

// Required:
$mail->setTo($email);          // string or array
$mail->setFrom($email);
$mail->setSender($name);       // display name
$mail->setSubject($subject);
// One of:
$mail->setHtml($html);         // HTML body
$mail->setText($text);         // Plain text body (or fallback)

// Optional:
$mail->setReplyTo($email);
$mail->addAttachment('/full/path/to/file.pdf');

$mail->send();  // throws Exception if required fields missing
```

### SMTP Configuration (set as properties before send())

```php
$mail->parameter          = $this->config->get('config_mail_parameter');   // sendmail -f flag
$mail->smtp_hostname      = $this->config->get('config_mail_smtp_hostname'); // 'smtp.host.com' or 'tls://smtp.host.com'
$mail->smtp_username      = $this->config->get('config_mail_smtp_username');
$mail->smtp_password      = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
$mail->smtp_port          = $this->config->get('config_mail_smtp_port');    // 25, 465, 587
$mail->smtp_timeout       = $this->config->get('config_mail_smtp_timeout'); // seconds
```

### Standard Pattern (Copy This Exactly)

```php
// From any controller or model:
$mail = new Mail($this->config->get('config_mail_engine'));
$mail->parameter     = $this->config->get('config_mail_parameter');
$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
$mail->smtp_username = $this->config->get('config_mail_smtp_username');
$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
$mail->smtp_port     = $this->config->get('config_mail_smtp_port');
$mail->smtp_timeout  = $this->config->get('config_mail_smtp_timeout');

$mail->setTo($customer_email);
$mail->setFrom($this->config->get('config_email'));
$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
$mail->setSubject(html_entity_decode($subject, ENT_QUOTES, 'UTF-8'));
$mail->setHtml($this->load->view('extension/module/my_module/mail/template', $data));
$mail->send();
```

### Mail Engine Config Keys

| Key | Value |
|---|---|
| `config_mail_engine` | `'mail'` (PHP mail()) or `'smtp'` |
| `config_mail_parameter` | Sendmail `-f admin@example.com` |
| `config_mail_smtp_hostname` | `'smtp.example.com'` or `'tls://smtp.example.com'` |
| `config_mail_smtp_username` | SMTP auth username |
| `config_mail_smtp_password` | SMTP auth password (stored HTML-encoded) |
| `config_mail_smtp_port` | 25 / 465 / 587 |
| `config_mail_smtp_timeout` | Socket timeout in seconds |
| `config_email` | Default From address |
| `config_mail_alert` | Array: `['order', 'customer', 'affiliate']` |
| `config_mail_alert_email` | Comma-separated additional alert recipients |

### Email Controllers (Event-Triggered)

These live in `catalog/controller/mail/` and are registered as event handlers:

| File | Trigger | Content |
|---|---|---|
| `order.php` | `model/checkout/order/addOrder/after` | Customer order confirmation (HTML) |
| `order.php::edit` | `model/checkout/order/addOrderHistory/after` | Status change notification (text) |
| `order.php::alert` | `model/checkout/order/addOrder/after` | Admin new order alert (text) |
| `register.php` | `model/account/customer/addCustomer/after` | Welcome email (text) |
| `register.php::alert` | Same | Admin new customer alert (text) |
| `affiliate.php` | `model/account/customer/addAffiliate/after` | Affiliate approved (text) |
| `forgotten.php` | Account forgotten password flow | Password reset link (text) |
| `transaction.php` | Customer transaction added | Points/credit balance (text) |
| `return.php` | Return status change | Status update (text) |
| `voucher.php` | Order completion with voucher | Voucher code to recipient (text) |

### Email View Templates

Located at `catalog/view/theme/{theme}/template/mail/`:
- `order_add.twig` — new order confirmation
- `order_edit.twig` — order status update
- `order_alert.twig` — admin order alert
- `register.twig`, `affiliate.twig`, `forgotten.twig`, etc.

### HTML vs Plain Text Rules

- `setHtml()` only → adapter auto-generates plain text fallback ("This is an HTML email")
- `setText()` only → plain text email
- Both → multipart/alternative (HTML + plain text)
- Adapter always base64-encodes body content
- Subject and sender name always base64-encoded in headers

### Sending to Multiple Recipients

```php
// Re-use same Mail object, change To, re-send:
$mail->setTo('first@example.com');
$mail->send();

$mail->setTo('second@example.com');
$mail->send();

// Or for comma-separated extra alert emails:
$emails = explode(',', $this->config->get('config_mail_alert_email'));
foreach ($emails as $email) {
    if (trim($email) && filter_var(trim($email), FILTER_VALIDATE_EMAIL)) {
        $mail->setTo(trim($email));
        $mail->send();
    }
}
```

---

## Section 36 — Custom Fields

### Database Schema

**oc_custom_field:**
`custom_field_id`, `type` (text/textarea/select/radio/checkbox/date/datetime/time/file), `value` (default), `validation` (regex), `location` (account/address), `status`, `sort_order`

**oc_custom_field_description:**
`custom_field_id`, `language_id`, `name` (display label)

**oc_custom_field_value:** (for select/radio/checkbox options)
`custom_field_value_id`, `custom_field_id`, `sort_order`

**oc_custom_field_value_description:**
`custom_field_value_id`, `language_id`, `custom_field_id`, `name`

**oc_custom_field_customer_group:**
`custom_field_id`, `customer_group_id`, `required` (0/1) — controls visibility and requirement per group

### Value Storage

Custom field values stored as **JSON** in entity tables:

```php
// oc_customer.custom_field (JSON):
{"account": {"1": "Company Name", "2": "VAT-123456"}}

// oc_address.custom_field (JSON):
{"address": {"3": "Floor 2"}}

// oc_order fields:
// custom_field (customer custom fields)
// payment_custom_field (payment address custom fields)
// shipping_custom_field (shipping address custom fields)
```

### Reading Custom Fields

```php
// Admin model (admin/model/customer/custom_field.php):
$field = $this->model_customer_custom_field->getCustomField($custom_field_id);
$fields = $this->model_customer_custom_field->getCustomFields($data);
$values = $this->model_customer_custom_field->getCustomFieldValues($custom_field_id);
$groups = $this->model_customer_custom_field->getCustomFieldCustomerGroups($custom_field_id);

// Catalog model (catalog/model/account/custom_field.php):
$fields = $this->model_account_custom_field->getCustomFields($customer_group_id);
// Returns fields with custom_field_value[] array pre-loaded for select/radio/checkbox
```

### Form Field Naming Convention

```
POST: custom_field[{location}][{custom_field_id}] = value

// Examples:
custom_field[account][1] = "Acme Corp"     // text field
custom_field[address][3] = "2nd Floor"     // text field
custom_field[account][5] = "42"            // select (option value ID)
custom_field[account][6] = ["42","43"]     // checkbox (array of value IDs)
```

### Validation in Controller

```php
$this->load->model('account/custom_field');
$custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

foreach ($custom_fields as $field) {
    if ($field['location'] == 'account') {
        $submitted = $this->request->post['custom_field']['account'][$field['custom_field_id']] ?? '';
        if ($field['required'] && empty($submitted)) {
            $this->error['custom_field'][$field['custom_field_id']] = 'Required!';
        }
        // Additional regex validation: preg_match($field['validation'], $submitted)
    }
}
```

---

## Section 37 — Download Products

### Database Schema

**oc_download:**
`download_id`, `filename` (actual file on disk), `mask` (display name), `date_added`

**oc_download_description:**
`download_id`, `language_id`, `name`

**oc_product_to_download:**
`product_id`, `download_id` — links downloads to products

Physical files stored in `DIR_DOWNLOAD` (default: `system/storage/download/`)

### Admin Model Methods

```php
// admin/model/catalog/download.php
addDownload($data)                  // filename, mask, download_description[language_id][name]
editDownload($download_id, $data)   // update filename/mask/descriptions
deleteDownload($download_id)        // deletes DB records only (not physical file)
getDownload($download_id)           // single record
getDownloads($data)                 // paginated list with sorting
```

### Download Availability Trigger

**Downloads only available when order reaches `config_complete_status`.**

```sql
-- Query verifies: customer owns order + order is complete + product has download
SELECT d.filename, d.mask FROM oc_order o
LEFT JOIN oc_order_product op ON o.order_id = op.order_id
LEFT JOIN oc_product_to_download p2d ON op.product_id = p2d.product_id
LEFT JOIN oc_download d ON p2d.download_id = d.download_id
WHERE o.customer_id = ? AND o.order_status_id IN (complete_status_ids)
AND d.download_id = ?
```

### Secure File Delivery

```php
// catalog/controller/account/download.php::download()
// 1. Verify login
// 2. getDownload($download_id) — validates customer ownership via complete order
// 3. If valid:
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $mask . '"');
header('Content-Length: ' . filesize($file));
ob_end_clean();
readfile($file);    // Serves internal file without exposing real path
exit();
```

URL pattern: `/account/download/download?download_id=123`

The `filename` (internal) is never exposed to customers; only `mask` (display name) is used in the Content-Disposition header.

---

## Section 38 — Report, Feed & Analytics Extensions

### Report Extensions

**File structure:**
```
admin/controller/extension/report/{code}.php  — index() settings form + report() data
admin/model/extension/report/{code}.php       — data queries
admin/view/template/extension/report/{code}_form.twig  — settings form
admin/view/template/extension/report/{code}_info.twig  — report display
admin/language/en-gb/extension/report/{code}.php
```

**Required methods:**
```php
public function index() {
    // Settings form (GET) + save settings (POST)
    $this->model_setting_setting->editSetting('report_{code}', $this->request->post);
    $this->response->redirect(...'type=report'...);
}

public function report() {
    // Renders and RETURNS (not setOutput()) report view
    // $pagination->url must include 'code={code}'
    return $this->load->view('extension/report/{code}_info', $data);
}

protected function validate() { /* permission check */ }
```

**How report is loaded:** `admin/controller/report/report.php` dispatches to `extension/report/{code}` and calls `report()`, embedding the returned HTML.

**Settings:**
```
report_{code}_status      → 0/1
report_{code}_sort_order  → menu order
```

**Admin examples:** `sale_order`, `sale_return`, `sale_shipping`, `sale_tax`, `sale_coupon`, `customer_activity`, `customer_order`, `customer_reward`, `customer_search`, `product_purchased`, `product_viewed`, `marketing`

---

### Feed Extensions

**File structure:**
```
catalog/controller/extension/feed/{code}.php  — index() generates output
catalog/model/extension/feed/{code}.php       — optional data queries
admin/language/en-gb/extension/feed/{code}.php
```

**Required method:**
```php
public function index() {
    if ($this->config->get('feed_{code}_status')) {
        // Generate XML/RSS/CSV string
        $output = '<?xml version="1.0" encoding="UTF-8"?>';
        // ... add content

        // Output directly — NOT load->view()
        $this->response->addHeader('Content-Type: application/xml');
        $this->response->setOutput($output);
    }
}
```

**Access URL** (public, no token):  
`/index.php?route=extension/feed/{code}`

**Settings:**
```
feed_{code}_status  → 0/1
```

**Built-in examples:** `google_sitemap` (XML sitemap), `google_base` (RSS product feed)

---

### Analytics Extensions

**File structure:**
```
catalog/controller/extension/analytics/{code}.php  — index() returns tracking code
admin/language/en-gb/extension/analytics/{code}.php
```

**Required method:**
```php
public function index() {
    // Return raw HTML/JavaScript string — NOT setOutput()
    return html_entity_decode($this->config->get('analytics_{code}_code'), ENT_QUOTES, 'UTF-8');
}
```

**How injected:** `catalog/controller/common/header.php` loops all enabled analytics:
```php
foreach ($analytics as $analytic) {
    if ($this->config->get('analytics_' . $analytic['code'] . '_status')) {
        $data['analytics'][] = $this->load->controller('extension/analytics/' . $analytic['code']);
    }
}
```

`header.twig` outputs `{{ analytic }}` for each — raw HTML/JS rendered inside `<body>`.

**Settings:**
```
analytics_{code}_status  → 0/1 (per-store capable — query with getSetting($key, $store_id))
```

**Built-in example:** `google` (Google Analytics tracking code)

---

### Extension Registration (All Types)

**oc_extension table:** `id`, `type`, `code`

```php
// Install:
$this->model_setting_extension->install($type, $code);
// → INSERT INTO oc_extension

// Uninstall:
$this->model_setting_extension->uninstall($type, $code);
// → DELETE FROM oc_extension + DELETE FROM oc_setting WHERE code = '{type}_{code}'

// Query:
$this->model_setting_extension->getInstalled($type);   // → array of codes
$this->model_setting_extension->getExtensions($type);  // → array of rows
```

**Optional install/uninstall controllers:**
```
admin/controller/extension/{type}/{code}/install.php   — called on extension enable
admin/controller/extension/{type}/{code}/uninstall.php — called on extension disable
```

**Permissions auto-added on install:**
```php
$this->model_user_user_group->addPermission($group_id, 'access', 'extension/{type}/{code}');
$this->model_user_user_group->addPermission($group_id, 'modify', 'extension/{type}/{code}');
```

### Comparison Table

| Aspect | Report | Feed | Analytics |
|---|---|---|---|
| Location | admin/ | catalog/ | catalog/ |
| Output method | `return view()` | `setOutput(string)` | `return string` |
| URL | admin route + user_token | Public route | Injected in header |
| Has model | Yes (queries) | Optional | No |
| Per-store | No | No | Yes |
| Content type | HTML table | XML/RSS/CSV | HTML/JavaScript |
