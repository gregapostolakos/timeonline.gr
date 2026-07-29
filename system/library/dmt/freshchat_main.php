<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$tmanalytics .= '<script type="text/javascript" platform="freshchat" nitro-exclude="">
function initFreshChat() {
window.fcWidget.init({
token: "' . $dmt['freshchat_code'] . '",
host: "' . (!empty($dmt['freshchat_host']) ? $dmt['freshchat_host'] : 'https://wchat.freshchat.com') .'" });
}
function initialize(i,t){var e;i.getElementById(t)?initFreshChat():((e=i.createElement("script")).id=t,e.async=!0,e.src="https://wchat.freshchat.com/js/widget.js",e.onload=initFreshChat,i.head.appendChild(e))}function initiateCall(){initialize(document,"freshchat-js-sdk")}window.addEventListener?window.addEventListener("load",initiateCall,!1):window.attachEvent("load",initiateCall,!1);
</script>';
?>