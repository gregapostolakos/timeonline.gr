<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$da_analytics = '';

switch ($page_type) {
			
    case "success":
        $da_analytics .= "window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);" . "\n";
        $da_analytics .= "window._adftrack.push({pm: " . (int)$dmt['da_code'] . ",pagename: encodeURIComponent('Conversion page'),divider: encodeURIComponent('|')," . "\n";
        $da_analytics .= "order: { sales: '" . (isset($_data['value']) ? $_data['value'] : 0.00) . "', orderid: '" . (isset($_data['order_id']) ? $_data['order_id'] : 0) . "', itms: [" . "\n";
        $item_index = 0;    
        if (isset($_data['items'])) {    
            foreach ($_data['items'] as $result) {
                if ($item_index > 0) {
                    $da_analytics .= ',';
                }
                $da_analytics .= "{productid: '" . $result['item_id'] . "',}";
                $item_index++;
            }    
        }    
        $da_analytics .= "]}});(function(){var s=document.createElement('script');s.type='text/javascript';s.async=true;s.src='https://track.adform.net/serving/scripts/trackpoint/async/';var x = document.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);})();" . "\n";

        break;
    
    case "product":

        $da_analytics .= "window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);" . "\n";
        $da_analytics .= "window._adftrack.push({ pm: " . (int)$dmt['da_code'] . ", pagename: encodeURIComponent('Product page'), divider: encodeURIComponent('|'), products: [" . "\n";
            if (isset($_data['items'])) {    
                $item_index = 0;    
                foreach ($_data['items'] as $result) {
                    if ($item_index > 0) {
                       
                    } else {
                        $da_analytics .=  "{productid: '" . $result['item_id'] . "',}";
                    }
                   
                    $item_index++;
                }    
            }    
        $da_analytics .= "]});(function () { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = 'https://track.adform.net/serving/scripts/trackpoint/async/'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })();" . "\n";
        break;
    
    case "listing":
        if (!isset($this->request->get['search']) && !empty($_data['category_name'])) {
            $da_analytics .= "window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);" . "\n";
            $da_analytics .= "window._adftrack.push({pm: " . (int)$dmt['da_code'] . ",divider: encodeURIComponent('|'),pagename: encodeURIComponent('Category'),order : { ". "\n";
            $da_analytics .= "itms: [{ categoryname: '" . (isset($_data['category_name']) ? $_data['category_name'] : '') ."', categoryid: '" . (isset($_data['category_id']) ? $_data['category_id'] : '') ."'}]". "\n";
            $da_analytics .= "}});(function () { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = 'https://track.adform.net/serving/scripts/trackpoint/async/'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })();" . "\n";
        }
        break;
    
    case "checkout":

        $da_analytics .= "window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);" . "\n";
        $da_analytics .= "window._adftrack.push({ pm: " . (int)$dmt['da_code'] . ", pagename: encodeURIComponent('Basket'), divider: encodeURIComponent('|'), order: { itms: [" . "\n";
        
        $item_index = 0;    
        
        if (isset($_data['items'])) {    
            foreach ($_data['items'] as $result) {
                if ($item_index > 0) {
                    $da_analytics .=  ',';
                }
                $da_analytics .=  "{productid: '" . $result['item_id'] . "',}";
                $item_index++;
            }    
        }  
        $da_analytics .= "]}});(function () { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = 'https://track.adform.net/serving/scripts/trackpoint/async/'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })();" . "\n";
        
        break;
    
    case "cart":

        $da_analytics .= "window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);" . "\n";
        $da_analytics .= "window._adftrack.push({ pm: " . (int)$dmt['da_code'] . ", pagename: encodeURIComponent('Basket'), divider: encodeURIComponent('|'), order: { itms: [" . "\n";
        
        $item_index = 0;    
        
        if (isset($_data['items'])) {    
            foreach ($_data['items'] as $result) {
                if ($item_index > 0) {
                    $da_analytics .=  ',';
                }
                $da_analytics .=  "{productid: '" . $result['item_id'] . "',}";
                $item_index++;
            }    
        }  
        $da_analytics .= "]}});(function () { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = 'https://track.adform.net/serving/scripts/trackpoint/async/'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })();" . "\n";
        
        break;
    

    default:
        $da_analytics .=  "window._adftrack = Array.isArray(window._adftrack) ? window._adftrack : (window._adftrack ? [window._adftrack] : []);" . "\n";
        $da_analytics .=  "window._adftrack.push({  pm: " . (int)$dmt['da_code'] . ",  divider: encodeURIComponent('|'), pagename: encodeURIComponent('Home') });" . "\n";
        $da_analytics .=  "(function () { var s = document.createElement('script'); s.type = 'text/javascript'; s.async = true; s.src = 'https://track.adform.net/serving/scripts/trackpoint/async/'; var x = document.getElementsByTagName('script')[0]; x.parentNode.insertBefore(s, x); })();" . "\n";

}

$tmanalytics .= $da_analytics;

?>