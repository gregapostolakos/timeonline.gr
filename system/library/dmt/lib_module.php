<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
if (isset($data)) {
	
	if (isset($setting['name']) & !empty($setting['name'])) {
		$list_name = $setting['name'];
		$list_id = strtolower(str_replace(' ', '_' , $setting['name']));
	}
            	
	$module_products = false;

	$dmt = $this->gtm->settings;
    $data['dmt'] = $dmt;
    $data['route'] = $route;
    
    $products = array();
    $return_data = false;
    
    if (isset($data['products'])) {
    	$products = $data['products'];
    	$return_data = 'products';
    } 
    
    if ($this->gtm->check_array($products) && count($products) > 0) {
		$module_products = $this->gtm->getModuleProducts($products,$list_name,$list_id);
	}

	if ($module_products && $return_data) {
		$data[$return_data] = $module_products;
	} 
}
?>