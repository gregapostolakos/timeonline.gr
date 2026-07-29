<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
if (isset($this->data['products'])) {
            	
	$list_name = '';
	$list_id = '';
	$selected_category = false;
	$selected_brand = false;
	$search = false;
	$route = '';
	
	if (isset($category_info['category_id']) && isset($category_info['name'])) {
		$list_name = $this->gtm->cleanStr($category_info['name']);
		$list_id = $category_info['category_id'];
		$selected_category = $category_info['category_id'];
	} 
	
	if (isset($manufacturer_info['manufacturer_id']) && isset($manufacturer_info['name'])) {
		$list_name = $this->gtm->cleanStr($manufacturer_info['name']);
		$list_id = $manufacturer_info['manufacturer_id'];
		$selected_brand = $manufacturer_info['name'];
	}
	
	if (isset($this->request->get['search'])) {
		$search = $this->gtm->cleanStr($this->request->get['search']);
		$list_name = 'Search Results: ' . $search;
		$list_id = 'search_' . strtolower(str_replace(' ', '_' ,$search));
	} 
	
	if (isset($this->request->get['route'])) {
    	$route = $this->request->get['route'];
    } 
    
    if ($route == 'product/special') {
    	$list_name = 'Sale';
		$list_id = 'sale';
    }
    
    if ($route == 'product/catalog') {
    	$list_name = 'Catalog';
		$list_id = 'catalog';
    }
    
    if ($route == 'product/vehicles') {
    	$list_name = 'Vehicles';
		$list_id = 'vehicles';
    }

	if ($route == 'product/latest') {
    	$list_name = 'New Arrivals';
		$list_id = 'latest';
    }

	$dmt = $this->gtm->settings;
    $array = explode("_", basename(__FILE__, '.php'));
    $filename = ucfirst(end($array));

    $this->data['dmt'] = $dmt;
    $this->data['route'] = $route;

    $gtm_arg = [
    	'list_name' => $list_name,
    	'list_id'	=> $list_id,
    	'search'	=> $search,
    	'route'		=> $route,
    	'brand'		=> $selected_brand,
    	'category'	=> $selected_category,
    ];
    
	$gtm_results = $this->gtm->getProducts($this->data['products'], $gtm_arg);
	
	$GLOBALS['tm'] = false;
	if ($gtm_results && isset($gtm_results['products'])) {
		$this->data['products'] = $gtm_results['products'];
		unset($gtm_results['products']);
		$GLOBALS['tm'] = $gtm_results;
	}
}
