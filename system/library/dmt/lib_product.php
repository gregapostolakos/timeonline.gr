<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
if (isset($product_info)) {
    
	$GLOBALS['tm'] = array();

	if (isset($this->request->get['route'])) {
    	$route = $this->request->get['route'];
    }

	$dmt = $this->gtm->settings;
	$data['dmt'] = $dmt;
	$data['route'] = $route;
	$tm_related_products = (isset($data['products']) ? $data['products'] : []);
	

    $data['j3popup'] = (isset($this->request->get['popup']) ? $this->request->get['popup'] : '') ;
    if (defined('JOURNAL3_ACTIVE')) {
    	$data['ttheme'] = 'journal3';
    }
    
	$gtm_results = $this->gtm->getProduct($product_id,$product_info, $tm_related_products);
	
	if ($gtm_results && isset($gtm_results['error'])) {
		if (isset($gtm_results['related']) && $gtm_results['related']){
		    $data['products'] = $gtm_results['related'];
		}
		$GLOBALS['tm'] = $gtm_results;
	}
}
?>