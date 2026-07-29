<?php
/* Digital Marketing Tools v:13.0 04-05-2025*/

$pinterest = '
!function(e){if(!window.pintrk){window.pintrk = function () {
window.pintrk.queue.push(Array.prototype.slice.call(arguments))};var
  n=window.pintrk;n.queue=[],n.version="3.0";var
  t=document.createElement("script");t.async=!0,t.src=e;var
  r=document.getElementsByTagName("script")[0];
  r.parentNode.insertBefore(t,r)}}("https://s.pinimg.com/ct/core.js");'. "\n";
  
$pinterest .= "pintrk('load', '" . $dmt['pinterest_tag'] . "', {em: '" . $dmt['em'] . "'});". "\n";
if(isset($marketing_block) && !$marketing_block){
    $pinterest .= "pintrk('setconsent', 'true');". "\n";
} else {  
    $pinterest .= "pintrk('setconsent', 'false');". "\n";    
}
$pinterest .= "pintrk('page');". "\n";

switch ($page_type) {
    
    case "success":

        if (isset($_data['pinterest_data'])) {
            $pinterest_data = json_encode($_data['pinterest_data'],true);
            $pinterest .= "pintrk('track', 'checkout'," . $pinterest_data . ");". "\n";
        }

        break;
    
    case "product":

        if (isset($_data['pinterest_data'])) {
            $pinterest_data = json_encode($_data['pinterest_data'],true);
            $pinterest .= "pintrk('track', 'viewcontent'," . $pinterest_data . ");". "\n";
        }

        break;
    
    case "listing":
        
        if (isset($_data['pinterest_data'])) {
            $pinterest_data = json_encode($_data['pinterest_data'],true);
            if (isset($this->request->get['search'])) {
                $pinterest .= "pintrk('track', 'search_query'," . $pinterest_data . ");". "\n";
            } else {
                $pinterest .= "pintrk('track', 'view_category'," . $pinterest_data . ");". "\n";
            }
        }
        
        break;
    
    case "checkout":

        if (isset($_data['pinterest_data'])) {
            $pinterest_data = json_encode($_data['pinterest_data'],true);
            $pinterest .= "pintrk('track', 'initiatecheckout'," . $pinterest_data . ");". "\n";
        }
    
        break;
    
    case "cart":

        if (isset($_data['pinterest_data'])) {
            $pinterest_data = json_encode($_data['pinterest_data'],true);
            $pinterest .= "pintrk('track', 'cart'," . $pinterest_data . ");". "\n";
        }
        
        break;
    
    case "confirm":
        
        break;
}


$tmanalytics .= $pinterest ;