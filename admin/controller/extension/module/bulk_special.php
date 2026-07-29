<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

class ControllerExtensionModuleBulkSpecial extends Controller
{
    private $error = [];

    public function index()
    {
        $this->load->language('extension/module/bulk_special');
        $this->load->model('extension/module/bulk_special');

        $this->document->setTitle($this->language->get('heading_title'));

        // Breadcrumbs
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/bulk_special', 'user_token=' . $this->session->data['user_token'], true)
        ];

        // Actions
        $data['add'] = $this->url->link(
            'extension/module/bulk_special/add',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        $data['delete'] = $this->url->link(
            'extension/module/bulk_special/delete',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        // Messages
        $data['error_warning'] = $this->error['warning'] ?? '';
        $data['success'] = $this->session->data['success'] ?? '';
        unset($this->session->data['success']);

        // Data
        $data['bulk_specials'] = [];

        foreach ($this->model_extension_module_bulk_special->getBulkSpecials() as $result) {
            $data['bulk_specials'][] = [
                'bulk_special_id' => $result['bulk_special_id'],
                'name' => $result['name'],
                'discount_percent' => $result['discount_percent'],
                'customer_group_ids' => json_decode($result['customer_group_ids'] ?? '[]', true),
                'date_start' => $result['date_start'],
                'date_end' => $result['date_end'],
                'status' => $result['status'],
                'date_modified' => $result['date_modified'],
                'edit' => $this->url->link(
                    'extension/module/bulk_special/edit',
                    'user_token=' . $this->session->data['user_token'] . '&bulk_special_id=' . $result['bulk_special_id'],
                    true
                )
            ];
        }

        // Layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/module/bulk_special_list', $data)
        );
    }

    public function add()
    {
        $this->load->language('extension/module/bulk_special');
        $this->load->model('extension/module/bulk_special');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_extension_module_bulk_special->addBulkSpecial($this->request->post);

            $this->session->data['success'] = $this->language->get('text_success_add');

            $this->response->redirect(
                $this->url->link('extension/module/bulk_special', 'user_token=' . $this->session->data['user_token'], true)
            );
        }

