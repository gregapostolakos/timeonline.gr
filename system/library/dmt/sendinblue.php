<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$sendinblue = false;
if (isset($_data['sendinblue'])) {
    $sendinblue = $_data['sendinblue'];
}
if (!$sendinblue) {
    $sendinblue = array(
        'email'         => (isset($dmt['email']) ? $dmt['email'] : ''),
        'first_name'    => (isset($dmt['first_name']) ? $dmt['first_name'] : ''),
        'last_name'     => (isset($dmt['last_name']) ? $dmt['last_name'] : ''),
        'phone'         => (isset($dmt['phone']) ? $dmt['phone'] : ''),
        'id'            => (isset($dmt['userid']) ? $dmt['userid'] : ''),
        'location'      => (isset($dmt['city']) ? $dmt['city'] : ''),
        'country'       => (isset($dmt['country']) ? $dmt['country'] : '')
        );
}
$tmanalytics .= '
(function() {
    window.sib = {
        equeue: [],
        client_key: "' . $dmt['sendinblue_code'] .'"
    };';
if (isset($dmt['email']) && !empty($dmt['email'])) {    
    $tmanalytics .= 'window.sib.email_id = "' . $dmt['email'] . '";';
}
$tmanalytics .= "  
	window.sendinblue = {};  
    for (var j = ['track', 'identify', 'trackLink', 'page'], i = 0; i < j.length; i++) {
    (function(k) {
        window.sendinblue[k] = function() {
            var arg = Array.prototype.slice.call(arguments);
            (window.sib[k] || function() {
                    var t = {};
                    t[k] = arg;
                    window.sib.equeue.push(t);
                })(arg[0], arg[1], arg[2]);
            };
        })(j[i]);
    }";
$tmanalytics .= '    
    var n = document.createElement("script"),
        i = document.getElementsByTagName("script")[0];
    n.type = "text/javascript", n.id = "sendinblue-js", n.async = !0, n.src = "https://sibautomation.com/sa.js?key=" + window.sib.client_key, i.parentNode.insertBefore(n, i), window.sendinblue.page();
})();';
if (isset($sendinblue['properties'])) {
$tmanalytics .= "sendinblue.identify('" . $sendinblue['email'] . "', {
  'FIRSTNAME': '" . (isset($sendinblue['properties']['FIRSTNAME']) ? $sendinblue['properties']['FIRSTNAME'] : '') . "',
  'LASTNAME' : '" . (isset($sendinblue['properties']['LASTNAME']) ? $sendinblue['properties']['LASTNAME'] : '') . "',
  'id': '" . (isset($sendinblue['eventdata']['id']) ?  $sendinblue['eventdata']['id'] : '') . "',
  'LOCATION' : '" . (isset($sendinblue['properties']['LOCATION']) ? $sendinblue['properties']['LOCATION'] : '') . "',
  'COUNTRY' : '" . (isset($sendinblue['properties']['COUNTRY']) ? $sendinblue['properties']['COUNTRY'] : '') . "',
  'LANGUAGE' : '" . $dmt['language'] . "',
  'SMS'  : '" . (isset($sendinblue['properties']['TELEPHONE']) ? $sendinblue['properties']['TELEPHONE'] : '') . "',
  'TELEPHONE'  : '" . (isset($sendinblue['properties']['TELEPHONE']) ? $sendinblue['properties']['TELEPHONE'] : '') . "'});" ;  
}
if (isset($_data['sendinblue'])) {
    switch ($page_type) {
	    case "success":
			$this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent');
	    	break;
			
		case "checkout":
			$this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent');
			break;
			
		case "cart":
			$this->gtm->sendinbluePost($_data['sendinblue'], 'trackEvent');
			break;
			
	}
}