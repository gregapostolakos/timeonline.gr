# OpenCart 3 — Module Creation Reference

## Two Module Patterns — Choose First

Before writing any code, decide which pattern fits:

| Pattern | Use when | Settings storage | Examples |
|---------|----------|-----------------|---------|
| **Single-instance** | One global settings page, module always active | `oc_setting` via `editSetting()` | Analytics snippet, global header banner |
| **Multi-instance** | Module placed in layouts multiple times, each with own settings | `oc_module` via `model/setting/module` | Featured products block, promo banner, slider |

This choice determines the admin controller structure, save logic, and how the catalog controller reads settings.

---

## Directory Structure

```
admin/
  controller/extension/module/my_module.php   ← settings page + install/uninstall
  model/extension/module/my_module.php        ← install() uninstall() + admin queries
  view/template/extension/module/
    my_module.twig                            ← admin settings form
    my_module_list.twig                       ← (multi-instance only) instance list
    my_module_form.twig                       ← (multi-instance only) add/edit form
  language/
    en-gb/extension/module/my_module.php
    el-gr/extension/module/my_module.php

catalog/
  controller/extension/module/my_module.php   ← front-end output
  model/extension/module/my_module.php        ← front-end DB queries
  view/theme/default/template/extension/module/
    my_module.twig                            ← HTML fragment (no header/footer)
  language/
    en-gb/extension/module/my_module.php
    el-gr/extension/module/my_module.php
```

> **Journal 3:** catalog Twig path is `catalog/view/theme/journal3/template/extension/module/my_module.twig`  
> If Journal doesn't find its own template it falls back to `default/` — usually fine for module fragments.

---

## Admin Controller — Single-Instance

The settings page. `install()` and `uninstall()` are called automatically by OC's extension system.

```php
<?php
// admin/controller/extension/module/my_module.php

class ControllerExtensionModuleMyModule extends Controller {

    private array $error = [];

    public function index(): void {
        $this->load->language('extension/module/my_module');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('extension/module/my_module');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->load->model('setting/setting');
            $this->model_setting_setting->editSetting('module_my_module', $this->request->post);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link(
                'marketplace/extension',
                'user_token=' . $this->session->data['user_token'] . '&type=module',
                true
            ));
        }

        $data['breadcrumbs'] = [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/my_module', 'user_token=' . $this->session->data['user_token'], true),
            ],
        ];

        $data['action'] = $this->url->link('extension/module/my_module', 'user_token=' . $this->session->data['user_token'], true);
        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

        $data['error_warning'] = $this->error['warning'] ?? '';

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        // POST > saved config > default
        $data['module_my_module_status'] = $this->request->post['module_my_module_status']
            ?? $this->config->get('module_my_module_status')
            ?? 0;
        $data['module_my_module_api_key'] = $this->request->post['module_my_module_api_key']
            ?? $this->config->get('module_my_module_api_key')
            ?? '';

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');
        $data['user_token']  = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('extension/module/my_module', $data));
    }

    public function install(): void {
        $this->load->model('extension/module/my_module');
        $this->model_extension_module_my_module->install();
    }

    public function uninstall(): void {
        $this->load->model('extension/module/my_module');
        $this->model_extension_module_my_module->uninstall();
    }

    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/module/my_module')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        // field validation
        if (empty($this->request->post['module_my_module_api_key'])) {
            $this->error['api_key'] = $this->language->get('error_api_key');
        }
        if ($this->error && !isset($this->error['warning'])) {
            $this->error['warning'] = $this->language->get('error_warning');
        }
        return !$this->error;
    }
}
```

---

## Admin Controller — Multi-Instance

For modules placed in layouts. Each instance has its own name and settings row in `oc_module`.

