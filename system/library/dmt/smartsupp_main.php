<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$tmanalytics .= "<script type=\"text/javascript\">
var _smartsupp = _smartsupp || {};
_smartsupp.key = '" . $dmt['smartsupp_code'] . "';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>";