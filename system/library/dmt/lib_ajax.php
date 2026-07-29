<?php
/* Digital Marketing Tools v:13.4 21-OCT-2025*/
/* routine to prepare tracking properties */
$tmanalytics .= "
var consent_state = mydata['datalayer']['consent_state'];
var consent = mydata['datalayer']['consent'];
var analytics_storage = mydata['datalayer']['analytics_storage'];
var ad_personalization = mydata['datalayer']['ad_personalization'];
var ad_user_data = mydata['datalayer']['ad_user_data'];
var ad_storage = mydata['datalayer']['ad_storage'];
var event_id = mydata['event_id'];
var functionality_storage = 'granted';
var security_storage = 'granted';
var personalization_storage = mydata['datalayer']['ad_personalization'];
if (mydata['snapchat']) { var snapchat_data = mydata['snapchat']; } else { var snapchat_data = '';}";
if (!$dmt['customer_data']) {
$tmanalytics .= "var ttqid =  '';var snapid =  '';var fbqid = '';";
} else {
$tmanalytics .= "gtmSaveData(mydata['user_data']);
if(window.localStorage) {
var fbqid = gtmGetFbqid();
var ttqid = gtmGeTtqid();
var snapid = gtmGetSnapid();
} else {
var ttqid =  mydata['ttqid'];
var snapid =  mydata['snapid'];
var fbqid = mydata['pixel'];
}
";
}
//Consent update section
$consent_code ='';
if (isset($dmt['eu_cookie']) && !$dmt['eu_cookie']) { //consent is off in extension
$consent_code = "
gtag('consent', 'update', {'security_storage': security_storage, 'functionality_storage' : functionality_storage,'analytics_storage': analytics_storage, 'ad_storage': ad_storage, 'ad_user_data' : ad_user_data, 'ad_personalization' : ad_personalization, 'personalization_storage': personalization_storage, });
if (ad_storage != 'granted'){ gtag('set', 'ads_data_redaction', true);	}" . "\n" ;
}
if ($dmt['eu_cookie']) { 
$consent_code = "if (consent_state === 'set') {
gtag('consent', 'update', {'security_storage': security_storage,'functionality_storage' : functionality_storage,'analytics_storage': analytics_storage,'ad_storage': ad_storage,'ad_user_data' : ad_user_data,'ad_personalization' : ad_personalization,'personalization_storage': personalization_storage});
if (ad_storage === 'granted'){ gtag('set', 'ads_data_redaction', false);}
if (analytics_storage === 'granted'){ gtag('set', 'url_passthrough', false);}
console.log('Consent update set and sent....');
}			
". "\n" ;
}
// consent update section ends
 
if ($dmt['consent_external'] && !$dmt['eu_cookie']) {
    $tmanalytics .= "console.log('DMT 3rd Party Consent mode active.');";	
} else {
	$tmanalytics .= $consent_code;
}
$tmanalytics .= "delete mydata['datalayer']['analytics_storage'];delete mydata['datalayer']['ad_storage'];delete mydata['datalayer']['ad_user_data'];delete mydata['datalayer']['ad_personalization'];";
if (!$xhr) {
$tmanalytics .= "dataLayer.push(mydata['datalayer']);";
}
/* GOOGLE ANALYTICS & GOOGLE ADS Routines Start */
if (!$xhr) {
if (isset($dmt['server']) && $dmt['server'] && isset($dmt['server_url']) && !empty($dmt['server_url'])) {
	$container_url = $dmt['server_url'];
	$container_code = '';
} else {
	$container_url = 'https://www.googletagmanager.com/gtm.js';
	$container_code = $dmt['code'];
}
$tmanalytics .= "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='" . $container_url ."?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','" . $container_code ."');" . "\n";
}

$search_term = '';
$search = false;

if (isset($this->request->get['search']) && !empty($this->request->get['search'])) {
	$search_term = $this->request->get['search'];
	$search = true;
}

