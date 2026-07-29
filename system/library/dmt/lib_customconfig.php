<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
/* system/library/dmt/lib_customconfig.php */
/* Plug in code for adding Custom Purchase event to Meta Pixel you can add your custom codes in pixel_custom_event.php */
define('CUSTOM_COST_EVENT', false);
define('CUSTOM_COST_EVENT_NAME', 'shipping/payment');

/* shipping/payment for shipping method / payment method */

define('CUSTOM_PIXEL_EVENT', false);

/* CUSTOM Tiktok events, making true you can add your custom tiktok codes in custom_tiktok_event.php */
define('CUSTOM_TIKTOK_EVENT', false);

/* CONSENT Bypass Based on Cloudflare $_SERVER["HTTP_CF_IPCOUNTRY"]*/
/* PROVIDE 2 letter country code saperated by comma */
define('CONSENT_BYPASS', false);
define('CONSENT_BYPASS_COUNTRY', 'US,CA,AU,PK');

/* RETURN STATUS ID as per your opencart store */
define('RETURN_STATUS', '7,11');

/* ORDER TOTAL keys for calculating total */
define('TOTAL_PLUS', 'cod_fee,codfee_payment,handling,klarna_fee,low_order_fee,advancedcodfee,xfeepro');
define('TOTAL_MINUS', 'credit,reward,voucher,payment_discount,xfeepro');


?>