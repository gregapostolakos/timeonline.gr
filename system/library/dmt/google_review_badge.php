<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$tmanalytics .= '<script src="https://apis.google.com/js/platform.js?onload=renderBadge" async defer></script>';
$tmanalytics .= '<script>window.renderBadge = function() {   var ratingBadgeContainer = document.createElement("div");
				  document.body.appendChild(ratingBadgeContainer);';
$tmanalytics .= " window.gapi.load('ratingbadge', function() {";
$tmanalytics .= ' window.gapi.ratingbadge.render(ratingBadgeContainer, {"merchant_id": ' . $dmt['merchant_id'] .', "position": "BOTTOM_LEFT"}); }); }</script>';
?>