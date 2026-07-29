<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
/* Plug in code for adding Custom Purchase event to Meta Pixel */
/* Removing the file is safe */
/* All data is available is $_data array() */

if (isset($dmt['custom_cost_event']) && $dmt['custom_cost_event']) {
    $showcost = true;
} else {
    $showcost = false;
}
$fb_custom = false;
$fb_custom2 = false;

$shipping_method = (isset($_data['shipping_method']) ? $_data['shipping_method'] : '');
$payment_method = (isset($_data['payment_method']) ? $_data['payment_method'] : '');
$shipping_method = str_replace(' / ', ' ', $shipping_method);
$shipping_method = str_replace('/ ', ' ', $shipping_method);
$shipping_method = str_replace('  ', '', $shipping_method);
$payment_method = str_replace(' / ', ' ', $payment_method);
$payment_method = str_replace('/ ', ' ', $payment_method);
$payment_method = str_replace('  ', '', $payment_method);

$userdata = $this->gtm->formatUserdata($dmt);
$pixel_user_data = $userdata['pixel_user_data'];

if (isset($_data['fb_data'])) {
    if ($showcost) {
        $fb_custom = [];
        $fb_custom = [
            'content_category' 	=> 'Custom',
            'content_ids' 		=> (isset($_data['fb_data']['content_ids']) ? $_data['fb_data']['content_ids'] : ''),
            'currency' 			=> (isset($_data['fb_data']['currency']) ? $_data['fb_data']['currency'] : ''),
            'num_items' 		=> (isset($_data['fb_data']['num_items']) ? $_data['fb_data']['num_items'] : 0),
            'value' 			=> (isset($_data['cost']) ? $_data['cost'] : 0),
            'content_name' 		=> $shipping_method . ' ' . $payment_method,
            'content_type' 		=> 'product',
        ];
    }
    $fb_custom2 = [];
    $fb_custom2 = [
        'content_category' 	=> 'Custom2',
        'content_ids' 		=> (isset($_data['fb_data']['content_ids']) ? $_data['fb_data']['content_ids'] : ''),
        'currency' 			=> (isset($_data['fb_data']['currency']) ? $_data['fb_data']['currency'] : ''),
        'num_items' 		=> (isset($_data['fb_data']['num_items']) ? $_data['fb_data']['num_items'] : 0),
        'content_name' 		=> $shipping_method . ' ' . $payment_method,
        'info'              => $shipping_method . ' ' . $payment_method,
        'content_type' 		=> 'product',
    ];
}


switch ($page_type) {
	
    case "success":
        if ($fb_custom && $this->gtm->check_array($fb_custom)) {
            $fb_custom_eventid = $this->gtm->eventid();
            $fbq .= "\n"."fbq('trackCustom','Custom'," .  json_encode($fb_custom) . ",{'eventID': '" . $fb_custom_eventid . "'});";
            $fb_custom['event_id'] = $fb_custom_eventid;
            $result = $this->gtm->facebookAPI($dmt,'Custom',$fb_custom,$pixel_user_data,$fb_custom_eventid);
        }
        if ($fb_custom2 && $this->gtm->check_array($fb_custom2)) {
            $fb_custom_eventid = $this->gtm->eventid();
            $fbq .= "\n"."fbq('trackCustom','Custom2'," .  json_encode($fb_custom2) . ",{'eventID': '" . $fb_custom_eventid . "'});";
            $fb_custom2['event_id'] = $fb_custom_eventid;
            $result = $this->gtm->facebookAPI($dmt,'Custom2',$fb_custom2,$pixel_user_data,$fb_custom_eventid);

        }
        
        break;

    case "checkout":    

        break;

    
    case "product":

        break;

    case "listing":    

        break;

    case "cart":

        break;

    case "home":    

        break;
}
?>