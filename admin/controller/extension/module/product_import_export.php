<?php
class ControllerExtensionModuleProductImportExport extends Controller {

        private $error = array();

        private $filter_keys = array(
                'filter_name',
                'filter_model',
                'filter_sku',
                'filter_ean',
                'filter_price_min',
                'filter_price_max',
                'filter_quantity_min',
                'filter_quantity_max',
                'filter_weight_min',
                'filter_weight_max',
                'filter_category_id',
                'filter_manufacturer_id',
                'filter_special',
                'filter_status',
                'sort',
                'order'
        );

        // Attribute columns για export/import
        private $attribute_columns = array(
                'Φύλο',
                'Αξεσουάρ',
                'Υλικό',
                'Σειρά',
                'Χρώμα',
                'Διάσταση',
                'Αδιάβροχο',
                'Διάμετρος Κάσας',
                'Κάντραν',
                'Εγγύηση',
                'Κρύσταλο',
                'Μηχανισμός',
                'Υλικό Δεσίματος',
                'Κάσα',
                'Δέσιμο',
                'Σχήμα Πλαισίου',
                'Χρώμα Δεσίματος',
                'Μέγεθος Δαχτυλιδιού',
                'Χρώμα Πλαισίου',
        );

        public function index() {
                $this->load->language('extension/module/product_import_export');

                $this->document->setTitle($this->language->get('heading_title'));

                if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
                        $this->load->model('setting/setting');

                        $this->model_setting_setting->editSetting('module_product_import_export', $this->request->post);

                        $this->session->data['success'] = $this->language->get('text_success');

                        $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
                }

                $data['breadcrumbs'] = array();

                $data['breadcrumbs'][] = array(
                        'text' => $this->language->get('text_home'),
                        'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
                );

                $data['breadcrumbs'][] = array(
                        'text' => $this->language->get('text_extension'),
                        'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
                );

                $data['breadcrumbs'][] = array(
                        'text' => $this->language->get('heading_title'),
                        'href' => $this->url->link('extension/module/product_import_export', 'user_token=' . $this->session->data['user_token'], true)
                );

                $data['action'] = $this->url->link('extension/module/product_import_export', 'user_token=' . $this->session->data['user_token'], true);

                $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

                $data['product_list'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true);

                $data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

                if (isset($this->session->data['success'])) {
                        $data['success'] = $this->session->data['success'];

                        unset($this->session->data['success']);
                } else {
                        $data['success'] = '';
                }

                if (isset($this->request->post['module_product_import_export_status'])) {
                        $data['module_product_import_export_status'] = $this->request->post['module_product_import_export_status'];
                } else {
                        $data['module_product_import_export_status'] = $this->config->get('module_product_import_export_status');
                }

                $data['header'] = $this->load->controller('common/header');
                $data['column_left'] = $this->load->controller('common/column_left');
                $data['footer'] = $this->load->controller('common/footer');