```php
<?php
// admin/controller/extension/module/my_module.php

class ControllerExtensionModuleMyModule extends Controller {

    private array $error = [];

    // Lists all instances
    public function index(): void {
        $this->load->language('extension/module/my_module');
        $this->document->setTitle($this->language->get('heading_title'));
        $this->load->model('setting/module');

        $data['breadcrumbs'] = $this->buildBreadcrumbs();
        $data['add'] = $this->url->link('extension/module/my_module/add', 'user_token=' . $this->session->data['user_token'], true);

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $modules = $this->model_setting_module->getModulesByCode('my_module');
        $data['modules'] = [];
        foreach ($modules as $module) {
            $data['modules'][] = [
                'module_id' => $module['module_id'],
                'name'      => $module['name'],
                'edit'      => $this->url->link('extension/module/my_module/edit',
                    'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'], true),
                'delete'    => $this->url->link('extension/module/my_module/delete',
                    'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module['module_id'], true),
            ];
        }

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');
        $data['user_token']  = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('extension/module/my_module_list', $data));
    }

    public function add(): void {
        $this->load->language('extension/module/my_module');
        $this->load->model('setting/module');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->model_setting_module->addModule([
                'name'    => $this->request->post['name'],
                'code'    => 'my_module',
                'setting' => $this->request->post,   // OC encodes as JSON internally
            ]);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/my_module',
                'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function edit(): void {
        $this->load->language('extension/module/my_module');
        $this->load->model('setting/module');

        if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
            $this->model_setting_module->editModule($this->request->get['module_id'], [
                'name'    => $this->request->post['name'],
                'code'    => 'my_module',
                'setting' => $this->request->post,
            ]);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('extension/module/my_module',
                'user_token=' . $this->session->data['user_token'], true));
        }

        $this->getForm();
    }

    public function delete(): void {
        $this->load->language('extension/module/my_module');
        $this->load->model('setting/module');

        if (isset($this->request->get['module_id']) && $this->validateDelete()) {
            $this->model_setting_module->deleteModule($this->request->get['module_id']);
            $this->session->data['success'] = $this->language->get('text_success');
        }
        $this->response->redirect($this->url->link('extension/module/my_module',
            'user_token=' . $this->session->data['user_token'], true));
    }

    protected function getForm(): void {
        $this->document->setTitle($this->language->get('heading_title'));

        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['error_name']    = $this->error['name']    ?? '';

        $data['breadcrumbs'] = $this->buildBreadcrumbs();

        if (!isset($this->request->get['module_id'])) {
            $data['action'] = $this->url->link('extension/module/my_module/add',
                'user_token=' . $this->session->data['user_token'], true);
        } else {
            $data['action'] = $this->url->link('extension/module/my_module/edit',
                'user_token=' . $this->session->data['user_token'] . '&module_id=' . $this->request->get['module_id'], true);
        }
        $data['cancel'] = $this->url->link('extension/module/my_module',
            'user_token=' . $this->session->data['user_token'], true);

        // Load existing instance settings
        $module_info = [];
        if (isset($this->request->get['module_id'])) {
            $this->load->model('setting/module');
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
            // $module_info['setting'] is already JSON-decoded by OC into an array
        }

        // POST > DB > default
        $data['name']   = $this->request->post['name']   ?? $module_info['name']   ?? '';
        $data['status'] = $this->request->post['status'] ?? $module_info['setting']['status'] ?? 0;
        $data['limit']  = $this->request->post['limit']  ?? $module_info['setting']['limit']  ?? 4;

        $data['header']      = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer']      = $this->load->controller('common/footer');
        $data['user_token']  = $this->session->data['user_token'];

        $this->response->setOutput($this->load->view('extension/module/my_module_form', $data));
    }

    protected function validate(): bool {
        if (!$this->user->hasPermission('modify', 'extension/module/my_module')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (empty($this->request->post['name'])) {
            $this->error['name'] = $this->language->get('error_name');
        }
        return !$this->error;
    }

    protected function validateDelete(): bool {
        if (!$this->user->hasPermission('modify', 'extension/module/my_module')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        return !$this->error;
    }

    public function install(): void {
        $this->load->model('extension/module/my_module');
        $this->model_extension_module_my_module->install();
    }

    public function uninstall(): void {
        $this->load->model('extension/module/my_module');
        $this->model_extension_module_my_module->uninstall();
    }

    private function buildBreadcrumbs(): array {
        return [
            [
                'text' => $this->language->get('text_home'),
                'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
            ],
            [
                'text' => $this->language->get('text_extension'),
                'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true),
            ],
            [
                'text' => $this->language->get('heading_title'),
                'href' => $this->url->link('extension/module/my_module', 'user_token=' . $this->session->data['user_token'], true),
            ],
        ];
    }
}
```

---

## Admin Model

`install()` and `uninstall()` live HERE (admin model), not in the catalog model.

