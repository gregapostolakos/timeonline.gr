<?php
/******************************************************
 * @package Digital Marketing Tools for OC1.5x, OC2x,3x
 * @version 13.6
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2017-2026 aits.xyz All rights reserved.
 * @email:info@aits.pk. 
 * $date: 08-FEB-2026
 * ADMIN/MODEL
*******************************************************/

class ModelExtensionModuleDMT extends Model {
	
	const TMV = '13.6';
	const TMC = 'GTM-TVKX9K8R';

    public function getTagmanger() {
		
		$tagmanager = array();
		$PREFIX = '';
		
		$cid = (isset($_COOKIE['_ga']) ? $_COOKIE['_ga'] : '');

		if(substr(VERSION,0,1)=='3' ) {
			$PREFIX = 'analytics_';
		}
		$vs = $this->getNewURL();
		$vs = base64_encode($vs);
		
		$tagmanager = array (
			'vs'					=> $vs
			);

		return $tagmanager;
		
	}

	public function getConfig() {
		$_data = [];
		$ver = substr(VERSION,0,1);
		$sub_ver = substr(VERSION,0,3);
		$store_id = 0;
		if (!isset($this->request->get['store_id'])) {
			$store_id = 0;
		} else {
			$store_id = $this->request->get['store_id'];
		}

		$token = isset($this->session->data['user_token']) ? 'user_token='.$this->session->data['user_token'] : 'token='.$this->session->data['token'];

		if ($ver == '1') {
			$this->language->load('module/dmt');
		} else {
			$this->load->language('extension/analytics/dmt');
		}
		$module_url = 'extension/analytics/tagmanager';
		$parent_url = 'marketplace/extension';
		$about_url = 'https://licence.aits.xyz/tagmanager/about.php';
		$PREFIX = 'analytics_';
		
		if ($sub_ver == '2.3') {
			$module_url = 'extension/analytics/tagmanager';
            $parent_url = 'extension/extension';
			$about_url = 'https://licence.aits.xyz/tagmanager/about.php';
			$PREFIX = '';
		}
		if ($sub_ver == '2.0') {
			$module_url = 'module/tagmanager';
			$parent_url = 'extension/module';
			$about_url = 'https://licence.aits.xyz/tagmanager/about.php';
			$PREFIX = '';
		}
		if ($sub_ver == '2.1' || $sub_ver == '2.2') {
			$module_url = 'analytics/tagmanager';
			$parent_url = 'extension/analytics';
			$about_url = 'https://licence.aits.xyz/tagmanager/about.php';
			$PREFIX = '';
        }
		if ($ver == '1') {
			$module_url = 'module/tagmanager';
			$parent_url = 'extension/module';
			$about_url = 'https://licence.aits.xyz/tagmanager/about15x.php';
			$PREFIX = '';
		}
		$ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $about_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        if(curl_errno($ch)){
			$_data['about_us_text'] = "";	
        } else {
           $_data['about_us_text'] = $result;
        }
        curl_close($ch);

		if (!$this->columnExistsInTable('analytics_tracking', 'gclid')) {
			$this->upgradev11x();
		}	
		
		$_data['change_store'] = $this->url->link($module_url, $token, 'SSL');
		$_data['text_version'] = self::TMV;
    	$_data['primary']	  = self::TMC;
		$_data['heading_title'] = 'Digital Marketing Tools v' . $_data['text_version'] ;
		$_data['text_container'] = sprintf($this->language->get('text_container'), $_data['primary']);
		$_data['help_container'] = sprintf($this->language->get('help_container'), $_data['primary']);
		$_data['product_map'] = array ('product_id','model','sku','model_product_id','product_id_currency');
		$_data['product_title'] = array ('name','brand_model');
		$_data['consent_layout'] = array ('box', 'box inline','box wide', 'cloud', 'cloud inline', 'bar','bar inline');
		$_data['pref_layout'] = array ('box', 'bar','bar wide');
		$_data['consent_position'] = array ('top left','top center', 'top right', 'middle left', 'middle center', 'middle right', 'bottom left', 'bottom center', 'bottom right','top', 'bottom');
		$_data['pref_position'] = array ('left', 'right');
		$_data['consent_theme'] = array ('light','dark','dark-turquoise', 'light-funky','elegant-black');
		$_data['badge_positions'] = array ('bottom left','bottom right');
		$_data['badge_color'] = array ('red','blue');
		$_data['page_routes'] = array ('page view','product view', 'add to cart','view cart', 'checkout','purchase', 'contact', 'signup','journal popup');
		$_data['alt_checkout'] = 'extension/quickcheckout/checkout'. "\n" .'onepagecheckout/checkout'. "\n" .'quickcheckout/checkout'. "\n" .'quick_checkout/checkout';
		$_data['alt_confirm'] = 	'extension/quickcheckout/confirm';
		$_data['alt_success'] = 	'extension/ordersuccess' . "\n" . 'extension/checkout/eghlresponse/success';
		$_data['short_date']	= $this->language->get('date_format_short');
		$_data['jumptab'] = (isset($this->request->get['tab']) ? $this->request->get['tab'] : '99');

		$this->load->model('localisation/order_status');

		$_data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		return $_data;
	}
	
