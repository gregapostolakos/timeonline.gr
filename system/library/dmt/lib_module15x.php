<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
if (isset($this->data)) {
	
	if (isset($setting['name']) & !empty($setting['name'])) {
		$list_name = $setting['name'];
		$list_id = strtolower(str_replace(' ', '_' , $setting['name']));
	}
	
	if (isset($this->data['heading_title']) & !empty($this->data['heading_title'])) {
		$list_name = $this->data['heading_title'];
		$list_id = strtolower(str_replace(' ', '_' , $list_name));
	}
            	
	$dmt = $this->gtm->settings;
    $this->data['dmt'] = $dmt;
    $this->data['route'] = $route;
    $gtm_products = array();
    $data_array = ['products','recent_products','product_features','most_viewed_products','special_products','latest_products',
    				'featured_products','bestseller_products'];
    
    			
	foreach ($data_array as $array_value) {
		if (isset($this->data[$array_value])) {
			$gtm_products = $this->data[$array_value];
			if ($this->gtm->check_array($gtm_products) && count($gtm_products) > 0) {
				$gtm_products_result = $this->gtm->getModuleProducts($gtm_products,$list_name,$list_id);
				if ($gtm_products_result) {
					$this->data[$array_value] = $gtm_products_result;
				}
			}
		}
	}    				
} 