```php
<?php
// admin/model/extension/module/my_module.php

class ModelExtensionModuleMyModule extends Model {

    public function install(): void {
        $this->db->query("
            CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "my_module` (
                `id`         INT(11)      NOT NULL AUTO_INCREMENT,
                `name`       VARCHAR(255) NOT NULL,
                `status`     TINYINT(1)   NOT NULL DEFAULT 1,
                `sort_order` INT(11)      NOT NULL DEFAULT 0,
                `date_added` DATETIME     NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");

        // Register any events
        $this->load->model('setting/event');
        $this->model_setting_event->addEvent(
            'my_module',
            'catalog/controller/common/header/before',
            'extension/module/my_module/eventHeader'
        );
    }

    public function uninstall(): void {
        $this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "my_module`");

        $this->load->model('setting/event');
        $this->model_setting_event->deleteEventByCode('my_module');
    }

    public function getItems(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "my_module` WHERE 1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        $sql .= " ORDER BY `sort_order` ASC, `date_added` DESC";

        if (isset($data['start']) || isset($data['limit'])) {
            $sql .= " LIMIT " . (int)($data['start'] ?? 0) . ", " . (int)($data['limit'] ?? 20);
        }

        return $this->db->query($sql)->rows;
    }

    public function getTotalItems(array $data = []): int {
        $sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "my_module` WHERE 1";

        if (!empty($data['filter_name'])) {
            $sql .= " AND `name` LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        return (int)$this->db->query($sql)->row['total'];
    }

    public function addItem(array $data): int {
        $this->db->query("
            INSERT INTO `" . DB_PREFIX . "my_module`
            SET `name`       = '" . $this->db->escape($data['name']) . "',
                `status`     = '" . (int)$data['status'] . "',
                `sort_order` = '" . (int)($data['sort_order'] ?? 0) . "',
                `date_added` = NOW()
        ");
        return $this->db->getLastId();
    }

    public function editItem(int $id, array $data): void {
        $this->db->query("
            UPDATE `" . DB_PREFIX . "my_module`
            SET `name`       = '" . $this->db->escape($data['name']) . "',
                `status`     = '" . (int)$data['status'] . "',
                `sort_order` = '" . (int)($data['sort_order'] ?? 0) . "'
            WHERE `id` = '" . (int)$id . "'
        ");
    }

    public function deleteItem(int $id): void {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "my_module` WHERE `id` = '" . (int)$id . "'");
    }
}
```

---

## Catalog Controller — Layout Module (Fragment)

The most common pattern. Called by OC's layout system with `?module_id=X` (multi-instance) or without (single-instance). **Never include header/footer here.**

```php
<?php
// catalog/controller/extension/module/my_module.php

class ControllerExtensionModuleMyModule extends Controller {

    // Layout module: returns HTML fragment
    public function index(): string {
        $this->load->language('extension/module/my_module');

        // Multi-instance: read settings from oc_module
        if (isset($this->request->get['module_id'])) {
            $this->load->model('setting/module');
            $module_info = $this->model_setting_module->getModule($this->request->get['module_id']);
            $setting = $module_info['setting'] ?? [];  // already decoded
        }

        // Single-instance: read from oc_setting via config
        $status = $setting['status'] ?? $this->config->get('module_my_module_status');
        if (!$status) {
            return '';  // module disabled — return empty string
        }

        $this->load->model('extension/module/my_module');

        $limit = (int)($setting['limit'] ?? $this->config->get('module_my_module_limit') ?? 4);
        $data['items'] = $this->model_extension_module_my_module->getActiveItems(['limit' => $limit]);

        if (!$data['items']) {
            return '';
        }

        $data['heading_title'] = $this->language->get('heading_title');

        return $this->load->view('extension/module/my_module', $data);
    }

    // Event handler (if registered in install())
    public function eventHeader(string &$route, array &$data, mixed &$output): void {
        // Inject something into the header data array
        $data['my_module_data'] = 'value';
    }
}
```

---

## Catalog Controller — Full Custom Page

For when the module adds a completely new page (not placed in a layout). Includes header/footer.

```php
<?php
class ControllerExtensionModuleMyModule extends Controller {

    public function index(): string {
        $this->load->language('extension/module/my_module');
        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('extension/module/my_module');

        // Breadcrumbs for SEO
        $data['breadcrumbs'] = [
            ['text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home')],
            ['text' => $this->language->get('heading_title'), 'href' => $this->url->link('extension/module/my_module')],
        ];

        $data['items'] = $this->model_extension_module_my_module->getActiveItems();
        $data['heading_title'] = $this->language->get('heading_title');

        // Full page: include layout blocks
        $data['column_left']    = $this->load->controller('common/column_left');
        $data['column_right']   = $this->load->controller('common/column_right');
        $data['content_top']    = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer']         = $this->load->controller('common/footer');
        $data['header']         = $this->load->controller('common/header');

        return $this->load->view('extension/module/my_module', $data);
    }
}
```

---

## Catalog Model

Front-end queries only. No `install()`/`uninstall()` here.

```php
<?php
// catalog/model/extension/module/my_module.php

