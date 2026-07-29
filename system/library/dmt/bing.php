<?php
/* Digital Marketing Tools v:13.3 25-AUG-2025*/

$tmanalytics .= '(function(w,d,t,r,u)
{
var f,n,i;
w[u]=w[u]||[],f=function()
{
var o={ti:"' . $dmt['bing_uetid'] . '", enableAutoSpaTracking: true};
o.q=w[u],w[u]=new UET(o),w[u].push("pageLoad")
},
n=d.createElement(t),n.src=r,n.async=1,n.onload=n.onreadystatechange=function()
{
var s=this.readyState;
s&&s!=="loaded"&&s!=="complete"||(f(),n.onload=n.onreadystatechange=null)
},
i=d.getElementsByTagName(t)[0],i.parentNode.insertBefore(n,i)
})
(window,document,"script","//bat.bing.com/bat.js","uetq");';

$bing_tags = "window.uetq = window.uetq || [];" . "\n";

if(isset($marketing_block) && !$marketing_block){
$bing_tags .= "window.uetq.push('consent', 'default', { 'ad_storage': 'granted' });". "\n";
} else {  
$bing_tags .= "window.uetq.push('consent', 'default', {'ad_storage': 'denied' });". "\n";    
}

$bing_email = '';
$bing_phone = '';

if (isset($dmt['email']) && !empty($dmt['email'])) {
    $bing_email = $dmt['email'];
}    
if (isset($dmt['telephone']) && !empty($dmt['telephone'])) {
    $bing_telephone = $dmt['telephone'];
}    
if (isset($_data['bing_data'])) {
    $_data['bing_data']['pid'] = array('email'     => $bing_email, 'phone_number' => $bing_phone);
}

switch ($page_type) {
    
    case "home":
        
        $bing_tags .= "window.uetq.push('event', '', {'ecomm_pagetype': 'home'});". "\n";
				
    case "success":

        if (isset($_data['bing_data'])) {
            $bing_data = json_encode($_data['bing_data'],true);
            $bing_tags .= "window.uetq.push('event', 'purchase'," . $bing_data . ");". "\n";
        }

        break;
    
    case "product":

        if (isset($_data['bing_data']) && $_data['bing_data']) {
            $bing_data = json_encode($_data['bing_data'],true);
            $bing_tags .= "window.uetq.push('event', 'view_item'," . $bing_data . ");". "\n";
        }

        break;
    
    case "listing":
        
        if (isset($_data['bing_data']) && $_data['bing_data']) {
            $bing_data = json_encode($_data['bing_data'],true);
            if (isset($this->request->get['search'])) {
                $bing_tags .= "window.uetq.push('event', 'view_search_result'," . $bing_data . ");". "\n";
            } else {
                $bing_tags .= "window.uetq.push('event', 'view_item_list'," . $bing_data . ");". "\n";
            }
        }

        break;
    
    case "checkout":

        if (isset($_data['bing_data']) && $_data['bing_data']) {
            $bing_data = json_encode($_data['bing_data'],true);
            $bing_tags .= "window.uetq.push('event', 'begin_checkout'," . $bing_data . ");". "\n";
        }
    
        break;
    
    case "cart":

        if (isset($_data['bing_data']) && $_data['bing_data']) {
            $bing_data = json_encode($_data['bing_data'],true);
            $bing_tags .= "window.uetq.push('event', 'cart'," . $bing_data . ");". "\n";
        }
        
        break;
    
    case "confirm":
        
        break;
}

if (!empty($bing_tags)) {
    $tmanalytics .= $bing_tags;
}