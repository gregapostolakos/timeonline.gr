<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$matomo_tracking = '';
$matomo_tracking .= html_entity_decode($dmt['matomo_code'], ENT_QUOTES, 'UTF-8'). "\n";

if (isset($_data['matomo'])) {
    $matomo_tracking .= '<script type="text/javascript" platform="matomo" category="analytics" nitro-exclude="">' . "\n";    
	switch ($page_type) {
				
		case "success":
            if (isset($_data['matomo']['items'])) {
                foreach ($_data['matomo']['items'] as $mitems) {
                    $matomo_tracking .= '_paq.push([\'addEcommerceItem\', "' . $mitems['sku'] . '", "' . $mitems['name'] . '", "' . $mitems['category'] . '",' . $mitems['price'] . ','. $mitems['quantity']. ']);'. "\n";
                }
            }
            if (isset($_data['matomo']['order_id'])) {
                $matomo_tracking .= '_paq.push([\'trackEcommerceOrder\', "' . $_data['matomo']['order_id'] . '", ' . $_data['matomo']['revenue'] . ']);'. "\n";
            }

		    break;
		
		case "product":

            $matomo_tracking .= "_paq.push(['setEcommerceView',";
            $matomo_tracking .= '"' . $_data['matomo']['sku'] . '", "' . $_data['matomo']['name'] . '", "' . $_data['matomo']['category'] . '", ' . $_data['matomo']['price'] . ']);'. "\n";
            $matomo_tracking .= "_paq.push(['trackPageView']); ". "\n";

		    break;
		
		case "listing":
            if (!isset($this->request->get['search']) && !empty($_data['matomo'])) {
                    $matomo_tracking .= "_paq.push(['setEcommerceView', false, false, '" . $_data['matomo'] . "'])". "\n";
                    $matomo_tracking .= "_paq.push(['trackPageView']); ". "\n";
		    }
		    break;
		
		case "checkout":

            if (isset($_data['matomo']['items'])) {
                foreach ($_data['matomo']['items'] as $mitems) {
                    $matomo_tracking .= '_paq.push([\'addEcommerceItem\', "' . $mitems['sku'] . '", "' . $mitems['name'] . '", "' . $mitems['category'] . '",' . $mitems['price'] . ','. $mitems['quantity']. ']);'. "\n";
                }
            }
            if (isset($_data['matomo']['total'])) {
                $matomo_tracking .= '_paq.push([\'trackEcommerceCartUpdate\', ' . $_data['matomo']['total'] . ']);'. "\n";
            }
	    
		    break;
		
		case "cart":

            if (isset($_data['matomo']['items'])) {
                foreach ($_data['matomo']['items'] as $mitems) {
                    $matomo_tracking .= '_paq.push([\'addEcommerceItem\', "' . $mitems['sku'] . '", "' . $mitems['name'] . '", "' . $mitems['category'] . '",' . $mitems['price'] . ','. $mitems['quantity']. ']);'. "\n";
                }
            }
            if (isset($_data['matomo']['total'])) {
                $matomo_tracking .= '_paq.push([\'trackEcommerceCartUpdate\', ' . $_data['matomo']['total'] . ']);'. "\n";
            }
		    
		    break;
		
		case "confirm":
		    
	        break;
	}
    $matomo_tracking .= "</script>"."\n";    
}

$tmanalytics .= $matomo_tracking;
