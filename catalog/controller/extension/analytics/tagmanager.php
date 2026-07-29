<?php
/******************************************************
 * @package Digital Marketing Tools for 1.5x,OC2.3,3x
 * @version 13.6
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2017-2026 aits.xyz All rights reserved.
 * @email:info@aits.pk. 
 * $date: 08-FEB-2026
 * controller/extension/analytics/tagmanager
*******************************************************/
class ControllerExtensionAnalyticsTagmanager extends Controller {

	private $path_include =  DIR_SYSTEM .'library/dmt/';
	private $extension_route =  'index.php?route=extension/analytics/tagmanager';
	

    public function index() {
    	$customcode = '';
    	$PREFIX = '';
    	$gdpr_analytics = 'granted';
		$gdpr_marketing = 'granted';
		$consent = 'grant';
		$allowAdFeatures = 'false';
		$tracking_block = false; 
		$marketing_block = false;
		$fbq ='';
		$ttq = '';	
		$scq = '';
		$event_id ='';
		$cdn_url = '';
		$tm_cdn = false;
		$eu_css = '';
		$eu_js = '';
		$tmanalytics = '';
		$xhr = false;
		$setting_tags = [];
		$dimemsion = [];
		$post_data = [];
		$parent = '';

    	
		if(substr(VERSION,0,1)=='3' ) {
			$PREFIX = 'analytics_';
		}
		
		if ($this->gtm == null) {

			if (is_file(DIR_SYSTEM . 'library/dmt.php')) {
	        	$dmt_lib = new Dmt($this->registry);
  				$this->registry->set('gtm', $dmt_lib);
			} 
		}
		
		if ($this->gtm == null) {
			return false;
		}	
		
		if (!method_exists($this->gtm, 'config')) {
			return false;
		}
		

		if (isset($this->request->get['debug'])) { 

		}	
		
		if (isset($this->request->get['route'])) { 
			if ($this->request->get['route'] == 'account/success' || $this->request->get['route'] == 'account/login' || $this->request->get['route'] == 'account/logout' || $this->request->get['route'] == 'account/account' || $this->request->get['route'] == 'contact/success'|| $this->request->get['route'] == 'checkout/confirm') { 
				$this->gtm->resetCustomerData(); 
			}
		}		
		
		$dmt = $this->gtm->config();
		
		if ($dmt['bot']) {
			return false;
		}
		
		if (!isset($dmt['status']) || !$dmt['status']) {
			return false;
		}

		if (isset($dmt['exclude_ip']) && isset($dmt['ip_address'])) {
			$exclude_ip = explode(PHP_EOL, $dmt['exclude_ip']);
			$exclude_ip = array_filter($exclude_ip, 'strlen');
			if (in_array($dmt['ip_address'], $exclude_ip)) {
				include( $this->path_include . 'lib_xhr_exclude.php');
				if(substr(VERSION,0,1)=='1' ) {
					$this->data['tmanalytics'] = $tmanalytics;
					$this->template = 'default/template/extension/analytics/dmt.tpl';
					$this->render();
					return;
				} else  {
					return $tmanalytics;
				}
			}
		}
		
		$tm_mode = (isset($dmt['ajax']) ? $dmt['ajax'] : false);
		
		$tm_delay = $dmt['delay'];
		$oc1 = false;
	
		if (isset($GLOBALS['tm'])) {
			$tm = $GLOBALS['tm'];
		}
		
		if (isset($GLOBALS['tm_m'])) {
			$tm_m = $GLOBALS['tm_m'];
		}
				
		unset($GLOBALS['tm']);
		unset($GLOBALS['tm_m']);
		
		if (defined('JOURNAL3_ACTIVE')) {
			$data['ttheme'] = 'journal3';
		}

		$page_type = (isset($tm['type']) ? $tm['type'] : '');
		$page_type = (isset($tm['page_type']) ? $tm['page_type'] : '');
		
		$xhr = (isset($this->request->get['popup']) ? true : false) ;
		if (isset($tm['xhr']) && $tm['xhr']) {
			$xhr = true;;
		}
		$tmdata = [];
		$route = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');
		$org_route = $route;

		$data['dmt'] = $dmt;
		$data['route'] = $route;
			
		if (substr(VERSION,0,1)=='1' ) {
			$oc1 = true;
			$this->data['dmt'] = $dmt;
			$this->data['route'] = $route;
		} 
		
		if (!empty($dmt['route_checkout'])) {	
			$route_checkout = explode(PHP_EOL, $dmt['route_checkout']);
			$dmt['route_checkout'] = array_filter($route_checkout, 'strlen');
		} else {
			$dmt['route_checkout'] = array('extension/quickcheckout/checkout','quickcheckout/checkout');
		}
		
		if (!empty($dmt['route_confirm'])) {	
			$route_confirm = explode(PHP_EOL, $dmt['route_confirm']);
			$dmt['route_confirm'] = array_filter($route_confirm, 'strlen');
		} else {
			$dmt['route_confirm'] = array('extension/quickcheckout/confirm');
		}

		if (!empty($dmt['route_success'])) {	
			$route_success = explode(PHP_EOL, $dmt['route_success']);
			$dmt['route_success'] = array_filter($route_success, 'strlen');
		} else {
			$dmt['route_success'] = array('extension/ordersuccess','extension/checkout/eghlresponse/success');
		}
		
		foreach ($dmt['route_success'] as $checkroute) {
			if (trim($checkroute) == $route){
				$route = 'checkout/success';
			}
		}
		
		foreach ($dmt['route_confirm'] as $checkroute) {
			if (trim($checkroute) == $route){
				$route = 'checkout/confirm';
			}
		}
		
		foreach ($dmt['route_checkout'] as $checkroute) {
			if (trim($checkroute) == $route){
				$route = 'checkout/checkout';
			}
		}
		
		if ($route == 'product/quickview' || $route == 'extension/module/pavquickview/show' || $route == 'journal2/quickview' ) { $xhr = true; }
	
		if ($page_type == 'product'){
			$tmdata = $tm;
		}
	
		if ($page_type == 'listing'){
			$tmdata = $tm;
		}
		
		if ($org_route == 'information/contact'){
			$page_type = 'contact';
		}
		
		if ($org_route == 'account/success'){
			$page_type = 'signup';
		}
		
		if ($org_route == 'common/home'){
			$page_type = 'home';
		}
		
		switch ($route) {
			case "checkout/cart":
				if ($this->cart->hasProducts()) {
					$tmdata = $this->gtm->prepareCart();
					$page_type = 'cart';
				}
				break;
			
			case "checkout/checkout":
				$prepare = ['page' => 'checkout','step' => '1',	'mode' => 'checkout'];
	
				$this->session->data['steps'] = 1;
				$this->session->data['reload_check'] = [];
	
				if ($this->cart->hasProducts()) {
					$tmdata = $this->gtm->prepareCheckout($prepare);
					$page_type = 'checkout';
				}
				break;
	
			case "checkout/confirm":
				if (isset($this->session->data['steps'])) {
					$this->session->data['steps'] ++;
	
				} else {
					$this->session->data['steps'] = 2;
				}
	
				$this->session->data['reload_check'] = [];
				
				$prepare = [ 'page' => 'confirm','step' => $this->session->data['steps'],'mode' => 'confirm'];

				if ($this->cart->hasProducts()) {
					$tmdata = $this->gtm->prepareConfirm($prepare);
					$page_type = 'confirm';
				}
				break;
	
			case "checkout/success":
				
				$gtm_orderid = false;
				$tmp_order_id = false;
				
				$this->session->data['success_called'] = 'yes';

				$tmp_order_id = (int)$this->gtm->readGTMCookie('dmt_orderid');
				
				$debug_order = [
					'order_id'			=> '',
					'dmt_session'		=> isset($this->session->data['dmt_order_id']) ? $this->session->data['dmt_order_id'] : 'empty',
					'dmt_cookie'		=> $tmp_order_id,
					'message'			=> 'started',
					'success'			=> 'false',
				]; 
				
				if (isset($this->session->data['dmt_order_id']) && (int)$this->session->data['dmt_order_id'] > 0) {
					$gtm_orderid = (int)$this->session->data['dmt_order_id'];
				} elseif (isset($this->session->data['xsuccess_order_id']) && $this->session->data['xsuccess_order_id']) {
					$gtm_orderid = (int)$this->session->data['xsuccess_order_id'];
					$debug_order['xsuccess_order_id'] = $gtm_orderid;
				} elseif (isset($this->session->data['order_id']) && $this->session->data['order_id']) {
					$gtm_order_id = (int)$this->session->data['order_id'];
					$debug_order['session_order_id'] = $gtm_orderid;
				} elseif (isset($this->request->get['gtm_trans'])) {
					$debug_order['alert0'] = 'using order id from url input';
					$gtm_orderid = (int)$this->request->get['gtm_trans'];
					$debug_order['_GET'] = $gtm_orderid;
				} elseif (isset($this->request->get['transaction_id'])) {
					$gtm_orderid = (int)$this->request->get['transaction_id'];
					$debug_order['alert0'] = 'using order id from url input';
					$debug_order['_GET'] = $gtm_orderid;
				}

				if ($gtm_orderid && $tmp_order_id) {
					if ($gtm_orderid != $tmp_order_id) {
						$debug_order['alert'] = 'gtm_orderid != tmp_order_id input missmatch detected.';
					}
				}

				if (!$gtm_orderid && $tmp_order_id) {
					$gtm_orderid = (int)$tmp_order_id;
					$debug_order['alert'] = 'using order id from saved cookie.';
				}
				
				$order_id  = (int)$gtm_orderid;
				$debug_order['order_id'] = $order_id;
				
				if ($order_id  && $order_id  > 0) {

					$order_data = $this->gtm->preparePurchase($order_id);
					$order_status_id = $this->gtm->OrderStatusCheck($order_id);
					
					$valid_status = true;
					
					if (isset($dmt['order_status']) && $this->gtm->check_array($dmt['order_status'])) {
						$valid_status = false;
						if (in_array($order_status_id, $dmt['order_status'])) {
							$valid_status = true;	
						}
					}
	
					if ($order_status_id == '0') {
						$debug_order['message'] = 'Order Success Called [ Order: ' . $order_id . ' ] Result: Order Status is not Complete to send hit';
					}
					
					if (isset($order_data['hit']) && $order_data['hit'] == '0' && $order_status_id > 0 && $valid_status) {
						$page_type = 'success';
						$tmdata = $order_data;
						
						if(substr(VERSION,0,1)=='1' ) {
							$this->data['order_data'] = $order_data; 
						} else {
							$data['order_data'] = $order_data; 
						}
						if (isset($dmt['debug']) && $dmt['debug']) {
							$debug_order['message'] = 'Order Success Called [ Order: ' . $order_id . ' ] Result: success order';
							$debug_order['success'] = 'yes';
							$this->session->data['success_called'] = 'completed';
						}
					} 
					if (isset($order_data['hit']) && $order_data['hit'] == '1') {
						if (isset($dmt['debug']) && $dmt['debug']) {
							$debug_order['message'] = 'Order Success Called Twice '. $order_id;
						}
						
					}
				} 
				if (isset($dmt['debug']) && $dmt['debug'] && isset($debug_order)) {
					$this->gtm->Log($debug_order);
				}
				$this->gtm->resetGTMCookie('dmt_orderid');
				unset($this->session->data['dmt_order_id']);
				unset($this->session->data['success_called']);
			break;
		}
		$check_orer = (int)$this->gtm->readGTMCookie('dmt_order_id');	
		if (isset($check_orer) && $check_orer > 0 && $route != 'checkout/success') {

			$order_status_id = $this->gtm->OrderStatusCheck($check_orer);
	
			if ($order_status_id != '0') {
				$debug_order = [
					'dmt_order_id'		=> $this->session->data['dmt_order_id'],
					'order_status_id'	=> $order_status_id,
					'message'			=> 'Order ID exist but not a success route',
					'cookie'			=> $check_orer,
					'route'				=> $data['route'],
				];
				if (isset($this->session->data['order_id'])) {
					$debug_order['session_order_id'] = $this->session->data['order_id'];
				}
				if (isset($this->session->data['xsuccess_order_id'])) {
					$debug_order['xsuccess_order_id'] = $this->session->data['xsuccess_order_id'];
				}
			}

			if (isset($dmt['debug']) && $dmt['debug'] && isset($debug_order)) {
				$this->gtm->Log($debug_order);
			}
		}	

		if (isset($tmdata)) {
			if (isset($tmdata['error']) && $tmdata['error'] =='false') {
				$_data = $tmdata;
			}
		}
		
		$cookie_data = $this->gtm->readConsent();
		$cc_enabled = $cookie_data['cc_enabled'];
		$gdpr_analytics = $cookie_data['gdpr_analytics'];
		$gdpr_marketing = $cookie_data['gdpr_marketing'];
		$ad_user_data = $cookie_data['ad_user_data'];
		$ad_personalization = $cookie_data['ad_personalization'];
		$allowAdFeatures = $cookie_data['allowAdFeatures'];
		$consent = $cookie_data['consent'];
		$tracking_block = $cookie_data['tracking_block'];
		$marketing_block = $cookie_data['marketing_block'];
		
		if (isset($xhr) && $xhr) {
			$parent = 'parent.';
		} else {
			$xhr = false;
		}

		$tmanalytics .= '<!-- Digital Marketing Tools Extension by aits.pk -->';
		$tmanalytics .= '<script type="text/javascript" nitro-exclude="">' . "\n";
		$tmanalytics .= 'var dataLayer = window.dataLayer = window.dataLayer || []; function gtag(){dataLayer.push(arguments);} var delayInMilliseconds = ' . $tm_delay . '; '.  "\n";
		if (!$dmt['consent_external']) {
			$tmanalytics .= "gtag('consent', 'default', {'ad_storage': 'denied','ad_user_data': 'denied','ad_personalization': 'denied','personalization_storage': 'denied','analytics_storage': 'denied','security_storage': 'granted','functionality_storage': 'granted','wait_for_update': 500});" .  "\n";
			$tmanalytics .= "gtag('set', 'ads_data_redaction', true);" .  "\n";
			$tmanalytics .= "gtag('set', 'url_passthrough', true);" .  "\n";
		} 
		$tmanalytics .= 'function whenAvailable(name, callback) {var interval = 10; window.setTimeout(function() {if (window[name]) {callback(window[name]);} else {window.setTimeout(arguments.callee, interval);}}, interval);}'."\n";
		$tmanalytics .= "function defer(method) {if (window.jQuery) {method();} else {console.log('jquery not loaded waiting');setTimeout(function() { defer(method) }, 50);}}".  "\n";
		$tmanalytics .= "function gtmSaveData(mydata){if(window.localStorage){if(mydata.user_id&&mydata.user_id!==null&&mydata.user_id!==''){localStorage.setItem('user_id',mydata.user_id)}if(mydata.em&&mydata.em!==null&&mydata.em!==''){localStorage.setItem('_hash1',mydata.em)}if(mydata.ph&&mydata.ph!==null&&mydata.ph!==''){localStorage.setItem('_hash2',mydata.ph)}if(mydata.ph_e164&&mydata.ph_e164!==null&&mydata.ph_e164!==''){localStorage.setItem('_hash3',mydata.ph_e164)}if(mydata.fn&&mydata.fn!==null&&mydata.fn!==''){localStorage.setItem('_hash4',mydata.fn)}if(mydata.ln&&mydata.ln!==null&&mydata.ln!==''){localStorage.setItem('_hash5',mydata.ln)}if(mydata.ad&&mydata.ad!==null&&mydata.ad!==''){localStorage.setItem('_hash6',mydata.ad)}if(mydata.ct&&mydata.ct!==null&&mydata.ct!==''){localStorage.setItem('_hash7',mydata.ct)}if(mydata.st&&mydata.st!==null&&mydata.st!==''){localStorage.setItem('_hash8',mydata.st)}if(mydata.pc&&mydata.pc!==null&&mydata.pc!==''){localStorage.setItem('_hash9',mydata.pc)}if(mydata.cc&&mydata.cc!==null&&mydata.cc!==''){localStorage.setItem('_hash10',mydata.cc)}if(mydata.external_id&&mydata.external_id!==null&&mydata.external_id!==''){localStorage.setItem('_external_id',mydata.external_id)}if(mydata.external_id_hash&&mydata.external_id_hash!==null&&mydata.external_id_hash!==''){localStorage.setItem('_hash11',mydata.external_id_hash)}}}" . "\n";
		$tmanalytics .= "function gtmGetSaveData(){if(window.localStorage){var user_id=localStorage.getItem('user_id');var em=localStorage.getItem('_hash1');var ph=localStorage.getItem('_hash2');var ph_e164=localStorage.getItem('_hash3');var fn=localStorage.getItem('_hash4');var ln=localStorage.getItem('_hash5');var ad=localStorage.getItem('_hash6');var ct=localStorage.getItem('_hash7');var st=localStorage.getItem('_hash8');var pc=localStorage.getItem('_hash9');var cc=localStorage.getItem('_hash10');var external_id_hash=localStorage.getItem('_hash11');var external_id=localStorage.getItem('_external_id');			var user={user_id:user_id,em:em,ph:ph,ph_e164:ph_e164,fn:fn,ln:ln,ad:ad,ct:ct,pc:pc,st:st,cc:cc,external_id:external_id};return user}}" ."\n";
		$tmanalytics .= "function gtmGetFbqid(){if(window.localStorage){var em=localStorage.getItem('_hash1');var ph=localStorage.getItem('_hash2');var ph_e164=localStorage.getItem('_hash3');var fn=localStorage.getItem('_hash4');var ln=localStorage.getItem('_hash5');var ad=localStorage.getItem('_hash6');var ct=localStorage.getItem('_hash7');var st=localStorage.getItem('_hash8');var pc=localStorage.getItem('_hash9');var cc=localStorage.getItem('_hash10');var external_id_hash=localStorage.getItem('_hash11');var external_id=localStorage.getItem('_external_id');var fbqid={em:em,ph:ph,fn:fn,ln:ln,ad:ad,ct:ct,zp:pc,st:st,country:cc,external_id:external_id};return fbqid}}" ."\n";
		$tmanalytics .= "function gtmGeTtqid(){if(window.localStorage){var em=localStorage.getItem('_hash1');var ph=localStorage.getItem('_hash2');var ph_e164=localStorage.getItem('_hash3');var fn=localStorage.getItem('_hash4');var ln=localStorage.getItem('_hash5');var ad=localStorage.getItem('_hash6');var ct=localStorage.getItem('_hash7');var st=localStorage.getItem('_hash8');var pc=localStorage.getItem('_hash9');var cc=localStorage.getItem('_hash10');var external_id_hash=localStorage.getItem('_hash11');var external_id=localStorage.getItem('_external_id');	var ttqid={email:em,phone_number:ph_e164,external_id:external_id_hash,first_name:fn,last_name:ln,city:ct,state:st,zip_code:pc,country:cc};return ttqid}}" . "\n";
		$tmanalytics .= "function gtmGetSnapid(){if(window.localStorage){var em=localStorage.getItem('_hash1');var ph=localStorage.getItem('_hash2');var ph_e164=localStorage.getItem('_hash3');var fn=localStorage.getItem('_hash4');var ln=localStorage.getItem('_hash5');var ad=localStorage.getItem('_hash6');var ct=localStorage.getItem('_hash7');var st=localStorage.getItem('_hash8');var pc=localStorage.getItem('_hash9');var cc=localStorage.getItem('_hash10');var external_id_hash=localStorage.getItem('_hash11');var external_id=localStorage.getItem('_external_id');var snapid={user_hashed_email:em,user_hashed_phone_number:ph,firstname:fn,lastname:ln,geo_city:ct,geo_region:st,geo_postal_code:pc,geo_country:cc,external_id:external_id_hash};return snapid}}" ."\n";
		$tmanalytics .= '</script>' . "\n";

		$dimemsion['ecomm_prodid']		= (isset($_data['datalayer']['ecomm_prodid']) ? $_data['datalayer']['ecomm_prodid'] : false);
		$dimemsion['ecomm_pagetype']	= (isset($_data['datalayer']['ecomm_pagetype']) ? $_data['datalayer']['ecomm_pagetype'] : false);
		$dimemsion['ecomm_totalvalue']	= (isset($_data['datalayer']['ecomm_totalvalue']) ? $_data['datalayer']['ecomm_totalvalue'] : false);
		$dimemsion['dynx_itemid']		= (isset($_data['datalayer']['dynx_itemid']) ? $_data['datalayer']['dynx_itemid'] : false);
		$dimemsion['dynx_itemid2']		= (isset($_data['datalayer']['dynx_itemid2']) ? $_data['datalayer']['dynx_itemid2'] : false);
		$dimemsion['dynx_pagetype']		= (isset($_data['datalayer']['dynx_pagetype']) ? $_data['datalayer']['dynx_pagetype'] : false);
		$dimemsion['dynx_totalvalue']	= (isset($_data['datalayer']['dynx_totalvalue']) ? $_data['datalayer']['dynx_totalvalue'] : false);
		
		$page_url = $dmt['url'];
		if (empty($dmt['url'])) {
			$page_url = $dmt['host_url'] . $dmt['path'];
		}
		
		$json_facebook = false;
		$json_tiktok = false;

		
		if (isset($_data['order_id'])) {
			$post_data['order_id'] = $_data['order_id'];
			$post_data['ga']	= (isset($_data['datalayer']['ga']) ? $_data['datalayer']['ga'] : false);
		}

		if (isset($_data['fb_data']) && $_data['fb_data']) {
			$post_data['pixel'] = $_data['fb_data'];
			if (isset($dmt['fb_catalog_id']) && !empty($dmt['fb_catalog_id'])) {
				$_data['fb_data']['product_catalog_id'] = $dmt['fb_catalog_id'];
				$post_data['product_catalog_id'] = $dmt['fb_catalog_id'];
			}
			if (isset($_data['fb_data'])) {
				$json_facebook = json_encode($_data['fb_data']);
			} 
			
		} 
		
		if ($dmt['tiktok_status']) {
			$post_data['tiktok'] = (isset($_data['tiktok']) ? $_data['tiktok'] : false);
			if (isset($_data['tiktok'])) {
				$json_tiktok = json_encode($_data['tiktok']);
			} else {
				$json_tiktok = json_encode(array());
			}
			
		}

		if ($dmt['snap_pixel_status']) {
			$post_data['snapchat'] = (isset($_data['snapchat_api']) ? $_data['snapchat_api'] : false);
			$post_data['snapchat_event'] = (isset($_data['snapchat']) ? $_data['snapchat'] : false);
		}
		$post_data['event_id'] = (isset($_data['event_id']) ? $_data['event_id'] : false);
		$post_data['page_type'] = (isset($page_type) ? $page_type : '');
		$post_data['route'] = (!empty($org_route) ? $org_route : $route);
		$post_data['url'] = $page_url;
		$post_Data['referrer'] = $dmt['referrer'];
		$post_data['dimemsion'] = $dimemsion;
		
		if (isset($_data['customer'])) {
			$post_data['customer_data'] = $_data['customer'];
		} 
			
		$token = $this->gtm->getAJAXtoken();
		$post_data['token'] = $token;
		
		$post_data = json_encode($post_data);
		
		$tmanalytics .= '<script type="text/javascript" category="setup" nitro-exclude="">' . "\n";
		
		if (isset($dmt['em']) && empty($dmt['em']) &&  $dmt['customer_data']) {
			if (!isset($customer_data['em'])) {
				$tmanalytics .= "if(window.localStorage) {var userdata = gtmGetSaveData();}";
				$tmanalytics .= "var xhr = new XMLHttpRequest();xhr.open(\"POST\", \"". $this->extension_route . "/usid&ts=\" +Math.random() );
				xhr.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded;charset=UTF-8\");xhr.responseType = 'json';xhr.onload = function () {if (xhr.readyState === xhr.DONE && xhr.status === 200) {
					var usid_data = xhr.response;}};xhr.send(JSON.stringify({ data: userdata }));" . "\n";
			}
		}
		
		if ($tm_mode) {
			$tmanalytics .= "
			defer(function () {"."$.ajax({cache: false, url: '". $this->extension_route . "/gtm&ts='+ Math.random(),type: 'post',
			data: JSON.stringify({ data: " . $post_data . " }),  contentType: \"application/json; charset=utf-8\",dataType: 'json',
			beforeSend: function() { },
			complete: function() { },
			success: function(mydata) {";
			include( $this->path_include . 'lib_ajax.php');
			$tmanalytics .= "},error: function(xhr, ajaxOptions, thrownError) {console.log(xhr.responseText);}});});";
		} else {
			$tmanalytics .= "var xhr = new XMLHttpRequest();xhr.open(\"POST\", \"". $this->extension_route . "/gtm&ts=\" +Math.random() );
			xhr.setRequestHeader(\"Content-type\", \"application/x-www-form-urlencoded;charset=UTF-8\");xhr.responseType = 'json';xhr.onload = function () {if (xhr.readyState === xhr.DONE && xhr.status === 200) {
			var mydata = xhr.response;";
			include( $this->path_include . 'lib_ajax.php');
			$tmanalytics .= "}};xhr.send(JSON.stringify({ data: " . $post_data . " }));" ;
		}	
		$tmanalytics .= "</script>"."\n";

		if(isset($tracking_block) && !$tracking_block){

			if (isset($dmt['twitter_tag']) && !empty($dmt['twitter_tag']) && $dmt['twitter_status'] == '1') {
				$tmanalytics .= '<script type="text/javascript" platform="twitter" category="analytics" nitro-exclude="">' . "\n";
				include( $this->path_include . 'twitter.php');
				$tmanalytics .= "</script>"."\n";
			}

			if (isset($dmt['luckyorange_status']) && !empty($dmt['luckyorange_siteid']) && $dmt['luckyorange_status'] == '1' ) {
				$tmanalytics .= '<script type="text/javascript" platform="luckyorange" category="analytics" nitro-exclude="">' . "\n";
				include( $this->path_include . 'lucky_main.php');
				$tmanalytics .= "</script>"."\n";
			}
		
			if (isset($dmt['skroutz_siteid']) && !empty($dmt['skroutz_siteid']) && $dmt['skroutz_status'] == '1') {
				$tmanalytics .= '<script type="text/javascript" platform="skroutz" category="analytics" nitro-exclude="">' . "\n";
				include( $this->path_include . 'skroutz.php');
				$tmanalytics .= "</script>"."\n";
			}

			if (isset($dmt['matomo_code']) && !empty($dmt['matomo_code']) && $dmt['matomo_status'] == '1') {
				include( $this->path_include . 'matomo.php');
			}
			
		}

		if (isset($dmt['bing_uetid']) && !empty($dmt['bing_uetid']) && $dmt['bing_status'] == '1') {
			$tmanalytics .= '<script type="text/javascript" platform="bing" category="analytics" nitro-exclude="">' . "\n";
			include( $this->path_include . 'bing.php');
			$tmanalytics .= "</script>"."\n";
		}

		if(isset($marketing_block) && !$marketing_block){

			if (isset($dmt['pinterest_tag']) && !empty($dmt['pinterest_tag']) && $dmt['pinterest_status'] == '1') {
				$tmanalytics .= '<script type="text/javascript" platform="pinterest" category="marketing" nitro-exclude="">' . "\n";
				include( $this->path_include . 'pinterest.php');
				$tmanalytics .= "</script>"."\n";
			}

			if (isset($dmt['clarity_id']) && !empty($dmt['clarity_id']) && $dmt['clarity_status']) {
				$tmanalytics .= '<script type="text/javascript" platform="clarity" category="marketing" nitro-exclude="">' . "\n";
				include( $this->path_include . 'clarity.php');
				$tmanalytics .= "</script>"."\n";
			}

			if (isset($dmt['hotjar_siteid']) && !empty($dmt['hotjar_siteid']) && $dmt['hotjar_status']) {
				$tmanalytics .= '<script type="text/javascript" platform="hotjar" category="marketing" nitro-exclude="">' . "\n";
				include( $this->path_include . 'hotjar.php');
				$tmanalytics .= "</script>"."\n";
			}
			
			if (isset($dmt['paypal_code']) && !empty($dmt['paypal_code']) && $dmt['paypal_status'] == '1' ) {
				$tmanalytics .= '<script type="text/javascript" platform="paypal" category="marketing" nitro-exclude="">' . "\n";
				include( $this->path_include . 'paypal.php');
				$tmanalytics .= "</script>"."\n";
			}
		
			if (isset($dmt['admitad_code']) && !empty($dmt['admitad_code']) && $dmt['admitad_status'] == '1' ) {
				include( $this->path_include . 'admitad.php');
			}
		
			if (isset($dmt['sendinblue_status']) && !empty($dmt['sendinblue_code']) && $dmt['sendinblue_status'] == '1' ) {
				$tmanalytics .= '<script type="text/javascript" platform="sendinblue" category="marketing" nitro-exclude="">' . "\n";
				include( $this->path_include . 'sendinblue.php');
				$tmanalytics .= "</script>"."\n";
			}
			
			if (isset($dmt['linkwise_status']) && !empty($dmt['linkwise_code']) && $dmt['linkwise_status'] == '1' ) {
				include( $this->path_include . 'linkwise.php');
			}

			if (isset($dmt['da_code']) && !empty($dmt['da_code']) && $dmt['da_status'] == '1') {
				$tmanalytics .= '<script type="text/javascript" platform="dataaudience" category="marketing" nitro-exclude="">' . "\n";
				include( $this->path_include . 'dataaudience.php');
				$tmanalytics .= "</script>"."\n";
			}

		}

		if (isset($dmt['cj_code']) && !empty($dmt['cj_code']) && $dmt['cj_status'] == '1') {
			$cookie_name = "cje";
			$domain = $this->gtm->getNewURL();
			$cjevent = false;
			$_GET_lower = array_change_key_case($_GET, CASE_LOWER);
			$cjevent = (isset($_GET_lower["cjevent"]) ? $_GET_lower["cjevent"] : false);
			if (isset($cjevent) && $cjevent) {
				setcookie($cookie_name, $cjevent, time() + (86400 * 395), "/", $domain, true, false);
			}
			include( $this->path_include . 'cj.php');
		}

		if ($page_type == 'success') {

			$hit_order = false;

			if (isset($_data['order_id'])) {
				$hit_order = $_data['order_id'];
			}	

			if ($hit_order) {
				$tmanalytics .= '<script type="text/javascript" nitro-exclude="">';
				$tmanalytics .= "defer(function () {" . "$.ajax({ url: '". $this->extension_route . "/hitorder&oid=" . $hit_order . "&v=" . $dmt['vs'] ."'});" ."});</script>"."\n";
			}
			
			if(isset($marketing_block) && !$marketing_block){
			
				if (isset($dmt['performant_code']) && isset($dmt['performant_confirm']) && $dmt['performant_status']) {
					include( $this->path_include . '2performant.php');
				}
			
				if (isset($dmt['merchant_id']) && !empty($dmt['merchant_id']) && $dmt['greview'] == '1') {	
					include( $this->path_include . 'google_review_success.php');
				}
			}
		}
				
		if (isset($dmt['eu_cookie']) && $dmt['eu_cookie'] && !isset($this->request->get['popup'])) {
			include( $this->path_include . 'lib_consent.php');
		} 

		include( $this->path_include . 'lib_xhr.php');
		
		if (isset($dmt['customcode']) && !empty($dmt['customcode'])) {
			$tmanalytics .= html_entity_decode($dmt['customcode'], ENT_QUOTES, 'UTF-8');
		}

		if (isset($dmt['gdpr_customcode']) && !empty($dmt['gdpr_customcode'])) {
			if(isset($marketing_block) && !$marketing_block){
				$tmanalytics .= html_entity_decode($dmt['gdpr_customcode'], ENT_QUOTES, 'UTF-8');
			}
		}
    
		$tmanalytics .= "<!-- End Digital Marketing Tools Extension by aits.pk -->" ."\n";

		if (isset($this->request->get['debug'])) {
			if ($this->request->get['debug'] == 'log') {
				$this->gtm->Log($data);
				$this->gtm->Log($_data);
			}
			if ($this->request->get['debug'] == 'show') {
			echo '<pre>';
			if(substr(VERSION,0,1)=='1' ) {
				print_r($this->data);
			} else {
				print_r(isset($_data) ? $_data : []);
				print_r($data);
			}
			
			die;
			}
			if ($this->request->get['debug'] == 'twig') {
			if(substr(VERSION,0,1)=='1' ) {
				$this->data['twig_debug'] = true;
			} else {
				$data['twig_debug'] = true;
			}
			
			}
		}

		if(substr(VERSION,0,1)=='1' ) {
			$this->data['tmanalytics'] = $tmanalytics;
			$this->template = 'default/template/extension/analytics/dmt.tpl';
			$this->render();
			return;
		} else  {
			return $tmanalytics;
		}
	}
	
	public function usid() {
		ob_start();
		$json = [];
		$post_data = file_get_contents('php://input');
		if (!isset($post_data) || empty($post_data)) {
		    echo 'oops hey you know what to do ;';
		    die;
		}
		
		if(isset($post_data)) {
			$data = json_decode($post_data,true);
			if (isset($data['data'])) {
				$user_data = $data['data'];
			} else {
				$data['status'] = 'failed';
				$data['message'] = 'task aborted';
			}
		}
		
		$customer_data = [];
		
		if (isset($user_data) && $this->gtm->check_array($user_data)) {
		
			$customer_data = [
	                'user_id' 		=> (isset($user_data['user_id']) ? $user_data['user_id'] : ''),
		            'customer_id'   => (isset($user_data['external_id']) ? $user_data['external_id'] : ''),
					'external_id' 	=> (isset($user_data['external_id']) ? $user_data['external_id'] : ''),
	                'email'         => '',
					'telephone'     => '',
					'em' 			=> (isset($user_data['em']) ? $user_data['em'] : ''),
					'ph' 			=> (isset($user_data['ph']) ? $user_data['ph'] : ''),
					'ph_e164'		=> (isset($user_data['ph_e164']) ? $user_data['ph_e164'] : ''),
					'fn' 			=> (isset($user_data['fn']) ? $user_data['fn'] : ''),
					'ln' 			=> (isset($user_data['ln']) ? $user_data['ln'] : ''),
					'ad' 			=> (isset($user_data['ad']) ? $user_data['ad'] : ''),
					'ct' 			=> (isset($user_data['ct']) ? $user_data['ct'] : ''),
					'pc' 			=> (isset($user_data['pc']) ? $user_data['pc'] : ''),
					'st' 			=> (isset($user_data['st']) ? $user_data['st'] : ''),
					'cc'			=> (isset($user_data['cc']) ? $user_data['cc'] : ''),
	            ];
	            
	            $this->gtm->saveCustomerData($customer_data);
	            $data = [];
	            $data['status'] = 'okay';
				$data['message'] = 'task completed';
		}       
		ob_end_clean();
		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Cache-Control: no-cache, no-store, must-revalidate');
		$this->response->addHeader('Pragma: no-cache');
		$this->response->addHeader('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
		if (!isset($data)) {
			$data['status'] = 'failed';
			$data['message'] = 'task aborted';
		}
		$this->response->setOutput(json_encode($data));
	}
	
	public function gtm() {
		
		ob_start();

		$debug = false;
		$json = [];
		$route ='';
		$url ='';
		$tt_data = [];
		$sc_data = [];
		$json['tiktok'] = [];
		$json['snapchat'] = [];
		$messages = [];
		$order_id = false;
		$post_data = file_get_contents('php://input');
		$send_ga = false;
		$ga_data = false;

		if ($this->gtm == null) {

			if (is_file(DIR_SYSTEM . 'library/dmt.php')) {
	        	$dmt = new Dmt($this->registry);
  				$this->registry->set('gtm', $dmt);
			} 
		}	
		
		if (!method_exists($this->gtm, 'config')) {

			return false;
		}

		if (!isset($post_data) || empty($post_data)) {
		    echo 'oops hey you know what to do ;';
		    die;
		}
		if(isset($post_data)) {
			$data = json_decode($post_data,true);
		}

		if (!isset($data['data']['token'])) {
			echo 'hey send it proper;';
			die;
		}
		
		$token = $this->gtm->getAJAXtoken();

		if ($data['data']['token'] != $token) {
			echo 'opps i dont know you....';
			die;
		}
		
		$dimemsion = [];
		if (isset($data['data']['dimemsion'])) {
			$dimemsion = $data['data']['dimemsion'];
		}
		if (isset($data['data']['route'])) {
			$route = $data['data']['route'];
		}
		if (isset($data['data']['customer_data'])) {
			$customer_data = $data['data']['customer_data'];
		}

		if (isset($data['data']['url'])) {
			$url = $data['data']['url'];
		} 
		
		if (isset($data['data']['referrer'])) {
			$referrer = $data['data']['referrer'];
		} else {
			$referrer = '';
		}
		$event_id = (isset($data['data']['event_id']) ? $data['data']['event_id'] : false);
		
		if (!$event_id) {
			$event_id = $this->gtm->eventid();
		}

		$page_type = (isset($data['data']['page_type']) ? $data['data']['page_type'] : '');
		
		if (isset($route)) { 
			if ($route == 'account/success' || $route == 'account/login' || $route == 'account/logout' || $route == 'account/account' || $route == 'contact/success'|| $route == 'checkout/confirm') { 
				$this->gtm->resetCustomerData(); 
			}
		}
		
		if (isset($route) && $route=='product/search') {
			$page_type = 'search';
		}

		$setting_tags = [];
		
		$setting_tags[] = ['route'	=> $route];
		$dmt = $this->gtm->config();
		
		if (isset($dmt['debug_api']) && $dmt['debug_api']) {
			$debug = true;	
		}
		
		$dmt['event_id'] = $event_id;
		$dmt['url'] = $url;
		$dmt['referrer'] = $referrer;
		
		$tag_initiate = $this->gtm->getDataLayerSettings($setting_tags,$dmt,$dimemsion);
		
		$json['event_id'] = $dmt['event_id'];
		
		$user_data = [];
		
		if (isset($customer_data) && $this->gtm->check_array($customer_data)) {
			$dmt['em'] = (isset($customer_data['em']) ? $customer_data['em'] : false);
			$dmt['fn'] = (isset($customer_data['fn']) ? $customer_data['fn'] : false);
			$dmt['ln'] = (isset($customer_data['ln']) ? $customer_data['ln'] : false);
			$dmt['ph'] = (isset($customer_data['ph']) ? $customer_data['ph'] : false);
			$dmt['ph_e164'] = (isset($customer_data['ph_e164']) ? $customer_data['ph_e164'] : false);
			$dmt['ad'] = (isset($customer_data['ad']) ? $customer_data['ad'] : false);
			$dmt['ct'] = (isset($customer_data['ct']) ? $customer_data['ct'] : false);
			$dmt['pc'] = (isset($customer_data['pc']) ? $customer_data['pc'] : false);
			$dmt['st'] = (isset($customer_data['st']) ? $customer_data['st'] : false);
			$dmt['cc'] = (isset($customer_data['cc']) ? $customer_data['cc'] : false);
		}
		
		$user_data = [
			'em'			=> (isset($dmt['em']) ? $dmt['em'] : ''),
			'fn'			=> (isset($dmt['fn']) ? $dmt['fn'] : ''),
			'ln'			=> (isset($dmt['ln']) ? $dmt['ln'] : ''),
			'ph'			=> (isset($dmt['ph']) ? $dmt['ph'] : ''),
			'ph_e164'		=> (isset($dmt['ph_e164']) ? $dmt['ph_e164'] : ''),
			'ad'			=> (isset($dmt['ad']) ? $dmt['ad'] : ''),
			'ct'			=> (isset($dmt['ct']) ? $dmt['ct'] : ''),
			'pc'			=> (isset($dmt['pc']) ? $dmt['pc'] : ''),
			'st'			=> (isset($dmt['st']) ? $dmt['st'] : ''),
			'cc'			=> (isset($dmt['cc']) ? $dmt['cc'] : ''),
			'user_id'		=> (isset($dmt['user_id']) ? $dmt['user_id'] : ''),
			'external_id'	=> (isset($dmt['external_id']) ? $dmt['external_id'] : ''),
			'user_agent'	=> (isset($dmt['user_agent']) ? $dmt['user_agent'] : ''),
			'locale'		=> (isset($dmt['locale']) ? $dmt['locale'] : ''),
			'ip_address'	=> (isset($dmt['ip_address']) ? $dmt['ip_address'] : ''),
		];
		
		if (empty($user_data['external_id'])) {
			$user_data['external_id'] = $this->session->getId();
		}
		
		$user_data['external_id_hash'] = (!empty($user_data['external_id']) ? $this->gtm->getHash($user_data['external_id']) : '');
		
		if (isset($data['data']['order_id'])) {
			$order_id = $data['data']['order_id'];
		}
		
		if ($page_type == 'success') {

			if (isset($data['data']['ga'])) {
				$ga_data = $data['data']['ga'];
			} 

			if ($order_id) {
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "analytics_tracking WHERE order_id = '" . (int)$order_id . "'");
        		$hitdata = [];
				
				if ($query->num_rows) {
					$hitdata = $query->row;
				}
			} else {
				if ($debug) {
					$this->gtm->Log('API hit record not loaded, (dmt/gtm) Order id = ' . $order_id);
				}
			}

			if ($order_id && $ga_data && $send_ga) {

				$ga_events[] = [
					'name' => 'purchase',
					'params' => $ga_data,
				];
				
				$ga4_payload = [
					'client_id' => $dmt['cid'],
					'user_id' => (isset($hitdata['uid']) ? $hitdata['uid'] : $dmt['user_id']),
					'events' => $ga_events
				];
				if (isset($hitdata['hit_ga']) && $hitdata['hit_ga'] == 0) {
					$ga4_payload = json_encode($ga4_payload);
					$result = $this->gtm->googleAPI($ga4_payload, $dmt, false);
					if (isset($result['error']) && !$result['error']) {
						$this->db->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit_ga = '1' WHERE order_id = '" . (int)$order_id . "'");
					}
				} else {
					$this->gtm->Log('GA API Call miss as hit data is missing or already hit');
				}
				if ($debug) {

					$result_message = '';
					$platform = 'GA4';

					if ($page_type == 'success') {
						$result_message = $platform . ' [ Order: ' . $order_id . ' ] / Result: success data posted';
					} else {
						$result_message = $platform . ' / Result: Success data sent';
					}

					if (isset($result['error']) && $result['error']) {
						$result_message = $platform . ' / Result: error occourced data not posted';
					}
					$messages[$platform] = [
						'post_result' => (isset($result['message']) ? $result['message'] : ''),
						'message' => $result_message,
					];

				}
		
			}
		}	

		if ($dmt['pixel']) {
			
			if (isset($data['data']['pixel'])) {
				$fb_data = $data['data']['pixel'];
			}
			
			if (isset($dmt['fbc']) && !empty($dmt['fbc'])) {
				$fbc = $dmt['fbc'];	
			} else {
				$fbc = $this->gtm->getFbc();
			}
			
			if (isset($dmt['fbp']) && !empty($dmt['fbp'])) {
				$fbp = $dmt['fbp'];
			} else {
				$fbp = $this->gtm->getFbp();
			}
			
			$json['pixel'] = [
				'em'			=> $user_data['em'],
				'fn'			=> $user_data['fn'],
				'ln'			=> $user_data['ln'],
				'ph'			=> $user_data['ph'],
				'ct'			=> $user_data['ct'],
				'zp'			=> $user_data['pc'],
				'st'			=> $user_data['st'],
				'country'		=> $user_data['cc'],
				'external_id'	=> $user_data['external_id_hash'],
			];
			
			$pixel_user_data = [
				'em'				=> $user_data['em'],
				'fn'				=> $user_data['fn'],
				'ln'				=> $user_data['ln'],
				'ph'				=> $user_data['ph'],
				'ct'				=> $user_data['ct'],
				'zp'				=> $user_data['pc'],
				'st'				=> $user_data['st'],
				'country'			=> $user_data['cc'],
				'external_id'		=> $user_data['external_id_hash'],
				'client_ip_address' => $user_data['ip_address'],
	            'client_user_agent' => $user_data['user_agent'],
			];

			if (!empty($fbc) && $fbc) {
				$pixel_user_data['fbc']	= $fbc;
			}
			if (!empty($fbp) && $fbp) {
				$pixel_user_data['fbp']	= $fbp;
			}
			
			if ($dmt['fb_api'])	{
				$result = $this->gtm->facebookAPI($dmt,'PageView',false,$pixel_user_data,'0-'.$dmt['event_id']);
			}
			
			if ($dmt['fb_api'] && isset($fb_data) && isset($page_type) && !empty($url)) {

				switch ($page_type) {
					
					case "success":
						if (isset($hitdata['hit_fb']) && $hitdata['hit_fb'] == 0) {
							$result = $this->gtm->facebookAPI($dmt,'Purchase',$fb_data,$pixel_user_data,$dmt['event_id']);
							if (isset($result['error']) && !$result['error']) {
								$this->db->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit_fb = '1' WHERE order_id = '" . (int)$order_id . "'");
							}
						} else {
							$this->gtm->Log('FB API Call miss as hit data is missing or already hit');
						}
					    break;
					
					case "product":
						$result = $this->gtm->facebookAPI($dmt,'ViewContent',$fb_data,$pixel_user_data,$dmt['event_id']);
					    break;
					
					case "listing":
					    $result = $this->gtm->facebookAPI($dmt,'ViewCategory',$fb_data,$pixel_user_data,$dmt['event_id']);
					    break;
					    
					case "search":
					    $result = $this->gtm->facebookAPI($dmt,'Search',$fb_data,$pixel_user_data,$dmt['event_id']);
					    break;    
					
					case "checkout":
					    $result = $this->gtm->facebookAPI($dmt,'InitiateCheckout',$fb_data,$pixel_user_data,$dmt['event_id']);
					    break;
					
					case "cart":
					    $result = $this->gtm->facebookAPI($dmt,'ViewCart',$fb_data,$pixel_user_data,$dmt['event_id']);
					    break;
					
					case "contact":
						$result = $this->gtm->facebookAPI($dmt,'Contact',false,$pixel_user_data,$dmt['event_id']);
						break;  
							
					case "signup":
						$result = $this->gtm->facebookAPI($dmt,'CompleteRegistration',false,$pixel_user_data,$dmt['event_id']);
						break;
				}

				if ($debug) {

					$result_message = '';
					$platform = 'Meta Facebook';

					if ($page_type == 'success') {
						$result_message = $platform . ' [ Order: ' . $order_id . ' ] / Result: success data posted';
					} else {
						$result_message = $platform . ' / Result: Success data sent';
					}

					if (isset($result['error']) && $result['error']) {
						$result_message = $platform . ' / Result: error occourced data not posted';
					}
					$messages[$platform] = [
						'post_result' => (isset($result['message']) ? $result['message'] : ''),
						'message' => $result_message,
					];

				}
			} 
			
		}

		if ($dmt['snap_pixel_status']) {
			
			if (isset($dmt['sc_cookie1']) && !empty($dmt['sc_cookie1'])) {
				$sc_cookie1 = $dmt['sc_cookie1'];	
			} else {
				$sc_cookie1 = $this->gtm->getSc_cookie1();
			}
			
			if (isset($dmt['sccid']) && !empty($dmt['sccid'])) {
				$sccid = $dmt['sccid'];
			} else {
				$sccid = $this->gtm->getScCid();
			}
			
			$snappixelid =  $dmt['snap_pixel_id'];
			
			if (isset($data['data']['snapchat'])) {
				$sc_data = $data['data']['snapchat'];
			}
			if (isset($data['data']['snapchat_event']) && $data['data']['snapchat_event']) {
				$json['snapchat'] = $data['data']['snapchat_event'];
			}
			
			$json['snapchat']['event_id'] = $json['event_id'];
			$json['snapchat']['client_deduplication_id'] = $json['event_id'];
			

			$snapchat_user_data = [
				'user_agent'		=> $user_data['user_agent'],
				'client_ip_address'	=> $user_data['ip_address'], 
			];
			
			if (!empty($user_data['em'])) {
				$snapchat_user_data['em'] = $user_data['em'];
			}
			if (!empty($user_data['ph'])) {
				$snapchat_user_data['ph'] = $user_data['ph'];
			}
			if (!empty($user_data['fn'])) {
				$snapchat_user_data['fn'] = $user_data['fn'];
			}
			if (!empty($user_data['ln'])) {
				$snapchat_user_data['ln'] = $user_data['ln'];
			}
			if (!empty($user_data['ct'])) {
				$snapchat_user_data['ct'] = $user_data['ct'];
			}
			if (!empty($user_data['st'])) {
				$snapchat_user_data['st'] = $user_data['st'];
			}
			if (!empty($user_data['pc'])) {
				$snapchat_user_data['zp'] = $user_data['pc'];
			}
			if (!empty($user_data['country'])) {
				$snapchat_user_data['country'] = $user_data['country'];
			}
			if (!empty($user_data['external_id'])) {
				$snapchat_user_data['external_id'] = $user_data['external_id_hash'];
			}
			if (!empty($sccid)) {
				$snapchat_user_data['sc_click_id'] = $sccid;
			}
			if (!empty($sc_cookie1)) {
				$snapchat_user_data['sc_cookie1'] = $sc_cookie1;
			}

			$json['snapid'] = [
				'user_hashed_email'			=> $user_data['em'],
				'user_hashed_phone_number'	=> $user_data['ph'],
				'firstname'					=> $user_data['fn'],
				'lastname'					=> $user_data['ln'],
				'geo_city'					=> $user_data['ct'],
				'geo_region'				=> $user_data['st'],
				'geo_postal_code'			=> $user_data['pc'],
				'geo_country'				=> $user_data['cc'],
				'external_id'				=> $user_data['external_id_hash'],
			];
			
			if ($dmt['snap_pixel_api'] && !empty($dmt['snap_pixel_token']) && isset($sc_data) && !empty($url)) {
				
				$result = $this->gtm->snapchatAPI($dmt,'PAGE_VIEW',false,$snapchat_user_data,'0-'.$dmt['event_id']);
			
				switch ($page_type) {
					
					case "success":
						if (isset($hitdata['hit_snapchat']) && $hitdata['hit_snapchat']== 0) {
							$result = $this->gtm->snapchatAPI($dmt,'PURCHASE',$sc_data,$snapchat_user_data,$dmt['event_id']);
							if (isset($result['error']) && !$result['error']) {
								$this->db->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit_snapchat = '1' WHERE order_id = '" . (int)$order_id . "'");
							}
						} else {
							$this->gtm->Log('Snapchat API Call miss as hit data is missing or already hit');
						}
					    break;
					
					case "product":
						$result = $this->gtm->snapchatAPI($dmt,'VIEW_CONTENT',$sc_data,$snapchat_user_data,$dmt['event_id']);
					    break;
					
					case "listing":
					    $result = $this->gtm->snapchatAPI($dmt,'LIST_VIEW',$sc_data,$snapchat_user_data,$dmt['event_id']);
					    break;
					    
					case "search":
					    $result = $this->gtm->snapchatAPI($dmt,'SEARCH',$sc_data,$snapchat_user_data,$dmt['event_id']);
					    break;    
					
					case "checkout":
					    $result = $this->gtm->snapchatAPI($dmt,'START_CHECKOUT',$sc_data,$snapchat_user_data,$dmt['event_id']);
					    break;
					
					case "cart":
					    $result = $this->gtm->snapchatAPI($dmt,'CUSTOM_EVENT_1',$sc_data,$snapchat_user_data,$dmt['event_id']);
					    break;
					    
					case "confirm":
					    $result = $this->gtm->snapchatAPI($dmt,'CUSTOM_EVENT_3',$sc_data,$snapchat_user_data,$dmt['event_id']);
					    break;    
					
					case "contact":
						$result = $this->gtm->snapchatAPI($dmt,'CUSTOM_EVENT_2',false,$snapchat_user_data,$dmt['event_id']);
						break;  
							
					case "signup":
						$result = $this->gtm->snapchatAPI($dmt,'CUSTOM_EVENT_4',false,$snapchat_user_data,$dmt['event_id']);
						break;
				}

				if ($debug) {

					$result_message = '';
					$platform = 'Snapchat';

					if ($page_type == 'success') {
						$result_message = $platform . ' [ Order: ' . $order_id . ' ] / Result: success data posted';
					} else {
						$result_message = $platform . ' / Result: Success data sent';
					}

					if (isset($result['error']) && $result['error']) {
						$result_message = $platform . ' / Result: error occourced data not posted';
					}
					$messages[$platform] = [
						'post_result' => (isset($result['message']) ? $result['message'] : ''),
						'message' => $result_message,
					];
				}
			} 
		
		}
		
		if ($dmt['tiktok_status']) {
			
			if (isset($dmt['ttclid']) && !empty($dmt['ttclid'])) {
				$ttclid = $dmt['ttclid'];	
			} else {
				$ttclid = $this->gtm->getTtclid();
			}
			
			if (isset($dmt['ttp']) && !empty($dmt['ttp'])) {
				$ttp = $dmt['ttp'];
			} else {
				$ttp = $this->gtm->getTtp();
			}

			$tiktok_pixel =  $dmt['tiktok_code'];
			
			if (isset($data['data']['tiktok'])) {
				$tt_data = $data['data']['tiktok'];
			}
			
			$tiktok_user_data = [
				'email'			=> $user_data['em'],
				'phone'			=> $user_data['ph_e164'],
				'external_id'	=> $user_data['external_id_hash'],
				'ttp'			=> $ttp,
				'ttclid'		=> $ttclid,
				'ip'			=> $user_data['ip_address'],
				'user_agent'	=> $user_data['user_agent'],
				'first_name'	=> $user_data['fn'],
				'last_name'		=> $user_data['ln'],
				'city'			=> $user_data['ct'],
				'state'			=> $user_data['st'],
				'zip_code'		=> $user_data['pc'],
				'country'		=> $user_data['cc'],
			];
			
			$json['ttqid'] = [
				'email'			=> $user_data['em'],
				'phone_number'	=> $user_data['ph_e164'],
				'external_id'	=> $user_data['external_id_hash'],
				'first_name'	=> $user_data['fn'],
				'last_name'		=> $user_data['ln'],
				'city'			=> $user_data['ct'],
				'state'			=> $user_data['st'],
				'zip_code'		=> $user_data['pc'],
				'country'		=> $user_data['cc'],
			];
			
			if ($dmt['tiktok_api'] && !empty($dmt['tiktok_token']) && isset($tt_data) && !empty($url)) {
			
				switch ($page_type) {
					
					case "success":
						if (isset($hitdata['hit_tiktok']) && $hitdata['hit_tiktok']== 0) {
							$result = $this->gtm->tiktokAPI($dmt,'Purchase',$tt_data,$tiktok_user_data,$dmt['event_id']);
							if (isset($result['error']) && !$result['error']) {
								$this->db->query("UPDATE `" . DB_PREFIX . "analytics_tracking` SET hit_tiktok = '1' WHERE order_id = '" . (int)$order_id . "'");
							}
						} else {
							$this->gtm->Log('Tiktok API Call miss as hit data is missing or already hit');
						}
					    break;
					
					case "product":
						$result = $this->gtm->tiktokAPI($dmt,'ViewContent',$tt_data,$tiktok_user_data,$dmt['event_id']);
					    break;
					
					case "listing":
					    $result = $this->gtm->tiktokAPI($dmt,'ViewCategory',$tt_data,$tiktok_user_data,$dmt['event_id']);
					    break;
					    
					case "search":
					    $result = $this->gtm->tiktokAPI($dmt,'Search',$tt_data,$tiktok_user_data,$dmt['event_id']);
					    break;    
					
					case "checkout":
					    $result = $this->gtm->tiktokAPI($dmt,'InitiateCheckout',$tt_data,$tiktok_user_data,$dmt['event_id']);
					    break;
					
					case "cart":
					    $result = $this->gtm->tiktokAPI($dmt,'ViewCart',$tt_data,$tiktok_user_data,$dmt['event_id']);
					    break;
					    
					case "confirm":
					    $result = $this->gtm->tiktokAPI($dmt,'AddPaymentInfo',$tt_data,$tiktok_user_data,$dmt['event_id']);
					    break;    
					
					case "contact":
						$result = $this->gtm->tiktokAPI($dmt,'Contact',false,$tiktok_user_data,$dmt['event_id']);
						break;  
							
					case "signup":
						$result = $this->gtm->tiktokAPI($dmt,'CompleteRegistration',false,$tiktok_user_data,$dmt['event_id']);
						break;
				}

				if ($debug) {

					$result_message = '';
					$platform = 'Tiktok';

					if ($page_type == 'success') {
						$result_message = $platform . ' [ Order: ' . $order_id . ' ] / Result: success data posted';
					} else {
						$result_message = $platform . ' / Result: Success data sent';
					}

					if (isset($result['error']) && $result['error']) {
						$result_message = $platform . ' / Result: error occourced data not posted';
					}
					$messages[$platform] = [
						'post_result' => (isset($result['message']) ? $result['message'] : ''),
						'message' => $result_message,
					];

				}
			} 
		}

		if ($debug) {
			$output = '';
			if ($this->gtm->check_array($messages)) {
				foreach ($messages as $key => $value) {
					$output .= "\n" . strtoupper($key) . " ----> " . $value['post_result'] . "\n" . $value['message'] . "\n";
				}
				if (isset($page_type) && $page_type == 'success') {
					$this->gtm->Log('API Post Results - ' . strtoupper($page_type) . $output);
				}
			}
		}
		ob_end_clean();
		$json['user_data'] = $user_data;
		$json['datalayer'] = $tag_initiate;
		$this->response->addHeader('Content-Type: application/json');
		$this->response->addHeader('Cache-Control: no-cache, no-store, must-revalidate');
		$this->response->addHeader('Pragma: no-cache');
		$this->response->addHeader('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
		$this->response->setOutput(json_encode($json));
	}
	
	public function sendorder() {
		ob_start();
		$json['error'] = true ;
		$json['message'] = 'error';
		
		$dmt = $this->gtm->settings;

		if (isset($this->request->get['oid'])) {
			$order_id = $this->request->get['oid'];
		}

		if (isset($this->request->get['v'])) {
			$v= $this->request->get['v'];
		}

		if (!$this->validate($v)) {
			$json['message'] = 'error processing -> key not verified';
		} else {
			if (isset($order_id) && !empty($order_id)) {
				$json = $this->gtm->apiOrderSend($order_id) ;
			}
		}
		ob_end_clean();
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}
	
	public function refund() {
		ob_start();
		$json['error'] = true ;
		$json['message'] = 'error';
		$dmt = $this->gtm->config();

		if (isset($this->request->get['oid'])) {
			$order_id = $this->request->get['oid'];
		}

		if (isset($this->request->get['v'])) {
			$v= $this->request->get['v'];
		}

		if (!$this->validate($v)) {
			$json['message'] = 'error processing -> key not verified';
		} else { 
			if (isset($this->request->get['order_status_id'])) {
				$order_status_id = $this->request->get['order_status_id'];
	
			} else {
				$json['message'] = 'error processing order status missing';
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput($json);
			}
	
			$status = $dmt['return_status'];
			
			if (in_array($order_status_id, $status, true)) {
				$json['error'] = true;
				if (isset($this->request->get['oid'])) {
					$order_id = (int)$this->request->get['oid'];
					if (isset($order_id) && !empty($order_id)) {
					    $json = $this->gtm->apiOrderRefund($order_id);
					} else {
					    $this->gtm->Log('Refund Routine Order id invalid. ' );
					}
				}
			} else {
				$json['message'] = 'error processing couldnt validate order status check return status id in config';
			}
		}
		ob_end_clean();
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function hitorder() {
		
		$json['error'] = true ;
		$json['message'] = 'error';
		$dmt = $this->gtm->settings;
		if (isset($this->request->get['v'])) {
			$v= $this->request->get['v'];
		}

		if (!$this->validate($v)) {
			$json['message'] = 'error processing -> key not verified ';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($json);
		} 

		if ($this->validate($v)) { 
			if (isset($this->request->get['oid'])) {
				$order_id = $this->request->get['oid'];
				$json = $this->gtm->GAupdateorder($order_id);
				if ($json) {
					unset($this->session->data['dmt_order_id']);
					setcookie("dmt_order_id", "", time() - 3600);
				} else {
					$this->gtm->Log('DMT API couldnt update order status after hit');
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

	}

	public function validate($str) {
		$dmt = $this->gtm->config();
		if (strtolower($str) != strtolower($dmt['vs'])) {
		  $this->gtm->Log('DMT API couldnt verify key '. $str.' vs ' . $dmt['vs']);
		  return false;
		} else {
		  return true;
		}
	}
}
?>