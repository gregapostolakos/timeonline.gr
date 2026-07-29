<?php 
/* Digital Marketing Tools v:13.0 04-05-2025*/
$tmanalytics .= '<script type="text/javascript" platform="event-handler" nitro-exclude="">';
$tmanalytics .= "defer(function () {
var dataLayer = window.dataLayer = window.dataLayer || [];
$(document).ajaxSuccess(function(event, xhr, settings, json) {
action = null;";
if (isset($page_type) && $page_type == 'checkout') {
$shipping_info = "\n" . "dataLayer.push({'event': 'add_shipping_info','eventAction': 'Add Shipping Info','eventLabel': 'Add Shipping Info','ga' : ec['ga']});" ;
$payment_info = "dataLayer.push({'event': 'add_payment_info','eventAction': 'Add Payment Info','eventLabel': 'Add Payment Info','ga' : ec['ga']});";
if(isset($dmt['pixel']) && $dmt['pixel']) {
$payment_info .= "if (postdata['fb_data']) {fbq('track','AddPaymentInfo', postdata['fb_data'],{'eventID': ec['event_id'] });}";
}
if (isset($dmt['tiktok_status']) && !empty($dmt['tiktok_code']) && $dmt['tiktok_status'] == '1' ) {
$payment_info .= $parent . "ttq.track('AddPaymentInfo', postdata['tiktok'], {'eventID': ec['event_id'] });"."\n";
}
if (isset($dmt['twitter_status']) && !empty($dmt['twitter_tag']) && $dmt['twitter_status'] == '1' ) {
if(isset($tracking_block) && !$tracking_block){
$payment_info .= "if (postdata['twitter_event'] && postdata['twitter_data']) {twq('event', postdata['twitter_event'], postdata['twitter_data']);}";
}
}
$tmanalytics .= "if (typeof settings !== 'undefined' && typeof settings.url !== 'undefined' && typeof settings.data !== 'undefined') {
if (settings.url.includes(\"shipping_method/save\")) {
if (json && json['ec_data'] && json['ec_data']['datalayer']) {
var ec = json['ec_data']['datalayer'];
var postdata = json['ec_data']; " . $shipping_info ."
}
}
if (settings.url.includes(\"payment_method/save\")) {
if (json && json['ec_data'] && json['ec_data']['datalayer']) {
var ec = json['ec_data']['datalayer'];
var postdata = json['ec_data'];" ."\n";
$tmanalytics .= $payment_info ."	
}
}";
if ($this->config->get('config_template') === 'journal2') {
$tmanalytics .= "
if (settings.url.includes(\"journal2/checkout/save\")) {
if (json && json['journal_shipping'] && json['journal_shipping']['datalayer']) {
var ec = json['journal_shipping']['datalayer'];
var postdata = json['journal_shipping'];" . $shipping_info ."
}
if (json && json['journal_payment'] && json['journal_payment']['datalayer']) {
var ec = json['journal_payment']['datalayer'];
var postdata = json['journal_payment'];" ."\n";
$tmanalytics .= $payment_info ."	
}
}
"."\n";
}	
if (isset($data['ttheme']) && $data['ttheme'] == 'journal3') {
$tmanalytics .= "
if (settings.url.includes(\"journal3/checkout/save\")) {
if (json && json['response']['journal_shipping'] && json['response']['journal_shipping']['datalayer']) {
var ec = json['response']['journal_shipping']['datalayer'];
var postdata = json['response']['journal_shipping'];" . $shipping_info ."
}
if (json && json['response']['journal_payment'] && json['response']['journal_payment']['datalayer']) {
var ec = json['response']['journal_payment']['datalayer'];
var postdata = json['response']['journal_payment'];" ."\n";
$tmanalytics .= $payment_info ."	
}
}
"."\n";
}
if (file_exists(DIR_APPLICATION . 'controller/extension/module/xtensions/checkout/checkoutxxx.php')) {
$tmanalytics .= "
if (settings.url.includes(\"xtensions/checkout/xpayment_address/validate\")) {
if (json && json['xtension_shipping'] && json['xtension_shipping']['datalayer']) {
var ec = json['xtension_shipping']['datalayer'];
var postdata = json['xtension_shipping'];" . $shipping_info ."
}}
if (settings.url.includes(\"xtensions/checkout/xpayment_method/validate\")) {
if (json && json['xtension_payment'] && json['xtension_payment']['datalayer']) {
var ec = json['xtension_payment']['datalayer'];
var postdata = json['xtension_payment'];" ."\n";
$tmanalytics .= $payment_info ."
}
}"."\n";
}
$tmanalytics .= "}";
}
$tmanalytics .= "
if (json && json['ec_data']) {
if (!json.error) { 
if(json['action'] && json['ec_data']['action'] == \"addToCart\") {
var ec = json['ec_data']['data'];
var postdata =  json['ec_data'];";
$tmanalytics .= $parent . "dataLayer.push({'event': 'ADD_CART','eventAction': 'ADD_CART','eventLabel': 'AddToCart','contents': [{'id' : ec['id'],'quantity' : ec['quantity']}],'content_name' : ec['name'],'content_type' : 'product','brand': ec['brand'],'category': ec['category'],'quantity':ec['quantity'],'number_items': ec['quantity'],'content_ids' : ec['id'],'remarketing_ids' : [{'id' : ec['id'],'google_business_vertical': 'retail'}],'value' : ec['value'],'currency' : ec['currency'],'ga' : ec['ga'],'event_id'	: ec['event_id'],});" . "\n";
if ($dmt['adword'] && $dmt['adword2'] && isset($dmt['ga2_addtocart']) && !empty($dmt['ga2_addtocart'])) {
$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_label' : '" . $dmt['ga2_addtocart'] . "', 'secondary_conversion_value' : ec['value']});". "\n";
}
if(isset($dmt['pixel']) && $dmt['pixel']) {
$tmanalytics .= $parent . "fbq('track','AddToCart', postdata['fb_data'], {'eventID': ec['event_id'] });" . "\n";
}
if($dmt['bing_status']) {
$tmanalytics .= $parent . "window.uetq.push('event', 'add_to_cart', postdata['bing_data']);" . "\n";
}
if($dmt['pinterest_status']) {
$tmanalytics .= $parent . "pintrk('track', 'addtocart', postdata['pinterest_data']);" . "\n";
}
if ($dmt['snap_pixel_status']) {
if(isset($tracking_block) && !$tracking_block){
$tmanalytics .= $parent . "snaptr('track','ADD_CART', postdata['snapchat']);" . "\n";
}
}
if (isset($dmt['tiktok_status']) && !empty($dmt['tiktok_code']) && $dmt['tiktok_status'] == '1' ) {
if(isset($tracking_block) && !$tracking_block){
$tmanalytics .= $parent . "ttq.track('AddToCart', postdata['tiktok'], {'eventID': ec['event_id'] });"."\n";
}
}
if (isset($dmt['twitter_status']) && !empty($dmt['twitter_tag']) && $dmt['twitter_status'] == '1' ) {
	if(isset($tracking_block) && !$tracking_block){
	$tmanalytics .= "if (postdata['twitter_event'] && postdata['twitter_event'] !== 0){ ". "\n";
	$tmanalytics .= $parent . "twq('event', postdata['twitter_event'], postdata['twitter_data']);". "\n";	
	$tmanalytics .= "}";
	}
}
$tmanalytics .="console.log('AddToCart Event Sent');
}
if(json['action'] && json['ec_data']['action'] == \"addToWishlist\") {
var ec = json['ec_data']['data'];
var postdata = json['ec_data'];";
$tmanalytics .= $parent . "dataLayer.push({'event': 'ADD_WISHLIST','eventAction': 'ADD_WISHLIST','eventLabel': 'AddToWishlist','ga' : ec['ga'],'event_id'	: ec['event_id'],});";
if (isset($dmt['twitter_status']) && !empty($dmt['twitter_tag']) && $dmt['twitter_status'] == '1' ) {
	if(isset($tracking_block) && !$tracking_block){
	$tmanalytics .= "if (postdata['twitter_event'] && postdata['twitter_event'] !== 0){ ". "\n";
	$tmanalytics .= $parent . "twq('event', postdata['twitter_event'], postdata['twitter_data']);". "\n";	
	$tmanalytics .= "}";
	}
}
if (isset($dmt['tiktok_status']) && !empty($dmt['tiktok_code']) && $dmt['tiktok_status'] == '1' ) {
	if(isset($tracking_block) && !$tracking_block){
	$tmanalytics .= $parent . "ttq.track('AddToWishlist', postdata['tiktok'], {'eventID': ec['event_id'] });"."\n";
	}
	}
if(isset($dmt['pixel']) && $dmt['pixel']) {
	$tmanalytics .= $parent . "fbq('track','AddToWishlist', postdata['fb_data'], {'eventID': ec['event_id'] });" . "\n";
	}
if ($dmt['snap_pixel_status']) {
	if(isset($tracking_block) && !$tracking_block){
	$tmanalytics .= $parent . "snaptr('track','ADD_TO_WISHLIST', postdata['snapchat']);" . "\n";
	}
}
$tmanalytics .= "console.log('Wishlist Event Sent');
}
if(json['action'] && json['ec_data']['action'] == \"RemoveCart\") {
var ec = json['ec_data']['data'];"; 
$tmanalytics .= $parent . "dataLayer.push({'event': 'REMOVE_CART','eventAction': 'REMOVE_CART','eventLabel': 'REMOVE CART','value' : ec['value'],'currency' : ec['currency'],'ga' : ec['ga'],'event_id'	: ec['event_id'],});";
$tmanalytics .="console.log('dmt Remove Cart Event Sent');}}}});";
$tmanalytics .="});";
/*$tmanalytics .="function GAPromotion(creative_name,creative_slot,promotion_id,promotion_name,items) {" . $parent . "dataLayer.push({'event': 'promotionClick','eventAction': 'promotionClick','eventLabel': 'promotionClick','creative_name': creative_name, 'creative_slot': creative_slot, 'promotion_id' : promotion_id, 'promotion_name': promotion_name, 'items': items});}";*/
$tmanalytics .="function trackProductClick(name,sku,price,brand,category,item_list_name,item_list_id) {". $parent . "dataLayer.push({'event': 'generic_productclick','eventAction': 'generic_productclick','eventLabel': 'generic_productclick','ga': {'item_list_id': item_list_id,'item_list_name': item_list_name,'items': [{'item_id': sku,'item_name': name,'item_brand': brand,'item_category': category,'item_list_id' : item_list_id,'item_list_name': item_list_name,'price': price,'quantity': 1}]}});}</script>";
?>