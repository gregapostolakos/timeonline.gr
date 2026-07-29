<?php
/* Digital Marketing Tools v:13.2 24-JUNE-2025*/
$consent_theme = (isset($dmt['consent_theme']) ? $dmt['consent_theme'] : '');

if ($consent_theme == 'dark'){
	$tmanalytics .= "<script type=\"text/javascript\" platform=\"consent-theme\" nitro-exclude=\"\">document.documentElement.classList.add('cc--darkmode');</script>";
} elseif ($consent_theme == 'dark-turquoise') {
	$tmanalytics .= "<script type=\"text/javascript\" platform=\"consent-theme\" nitro-exclude=\"\">document.documentElement.classList.add('cc--dark-turquoise');</script>";
} elseif ($consent_theme == 'light-funky') {
	$tmanalytics .= "<script type=\"text/javascript\" platform=\"consent-theme\" nitro-exclude=\"\">document.documentElement.classList.add('cc--light-funky');</script>";
} elseif ($consent_theme == 'elegant-black') {
	$tmanalytics .= "<script type=\"text/javascript\" platform=\"consent-theme\" nitro-exclude=\"\">document.documentElement.classList.add('cc--elegant-black');</script>";
}
$store_id = (int)$this->config->get('config_store_id');
$server_url = $this->config->get('config_ssl');
$vc = rand();
$tmanalytics .= '<link rel="stylesheet" href="' . $server_url . 'catalog/view/javascript/dmt/cookieconsent.css">';
$tmanalytics .= '<link rel="stylesheet" href="' . $server_url . 'catalog/view/javascript/dmt/consent-themes.css">';
if (isset($dmt['cookie_custom']) && $dmt['cookie_custom']) {
	$tmanalytics .= '<style>[data-role=all] { ';
	$tmanalytics .= (!empty($dmt['cookie_b1_background']) ? 'background-color:' . $dmt['cookie_b1_background'] . '!important;border-color:' . $dmt['cookie_b1_background'] . '!important;' : '');
	$tmanalytics .= (!empty($dmt['cookie_b1_color']) ? 'color:' . $dmt['cookie_b1_color'] . '!important;' : '');
	$tmanalytics .= '}[data-role=necessary] { ';
	$tmanalytics .= (!empty($dmt['cookie_b2_background']) ? 'background-color:' . $dmt['cookie_b2_background'] . '!important;' : '');
	$tmanalytics .= (!empty($dmt['cookie_b2_color']) ? 'color:' . $dmt['cookie_b2_color'] . '!important;' : '');		
	$tmanalytics .= '}[data-role=save] { ';	
	$tmanalytics .= (!empty($dmt['cookie_b3_background']) ? 'background-color:' . $dmt['cookie_b3_background'] . '!important;' : '');
	$tmanalytics .= (!empty($dmt['cookie_b3_color']) ? 'color:' . $dmt['cookie_b3_color'] . '!important;' : '');		
	$tmanalytics .= '}[data-role=show] { ';	
	$tmanalytics .= (!empty($dmt['cookie_b3_background']) ? 'background-color:' . $dmt['cookie_b3_background'] . '!important;' : '');
	$tmanalytics .= (!empty($dmt['cookie_b3_color']) ? 'color:' . $dmt['cookie_b3_color'] . '!important;' : '');		
	$tmanalytics .= '}</style>' . "\n";
}
if (isset($dmt['cookie_reject']) && $dmt['cookie_reject']) {
	$tmanalytics .= '<style>.cm-wrapper .cm__btn[data-role="necessary"] {display: none !important;}</style>' . "\n";
	}
$tmanalytics .= '<script type="module" nitro-exclude="" src="' . $server_url . 'catalog/view/javascript/dmt/consent_' . $store_id . '.js?vc=' . $vc .'" defer></script>';
$tmanalytics .= "\n";
?>