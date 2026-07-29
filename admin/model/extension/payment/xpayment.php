<?php
class ModelExtensionPaymentXpayment extends Model {
    use OCM\Traits\Back\Model\Crud;
    use OCM\Traits\Back\Model\Product;
    private $name = 'xpayment';
    public function addDBTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `".DB_PREFIX."xpayment` (
              `id` int(8) NOT NULL AUTO_INCREMENT,
              `method_data` MEDIUMTEXT NULL,
              `tab_id` int(8) NULL,
              `sort_order` int(8) NULL,
               PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
        ";
        $query = $this->db->query($sql);
    }
    private function getPaymentModulePath($code) {
        $dir = VERSION >= '2.2.0' ? 'en-gb' : 'english';
        $language_dir = DIR_LANGUAGE . $dir . '/extension/payment/';
        $controller_dir = DIR_APPLICATION . 'controller/extension/payment/';
        $model_dir = DIR_CATALOG . 'model/extension/payment/';
        $controller_class = 'ControllerExtensionPayment';
        $model_class = 'ModelExtensionPayment';
        $lang_path =  $language_dir . $code . '.php';
        $controller_path = $controller_dir . $code . '.php';
        $model_path = $model_dir . $code . '.php';
        return array(
            'language_dir'      => $language_dir,
            'controller_dir'    => $controller_dir,
            'language'          => $lang_path,
            'controller'        => $controller_path,
            'model'             => $model_path,
            'controllerClass'   => $controller_class,
            'modelClass'        => $model_class
        );
    }
    public function checkPermission($error) {
        if (VERSION >= '4.0.0.0') return '';
        $file = '';
        $path = $this->getPaymentModulePath('');
        if (!is_writable($path['language_dir'])) {
            $file .= rtrim(str_replace(DIR_APPLICATION, 'admin/', $path['language_dir']),'/');
        }
        if (!is_writable($path['controller_dir'])) {
            $file .= ($file ? ' AND ' : '') . rtrim(str_replace(DIR_APPLICATION, 'admin/', $path['controller_dir']),'/');
        }
        return $file ? sprintf($error, $file) : '';
    }
    public function createPayment($code, $title) {
        if (VERSION < '4.0.0.0') {
            $path = $this->getPaymentModulePath($code);
            $language = '<?php $_[\'heading_title\'] = "' . $title . '";';
            $controller = '<?php class ' . $path['controllerClass'] . ucfirst($code) . ' extends Controller { }';
            $model = '<?php class ' . $path['modelClass'] . ucfirst($code) . ' extends Model { }';
            /* Language */
            $lang_file = @fopen($path['language'], 'w');
            @fwrite($lang_file, $language);
            @fclose($lang_file);
            /* Controller */
            if (!file_exists($path['controller'])) {
                $controller_file = @fopen($path['controller'], 'w');
                @fwrite($controller_file, $controller);
                @fclose($controller_file);
            }
            /* catalog model */
            if (!file_exists($path['model'])) {
                $model_file = @fopen($path['model'], 'w');
                @fwrite($model_file, $model);
                @fclose($model_file);
            }
        }
        if (!$this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment' AND `code` = '". $code . "'")->row) {
            $sql = "INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'payment', `code` = '" . $this->db->escape($code) . "'";
            if (VERSION > '4.0.0.0') {
                $sql .= ", extension = 'xpayment'";
            }
            $this->db->query($sql);
        }    
    }
    public function deletePayment($tab_id) {
        $code = 'xpayment' . $tab_id; 
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment' AND `code` = '" . $this->db->escape($code) . "'");
        if (VERSION < '4.0.0.0') {
            $path = $this->getPaymentModulePath($code);
            if (file_exists($path['language'])) {
                @unlink($path['language']);
            }
            if (file_exists($path['controller'])) {
                @unlink($path['controller']);
            }
            if (file_exists($path['model'])) {
                @unlink($path['model']);
            }
        }    
    }
    public function removeAllExtension() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment' AND `code` like 'xpayment%' AND `code` != 'xpayment'");
    }
    public function clearExtensions() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `type` = 'payment' AND `code` like 'xpayment%' AND `code` != 'xpayment' AND `code` NOT IN (SELECT CONCAT('xpayment', `tab_id`) AS `code` FROM `" . DB_PREFIX . "xpayment`)");
    }
    public function getTabIdByOrderId($order_id) {
        $tab_id = 0;
        $row = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE `order_id` = '" . (int)$order_id . "'")->row;
        if ($row) {
            $payment_code = '';
            if (!empty($row['payment_method'])) {
                $payment_method = json_decode($row['payment_method'], true);
                if (is_array($payment_method) && isset($payment_method['code'])) {
                    $payment_code = $payment_method['code'];
                }
            }
            if (!$payment_code && isset($row['payment_code'])) {
                $payment_code = $row['payment_code'];
            }
            if (strpos($payment_code, 'xpayment') !== false) {
                $tab_id = str_replace(array('xpayment', '.'), '', $payment_code);
            }
        }
        return $tab_id;
    }
    public function getAddon($name, $path) {
        if (!$name) return false;
        $name = 'xpayment_' . trim($name);
        if (VERSION >= '4.0.0.0') {
            $file  = DIR_EXTENSION . 'xpayment/' . 'admin/model/payment/' . $name . '.php';
        } else {
            $file  = DIR_APPLICATION . 'model/' . $path . $name . '.php';
        }
        if (file_exists($file)) {
            $this->load->model($path . $name);
            $key = 'model_' . str_replace('/', '_', $path) . $name;
            return $this->{$key};
        } else {
            return false;
        }
    }

    public function onAdminMenu($ocm, $data) {
        $menu = array(
            'id'       => 'menu-xpayment',
            'icon'     => 'fa fa-credit-card"', 
            'name'     => 'X-Payment',
            'href'     => $ocm->url->getExtensionURL(),
            'children' => array()
        );

        // find a child menu if available, otherwise adds to the main menu
        $dest_menu = $ocm->misc->getMenuIndex('menu-extension', $data['menus']);
        if ($dest_menu >= 0) {
            array_push($data['menus'][$dest_menu]['children'], $menu); 
        } else {
           array_push($data['menus'], $menu); 
        } 
        return $data;
    }
}