	public function GetTagmanagerVariables($data=array(), $store_id=0) {
		
		if (!$this->check_array($data)) {
			$data = [];
		}
		
		$switches = [
					'pagecache', 'ajax', 'cache', 'customer_data', 'tax_override', 'debug', 'debug_api', 'ga4_status', 
					'adword', 'adword_ec', 'aw_shipping_exclude','aw_tax_exclude','aw_optional','adword2','remarketing',
					'pixel','fb_api','fb_shipping_exclude', 'fb_tax_exclude','alt_currency_status','snap_pixel_status', 
					'tiktok_status', 'twitter_status', 'pinterest_status', 'skroutz_status', 'luckyorange_status','glami_status',
					'bing_status', 'greview', 'greview_badge', 'admitad_status', 
					'admitad_retag_status', 'sendinblue_status', 'linkwise_status', 'performant_status', 'performant_currency_status',
					'clarity_status', 'paypal_status', 'hotjar_status','zenchat_status','zopimchat_status','freshchat_status', 
					'hubspot_status','smartsupp_status', 'eu_cookie', 'eu_cookie_enforce','cookie_badge', 'server', 'admin', 'mp',
					'debug_order', 'matomo_status','cj_status','da_status', 'disable_interaction','snap_pixel_api',
					'tiktok_api','snap_pixel_api_debug','tiktok_api_debug','tiktok_api_testcode','fb_api_debug','cookie_custom','extended_menu',
					'consent_external','chat_widget_status','fb_purchase','cookie_reject','pinterest_api','pinterest_api_debug','tiktok_alt_currency_status',
					'api_async',
		];
					
		$text = [
				'customcode', 'tax_override_value', 'ga4_mid', 'ga4_api', 'conversion_id', 'conversion_label',
				'aw_merchant_id','aw_feed_country', 'aw_feed_language',  'conversion_id2', 
				'conversion_label2', 'conversion_route2', 'conversion_value2','pixelcode', 'fb_token', 'pixel_test_code', 'fb_catalog_id',
				'alt_currency', 'snap_pixel_id', 'tiktok_code', 'twitter_tag', 'pinterest_tag', 'skroutz_siteid','luckyorange_siteid', 
				'glami_code', 'bing_uetid', 'merchant_id', 'admitad_code', 'admitad_category', 
				'admitad_additional_type', 'admitad_invoice_broker', 'admitad_invoice_category','admitad_retag_code1','admitad_retag_code2',
				'admitad_retag_code3', 'admitad_retag_code4', 'admitad_retag_code5', 'sendinblue_code', 'linkwise_code', 'linkwise_decimal',
				'performant_code', 'performant_confirm', 'performant_currency','clarity_id','paypal_code', 'hotjar_siteid', 'zenchat_code',
				'zopimchat_code', 'freshchat_code', 'freshchat_host','hubspot_code', 'smartsupp_code', 'cookie_position', 'cookie_position_mobile',
				'cookie_bg_popup','cookie_text_popup', 'cookie_bg_button', 'cookie_text_button', 'cookie_heading_color', 'gdpr_customcode',
				'cookie_badge_position', 'cookie_badge_color', 'cookie_title_1', 'cookie_text_1','cookie_text2_1', 'cookie_url_1','cookie_link_1', 
				'cookie_button1_1', 'cookie_button2_1', 'cookie_button3_1', 'cookie_essential_1', 'cookie_analytics_1', 'cookie_marketing_1',
				'server_url', 'ptitle', 'id_prefix','pmap', 'id_suffix', 'code', 'vs', 'twitter_purchase',
				'twitter_payment','twitter_checkout','twitter_addcart','twitter_addwishlist','twitter_viewcontent','twitter_search','twitter_pageview',
				'matomo_code','cookie_essential_title_1', 'cookie_analytics_title_1', 'cookie_marketing_title_1','cj_code','cj_currency','cj_currency_value',
				'cj_actionid','da_code','consent_theme', 'consent_layout', 'consent_position', 'pref_layout', 'consent_position','consent_modal_title', 
				'consent_modal_desc','consent_modal_accept','consent_modal_reject','consent_modal_setting','consent_modal_link','pref_modal_title',
				'pref_modal_accept','pref_modal_reject','pref_modal_save','pref_modal_heading','pref_modal_text','pref_modal_necessary_title',
				'pref_modal_necessary_desc','pref_modal_analytics_title','pref_modal_analytics_desc','pref_modal_marketing_title','pref_modal_marketing_desc',
				'pref_modal_moreinfo_title','pref_modal_moreinfo_desc','snap_pixel_token','tiktok_token','ga2_pageview', 'ga2_productview', 'ga2_addtocart',
				'ga2_viewcart',	'ga2_checkout', 'ga2_contact','ga2_signup',	'ga2_pageview_value', 'ga2_contact_value', 'ga2_signup_value', 
				'cookie_b1_background','cookie_b2_background','cookie_b3_background','cookie_b1_color','cookie_b2_color','cookie_b3_color','chat_widget',
				'aw_tagid','exclude_ip','pinterest_token','tiktok_alt_currency',
		];
		
		$text_array = ['order_status'];
		
		foreach($switches as $key) {
			if (!array_key_exists($key, $data)) {
				$data[$key] = 0;
			}
        }
        
        foreach($text as $key) {
			if (!array_key_exists($key, $data)) {
				$data[$key] ='';
			}
        }
        
        foreach($text_array as $key) {
			if (!array_key_exists($key, $data)) {
				$data[$key] =array();
			}
        }
        
        return $data;
	}

