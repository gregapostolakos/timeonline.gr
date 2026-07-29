<?php
/* Digital Marketing Tools v:13.0 20-04-2025*/
if (isset($_data['affiliate_gateway'])) {
$desc_text = '';
foreach ($_data['affiliate_gateway'] as $result){
	$desc_text = $result['name'];
}
$tmanalytics .= "<iframe height='1' width='1' scrolling='no' marginheight='0' marginwidth='0' frameborder='0' src='https://event.2performant.com/events/salecheck?amount="
				. $_data['performant_value'] . "&campaign_unique=" . $dmt['performant_code'] . "&confirm=" . $dmt['performant_confirm'] . "&description=" . $desc_text . 
				"&transaction_id=" . $_data['order_id'] . "'></iframe>";
}