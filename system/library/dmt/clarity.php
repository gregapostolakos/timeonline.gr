<?php
/* Digital Marketing Tools v:13.3 27-08-2025*/
$tmanalytics .= '(function(c,l,a,r,i,t,y){
        c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
        t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
        y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
    })(window, document, "clarity", "script", "'. $dmt['clarity_id'] .'");' . "\n";

$clarity_analytics = 'denied';
$clarity_marketing = 'denied';
if(isset($tracking_block) && !$tracking_block){
    $clarity_analytics = 'granted';
}

if(isset($marketing_block) && !$marketing_block){
$clarity_marketing = 'granted';
} 

$tmanalytics .= "window.clarity('consentv2', {ad_storage: '" . $clarity_marketing . "', analytics_storage: '" . $clarity_analytics . "'});". "\n";