<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$cj_tracking = "";

$cj_item = [];
$cj_value = 0;
$cj_customer_country = '';
$cj_customer_status ='';

if (isset($_data['cj_items'])) {
    $cj_item = $_data['cj_items'];
}    
if (isset($_data['cj_value'])) {
    $cj_value = $_data['cj_value'];
}
if (isset($_data['datalayer']['country_code'])) {
    $cj_customer_country = $_data['datalayer']['country_code'];
}
if (isset($_data['datalayer']['new_customer']) && $_data['datalayer']['new_customer']) {
    $cj_customer_status = 'new';
} else {
    $cj_customer_status = 'return';
}

$cj_code = $dmt['cj_code'];
$cj_actionid = $dmt['cj_actionid'];
$cj_currency = $dmt['cj_currency'];
$cj_currency_value = $dmt['cj_currency_value'];
$cj_page = $page_type;

if (!isset($cjevent) || empty($cjevent)) {
    $cjevent = (isset($_COOKIE['cje']) ? $_COOKIE['cje'] : '');
}

if (isset($_data['cj_items'])) {

    switch ($page_type) {
				
		case "success":

            $cj_url = 'https://www.emjcd.com/u?CID=' . $cj_code . '&TYPE=' . $cj_actionid . '&METHOD=S2S&SIGNATURE=&CJEVENT=' .
            $cjevent . '&eventTime=&OID=' . $_data['order_id'] . '&currency=' . $cj_currency . '&coupon=' . $_data['coupon'] ;
            
            $cjcount = 1;
            foreach ($cj_item as $cji) {
                if (isset($cji['item_id']) && !empty($cji['item_id'])) {
                    $cj_url .= '&ITEM' . $cjcount . '=' . $cji['item_id'] . 
                               '&AMT' . $cjcount . '=' . $cji['price'] . 
                               '&QTY' . $cjcount . '=' . $cji['quantity'] . 
                               '&DCNT' . $cjcount . '=' . $cji['discount'];
                    $cjcount++;
                }
            }

            $cj_url .='discount=&amount='. $cj_value .'&customerCountry=' . $cj_customer_country . '&customerStatus=' . $cj_customer_status .'&promotion=';
            
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $cj_url);    
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); 

            $response = curl_exec($curl);

            if (curl_errno($curl)) {
                $this->gtm->Log("cURL error: " . curl_error($curl));
            } else {
                $this->gtm->Log("CJ Response: " . htmlspecialchars($response));
            }     
            
		    break;
		
		case "product":

           
		    break;
		
		case "listing":
           
		
		case "checkout":

            
	    
		    break;
		
		case "cart":

           
		    
		    break;
		
		case "confirm":
		    
	        break;
	}
    $cj_tracking .= "\n";    
}

$tmanalytics .= $cj_tracking;