	public function getlang() {
		
		$languageVariables = [
		    'heading_title','primary','entry_server','entry_server_url','text_edit','text_enabled','text_disabled','text_signup',
		    'text_about','text_about_cookie','text_version','heading_container','text_container','text_order','entry_primary',
			'entry_status','entry_admin','entry_cache','entry_debug','entry_customer_data','entry_ga4_status','entry_ga4_mid',
			'entry_ga4_api','entry_custom_dimension1','entry_custom_dimension2','entry_custom_dimension3','entry_custom_dimension4',
			'entry_custom_dimension5','entry_custom_dimension6','entry_custom_dimension7','entry_custom_dimension8',
			'entry_custom_dimension9','entry_custom_dimension','entry_greview','entry_aw_tagid',
			'entry_greview_badge','entry_merchant_id','entry_remarketing','entry_product','entry_ptitle','entry_id_prefix',
			'entry_id_suffix','entry_customcode','entry_adword','entry_adword2','entry_conversion_id','entry_conversion_label',
			'entry_adword_ec','entry_conversion_id2','entry_aw_optional','entry_aw_merchant_id','entry_aw_feed_country',
			'entry_aw_feed_language','entry_pixel','entry_pixelcode','entry_fb_api','entry_fb_token','entry_alt_currency',
			'entry_alt_currency_status','entry_alt_currency_val','entry_fb_catalog_id','entry_twitter_status','entry_twitter_tag',
			'entry_pinterest_status','entry_pinterest_tag','entry_glami_status','entry_glami_code','entry_hotjar_status',
			'entry_hotjar_siteid','entry_luckyorange_status','entry_luckyorange_siteid','entry_tiktok_status','entry_tiktok_code',
			'entry_clarity_status','entry_clarity_siteid','entry_bing_status','entry_bing_uetid','entry_skroutz_status',
			'entry_skroutz_siteid',	'entry_skroutz_manual_tax','entry_skroutz_manual_tax_value','entry_skroutz_payment_fee',
			'entry_admitad_status','entry_admitad_code','entry_admitad_category',
			'entry_admitad_additional_type','entry_admitad_invoice_broker','entry_admitad_invoice_category','entry_admitad_retag_status',
			'entry_admitad_retag_code','entry_admitad_retag_code1','entry_admitad_retag_code2','entry_admitad_retag_code3',
			'entry_admitad_retag_code4','entry_admitad_retag_code5','entry_linkwise_status','entry_linkwise_code','entry_linkwise_decimal',
			'entry_freshchat_status','entry_freshchat_code','entry_freshchat_host','entry_snap_pixel_status','entry_snap_pixel_id',
			'entry_performant_status','entry_performant_code','entry_performant_confirm','entry_affgateway_status',
			'entry_affgateway_code','entry_sendinblue_status','entry_sendinblue_code','entry_zopim_status','entry_zopim_code',
			'entry_zenchat_status','entry_zenchat_code','entry_zopimchat_status','entry_zopimchat_code','entry_hubspot_status',
			'entry_hubspot_code','entry_smartsupp_status','entry_smartsupp_code','entry_paypal_status','entry_paypal_code',
			'entry_route_checkout','entry_route_success','column_oid','column_status','column_action','help_server','help_server_url',
			'help_ga4','help_ga4_api','help_secondary','help_conversion_id','help_conversion_label','help_conversion_value2',
			'help_remarketing','help_product','help_ptitle','help_cache','help_ac','help_ac_value',	'help_route','help_route_checkout',
			'help_route_success','help_id_prefix','help_id_suffix','help_customcode','help_aw','help_aw_ec','help_aw_secondary',
			'help_aw_optional','help_aw_merchant','help_aw_country','help_aw_language','help_cenforce','help_ctitle','help_ctext',
			'help_clink','help_debug','button_save','button_cancel','button_send','button_refund','error_permission','error_primary','error_secondary',
			'error_warrning','entry_debug_api','help_debug_api','entry_performant_tax','entry_performant_tax_value','entry_performant_currency',
			'help_customer_data','entry_pixel_test_code','help_pixel_test_code','entry_ajax','help_ajax','entry_shipping_cost','entry_tax_override',
			'entry_tax_override_value','help_tax_override','help_tax_override','entry_tax_exclude','entry_shipping_exclude','help_tax_exclude',
			'help_shipping_exclude','help_cenforce', 'help_ctext2', 'help_clinktext', 'help_customcodecookie','help_tax_override_value', 'help_ga4_id',
			'help_pixel_id', 'help_pixel_token', 'help_snap_id', 'help_tiktok_id','help_twitter_id', 'help_pinterest_id','help_skroutz_id',
			'help_glami_id', 'help_bing_id','help_luckyorange_id', 'entry_debug_order', 'help_debug_order', 'column_payment_code','column_payment','column_date','column_api',
			'entry_twitter_purchase', 'entry_twitter_payment','entry_twitter_checkout','entry_twitter_addcart','entry_twitter_addwishlist','entry_twitter_viewcontent',
			'entry_twitter_search','entry_twitter_pageview','help_twitter_purchase','help_twitter_payment','help_twitter_checkout','help_twitter_addcart',
			'help_twitter_addwishlist','help_twitter_viewcontent','help_twitter_search','help_twitter_pageview','text_store','entry_matomo_status','entry_matomo_code',
			'help_matomo_code','entry_cj_code','entry_cj_status','entry_cj_actionid', 'entry_cj_currency', 'entry_cj_currency_value', 'help_cj_code', 
			'help_cj_actionid', 'help_cj_currency', 'help_cj_currency_value', 'entry_da_status', 'entry_da_code','text_consent_title', 'text_consent_text',
			'text_consent_accept', 'text_consent_reject', 'text_consent_setting', 'text_consent_link', 'text_pref_modal_title', 'text_pref_modal_accept',
			'text_pref_modal_reject', 'text_pref_modal_save', 'text_pref_modal_heading', 'text_pref_modal_text', 'text_pref_modal_necessary_title', 
			'text_pref_modal_necessary_desc','text_pref_modal_analytics_title', 'text_pref_modal_analytics_desc','text_pref_modal_marketing_title', 
			'text_pref_modal_marketing_desc','text_pref_modal_moreinfo_title', 'text_pref_modal_moreinfo_desc','entry_eu_cookie', 'entry_eu_cookie_enforce', 
			'entry_cookie_layout', 'entry_cookie_position', 'entry_consent_modal_title', 'entry_consent_modal_desc', 'entry_consent_modal_accept', 
			'entry_consent_modal_reject', 'entry_consent_modal_setting', 'entry_consent_modal_link', 'entry_pref_modal_title', 'entry_pref_modal_accept', 
			'entry_pref_modal_reject','entry_pref_modal_save','entry_pref_modal_heading','entry_pref_modal_text','entry_pref_modal_necessary_title',
			'entry_pref_modal_necessary_desc','entry_pref_modal_analytics_title','entry_pref_modal_analytics_desc','entry_pref_modal_marketing_title',
			'entry_pref_modal_marketing_desc','entry_pref_modal_moreinfo_title','entry_pref_modal_moreinfo_desc','entry_consent_theme', 'entry_consent_layout',
			'Consent Modal Layout','entry_consent_position','entry_pref_layout','entry_pref_position','entry_disable_interaction','entry_cookie_badge',
			'entry_cookie_badge_position','entry_cookie_badge_color','entry_snap_pixel_api','entry_snap_pixel_token','entry_tiktok_api','entry_tiktok_token',
			'help_snap_pixel_api','help_tiktok_api','help_snap_pixel_token','help_tiktok_token','entry_snap_pixel_api_debug','help_snap_pixel_api_debug',
			'entry_tiktok_api_testcode','entry_tiktok_api_debug','entry_fb_api_debug','help_tiktok_api_debug','help_tiktok_api_testcode','help_fb_api_debug',
			'help_fb_api_testcode','entry_ga2_pageview', 'entry_ga2_productview', 'entry_ga2_addtocart','entry_ga2_viewcart','entry_ga2_checkout', 
			'entry_ga2_contact','entry_ga2_signup','entry_ga2_purchase', 'entry_ga2_pageview_value', 'entry_ga2_contact_value','entry_ga2_signup_value',
			'help_ga2_pageview', 'help_ga2_productview', 'help_ga2_addtocart', 'help_ga2_viewcart', 'help_ga2_checkout', 'help_ga2_contact', 'help_aw_tagid',
			'help_ga2_signup', 'entry_cookie_b1_background','entry_cookie_b2_background','entry_cookie_b3_background','entry_cookie_b1_color','entry_cookie_b2_color',
			'entry_cookie_b3_color','help_button_color','entry_cookie_custom','entry_conversion_value2','entry_extended_menu', 'entry_chat_widget','entry_chat_widget_status',
			'help_extended_menu','entry_consent_external','help_consent_external','help_disable','help_fb_api_status','help_alt_currency','help_consent','help_chat_widget',
			'column_ads','entry_exclude_ip','entry_order_status','help_exclude_ip','help_order_status','entry_fb_purchase','help_fb_purchase',
			'entry_cookie_reject','help_cookie_reject','entry_pinterest_api', 'entry_pinterest_token', 'entry_pinterest_api_debug','help_pinterest_api',
			'help_pinterest_token', 'help_pinterest_debug','entry_tiktok_alt_currency','entry_tiktok_alt_currency_status','entry_api_async','help_api_async',
			'column_total',
	];

		foreach ($languageVariables as $languageVariable) {
    		$data[$languageVariable] = $this->language->get($languageVariable);
		}
		return $data;
	}

