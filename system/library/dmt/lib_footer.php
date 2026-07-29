<?php 
/* Digital Marketing Tools v:13.0 15-04-2025*/
$ver = substr(VERSION,0,1);
$tmanalytics = '';
if (!method_exists($this, 'gtm')) {
		
	$dmt = $this->gtm->settings;
	$cdn_url = '';
	$tm_cdn = false;
	$eu_css = '';
	$eu_js = '';
	
	$j3popup = (isset($this->request->get['popup']) ? $this->request->get['popup'] : false) ;
    
    if(substr(VERSION,0,1)=='1' ) {
    	$this->data['route'] = (isset($this->request->get['route']) ? $this->request->get['route'] : 'common/home');
    	if ($this->data['route'] == 'journal2/quickview') {
    		$j3popup = 'quickview';
    	}
    }
	
if (isset($dmt['status']) && $dmt['status'] && !$j3popup) {

	if (isset($dmt['eu_cookie']) && $dmt['eu_cookie']) {
		if ($ver == '4') {
			$b_img = HTTP_SERVER . 'extension/dmt/catalog/view/javascript/dmt/cookie.png';
		} else {
			$b_img = 'catalog/view/javascript/dmt/cookie.png';
		}
		$tmanalytics .= "<script nitro-exclude=\"\">if(window.localStorage){localStorage.setItem('consent_default',false);}</script>";	
		if (isset($dmt['cookie_badge']) && $dmt['cookie_badge']=='1') {
		
			switch ($dmt['cookie_badge_position']) {
				case "bottom left":
					$eu_css = "div.cookie_button {width: 0;height: 0;position: fixed;z-index: 999;}div.cookie_button:hover {cursor: pointer;}div.cookie_button img {height: 25px;width: 25px;position: fixed;bottom: 10px;left: 10px;}div.cookie_button {border-bottom: 75px solid " . $dmt['cookie_badge_color'] . ";border-right: 75px solid transparent;left: 0px; bottom: 0px;}";
					break;
				case "bottom right":
					$eu_css = "div.cookie_button {width: 0;height: 0;position: fixed;z-index: 999;}div.cookie_button:hover {cursor: pointer;}div.cookie_button img {height: 25px;width: 25px;position: fixed;bottom: 10px;right: 10px;}div.cookie_button {border-bottom: 75px solid " . $dmt['cookie_badge_color'] . ";border-left: 75px solid transparent;right: 0px; bottom: 0px;}";
					break;
			}
			$tmanalytics .= "<style>" . $eu_css . "</style>";
			$tmanalytics .= "<div data-cc='show-preferencesModal' class=\"cookie_button\"><img src=\"" . $b_img . "\" alt=\"Cookies Settings\"></div>";
		}
	}

    if (isset($dmt['freshchat_code']) && !empty($dmt['freshchat_code']) && $dmt['freshchat_status'] == '1' ) {
		include('freshchat_main.php');
	}
	
	if (isset($dmt['hubspot_code']) && !empty($dmt['hubspot_code']) && $dmt['hubspot_status'] == '1' ) {
		include('hubspot_main.php');			
	}
	
	if (isset($dmt['smartsupp_code']) && !empty($dmt['smartsupp_code']) && $dmt['smartsupp_status'] == '1' ) {
		include('smartsupp_main.php');		
	}
	
	if (isset($dmt['zenchat_code']) && !empty($dmt['zenchat_code']) && $dmt['zenchat_status'] == '1' ) {
		include('zenchat_main.php');		
	}
			
	if (isset($dmt['zopimchat_code']) && !empty($dmt['zopimchat_code']) && $dmt['zopimchat_status'] == '1' ) {
		include('zopimchat_main.php');
	}

	if (isset($dmt['merchant_id']) && !empty($dmt['merchant_id']) && $dmt['greview'] == '1' && $dmt['greview_badge'] == '1') {
		if(isset($marketing_block) && !$marketing_block){
			include('google_review_badge.php');
		}
	}

	if (isset($dmt['chat_widget']) && !empty($dmt['chat_widget']) && $dmt['chat_widget_status']) {
		include( $this->path_include . 'chat_widget.php');
	}
	
	if(substr(VERSION,0,1)=='1' ) {
		$this->data['dmt'] = $dmt;
		$this->data['j3popup'] = $j3popup;
		$this->data['tmanalytics'] = $tmanalytics;
	} else {
		$data['dmt'] = $dmt;
		$data['j3popup'] = $j3popup;
		$data['tmanalytics'] = $tmanalytics;
	}
	if (isset($this->data)) {
		$this->data['tmanalytics'] = $tmanalytics;
		$this->data['dmt'] = $dmt;
		$this->data['j3popup'] = $j3popup;
	}

}
}
?>