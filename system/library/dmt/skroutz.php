<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$tmanalytics .= "(function(a,b,c,d,e,f,g){a['SkroutzAnalyticsObject']=e;a[e]= a[e] || function(){
    (a[e].q = a[e].q || []).push(arguments);};f=b.createElement(c);f.async=true;
    f.src=d;g=b.getElementsByTagName(c)[0];g.parentNode.insertBefore(f,g);
  })(window,document,'script','https://analytics.skroutz.gr/analytics.min.js','skroutz_analytics');
  skroutz_analytics('session', 'connect', '" . $dmt['skroutz_siteid'] . "'); ". "\n";
  
  if ($page_type == 'success') {
    if (isset($_data['skroutz']['order'])) {
      $tmanalytics .= "whenAvailable(\"skroutz_analytics\", function(t) {skroutz_analytics('ecommerce', 'addOrder', " . json_encode($_data['skroutz']['order']) . ");});";
    }
      if (isset($_data['skroutz']['items'])){ 
		    foreach ($_data['skroutz']['items'] as $item) { 
			    $tmanalytics .= "whenAvailable(\"skroutz_analytics\", function(t) {skroutz_analytics('ecommerce', 'addItem', " . json_encode($item) . ");});";
		    }
	    }
  }
$tmanalytics .=  "\n";