	public function getAdsHit($order_id) {
		$sql  = "SELECT * FROM " . DB_PREFIX . "analytics_tracking` WHERE order_id = '" . (int)$order_id . "')";
		$query = $this->db->query($sql);

		if ($query->row) {
			return $query->row;
		} else {
			return false;
		}

	}

	public function getTransactions($filter_data, $store_id,$tagmanager) {
		
		$sql  = "SELECT *, (SELECT ot.name FROM " . DB_PREFIX . "order_status ot WHERE os.order_status_id = ot.order_status_id AND ot.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status , os.date_added, os.payment_code,os.payment_method,os.total FROM `" . DB_PREFIX . "analytics_tracking` AS o LEFT JOIN `" . DB_PREFIX ."order` AS os ON o.order_id = os.order_id";
		$sql .= " WHERE os.order_status_id > 0 AND os.store_id = '" . $store_id . "'";
		$sql .= " ORDER BY id DESC";
		if (isset($filter_data['start']) || isset($filter_data['limit'])) {
			if ($filter_data['start'] < 0) {
				$filter_data['start'] = 0;
			}

			if ($filter_data['limit'] < 1) {
				$$filter_data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$filter_data['start'] . "," . (int)$filter_data['limit'];
		}
		$query = $this->db->query($sql);
		$orders = array();
		foreach ($query->rows as $result) {

			$show_apisend = false;

			if ($result['hit']=="0" && $result['hit_ga']=="0") {
				$show_apisend = true;
			}
			if ($tagmanager['fb_api'] && $result['hit']=="0" && $result['hit_fb']=="0") {
				$show_apisend = true;
			}
			if ($tagmanager['snap_pixel_api'] && $result['hit']=="0" && $result['hit_snapchat']=="0") {
				$show_apisend = true;
			}
			if ($tagmanager['tiktok_api'] && $result['hit']=="0" && $result['hit_tiktok']=="0") {
				$show_apisend = true;
			}
			if ($result['hit']=="0" && $result['hit_ga']) {
				$result['hit'] = 3;
			}

			$ads_hit = '';

			$image_url = 'view/javascript/dmt/img/';

			if (!empty($result['gclid'])){ 
				$ads_hit .= "<img src='" . $image_url . "ga.png' alt='google' width='25'/>";
			}
			if (!empty($result['fbc'])){ 
				$ads_hit .= "<img src='" . $image_url . "meta2.png' alt='meta' width='25'/>";
			}
			if (!empty($result['ttclid'])){ 
				$ads_hit .= "<img src='" . $image_url . "tiktok.png' alt='tiktok' width='25'/>";
			}
			if (!empty($result['sc_click_id'])){ 
				$ads_hit .= "<img src='" . $image_url . "snapchat.png' alt='snapchat' width='25'/>";
			}

			$hit_image = '';

			if ($result['hit_ga']== "1"){
				$hit_image .= "<img src='" . $image_url . "google-ga4.png' alt='google' width='25'/>";
			}
			if ($result['hit_fb']== "1"){
				$hit_image .= "<img src='" . $image_url . "facebook.png' alt='meta'  width='25'/>";
			}
			if ($result['hit_tiktok']== "1"){
				$hit_image .= "<img src='" . $image_url . "tiktok.png' alt='tiktok'  width='25'/>";
			}
			if ($result['hit_snapchat']== "1"){
				$hit_image .= "<img src='" . $image_url . "snapchat.png' alt='snapchat'  width='25'/>";
			}

			$api_action = '';
			
			if ($show_apisend) {
				$api_action = '<div id="div-send-' . $result['id'] . '" data-loading-text="loading" onclick="hitorder(' . $result['order_id'] . ',' . $result['id'] . ');" class="btn btn-primary"><i class="fa fa-send-o"></i> SEND </div>';
			}

			$status = '';

			if ($result['hit']=="0"){ 
				$status = '<span style="color:red">Not Sent</span>';
			} elseif ($result['hit'] == "1"){ 
				$status = 'Sent';
			} elseif ($result['hit'] == "2"){ 
				$status = 'Refunded/Canceled';
			} elseif ($result['hit'] == "3"){ 
				$status = 'Sent Manually';
			}

			$orders[] = array(
				'id'            => $result['id'],
				'order_id'      => $result['order_id'],
				'show_apisend'  => $show_apisend,
				'api_action'    => $api_action,
				'hit'			=> $result['hit'],
				'hit_ga'		=> $result['hit_ga'],
				'hit_fb'		=> $result['hit_fb'],
				'hit_snapchat'	=> $result['hit_snapchat'],
				'hit_tiktok'	=> $result['hit_tiktok'],
				'ads_hit'		=> $ads_hit,
				'hit_image'		=> $hit_image,
				'status'        => $status,
				'payment_method'=> strip_tags($result['payment_method']) . ' (Code: ' . $result['payment_code'] . ')',
				'total'         => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date'			=> date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'order_status'  => $result['order_status'] ? $result['order_status'] : '',
			);
		}


		return $orders;
	}
	
	public function getTotalTransactions($data = array(),$store_id=0) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "analytics_tracking` AS o LEFT JOIN `" . DB_PREFIX ."order` AS os ON o.order_id = os.order_id";
		$sql .= " WHERE os.order_status_id > 0 AND os.store_id = '" . $store_id . "'";

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getLog() {
		
		$file = DIR_LOGS . 'dmt.log';
		$log = [];
		$log['error'] = false;
		$log['message'] = '';
		$log['data'] ='';

		if (file_exists($file)) {
			$size = filesize($file);

			if ($size >= 5242880) {
				$suffix = array('B','KB','MB','GB','TB','PB','EB','ZB','YB');
				$i = 0;
				while (($size / 1024) > 1) {
					$size = $size / 1024;
					$i++;
				}
				$log['error'] = true;
				$log['message'] = sprintf($this->language->get('error_warning'), basename($file), round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i]);
				return $log;
			} else {
				$log_text = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$log_text = array_reverse($log_text);
				$log['data'] = implode("\n", $log_text);
				return $log;
			}
		}
	}