class ModelExtensionModuleMyModule extends Model {

    public function getActiveItems(array $data = []): array {
        $sql = "SELECT * FROM `" . DB_PREFIX . "my_module` WHERE `status` = 1 ORDER BY `sort_order` ASC";

        if (!empty($data['limit'])) {
            $sql .= " LIMIT " . (int)$data['limit'];
        }

        return $this->db->query($sql)->rows;
    }

    public function getItem(int $id): array {
        return $this->db->query(
            "SELECT * FROM `" . DB_PREFIX . "my_module` WHERE `id` = '" . (int)$id . "' AND `status` = 1"
        )->row;
    }
}
```

---

## Language Files

### Admin — English (`admin/language/en-gb/extension/module/my_module.php`)

```php
<?php
$_['heading_title']    = 'My Module';
$_['text_extension']   = 'Extensions';
$_['text_success']     = 'Success: You have modified My Module!';
$_['text_edit']        = 'Edit My Module';
$_['text_enabled']     = 'Enabled';
$_['text_disabled']    = 'Disabled';
$_['entry_status']     = 'Status';
$_['entry_api_key']    = 'API Key';
$_['button_save']      = 'Save';
$_['button_cancel']    = 'Cancel';
$_['error_permission'] = 'Warning: You do not have permission to modify My Module!';
$_['error_warning']    = 'Warning: Please check the form carefully for errors!';
$_['error_api_key']    = 'API Key required!';
```

### Admin — Greek (`admin/language/el-gr/extension/module/my_module.php`)

```php
<?php
$_['heading_title']    = 'My Module';
$_['text_extension']   = 'Επεκτάσεις';
$_['text_success']     = 'Επιτυχία: Το My Module ενημερώθηκε!';
$_['text_edit']        = 'Επεξεργασία My Module';
$_['text_enabled']     = 'Ενεργό';
$_['text_disabled']    = 'Ανενεργό';
$_['entry_status']     = 'Κατάσταση';
$_['entry_api_key']    = 'API Key';
$_['button_save']      = 'Αποθήκευση';
$_['button_cancel']    = 'Ακύρωση';
$_['error_permission'] = 'Προσοχή: Δεν έχετε δικαίωμα τροποποίησης!';
$_['error_warning']    = 'Προσοχή: Ελέγξτε τη φόρμα για σφάλματα!';
$_['error_api_key']    = 'Απαιτείται API Key!';
```

### Catalog — English (`catalog/language/en-gb/extension/module/my_module.php`)

```php
<?php
$_['heading_title']   = 'My Module';
$_['text_no_results'] = 'No items found.';
```

### Catalog — Greek (`catalog/language/el-gr/extension/module/my_module.php`)

```php
<?php
$_['heading_title']   = 'My Module';
$_['text_no_results'] = 'Δεν βρέθηκαν αποτελέσματα.';
```

---

## Admin Twig — Settings Form

```twig
{# admin/view/template/extension/module/my_module.twig #}
{{ header }}{{ column_left }}
<div id="content">
  <div class="page-header">
    <div class="container-fluid">
      <div class="pull-right">
        <button type="submit" form="form-my-module" class="btn btn-primary">
          <i class="fa fa-save"></i> {{ button_save }}
        </button>
        <a href="{{ cancel }}" class="btn btn-default">
          <i class="fa fa-reply"></i> {{ button_cancel }}
        </a>
      </div>
      <h1>{{ heading_title }}</h1>
      <ul class="breadcrumb">
        {% for breadcrumb in breadcrumbs %}
          <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
        {% endfor %}
      </ul>
    </div>
  </div>

  <div class="container-fluid">
    {% if error_warning %}
      <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {{ error_warning }}</div>
    {% endif %}
    {% if success %}
      <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ success }}</div>
    {% endif %}

    <div class="panel panel-default">
      <div class="panel-heading"><h3 class="panel-title">{{ text_edit }}</h3></div>
      <div class="panel-body">
        <form action="{{ action }}" method="post" id="form-my-module" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 control-label">{{ entry_api_key }}</label>
            <div class="col-sm-10">
              <input type="text" name="module_my_module_api_key"
                value="{{ module_my_module_api_key }}" class="form-control"/>
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 control-label">{{ entry_status }}</label>
            <div class="col-sm-10">
              <select name="module_my_module_status" class="form-control">
                <option value="1" {{ module_my_module_status ? 'selected' : '' }}>{{ text_enabled }}</option>
                <option value="0" {{ not module_my_module_status ? 'selected' : '' }}>{{ text_disabled }}</option>
              </select>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
{{ footer }}
```

---

## Catalog Twig — Module Fragment

```twig
{# catalog/view/theme/default/template/extension/module/my_module.twig #}
{# NO header/footer — this is a fragment embedded in a layout #}

<div class="my-module">
  <h3>{{ heading_title }}</h3>
  {% for item in items %}
    <div class="my-module-item">
      <span>{{ item.name }}</span>
    </div>
  {% else %}
    <p>{{ text_no_results }}</p>
  {% endfor %}
</div>
```

---

## Catalog Twig — Full Page

```twig
{# catalog/view/theme/default/template/extension/module/my_module.twig #}
{{ header }}
<div class="container">
  <ul class="breadcrumb">
    {% for breadcrumb in breadcrumbs %}
      <li><a href="{{ breadcrumb.href }}">{{ breadcrumb.text }}</a></li>
    {% endfor %}
  </ul>

  <div class="row">
    {% if column_left %}
    <aside class="col-sm-3">{{ column_left }}</aside>
    {% endif %}

    <div id="content" class="{{ column_left or column_right ? 'col-sm-9' : 'col-sm-12' }}">
      {{ content_top }}
      <h1>{{ heading_title }}</h1>
      {% for item in items %}
        <div>{{ item.name }}</div>
      {% else %}
        <p>{{ text_no_results }}</p>
      {% endfor %}
      {{ content_bottom }}
    </div>

    {% if column_right %}
    <aside class="col-sm-3">{{ column_right }}</aside>
    {% endif %}
  </div>
</div>
{{ footer }}
```

---

## Module Instances — `model/setting/module` API

Used by multi-instance modules. The `setting` field is stored as JSON in `oc_module`.

```php
// Load the model
$this->load->model('setting/module');

// Get single instance (returns array with 'setting' already decoded)
$module_info = $this->model_setting_module->getModule(int $module_id): array;
// $module_info['module_id'], ['name'], ['code'], ['setting'] (array)

// Get all instances for a module type
$instances = $this->model_setting_module->getModulesByCode('my_module'): array;

// Create new instance — returns new module_id
$module_id = $this->model_setting_module->addModule([
    'name'    => 'Banner – Homepage Hero',   // human label shown in Layout editor
    'code'    => 'my_module',
    'setting' => $this->request->post,        // OC json_encodes internally
]): int;

// Update instance
$this->model_setting_module->editModule(int $module_id, array $data): void;

// Delete instance
$this->model_setting_module->deleteModule(int $module_id): void;
```

**`oc_module` table schema:**

| Column | Type | Notes |
|--------|------|-------|
| `module_id` | INT AUTO_INCREMENT | PK |
| `name` | VARCHAR(64) | Shown in Layout editor dropdown |
| `code` | VARCHAR(32) | Must match extension code e.g. `my_module` |
| `setting` | TEXT | JSON-encoded settings |

**Catalog: read instance settings**

```php
// The layout system passes ?module_id=X when calling the catalog controller
$module_id = (int)($this->request->get['module_id'] ?? 0);

if ($module_id) {
    $this->load->model('setting/module');
    $module_info = $this->model_setting_module->getModule($module_id);
    $status = $module_info['setting']['status'] ?? 0;
    $limit  = $module_info['setting']['limit']  ?? 4;
} else {
    // Fallback to global config (single-instance mode)
    $status = $this->config->get('module_my_module_status');
}
```

---

## AJAX / JSON Response

Used in admin for inline actions (toggle status, autocomplete, delete without page reload).

```php
// In admin controller action method
public function autocomplete(): void {
    $this->load->model('extension/module/my_module');

    $json = [];

    if (isset($this->request->get['filter_name'])) {
        $results = $this->model_extension_module_my_module->getItems([
            'filter_name' => $this->request->get['filter_name'],
            'limit'       => 10,
        ]);

        foreach ($results as $result) {
            $json[] = [
                'id'   => $result['id'],
                'name' => html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'),
            ];
        }
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}

// Toggle status — called via AJAX
public function toggleStatus(): void {
    $this->load->model('extension/module/my_module');

    $json = ['error' => ''];

    if (!$this->user->hasPermission('modify', 'extension/module/my_module')) {
        $json['error'] = $this->language->get('error_permission');
    } else {
        $id     = (int)($this->request->post['id'] ?? 0);
        $status = (int)($this->request->post['status'] ?? 0);
        $this->model_extension_module_my_module->editStatus($id, $status);
        $json['success'] = true;
    }

    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput(json_encode($json));
}
```

**jQuery AJAX call in Twig:**

```twig
<script>
$.ajax({
    url: 'index.php?route=extension/module/my_module/autocomplete&user_token={{ user_token }}',
    type: 'get',
    data: { filter_name: searchTerm },
    dataType: 'json',
    success: function(json) {
        // handle results
    }
});
</script>
```

---

## `$this->document` API

Call these in controllers before loading the view.

```php
// Page <title>
$this->document->setTitle('Page Title');

// <meta> tags
$this->document->setDescription('Meta description');
$this->document->setKeywords('keyword1, keyword2');
$this->document->setRobots('index, follow');   // or 'noindex, nofollow'

// CSS (path relative to DIR_TEMPLATE or absolute URL)
$this->document->addStyle('catalog/view/theme/default/stylesheet/my_module.css');

// JS — position: 'header' | 'footer'
$this->document->addScript('catalog/view/javascript/my_module.js', 'footer');

// Canonical URL (for SEO)
$this->document->addLink($this->url->link('extension/module/my_module'), 'canonical');

// In Twig, the common/header controller renders all added styles/scripts automatically.
// No manual output needed.
```

---

## Events System

### Register in `install()` (admin model)

```php
$this->load->model('setting/event');
$this->model_setting_event->addEvent(
    'my_module',                                    // unique code for this module
    'catalog/controller/common/header/before',      // trigger point
    'extension/module/my_module/eventHeader'        // handler route
);
```

### Common trigger points

| Trigger | When called |
|---------|-------------|
| `catalog/controller/common/header/before` | Before header controller runs |
| `catalog/controller/common/header/after` | After header output is built |
| `catalog/controller/product/product/before` | Before product page |
| `catalog/controller/product/product/after` | After product page |
| `catalog/model/catalog/product/getProduct/after` | After getProduct() returns |
| `admin/controller/catalog/product/before` | Before admin product save |

### Handler signature

```php
// catalog/controller/extension/module/my_module.php
public function eventHeader(string &$route, array &$data, mixed &$output): void {
    // $route   — the route string being dispatched
    // $data    — the data array passed to the controller/view
    // $output  — the rendered output (string) for /after events
    $data['my_module_token'] = 'value';
}
```

### Unregister in `uninstall()` (admin model)

```php
$this->load->model('setting/event');
$this->model_setting_event->deleteEventByCode('my_module');
```

---

## URL Helpers

```php
// Catalog URL (no SSL param needed for links)
$url = $this->url->link('extension/module/my_module');
$url = $this->url->link('extension/module/my_module', 'id=42');

// With HTTPS (third param true)
$url = $this->url->link('extension/module/my_module', '', true);

// Admin URL — always needs user_token
$url = $this->url->link(
    'extension/module/my_module',
    'user_token=' . $this->session->data['user_token'],
    true
);

// Admin URL with extra params
$url = $this->url->link(
    'extension/module/my_module/edit',
    'user_token=' . $this->session->data['user_token'] . '&module_id=' . $module_id,
    true
);

// Redirect (catalog or admin)
$this->response->redirect($url);
$this->response->redirect($this->url->link('common/home'));
```

---

## Session / Request / Cookie

```php
// Session — read
$value = $this->session->data['my_key'] ?? null;

// Session — write
$this->session->data['my_key'] = 'value';

// Session — delete
unset($this->session->data['my_key']);

// GET
$id   = (int)($this->request->get['id'] ?? 0);
$name = $this->request->get['name'] ?? '';

// POST — always escape before DB use
$name = $this->db->escape($this->request->post['name'] ?? '');

// POST — numeric
$qty = (int)($this->request->post['qty'] ?? 1);

// Request method check
if ($this->request->server['REQUEST_METHOD'] === 'POST') { ... }

// Cookie (write via header — OC has no cookie helper)
$this->response->addHeader(
    'Set-Cookie: my_cookie=' . urlencode($value) . '; Path=/; HttpOnly; SameSite=Lax'
);

// Cookie (read)
$value = $this->request->cookie['my_cookie'] ?? '';
```

---

## Config / Settings

```php
// Read global config (oc_setting, store_id = 0)
$status = $this->config->get('module_my_module_status');  // returns null if not set
$limit  = (int)$this->config->get('module_my_module_limit');

// Naming convention: {group}_{key}
// group = 'module_my_module'  →  keys saved as 'module_my_module_status' etc.

// Save settings (admin controller, after POST)
$this->load->model('setting/setting');
$this->model_setting_setting->editSetting('module_my_module', $this->request->post);
// OC saves every key in $post as group_key in oc_setting

// Read single setting directly from model (useful when config cache is stale)
$this->load->model('setting/setting');
$value = $this->model_setting_setting->getSettingValue('module_my_module_api_key');

// Multi-store: save for specific store
$this->model_setting_setting->editSetting('module_my_module', $this->request->post, $store_id);
```

---

## OCMOD Reference

### File structure

```xml
<?xml version="1.0" encoding="utf-8"?>
<modification>
  <name>My Module — Header Modification</name>
  <code>my_module_header</code>
  <version>1.0.0</version>
  <author>Your Name</author>
  <link>https://yoursite.com</link>

  <file path="catalog/controller/common/header.php">
    <operation>
      <search><![CDATA[// search string with enough context to be unique]]></search>
      <add position="after"><![CDATA[
// new code here
$data['my_variable'] = 'value';
      ]]></add>
    </operation>
  </file>

  <file path="catalog/view/theme/*/template/common/header.twig">
    <operation>
      <search><![CDATA[<div id="header">]]></search>
      <add position="before"><![CDATA[
<div id="my-banner">{{ my_variable }}</div>
      ]]></add>
    </operation>
  </file>
</modification>
```

### Position values

| Value | Effect |
|-------|--------|
| `before` | Insert before the matched string |
| `after` | Insert after the matched string |
| `replace` | Replace the matched string entirely |

### Key rules

- Always wrap with `<![CDATA[ ]]>`
- `<search>` must match exactly one location — include enough surrounding lines for uniqueness
- Wildcard `*` works in `path` (useful for theme-agnostic Twig mods)
- After upload: Admin → Extensions → Modifications → **Refresh**
- Test on clean OC before committing — OCMOD errors fail silently

---

## Common Pitfalls

| Pitfall | Fix |
|---------|-----|
| Class name doesn't match file path | `ControllerExtensionModuleMyModule` must be at `extension/module/my_module.php` exactly — OC autoloader is strict |
| `install()` not being called | Must be in the **admin** model, not catalog — OC extension system calls `admin/controller/extension/module/my_module→install()` which should delegate to admin model |
| Layout module showing blank | Check: status enabled, `return ''` on empty data, correct template path, Journal cache cleared |
| Module not showing in Layouts | Must be registered in `oc_extension` table — installing via admin extension page does this automatically |
| Twig cache shows old template | Clear `system/storage/cache/` |
| `model_extension_module_my_module` not found | Check `$this->load->model('extension/module/my_module')` is called first |
| OCMOD not applying | Modifications → Refresh; check XML is valid; verify `<search>` string matches exactly |
| `user_token` missing in admin redirect | Always append `user_token=` . `$this->session->data['user_token']` to every admin URL |
| Settings not saving | Check `editSetting()` group name matches config key prefix (`module_my_module`) |
| PHP 8.x deprecation: `${var}` | Replace with `{$var}` in strings; remove `create_function`; use `str_contains` / `str_starts_with` |
| Journal Twig not found | Add template under `journal3/` path; Journal falls back to `default/` if not found |
| Multi-instance: wrong settings loading | Always read from `model/setting/module→getModule($module_id)`, not from `$this->config` |
