<?php
namespace OCM\Traits\Back\Controller;
trait Common {
    private function validate() {
        if (!$this->user->hasPermission('modify', $this->ext_path)) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if (!$this->error) {
            return true;
        } else {
            return false;
        }
    }
    public function install() {
        if ($this->tables) {
            $this->load->model($this->ext_path);
            $this->{$this->ext_key}->addDBTables();
        }
        $this->ocm->util->addEvents($this->events);
        if (method_exists($this, 'onInstall')) {
            $this->onInstall();
        }
    }
    public function uninstall() {
        $this->ocm->util->removeDBTables($this->tables);
        $this->ocm->util->deleteEvents();
        if (method_exists($this, 'onUninstall')) {
            $this->onUninstall();
        }
    }
    public function upgrade() {
        $update_status = $this->ocm->util->isDBBUpdateAvail($this->tables, $this->events);
        if ($update_status['db']) {
            $this->{$this->ext_key}->addDBTables();
            $this->ocm->util->safeDBColumnAdd($this->tables);
        }
        if ($update_status['event']) {
            $this->ocm->util->addEvents($this->events);
        }
    }
    /* m@nu@l k#y ver1f1c@ti0n */
    public function awpdz() {
        if (isset($this->request->get['_key']) && $this->request->get['_key']) {
            $this->ocm->wpd($this->request->get['_key']);
            $this->response->redirect($this->ocm->url->getExtensionURL());
        }
    }
    public function setPagination(&$data, $row_total, $page, $url) {
        $page_limit = $this->getPaginationLimit();
        if (VERSION >= '4.0.0.0') {
            $data['pagination'] = $this->load->controller('common/pagination', [
                'total' => $row_total,
                'page'  => $page,
                'limit' => $page_limit,
                'url'   => $this->ocm->url->link($this->ext_path,  $url . '&page={page}', true)
            ]);
        } else {
            $pagination = new \Pagination();
            $pagination->total = $row_total;
            $pagination->page = $page;
            $pagination->limit = $page_limit;
            $pagination->url = $this->ocm->url->link($this->ext_path,  $url . '&page={page}', true);
            $data['pagination'] = $pagination->render();
        }
        $data['results'] = sprintf($this->language->get('text_pagination'), ($row_total) ? (($page - 1) * $page_limit) + 1 : 0, ((($page - 1) * $page_limit) > ($row_total - $page_limit)) ? $row_total : ((($page - 1) * $page_limit) + $page_limit), $row_total, ceil($row_total / $page_limit));
    }
    public function getPaginationLimit() {
        return VERSION >= '4.0.0.0' ? $this->config->get('config_pagination_admin') : $this->config->get('config_limit_admin');
    }
}