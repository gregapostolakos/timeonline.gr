<?php
class ModelExtensionTotalXfeepro extends Model {
    use OCM\Traits\Back\Model\Crud;
    use OCM\Traits\Back\Model\Product;
    private $name = 'xfeepro';
    public function addDBTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS `".DB_PREFIX."xfeepro` (
              `id` int(8) NOT NULL AUTO_INCREMENT,
              `method_data` MEDIUMTEXT NULL,
              `tab_id` int(8) NULL,
              `sort_order` int(8) NULL,
               PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
        ";
        $query = $this->db->query($sql);
    }
    public function onAdminMenu($ocm, $data) {
        $menu = array(
            'id'       => 'menu-xfeepro',
            'icon'     => 'fa fa-money"', 
            'name'     => 'X-Feepro',
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