                $this->response->setOutput($this->load->view('extension/module/product_import_export', $data));
        }

        public function install() {
                $this->load->model('extension/module/product_import_export');

                $this->model_extension_module_product_import_export->install();
        }

        public function uninstall() {
                $this->load->model('extension/module/product_import_export');

                $this->model_extension_module_product_import_export->uninstall();
        }

        public function export() {
                if (!$this->user->hasPermission('access', 'extension/module/product_import_export')) {
                        $this->response->redirect($this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'], true));
                }

                $this->load->model('catalog/product');

                $filter_data = array();

                foreach ($this->filter_keys as $key) {
                        if (isset($this->request->get[$key]) && $this->request->get[$key] !== '') {
                                $filter_data[$key] = $this->request->get[$key];
                        }
                }

                $products = $this->model_catalog_product->getProducts($filter_data);

                // Φόρτωσε όλα τα attributes για τα προϊόντα
                $product_ids = array_column($products, 'product_id');
                $all_attributes = array();

                if (!empty($product_ids)) {
                        $ids_str = implode(',', array_map('intval', $product_ids));
                        $attr_query = $this->db->query("
                                SELECT pa.product_id, ad.name, pa.text
                                FROM " . DB_PREFIX . "product_attribute pa
                                JOIN " . DB_PREFIX . "attribute_description ad ON ad.attribute_id = pa.attribute_id AND ad.language_id = 1
                                WHERE pa.product_id IN (" . $ids_str . ")
                                AND pa.language_id = 1
                                AND ad.name IN ('" . implode("','", array_map(array($this->db, 'escape'), $this->attribute_columns)) . "')
                        ");

                        foreach ($attr_query->rows as $row) {
                                $all_attributes[$row['product_id']][$row['name']] = $row['text'];
                        }
                }

                $handle = fopen('php://temp', 'w+');

                // Header με attribute columns (χωρίς special_price)
                $header = array('product_id', 'name', 'model', 'sku', 'ean', 'quantity', 'price', 'status', 'manufacturer', 'weight', 'date_modified');
                foreach ($this->attribute_columns as $attr_col) {
                        $header[] = 'attr_' . $attr_col;
                }
                fputcsv($handle, $header, ';');

                foreach ($products as $product) {
                        $price = (float)$product['price'];
                        $active_price = (float)$product['active_price'];

                        $row = array(
                                $product['product_id'],
                                html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'),
                                $product['model'],
                                $product['sku'],
                                $product['ean'],
                                $product['quantity'],
                                $this->formatDecimal($price),
                                $product['status'],
                                isset($product['manufacturer_name']) ? $product['manufacturer_name'] : '',
                                $this->formatDecimal($product['weight']),
                                $product['date_modified']
                        );

                        // Πρόσθεσε attribute values
                        $pid = $product['product_id'];
                        foreach ($this->attribute_columns as $attr_col) {
                                $row[] = isset($all_attributes[$pid][$attr_col]) ? $all_attributes[$pid][$attr_col] : '';
                        }

                        fputcsv($handle, $row, ';');
                }

                rewind($handle);

                $csv = stream_get_contents($handle);

                fclose($handle);

                $this->response->addHeader('Content-Type: text/csv; charset=utf-8');
                $this->response->addHeader('Content-Disposition: attachment; filename="products_' . date('Y-m-d_His') . '.csv"');
                $this->response->addHeader('Pragma: no-cache');
                $this->response->addHeader('Expires: 0');

                $this->response->setOutput("\xEF\xBB\xBF" . $csv);
        }

        public function import() {
                $this->load->language('extension/module/product_import_export');

                $json = array();

                if (!$this->user->hasPermission('modify', 'extension/module/product_import_export')) {
                        $json['error'] = $this->language->get('error_permission');
                } elseif (!isset($this->request->files['import_file']) || ($this->request->files['import_file']['error'] != UPLOAD_ERR_OK) || !is_uploaded_file($this->request->files['import_file']['tmp_name'])) {
                        $json['error'] = $this->language->get('error_upload');
                } elseif (!in_array(strtolower(pathinfo($this->request->files['import_file']['name'], PATHINFO_EXTENSION)), array('csv', 'txt'))) {
                        $json['error'] = $this->language->get('error_filetype');
                } else {
                        $this->load->model('extension/module/product_import_export');

                        $result = $this->model_extension_module_product_import_export->import($this->request->files['import_file']['tmp_name']);

                        if (isset($result['error'])) {
                                $json['error'] = $this->language->get($result['error']);
                        } else {
                                $json['success'] = sprintf($this->language->get('text_import_result'), $result['updated'], $result['created'], $result['skipped']);
                                $json['row_errors'] = $result['errors'];
                        }
                }

                $this->response->addHeader('Content-Type: application/json');
                $this->response->setOutput(json_encode($json));
        }

        public function eventProductListAfter(&$route, &$data, &$output) {
                if (!$this->config->get('module_product_import_export_status')) {
                        return;
                }

                if (!$this->user->hasPermission('access', 'extension/module/product_import_export')) {
                        return;
                }

                $anchor = '<i class="fa fa-plus"></i></a>';

                $position = strpos($output, $anchor);

                if ($position === false) {
                        return;
                }

                $this->load->language('extension/module/product_import_export');

                $url = '';

                foreach ($this->filter_keys as $key) {
                        if (isset($this->request->get[$key]) && $this->request->get[$key] !== '') {
                                $url .= '&' . $key . '=' . urlencode(html_entity_decode($this->request->get[$key], ENT_QUOTES, 'UTF-8'));
                        }
                }

                $export = $this->url->link('extension/module/product_import_export/export', 'user_token=' . $this->session->data['user_token'] . $url, true);
                $import = $this->url->link('extension/module/product_import_export/import', 'user_token=' . $this->session->data['user_token'], true);

                $buttons = ' <button type="button" data-toggle="tooltip" title="' . $this->language->get('button_import') . '" class="btn btn-info" onclick="$(\'#modal-product-import\').modal(\'show\');"><i class="fa fa-upload"></i></button>';
                $buttons .= ' <a href="' . $export . '" data-toggle="tooltip" title="' . $this->language->get('button_export') . '" class="btn btn-success"><i class="fa fa-download"></i></a>';

                $position += strlen($anchor);

                $output = substr($output, 0, $position) . $buttons . substr($output, $position);

                $modal = $this->importModal(html_entity_decode($import, ENT_QUOTES, 'UTF-8'));

                if (strpos($output, '</body>') !== false) {
                        $output = str_replace('</body>', $modal . "\n</body>", $output);
                } else {
                        $output .= $modal;
                }
        }

        protected function validate() {
                if (!$this->user->hasPermission('modify', 'extension/module/product_import_export')) {
                        $this->error['warning'] = $this->language->get('error_permission');
                }

                return !$this->error;
        }

        private function formatDecimal($value) {
                return (string)(float)$value;
        }

        private function importModal($import_url) {
                $html = <<<'HTML'
<div class="modal fade" id="modal-product-import" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal">&times;</button>
        <h4 class="modal-title"><i class="fa fa-upload"></i> {text_import_title}</h4>
      </div>
      <div class="modal-body">
        <div id="product-import-result"></div>
        <form id="form-product-import">
          <div class="form-group">
            <label class="control-label" for="input-product-import-file">{entry_import_file}</label>
            <input type="file" name="import_file" accept=".csv,.txt" id="input-product-import-file" />
          </div>
        </form>
        <p class="help-block">{text_import_help}</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{button_close}</button>
        <button type="button" id="button-product-import-run" class="btn btn-primary"><i class="fa fa-upload"></i> {button_import_run}</button>
      </div>
    </div>
  </div>
</div>
<script type="text/javascript">
$('#button-product-import-run').on('click', function() {
        var node = document.getElementById('input-product-import-file');

        if (!node.files.length) {
                $('#product-import-result').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> {error_file}</div>');
                return;
        }

        var formData = new FormData();
        formData.append('import_file', node.files[0]);

        $.ajax({
                url: '{import_url}',
                type: 'post',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                beforeSend: function() {
                        $('#button-product-import-run').prop('disabled', true).find('i').attr('class', 'fa fa-circle-o-notch fa-spin');
                },
                complete: function() {
                        $('#button-product-import-run').prop('disabled', false).find('i').attr('class', 'fa fa-upload');
                },
                success: function(json) {
                        var html = '';

                        if (json.error) {
                                html += '<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + json.error + '</div>';
                        }

                        if (json.success) {
                                html += '<div class="alert alert-success"><i class="fa fa-check-circle"></i> ' + json.success + '</div>';

                                if (json.row_errors && json.row_errors.length) {
                                        html += '<div class="alert alert-warning"><ul style="margin-bottom: 0; padding-left: 18px;">';

                                        for (var i = 0; i < json.row_errors.length; i++) {
                                                html += '<li>' + json.row_errors[i] + '</li>';
                                        }

                                        html += '</ul></div>';
                                }

                                $('#modal-product-import').data('imported', true);
                        }

                        $('#product-import-result').html(html);
                },
                error: function(xhr, status, error) {
                        $('#product-import-result').html('<div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i> ' + error + '</div>');
                }
        });
});

$('#modal-product-import').on('hidden.bs.modal', function() {
        if ($(this).data('imported')) {
                location.reload();
        }
});
</script>
HTML;

                return str_replace(array(
                        '{import_url}',
                        '{text_import_title}',
                        '{entry_import_file}',
                        '{text_import_help}',
                        '{button_import_run}',
                        '{button_close}',
                        '{error_file}'
                ), array(
                        $import_url,
                        $this->language->get('text_import_title'),
                        $this->language->get('entry_import_file'),
                        $this->language->get('text_import_help'),
                        $this->language->get('button_import_run'),
                        $this->language->get('button_close'),
                        $this->language->get('error_file')
                ), $html);
        }
}