if (isset($this->request->get['query']) && !empty($this->request->get['query'])) {
	$search_term = $this->request->get['query'];
	$search = true;
}


switch ($page_type) {
			
	case "success":
	
		if (isset($_data['datalayer'])) {
			$tmanalytics .= $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");". "\n";
		}
		if ($dmt['adword'] && $dmt['adword2'] && !isset($dmt['ga2_purchase']) && !empty($dmt['ga2_purchase'])) {
			$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_event_label' : 'purchase_secondary', 'secondary_conversion_label' : '" . $dmt['ga2_purchase'] . "', 'secondary_conversion_value' : '" . $_data['revenue'] ."'});". "\n";
		}

		if ($dmt['pixel'] && $json_facebook) {
			
			if (isset($dmt['fb_purchase']) && $dmt['fb_purchase']) {
				$fbq = '';
			} else {
				$fbq = $parent . "fbq('track','Purchase'," .  $json_facebook . ",{'eventID': event_id });";	
			}
		}
		
		if ($dmt['tiktok_status'] && $json_tiktok) {
			$ttq = $parent . "ttq.track('Purchase'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
		}
		
		if ($dmt['snap_pixel_status']) {
			$scq = $parent . "snaptr('track', 'PURCHASE',snapchat_data);". "\n";
		}

		break;
	
	case "product":
		
		if (isset($_data['datalayer'])){
			$tmanalytics .= $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");". "\n";
		}

		if ($dmt['adword'] && $dmt['adword2'] && isset($dmt['ga2_productview']) && !empty($dmt['ga2_productview'])) {
			$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_event_label' : 'product', 'secondary_conversion_label' : '" . $dmt['ga2_productview'] . "', 'secondary_conversion_value' : '" . (isset($_data['datalayer']['value']) ? $_data['datalayer']['value'] : 1) ."'});". "\n";
		} 
		
		if ($dmt['pixel']) {
			$fbq = $parent . "fbq('track','ViewContent'," .  $json_facebook . ",{'eventID': event_id });";
		}

		if ($dmt['tiktok_status']) {
			$ttq = $parent . "ttq.track('ViewContent'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
		}

		if ($dmt['snap_pixel_status']) {
			$scq  = $parent . "snaptr('track', 'VIEW_CONTENT',snapchat_data);". "\n";
		}

		break;
	
	case "listing":
		
		if (!isset($_data['ttheme'])) { 
			
			if (isset($_data['datalayer'])){
				$tmanalytics .= $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");". "\n";
			}
			if ($search && !empty($search_term)) {
				$tmanalytics .= "dataLayer.push({'event' : 'search' , 'search_term' : '" . $search_term . "'});". "\n";
			}
		}

		if ($dmt['pixel']) {
			if ($route == 'product/search') {
				$fbq = $parent . "fbq('trackCustom','Search'," .  $json_facebook . ",{'eventID': event_id });";
			} else {
				$fbq = $parent .  "fbq('trackCustom','ViewCategory'," .  $json_facebook . ",{'eventID': event_id });";
			}
		}

		if ($dmt['tiktok_status']) {
			if (isset($_data['tiktok']['query']) && !empty($_data['tiktok']['query'])) {
				$ttq = $parent . "ttq.track('Search'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
			} else {
				$ttq = $parent . "ttq.track('ViewCategory'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
			}
		}

		if ($dmt['snap_pixel_status']) {
			if (isset($_data['snapchat']['search_string']) && !empty($_data['snapchat']['search_string'])) {
				$scq  = $parent . "snaptr('track', 'SEARCH',snapchat_data);". "\n";
			} else {
				$scq  = $parent . "snaptr('track', 'LIST_VIEW',snapchat_data);". "\n";
			}
		}
		
		
		break;
	
	case "checkout":
		
		if (isset($_data['datalayer'])){ 
			$tmanalytics .= $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");". "\n";
		}
		
		if ($dmt['adword'] && $dmt['adword2'] && isset($dmt['ga2_checkout']) && !empty($dmt['ga2_checkout'])) {
			$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_event_label' : 'checkout', 'secondary_conversion_label' : '" . $dmt['ga2_checkout'] . "', 'secondary_conversion_value' : '" . (isset($_data['datalayer']['value']) ? $_data['datalayer']['value'] : 1) ."'});". "\n";
		} 

		if ($dmt['pixel']) {
			$fbq = $parent . "fbq('track','InitiateCheckout'," .  $json_facebook . ",{'eventID': event_id });";
		}
		
		if ($dmt['tiktok_status']) {
			$ttq = $parent . "ttq.track('InitiateCheckout'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
		}

		if ($dmt['snap_pixel_status']) {
			$scq = $parent . "snaptr('track', 'START_CHECKOUT',snapchat_data);". "\n";
		}

		break;
	
	case "cart":
	
	    if (isset($this->session->data['addtocart'])) {
	    	$tmanalytics .= (!empty($this->session->data['addtocart']) ? $this->session->data['addtocart'] : ''). "\n";
			unset($this->session->data['addtocart']);
			if (isset($_data['datalayer'])){
		        $tmanalytics .= "setTimeout(function() {" . $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");}, delayInMilliseconds);". "\n";
		    }
		} else {
		    if (isset($_data['datalayer'])){
		        $tmanalytics .= $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");". "\n";
		    }
		}
		
		if ($dmt['adword'] && $dmt['adword2'] && isset($dmt['ga2_viewcart']) && !empty($dmt['ga2_viewcart'])) {
			$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_event_label' : 'view_cart', 'secondary_conversion_label' : '" . $dmt['ga2_viewcart'] . "', 'secondary_conversion_value' : '" . (isset($_data['datalayer']['value']) ? $_data['datalayer']['value'] : 1) ."'});". "\n";
		} 

		if ($dmt['pixel']) {
			$fbq = $parent . "fbq('track','ViewCart'," .  $json_facebook . ",{'eventID': event_id });";
		}

		if ($dmt['tiktok_status']) {
			$ttq = $parent . "ttq.track('ViewCart'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
		}

		if ($dmt['snap_pixel_status']) {
			$scq  = $parent . "snaptr('track', 'CUSTOM_EVENT_1',snapchat_data);". "\n";
		}

		break;
	
	case "confirm":
		
		if (isset($_data['datalayer'])){ 
			$tmanalytics .= $parent . "dataLayer.push(" . json_encode($_data['datalayer']) . ");". "\n";
		}

		if ($dmt['tiktok_status']) {
			$ttq = $parent . "ttq.track('AddPaymentInfo'," .  $json_tiktok . ",{'event_id': event_id });". "\n";
		}

		if ($dmt['snap_pixel_status']) {
			$scq  = $parent . "snaptr('track', 'CUSTOM_EVENT_3',snapchat_data);". "\n";
		}

		break;
	
	case "contact":
		
		$tmanalytics .= "dataLayer.push({'event' : 'contact', 'eventAction' : 'contact','eventLabel': 'contact'});". "\n";
		
		if ($dmt['adword'] && $dmt['adword2'] && isset($dmt['ga2_contact']) && !empty($dmt['ga2_contact'])) {
			$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_event_label' : 'contact','secondary_conversion_label' : '" . $dmt['ga2_contact'] . "', 'secondary_conversion_value' : '" . (isset($dmt['ga2_contact_value']) && !empty($dmt['ga2_contact_value']) ? $dmt['ga2_contact_value'] : 1) ."'});". "\n";
		}

		if ($dmt['tiktok_status']) {
			$ttq = $parent . "ttq.track('Contact',{'event_id': event_id });". "\n";
		}

		if ($dmt['snap_pixel_status']) {
			$scq  = $parent . "snaptr('track', 'CUSTOM_EVENT_2',snapchat_data);". "\n";
		}
	
		break;
		
	case "signup":
		
		if ($dmt['adword'] && $dmt['adword2'] && isset($dmt['ga2_signup']) && !empty($dmt['ga2_signup'])) {
			$tmanalytics .= $parent . "dataLayer.push({'event'	: 'secondary_conversion', 'secondary_conversion_event_label' : 'signup','secondary_conversion_label' : '" . $dmt['ga2_signup'] . "', 'secondary_conversion_value' : '" . (isset($dmt['ga2_signup_value']) && !empty($dmt['ga2_signup_value']) ? $dmt['ga2_signup_value'] : 1) ."'});". "\n";
		}

		if ($dmt['tiktok_status']) {
			$ttq = $parent . "ttq.track('CompleteRegistration',{'event_id': event_id });". "\n";
		}

		if ($dmt['snap_pixel_status']) {
			$scq  = $parent . "snaptr('track', 'SIGN_UP',snapchat_data);". "\n";
		}

		break;
	
	case "home":
		
		$tmanalytics .= $parent . "dataLayer.push({'event' : 'home', 'eventAction' : 'home','eventLabel': 'Home Page'});". "\n";
		
		break;
}
/* CUSTOM EVENTS */
if (!$xhr) {
if ($dmt['pixel']) {
	if (isset($dmt['custom_pixel_event']) && $dmt['custom_pixel_event']) {
		if (is_file($this->path_include . 'custom_pixel_event.php')) {
			include_once($this->path_include . 'custom_pixel_event.php');
			
		}
	}
}
if ($dmt['tiktok_status']) {
	if (isset($dmt['custom_tiktok_event'])) {
		if (is_file($this->path_include . 'custom_tiktok_event.php')) {
			include_once($this->path_include . 'custom_tiktok_event.php');
		}
	}
}
}
/* GOOGLE ANALYTICS & GOOGLE ADS Routines Ends */
if (isset($dmt['pixelcode']) && !empty($dmt['pixelcode']) && $dmt['pixel'] == '1') { 
if (!$xhr) {
$tmanalytics .= "!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script','https://connect.facebook.net/en_US/fbevents.js');";
$tmanalytics .= "fbq('set', 'autoConfig', 'false', '" . $dmt['pixelcode'] . "');
if (consent) {fbq('consent', consent)};
fbq('init', '" . $dmt['pixelcode'] . "',fbqid);
fbq('track','PageView',{},{'eventID': '0-'+event_id });" . "\n";
}
$tmanalytics .= $fbq;
}
if(isset($marketing_block) && !$marketing_block){
if (isset($dmt['snap_pixel_id']) && !empty($dmt['snap_pixel_id']) && $dmt['snap_pixel_status'] == '1') {
if (!$xhr) {	
$tmanalytics .= "(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
a.queue=[];var s='script';r=t.createElement(s);r.async=!0;r.src=n;var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u);})(window,document,'https://sc-static.net/scevent.min.js');" . "\n";
$tmanalytics .= "snaptr('init', '" . $dmt['snap_pixel_id'] . "',snapid); " . "\n";
$tmanalytics .= "snaptr('track', 'PAGE_VIEW',{'client_deduplication_id': '0-'+ event_id, 'event_id' : '0-'+ event_id });". "\n";
}
$tmanalytics .= $scq;
}
if (isset($dmt['tiktok_status']) && !empty($dmt['tiktok_code']) && $dmt['tiktok_status'] == '1' ) {
if (!$xhr) {	
$tmanalytics .= '!function (w, d, t) {w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};';
$tmanalytics .= "ttq.holdConsent();ttq.load('" . $dmt['tiktok_code'] . "'); " . "\n";
$tmanalytics .= "if (consent === 'grant') { ttq.grantConsent();} else { ttq.revokeConsent(); }" . "\n";
$tmanalytics .= "ttq.page(); }(window, document, 'ttq');" . "\n";
$tmanalytics .= "ttq.identify(ttqid);". "\n";
}
$tmanalytics .= $ttq . "\n";
}
}
?>