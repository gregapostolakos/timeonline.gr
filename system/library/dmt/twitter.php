<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$twq = "\n";

if (isset($dmt['twitter_pageview']) && !empty($dmt['twitter_pageview'])) {
	$twq .= "twq('event', '" . $dmt['twitter_pageview'] . "', {});" . "\n";
}

if (isset($_data['twitter_data']) && isset($_data['twitter_event']) && $_data['twitter_event'] != '0') {

	$twq .= "twq('event', '" . $_data['twitter_event'] . "', " . json_encode($_data['twitter_data']) . ");" . "\n";

}

$tmanalytics .= "
  !function(e,t,n,s,u,a){e.twq||(s=e.twq=function(){s.exe?s.exe.apply(s,arguments):s.queue.push(arguments);
},s.version='1.1',s.queue=[],u=t.createElement(n),u.async=!0,u.src='https://static.ads-twitter.com/uwt.js',
a=t.getElementsByTagName(n)[0],a.parentNode.insertBefore(u,a))}(window,document,'script');
twq('config','" . $dmt['twitter_tag'] . "');" . $twq;