        $this->getForm();
    }

    public function edit()
    {
        $this->load->language('extension/module/bulk_special');
        $this->load->model('extension/module/bulk_special');

        if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
            $this->model_extension_module_bulk_special->editBulkSpecial(
                (int)$this->request->get['bulk_special_id'],
                $this->request->post
            );

            $this->session->data['success'] = $this->language->get('text_success_edit');

            $this->response->redirect(
                $this->url->link('extension/module/bulk_special', 'user_token=' . $this->session->data['user_token'], true)
            );
        }

        $this->getForm();
    }

    protected function getForm()
    {
        $this->load->language('extension/module/bulk_special');
        $this->load->model('extension/module/bulk_special');
        $this->load->model('customer/customer_group');

        // User token
        $data['user_token'] = $this->session->data['user_token'];

        // Breadcrumbs
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/module/bulk_special', 'user_token=' . $this->session->data['user_token'], true)
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_form'),
            'href' => ''
        ];

        // Errors
        $data['error_warning'] = $this->error['warning'] ?? '';

        // Form data
        if (isset($this->request->get['bulk_special_id'])) {
            $bulk_special = $this->model_extension_module_bulk_special->getBulkSpecial((int)$this->request->get['bulk_special_id']);

            $this->load->model('catalog/category');

            $category_ids = json_decode($bulk_special['category_ids'], true) ?? [];

            $data['categories'] = [];

            foreach ($category_ids as $category_id) {
                $category_info = $this->model_catalog_category->getCategory($category_id);

                if ($category_info) {
                    $data['categories'][] = [
                        'category_id' => $category_info['category_id'],
                        'name' => $category_info['name']
                    ];
                }
            }

            $ex_category_ids = json_decode($bulk_special['ex_category_ids'], true) ?? [];

            $data['ex_categories'] = [];

            foreach ($ex_category_ids as $category_id) {
                $category_info = $this->model_catalog_category->getCategory($category_id);

                if ($category_info) {
                    $data['ex_categories'][] = [
                        'category_id' => $category_info['category_id'],
                        'name' => $category_info['name']
                    ];
                }
            }

            $this->load->model('catalog/manufacturer');

            $manufacturer_ids = json_decode($bulk_special['manufacturer_ids'], true) ?? [];

            $data['manufacturers'] = [];

            foreach ($manufacturer_ids as $manufacturer_id) {
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

                if ($manufacturer_info) {
                    $data['manufacturers'][] = [
                        'manufacturer_id' => $manufacturer_info['manufacturer_id'],
                        'name' => $manufacturer_info['name']
                    ];
                }
            }

            $ex_manufacturer_ids = json_decode($bulk_special['ex_manufacturer_ids'], true) ?? [];

            $data['ex_manufacturers'] = [];

            foreach ($ex_manufacturer_ids as $manufacturer_id) {
                $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

                if ($manufacturer_info) {
                    $data['ex_manufacturers'][] = [
                        'manufacturer_id' => $manufacturer_info['manufacturer_id'],
                        'name' => $manufacturer_info['name']
                    ];
                }
            }

            $this->load->model('catalog/product');

            $product_ids = json_decode($bulk_special['product_ids'], true) ?? [];

            $data['products'] = [];

            foreach ($product_ids as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    $data['products'][] = [
                        'product_id' => $product_info['product_id'],
                        'name' => $product_info['name']
                    ];
                }
            }

            $ex_product_ids = json_decode($bulk_special['ex_product_ids'], true) ?? [];

            $data['ex_products'] = [];

            foreach ($ex_product_ids as $product_id) {
                $product_info = $this->model_catalog_product->getProduct($product_id);

                if ($product_info) {
                    $data['ex_products'][] = [
                        'product_id' => $product_info['product_id'],
                        'name' => $product_info['name']
                    ];
                }
            }

            $customer_group_ids = json_decode($bulk_special['customer_group_ids'], true) ?? [];

            $data['bulk_special_id'] = $bulk_special['bulk_special_id'];
            $data['name'] = $bulk_special['name'];
            $data['customer_group_ids'] = $customer_group_ids;
            $data['priority'] = $bulk_special['priority'];
            $data['discount_percent'] = $bulk_special['discount_percent'];
            $data['date_start'] = $bulk_special['date_start'] != '0000-00-00' ? $bulk_special['date_start'] : '';
            $data['date_end'] = $bulk_special['date_end'] != '0000-00-00' ? $bulk_special['date_end'] : '';
            $data['status'] = $bulk_special['status'];
        } else {
            $data['name'] = '';
            $data['customer_group_ids'] = [];
            $data['priority'] = 0;
            $data['discount_percent'] = '';
            $data['date_start'] = '';
            $data['date_end'] = '';
            $data['status'] = 1;
        }

        // Customer groups
        $data['customer_groups'] = $this->model_customer_customer_group->getCustomerGroups();

        // Actions
        $data['action'] = isset($this->request->get['bulk_special_id'])
            ? $this->url->link(
                'extension/module/bulk_special/edit',
                'user_token=' . $this->session->data['user_token'] . '&bulk_special_id=' . (int)$this->request->get['bulk_special_id'],
                true
            )
            : $this->url->link(
                'extension/module/bulk_special/add',
                'user_token=' . $this->session->data['user_token'],
                true
            );

        $data['cancel'] = $this->url->link(
            'extension/module/bulk_special',
            'user_token=' . $this->session->data['user_token'],
            true
        );

        // Layout
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput(
            $this->load->view('extension/module/bulk_special_form', $data)
        );
    }

    protected function validateForm(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/bulk_special')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        if (empty($this->request->post['name'])) {
            $this->error['warning'] = $this->language->get('error_name');
        }

        if (empty($this->request->post['discount_percent'])) {
            $this->error['warning'] = $this->language->get('error_discount');
        }

        return !$this->error;
    }

    public function delete(): void
    {
        $this->load->language('extension/module/bulk_special');
        $this->load->model('extension/module/bulk_special');

        if (isset($this->request->post['selected']) && $this->validateDelete()) {

            foreach ($this->request->post['selected'] as $bulk_special_id) {
                $this->model_extension_module_bulk_special->deleteBulkSpecial((int)$bulk_special_id);
            }

            $this->session->data['success'] = $this->language->get('text_success_delete');
        }

        $this->response->redirect($this->url->link('extension/module/bulk_special', 'user_token=' . $this->session->data['user_token'], true));
    }

    protected function validateDelete(): bool
    {
        if (!$this->user->hasPermission('modify', 'extension/module/bulk_special')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        return !$this->error;
    }

    public function install(): void
    {
        $this->load->model('extension/module/bulk_special');
        $this->model_extension_module_bulk_special->install();

        $this->load->model('user/user_group');

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'access',
            'extension/module/bulk_special'
        );

        $this->model_user_user_group->addPermission(
            $this->user->getGroupId(),
            'modify',
            'extension/module/bulk_special'
        );
    }

    public function uninstall(): void
    {
        $this->load->model('extension/module/bulk_special');
        $this->model_extension_module_bulk_special->uninstall();
    }
}