	public function getSettingValue($key, $store_id = 0) {
		$ver = substr(VERSION,0,1);
		$sub_ver = substr(VERSION,0,3);
		
		if ($sub_ver == '2.0') {
			$this->load->model('setting/setting');
			$data = $this->model_setting_setting->getSetting($key,$store_id);
			if (isset($data[$key])) {
				$settings = json_encode($data[$key]);
			} else {
				$settings = false;
			}
			return $settings;
			
		} 
		
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "setting WHERE store_id = '" . (int)$store_id . "' AND `key` = '" . $this->db->escape($key) . "'");
			
		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}
	
	public function getNewURL() {
		$url = false;
		$temp = $this->request->server['SERVER_NAME'];
		$explode = explode(".", $temp);
		$counter = $this->check_array($explode);
		if ($counter) {
			$i = count($explode);
			if ($i == 2) {
				$url = $explode[0] . '.' . $explode[1];
			} elseif ($i == 3) {
				if (strtolower($explode[0]) != 'www' ) {
					$url = $explode[0] . '.' . $explode[1] . '.' . $explode[2];
				} else {
					$url = $explode[1] . '.' . $explode[2];
				}
			} elseif ($i == 4) {
				$url = $explode[1] . '.' . $explode[2] . '.' . $explode[3];
			}
		}
		return $url;
	}
	
	public function check_array($var) {
        return is_array($var)
          || $var instanceof \Countable
          || $var instanceof \SimpleXMLElement
          || $var instanceof \ResourceBundle;
    }
    
    public function upgrade() {

		return;
    	
    	$data = array();
    	
    	if(substr(VERSION,0,1)=='3' ) {
			$PREFIX = 'analytics_';
			$ver = '3';
		} else {
			$ver = '2';
			$PREFIX = '';
		}
		
		if (!isset($this->request->get['store_id'])) {
			$store_id = 0;
		} else {
			$store_id = $this->request->get['store_id'];
		}
		
		$dmt = $this->model_setting_setting->getSetting($PREFIX . 'dmt',$store_id);
		$dmt_status = (isset($dmt[$PREFIX . 'dmt_status']) ? $dmt[$PREFIX . 'dmt_status'] : false);
		$dmt_data = (isset($dmt[$PREFIX . 'dmt_data']) ? $tagmanager[$PREFIX . 'dmt_data'] : false);
		
		if ($dmt_status || $dmt_data){
			return false;
		}
		
		$tagmanager = $this->model_setting_setting->getSetting('tagmanager',$store_id);
    	$tagmanager_data = (isset($tagmanager[$PREFIX . 'tagmanager_data']) ? $tagmanager[$PREFIX . 'tagmanager_data'] : false);
    	$tagmanager_status = (isset($tagmanager[$PREFIX . 'tagmanager_status']) ? $tagmanager[$PREFIX . 'tagmanager_status'] : false);
    	
    	$dmt = array(
    		$PREFIX . 'dmt_status'	=> $tagmanager_status,
			$PREFIX . 'dmt_data'	=> $tagmanager_data,	
    		);
    		
    	$this->model_setting_setting->editSetting($PREFIX . 'dmt', $dmt, $store_id);
    	
        return $true;
    }

	public function writeConfig($eu_cookie,$store_id) {
		$this->load->model('localisation/language');
		$file = DIR_CATALOG .'view/javascript/dmt/consent_' . $store_id. '.js';
		$myfile = fopen($file, "w") or die("Unable to open file!");
		$languages = $this->model_localisation_language->getLanguages();
		$text = "import 'https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.umd.js';" . "\n";
		$text .='
		CookieConsent.run({
		cookie: {
        	name: "_consents",
    	},
		guiOptions: {
			consentModal: {
				layout: "' . $eu_cookie['consent_layout'] . '",
				position: "' . $eu_cookie['consent_position'] . '",
				equalWeightButtons: true,
				flipButtons: false
			},
			preferencesModal: {
				layout: "' . $eu_cookie['pref_layout'] . '",
				position: "' . $eu_cookie['pref_position'] . '",
				equalWeightButtons: true,
				flipButtons: false
			}
		},
		onFirstConsent: () => {
	       var c_functionality = "granted";
			var c_security = "granted";
			var c_analytics = "denied";
			var c_marketing = "denied";
			var c_personalization = "denied";
			var c_url_passthrough = "true";
			var c_ads_data_redaction = "true";
			var c_consent = "revoke";
			
		';

		if (isset($eu_cookie['eu_cookie_enforce']) && $eu_cookie['eu_cookie_enforce']) { 

	    $text .='    
	        if(CookieConsent.acceptedCategory("analytics")){
            	var c_analytics = "granted";
				var c_url_passthrough = "false";
			} else {
        		var c_analytics = "denied";
				var c_url_passthrough = "true";
        	}
        
	        if(CookieConsent.acceptedCategory("marketing")){
	        	var c_marketing = "granted";
				var c_personalization = "granted";
				var c_consent = "grant";
				var c_ads_data_redaction = "false";
			} else {
				var c_marketing = "denied";
				var c_personalization = "denied";
				var c_consent = "revoke";
				var c_ads_data_redaction = "true";
			}		
		';
			
		$text .="
			var consent_default = false;
			var consent_action = 'update';
			var wait_for_update = 500;
			
			gtag('set', 'url_passthrough', c_url_passthrough);
			gtag('set', 'ads_data_redaction', c_ads_data_redaction);
			
			gtag('consent', consent_action, {
				'security_storage': c_security,
				'functionality_storage' : c_functionality,
				'analytics_storage': c_analytics,
				'ad_storage': c_marketing,
				'ad_user_data': c_marketing,
				'ad_personalization': c_personalization,
				'personalization_storage': c_personalization
			});

			whenAvailable('fbq', function(t) {fbq('consent', c_consent);});

			console.log('Consent updated');
			
		";
					
		} else {

		$text .="gtag('consent', 'update', {
					'security_storage': 'granted',
					'functionality_storage' : 'granted',
					'analytics_storage': 'granted',
					'ad_storage': 'granted',
					'ad_user_data':'granted',
					'ad_personalization':'granted',
					'personalization_storage': 'granted'					
				});
				gtag('set', 'ads_data_redaction', false);
				gtag('set', 'url_passthrough', false);
		 ";
		}		

	    $text .='
	    },
	
	    onConsent: () => {
			
		},
	
	    onChange: () => {';
		$text .='
			var c_functionality = "granted";
			var c_security = "granted";
			var c_analytics = "denied";
			var c_marketing = "denied";
			var c_personalization = "denied";
			var c_url_passthrough = "true";
			var c_ads_data_redaction = "true";
			var c_consent = "revoke";
			
		';
		if (isset($eu_cookie['eu_cookie_enforce']) && $eu_cookie['eu_cookie_enforce']) { 
		
			$text .='
	    
			if(CookieConsent.acceptedCategory("analytics")){
            	var c_analytics = "granted";
				var c_url_passthrough = "false";
			} else {
        		var c_analytics = "denied";
				var c_url_passthrough = "true";
        	}
        
	        if(CookieConsent.acceptedCategory("marketing")){
	        	var c_marketing = "granted";
				var c_personalization = "granted";
				var c_consent = "grant";
				var c_ads_data_redaction = "false";
			} else {
				var c_marketing = "denied";
				var c_personalization = "denied";
				var c_consent = "revoke";
				var c_ads_data_redaction = "true";
			}		
			';

			$text .="

			var consent_default = false;
			var consent_action = 'update';
			var wait_for_update = 500;
			
			gtag('set', 'url_passthrough', c_url_passthrough);
			gtag('set', 'ads_data_redaction', c_ads_data_redaction);
			
			gtag('consent', consent_action, {
				'security_storage': c_security,
				'functionality_storage' : c_functionality,
				'analytics_storage': c_analytics,
				'ad_storage': c_marketing,
				'ad_user_data': c_marketing,
				'ad_personalization': c_personalization,
				'personalization_storage': c_personalization
			});

			whenAvailable('fbq', function(t) {fbq('consent', c_consent);});
			console.log('Consent Updated...');";

		} else {
			
			$text .="gtag('consent', 'update', {
					'security_storage': 'granted',
					'functionality_storage' : 'granted',
					'analytics_storage': 'granted',
					'ad_storage': 'granted',
					'ad_user_data':'granted',
					'ad_personalization':'granted',
					'personalization_storage': 'granted'
				});
				gtag('set', 'ads_data_redaction', false);
				gtag('set', 'url_passthrough', false);";

		}
	    
		$text .='},
	    
	    categories: {
	        necessary: {
	            readOnly: true,
	            enabled: true
	        },
	        analytics: {
	            autoClear: {
	                cookies: [
	                    {
	                        name: /^(_ga|_gid)/
	                    }
	                ]
	            }
	        },
	        marketing: {
				autoClear: {
	                cookies: [
	                    {
	                        name: /^(kl_csrftoken|__kla_id)/
	                    }
	                ]
	            }
	        	
	        },
	    },
		language: {
			default: "en",
			autoDetect: "document",
			translations: {';
		$i = 0;	
		foreach ($languages as $language) {
			$code = substr($language['code'],0,2);
			$lang = $language['language_id'];
			
			if (isset($eu_cookie['consent_modal_title_'.$lang])) {
			if ($i > 0) { $text .= ',';}
			$i++;
			$text .= $code . ': {
								consentModal: {
											title: "' . $this->cleanVar($eu_cookie['consent_modal_title_'.$lang]) . '",
											description: "' . $this->cleanVar($eu_cookie['consent_modal_desc_'.$lang]) . '",
											closeIconLabel: "",
											acceptAllBtn: "' .$this->cleanVar($eu_cookie['consent_modal_accept_'.$lang]) . '",
											acceptNecessaryBtn: "' . $this->cleanVar($eu_cookie['consent_modal_reject_'.$lang]) . '",
											showPreferencesBtn: "' . $this->cleanVar($eu_cookie['consent_modal_setting_'.$lang]) . '",
											footer: "' . $this->cleanVar($eu_cookie['consent_modal_link_'.$lang]) . '"
										},
										preferencesModal: {
											title: "' . $this->cleanVar($eu_cookie['pref_modal_title_'.$lang]) . '",
											closeIconLabel: "Close modal",
											acceptAllBtn: "' . $this->cleanVar($eu_cookie['pref_modal_accept_'.$lang]) . '",
											acceptNecessaryBtn: "' . $this->cleanVar($eu_cookie['pref_modal_reject_'.$lang]) . '",
											savePreferencesBtn: "' . $this->cleanVar($eu_cookie['pref_modal_save_'.$lang]) . '",
											serviceCounterLabel: "Service|Services",
											sections: [
												{
													title: "' . $this->cleanVar($eu_cookie['pref_modal_heading_'.$lang]) . '",
													description: "' . $this->cleanVar($eu_cookie['pref_modal_text_'.$lang]) . '"
												},
												{
													title: "' . $this->cleanVar($eu_cookie['pref_modal_necessary_title_'.$lang]) . ' <span class=\"pm__badge\">Always Enabled</span>",
													description: "' . $this->cleanVar($eu_cookie['pref_modal_necessary_desc_'.$lang]) . '",
													linkedCategory: "necessary"
												},
												{
													title: "' . $this->cleanVar($eu_cookie['pref_modal_analytics_title_'.$lang]) . '",
													description: "' . $this->cleanVar($eu_cookie['pref_modal_analytics_desc_'.$lang]) . '",
													linkedCategory: "analytics"
												},
												{
													title: "' . $this->cleanVar($eu_cookie['pref_modal_marketing_title_'.$lang]) . '",
													description: "' . $this->cleanVar($eu_cookie['pref_modal_marketing_desc_'.$lang]) . '",
													linkedCategory: "marketing"
												},
												{
													title: "' . $this->cleanVar($eu_cookie['pref_modal_moreinfo_title_'.$lang]) . '",
													description: "' . $this->cleanVar($eu_cookie['pref_modal_moreinfo_desc_'.$lang]). '"
												}
											]
										}
									}
				';
			}
		}	
		$text .= '
			}
		},
			disablePageInteraction: ' . $eu_cookie['disable_interaction'] . '
		});
		
		';
		fwrite($myfile, $text);
		fclose($myfile);
	}

	public function upgradev11x() {

		$coloumn_array = array('event_id','gclid','fbp','fbc','ttp','ttclid','sc_click_id','sc_cookie1','locale');
		
		foreach($coloumn_array as $column) {
			if (!$this->columnExistsInTable('analytics_tracking', $column)) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "analytics_tracking` ADD `" . $column ."` VARCHAR(164) NULL;");
			}
		}
		
		$coloumn_array = array('hit_ga','hit_fb', 'hit_tiktok', 'hit_snapchat','refund');

		foreach($coloumn_array as $column) {
			if (!$this->columnExistsInTable('analytics_tracking', $column)) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "analytics_tracking` ADD `" . $column ."` TINYINT(1) NOT NULL DEFAULT '0';");
			}
		}

		$coloumn_array = array('geoid','sr', 'vp', 'dr');

		foreach($coloumn_array as $column) {
			if ($this->columnExistsInTable('analytics_tracking', $column)) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "analytics_tracking` DROP `" . $column ."` ;");
			}
		}
	}

	public function createDB() {

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "analytics_tracking` (
			`id` int(11) NOT NULL AUTO_INCREMENT,
			`order_id` int(15) DEFAULT NULL,
			`cid` varchar(128) DEFAULT NULL,
			`uid` varchar(64) DEFAULT NULL,
			`ip` varchar(100) DEFAULT NULL,
			`ul` varchar(64) DEFAULT NULL,
			`tid` varchar(24) DEFAULT NULL,	
			`user_agent` varchar(250) DEFAULT NULL,
			`currency_code` varchar(11) DEFAULT NULL,
			`currency_id` int(11) DEFAULT NULL,
			`event_id` varchar(64) DEFAULT NULL,
			`gclid` VARCHAR(164) DEFAULT NULL,
			`fbp` varchar(164) DEFAULT NULL,
			`fbc` varchar(164) DEFAULT NULL,
			`ttp` varchar(164) DEFAULT NULL,
			`ttclid` varchar(164) DEFAULT NULL,
			`sc_click_id` varchar(164) DEFAULT NULL,
			`sc_cookie1` varchar(164) DEFAULT NULL,
			`hit` tinyint(1) NOT NULL DEFAULT '0',
			`hit_ga` tinyint(1) NOT NULL DEFAULT 0,
			`hit_fb` tinyint(1) NOT NULL DEFAULT 0,
			`hit_tiktok` tinyint(1) NOT NULL DEFAULT 0,
			`hit_snapchat` tinyint(1) NOT NULL DEFAULT 0,
			`refund` tinyint(1) NOT NULL DEFAULT 0,
			 PRIMARY KEY (`id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1;");
	}

	private function columnExistsInTable($table, $column) {
        $query = $this->db->query("DESC `" . DB_PREFIX . $table . "`;");
        foreach($query->rows as $row) {
            if($row['Field'] == $column) {
                return true;
            }
        }
        return false;
    }

	public function getTLD() {
        $tlds = array('abc','ac','adult','ae','af','afl','africa','ag','am', 'anz', 'ao', 'ar', 'arab',  
					'as', 'asia', 'aw', 'aws', 'ax', 'axa', 'az', 'ba', 'be', 'bf', 'bg', 'bh', 'bi', 'bio',
					'biz', 'bj', 'br', 'com', 'edu', 'gov', 'info', 'jobs', 'mil', 'mobi', 'net', 'org','xyz',
					'post','pro', 'tel', 'travel', 'xxx','cz','sm','us', 'uk', 'ca', 'au', 'de', 'fr', 'in',
					'cn', 'ru', 'jp','br', 'za', 'mx','es', 'it', 'nl', 'se', 'no', 'fi', 'dk', 'pl', 'ch',
					'ro', 'gr','rs', 'sas',
					
					'co.uk', 'org.uk', 'gov.uk', 'ac.uk','com.au', 'net.au', 'org.au', 'edu.au','co.in',
					'net.in', 'org.in','com.br', 'net.br', 'org.br','co.nz', 'gov.nz','co.za', 'org.za',
					'uk.com', 'com.gr','com.ua','com.kw','com.pk','net.pk','org.pk','com.tr',
					
				);
		return $tlds;
	}

	public function cleanVar($var=false){
		if ($var) {
			$var = trim($var);
			$var = str_replace("\n", '', $var);
			$var = str_replace("\r", '<br>', $var);
			$var = html_entity_decode($var, ENT_QUOTES, 'UTF-8');
			$var = str_replace('"', '\"', $var);
			return $var;
		}
	}
	
    
}					
?>