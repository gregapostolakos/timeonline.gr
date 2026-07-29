<?php
// EN-GB
$_['text_version']                      = '';
$_['heading_title']                     = '<img style="vertical-align:top;width:16px;height:16px;padding-right:4px;" src="view/javascript/dmt/img/icon2.png"/>Digital Marketing Tools (Analytics / Pixel / Google Ads)';
$_['doc_title']                         = 'Digital Marketing Tools (Analytics / Pixel / Google Ads)';

// Text
$_['primary']                           = '';
$_['text_extension']                    = 'Extensions';
$_['text_success']	                    = 'Success: You have modified Digital Marketing Tools!';
$_['text_signup']                       = 'Login to your <a href="https://tagmanager.google.com" target="_blank"><u>Google Tag Manager</u></a> account and after creating your website workspace container copy and paste the Workspace code i.e. GTM-XXXXX';
$_['text_default']                      = 'Default';
$_['text_edit']                         = 'Edit Settings';
$_['text_about']                        = '<p>For support please visit our support center </p><br><div class="col-sm-12"><center><div class="btn btn-success col-sm-8" style="margin:15px"><a href="https://support.aits.xyz/" target="blank" style="color:white;padding-top:10px;" >Support</a></div><center></div><p>We are always improving and adding new features. If your required tracking platform is not supported by our extension feel free to email us at support@aits.xyz and we will assist you.</p><p>We also offer custom developement and Opencart trouble shooting & development. If you have any query please email us at support@aits.xyz';
$_['text_about_cookie']                 = '<p>Cookie Consent will display a popup on user first visit. There are two modes standard and enforced (EU / GDPR). </p><p>When enfored mode is enabled the user must accept cookies before any 3rd party tracking code is fired.</p>'; 
$_['heading_container']                 = 'Google Tag Manager Container';
$_['text_container']                    = '<p>You can use our pre-built container to run the extension using the <br>container id <strong>%s</strong> in primary field.<br></p>';
$_['text_order']                        = '';
$_['text_consent_title']                = 'Cookie Consent';
$_['text_consent_text']                 = 'We use cookies to give you the best online experience. By using our website you agree to our use of cookies in accordance with our Cookie Policy';
$_['text_consent_accept']               = 'Accept all';
$_['text_consent_reject']               = 'Reject all';
$_['text_consent_setting']              = 'Manage preferences';
$_['text_consent_link']                 = '<a href="#link">Privacy Policy</a>\n<a href="#link">Terms and conditions</a>';
$_['text_pref_modal_title']             = 'Consent Preferences Center';
$_['text_pref_modal_accept']            = 'Accept All';
$_['text_pref_modal_reject']            = 'Reject all';
$_['text_pref_modal_save']              = 'Save preferences';
$_['text_pref_modal_heading']           = 'Cookie Usage';  
$_['text_pref_modal_text']              = 'We use cookies to give you the best online experience. By using our website you agree to our use of cookies in accordance with our Cookie Policy';
$_['text_pref_modal_necessary_title']   = 'Necessary Cookies';  
$_['text_pref_modal_necessary_desc']    = 'Necessary cookies are required to enable the basic features of this site, such as providing secure log-in or adjusting your consent preferences. These cookies do not store any personally identifiable data.';  
$_['text_pref_modal_analytics_title']   = 'Analytics Cookies';  
$_['text_pref_modal_analytics_desc']    = 'Analytical cookies are used to understand how visitors interact with the website. These cookies help provide information on metrics such as the number of visitors, bounce rate, traffic source, etc.';
$_['text_pref_modal_marketing_title']   = 'Marketing Cookies';  
$_['text_pref_modal_marketing_desc']    = 'Advertisement cookies are used to provide visitors with customized advertisements based on the pages you visited previously and to analyze the effectiveness of the ad campaigns.';
$_['text_pref_modal_moreinfo_title']    = 'More information';  
$_['text_pref_modal_moreinfo_desc']     = 'For any query in relation to my policy on cookies and your choices, please <a class="cc__link" href="?route=information/contact">contact us</a>.';  


// Entry
/* GENERAL */
$_['text_store']	                = 'Stores';
$_['entry_primary']					= 'TAG Manager Container id' ;
$_['entry_server']					= 'Tag Manager Custom URL' ;
$_['entry_status']					= 'Extension Status';
$_['entry_cache']					= 'Enable DB Query Cache';
$_['entry_debug']					= 'Debug Message in Error Log';
$_['entry_debug_api']				= 'Debug API Message in Error Log';
$_['entry_customcode']				= 'Header Custom Code';
$_['entry_shipping_cost']			= 'Do Not Send Shipping Cost';
$_['entry_tax_override']			= 'Override Tax';
$_['entry_tax_override_value']		= 'Tax Value';
$_['entry_shipping_exclude']		= 'Exclude Shipping';
$_['entry_tax_exclude']				= 'Exclude Tax';
$_['entry_customer_data']		    = 'Enable User Data';
$_['help_container']                = 'Enter Container ID staring with GTM-xxxx, you can use our container as well <strong>%s</strong>';
$_['help_cache']				    = 'Recomended to enable the extension DB Query cache to save resoruces';
$_['help_debug']				    = 'Enable / Disable debug log message in error log by tagmanager';
$_['help_tax_override']			    = 'Add a Tax value if you do not have Tax setup in Opencart to send ex-VAT/Tax values'; 
$_['help_tax_override_value']	    = 'Tax value i.e 24 without % sign for 24% tax';
$_['help_shipping_exclude']		    = 'Exclude shipping cost from Revenue on Order Complete'; 
$_['help_tax_exclude']			    = 'Exclude Tax from revenue on Order Complete';
$_['help_customcode']		        = 'Custom code for header placement could be meta tags, jscript tracking etc.';
$_['help_debug_api']		        = 'Show API logs in Tag manager log for all api calls i.e. Google Measuement Protocol or Conversion api';
$_['help_customer_data']		    = 'Allow to send customer data for all platforms i.e. Enhanced Conversion, Pixel, Snapchat, Tiktok, Twitter etc.';
$_['help_disable']		            = 'Enable or Disable the extension. to completely disable the extension also disable the OCMOD';

/* ADVANCED */
$_['entry_server_url']				= 'Full Path to load Container' ;
$_['entry_admin']					= 'Exclude Admin Visit from Analytics';
$_['entry_product']					= 'Product Identifier';
$_['entry_ptitle']					= 'Product Title Modifier';
$_['entry_id_prefix']				= 'Product ID Prefix';
$_['entry_id_suffix']				= 'Product ID Suffix';
$_['entry_route_checkout']			= 'Additional Checkout route';
$_['entry_route_success']			= 'Additional Success route';
$_['entry_extended_menu']			= 'Show Extended Menu';
$_['entry_exclude_ip']	    		= 'Exclude IP Address';
$_['entry_order_status']	   		= 'Order Status for Conversion';
$_['entry_api_async']	   		    = 'API Asynchronous Processing';
$_['help_product']				    = 'The unique field used to map the Remarketing data with Merchant Center Shopping Feed or Facebook Catalog';
$_['help_ptitle']				    = 'You can use this setting to shorten the product title sent to Analytics for easy identification in reports';
$_['help_route']				    = 'Additional route i.e quickcheckout/mycheckout in case you have custom Checkout or Success handler one route per line. Please do not remove default entries.';
$_['help_route_checkout']		    = 'One entry per line. if route=checkout/custom checkout add only checkout/custom checkout.<br>Please do not remove default entries.';
$_['help_route_success']		    = 'One entry per line. if route=checkout/order success add only checkout/order success.<br>Please do not remove default entries.';
$_['help_id_prefix']		        = 'Add a prefix to product id field to match your product feed';
$_['help_id_suffix']		        = 'Add a suffix to product id field to match your product feed';
$_['help_ajax']		                = 'Use jquery AJAX instead of XMLHTTP, only recommended if there is any issue with AJAX calls';
$_['help_extended_menu']		    = 'Add Digital Marketing Tool Extended menu in left column';
$_['help_server']				    = 'Use Custom Domain for First Party Cookies i.e. Google, Cloudflare, CDN, etc. <a href="https://developers.google.com/tag-platform/tag-manager/first-party/setup-guide.md?setup=manual#gtag.js" target="_blank">Read More</a>';
$_['help_server_url']			    = 'Add complete path to load the container i.e. /metrics/ or youdoamin.com/metrics/ or https://yourdomain.com/metrics/';
$_['entry_debug_order']			    = 'Log Order Success data';
$_['help_debug_order']              = 'Save Order success page data in to logs';
$_['help_exclude_ip']               = 'WARNING: Internal IP address to exclude from tracking, one per line, Do not use if you have any kind of cache extension or using cloudflare.';
$_['help_order_status']             = 'WARNING: Select all applicable order status, The Conversion event will only fire on selected status. Leave unchek if unsure.';
$_['help_api_async']	   		    = 'Asynchronous Processing for API calls, enable if you are having slow page load when conversion api is active';

/* GOOGLE ANALYTICS */
$_['entry_ga4_status']				= 'Google GA4 Analytics Status';
$_['entry_ga4_mid']					= 'Google GA4 Measurement ID';
$_['entry_ga4_api']					= 'Google GA4 Measurement API Secret';
$_['help_ga4']					    = 'Enable Google Analytics 4 tracking,make if you are using old tracking use Universal Analytics';
$_['help_ga4_api']				    = 'Google Analytics 4 Measurement API Secret to be used for server to server communication (beta)';
$_['help_ga4_id']                   = 'Enter GA4 Measurement ID starting with G-xxxxxxx';

/* GOOGLE ADS */
$_['entry_adword']					= 'Google Ads Conversion Tracking';
$_['entry_adword2']					= 'Secondary Conversion Tracking';
$_['entry_adword_ec']				= 'Enhanced Conversions';
$_['entry_aw_tagid']				= 'Google TAG ID for Ads';
$_['entry_conversion_id']			= 'Conversion ID';
$_['entry_conversion_label']		= 'Conversion Label';
$_['entry_conversion_id2']			= 'Secondary Conversion ID';
$_['entry_conversion_value2']		= 'Value';
$_['entry_aw_optional']	        	= 'Product Optional Data';
$_['entry_aw_merchant_id']	    	= 'Merchant Center Id';
$_['entry_aw_feed_country']		    = 'Feed Country';
$_['entry_aw_feed_language']		= 'Feed Language';
$_['entry_remarketing']				= 'Google Ads Remarketing';
$_['entry_ajax']			        = 'JQUERY AJAX Mode';
$_['entry_ga2_pageview']			= 'Page View event Conversion Label';
$_['entry_ga2_productview']			= 'Product View event Conversion Label';
$_['entry_ga2_addtocart']			= 'Add to Cart event Conversion Label';
$_['entry_ga2_viewcart']			= 'View Cart event Conversion Label';
$_['entry_ga2_checkout']			= 'Checkout event Conversion Label';
$_['entry_ga2_contact']			    = 'Contact event Conversion Label';
$_['entry_ga2_signup']			    = 'Signup event Conversion Label';
$_['entry_ga2_pageview_value']		= 'Default Conversion Value for Page View';
$_['entry_ga2_contact_value']		= 'Default Conversion Value for Contact';
$_['entry_ga2_signup_value']		= 'Default Conversion Value for Signup';
$_['help_ga2_pageview']			    = 'Add Conversion Label for respective event, leave blank to disable';
$_['help_ga2_productview']			= 'Conversion Label for Product View event, leave blank to disable';
$_['help_ga2_addtocart']			= 'Conversion Label for Add to Cart event, leave blank to disable';
$_['help_ga2_viewcart']			    = 'Conversion Label for View Cart event, leave blank to disable';
$_['help_ga2_checkout']			    = 'Conversion Label for Checkout event, leave blank to disable';
$_['help_ga2_contact']			    = 'Conversion Label for Contact event, leave blank to disable';
$_['help_ga2_signup']			    = 'Conversion Label for Signup event, leave blank to disable';
$_['help_aw']          		        = 'Enable Google Ads Conversion Tracking ';
$_['help_aw_ec']          		    = 'Send Enhanced conversions data to Google Ads email, phone, name, address, country on Conversion';
$_['help_aw_secondary']             = 'Track various events as Secondary Conversions, In your Google Ads Accounts setup the Secondary Conversion and enable them here by providing their labels';
$_['help_aw_optional']              = 'Optional Set up and test reporting conversions with cart data (Beta) ';
$_['help_aw_merchant']              = 'The Merchant Center ID where your items are uploaded';
$_['help_aw_country']               = 'The country associated with the feed where your items are uploaded. Use CLDR territory codes.';
$_['help_aw_language']              = 'The language associated with the feed where your items are uploaded. Use ISO 639-1 language codes.';
$_['help_aw_merchant']              = 'The Merchant Center ID where your items are uploaded';
$_['help_conversion_id']		    = 'Google Ads Conversion id Enter without AW-, only nummbers';
$_['help_conversion_label']		    = 'Google Ads Conversion Label from the Conversion tracking code.';
$_['help_remarketing']			    = 'Google Ads Remarketing / Dynamic Remarketing';
$_['help_conversion_value2']	    = 'Fixed Conversion Values for event, required for these events i.e. 1';
$_['help_aw_tagid']	                = 'Google ADS TAG ID, leave blank if you are unsure. Required if you have modified your Tag';

/* CUSTOM DIMENSION */

$_['entry_custom_dimension']		= 'Enable Custom Dimensions';
$_['entry_custom_dimension1']		= 'Custom Dimension ';
$_['entry_custom_dimension2']		= 'Custom Dimension ';
$_['entry_custom_dimension3']		= 'Custom Dimension ';
$_['entry_custom_dimension4']		= 'Custom Dimension ';
$_['entry_custom_dimension5']		= 'Custom Dimension ';
$_['entry_custom_dimension6']		= 'Custom Dimension ';
$_['entry_custom_dimension7']		= 'Custom Dimension ';
$_['entry_custom_dimension8']		= 'Custom Dimension ';
$_['entry_custom_dimension9']		= 'Custom Dimension ';

/* Google Reviews*/
$_['entry_greview']					= 'Google Customer Satisfaction';
$_['entry_greview_badge']			= 'Google Review Badge';
$_['entry_merchant_id']				= 'Google Merchant Id';

/* Meta Pixel */
$_['entry_pixel']					= 'Meta Pixel Tracking';
$_['entry_pixelcode']				= 'Pixel Tracking ID';
$_['entry_fb_api']				    = 'Conversion API';
$_['entry_fb_token']				= 'Conversion API Access Token';
$_['entry_fb_catalog_id']			= 'Catalog ID (for Dynamic Ads)';
$_['entry_fb_purchase']	    		= 'Conversion Event via API only';
$_['entry_alt_currency']			= 'Alternate Currency';
$_['entry_alt_currency_status']		= 'Use Alternate Currency';
$_['entry_alt_currency_val']		= 'Alternate Currency Value';
$_['entry_pixel_test_code']			= 'Conversion API Test Code';
$_['entry_fb_api_debug']		    = 'Conversion API Debug / Testing';
$_['help_pixel_id']                 = 'Add the Pixel tracking id, only numbers ie. 34893747234332';
$_['help_pixel_token']              = 'Add Pixel Conversion API Token, you can generate the token from Pixel Event management page';
$_['help_pixel_test_code']			= 'Conversion API Test code from you Event Manager screen, leave blank for production sites';
$_['help_ac']					    = 'Use different currency in case your store currency is not supported. Must be setup in Localisation/Currency';
$_['help_ac_value']				    = 'If you want to use different value for conversion than store. Leave black for default';
$_['help_fb_api_debug']             = 'Run Meta API in debug mode for validation only for production keep it disabled';
$_['help_fb_api_testcode']          = 'Meta Pixel Conversion API Test Code for testing conversion events';
$_['help_fb_api_status']            = 'Enable/Disable Meta Conversion API';
$_['help_alt_currency']             = 'User Alternate Currency for Meta Pixel Reporting';
$_['help_fb_purchase']              = 'User Only Conversion API to send Purchase event, in case you are having issues with duplication';

/* Twitter */
$_['entry_twitter_status']			= 'Twitter Analytics';
$_['entry_twitter_tag']		    	= 'Twitter Analytics Tag';
$_['entry_twitter_purchase']	    = 'Purchase Event ID';
$_['entry_twitter_payment']		    = 'Payment Info Event ID';
$_['entry_twitter_checkout']	    = 'Initiate Checkout Event ID';
$_['entry_twitter_addcart']		    = 'Add to Cart Event ID';
$_['entry_twitter_addwishlist']	    = 'Add to Wishlist Event ID';
$_['entry_twitter_viewcontent']	    = 'View Content Event ID';
$_['entry_twitter_search']		    = 'Search Event ID';
$_['entry_twitter_pageview']	    = 'PageView Event ID';
$_['help_twitter_id']               = 'Add tracking tag code only ie. xxxxxxxxx';
$_['help_twitter_purchase']	        = 'Unique Event ID setup in your Twitter Analytics for Purchase Event';
$_['help_twitter_payment']		    = 'Unique Event ID setup in your Twitter Analytics for Payment Info Event';
$_['help_twitter_checkout']	        = 'Unique Event ID setup in your Twitter Analytics for Initiate Checkout Event';
$_['help_twitter_addcart']		    = 'Unique Event ID setup in your Twitter Analytics for Add to Cart Event';
$_['help_twitter_addwishlist']	    = 'Unique Event ID setup in your Twitter Analytics for Add to Wishlist Event';
$_['help_twitter_viewcontent']	    = 'Unique Event ID setup in your Twitter Analytics for View Content Event';
$_['help_twitter_search']		    = 'Unique Event ID setup in your Twitter Analytics for Search Event';
$_['help_twitter_pageview']	        = 'Unique Event ID setup in your Twitter Analytics for PageView Event';

/* Pinterest */
$_['entry_pinterest_status']		= 'Pinterest Analytics';
$_['entry_pinterest_tag']		    = 'Pinterest Analytics Tag';
$_['entry_pinterest_api']			= 'Pinterest API';
$_['entry_pinterest_token']		    = 'Pinterest API Token';
$_['entry_pinterest_api_debug']     = 'Pinterest API Debug / Testing';
$_['help_pinterest_id']             = 'Add tracking tag code only ie. xxxxxxxxx';
$_['help_pinterest_api']            = 'Enable Pinterest Conversion API';
$_['help_pinterest_token']          = 'Pinterest Conversion API Access-Token';
$_['help_pinterest_debug']          = 'Run Pinterest API in debug mode for validation only for production keep it disabled';

/* Glami */
$_['entry_glami_status']			= 'Glami Pixel';
$_['entry_glami_code']			    = 'Glami API Key';
$_['help_glami_id']                 = 'Add your api key ie. xxxxxx';

/* Hotjar */
$_['entry_hotjar_status']			= 'HotJar Tracking';
$_['entry_hotjar_siteid']			= 'HotJar Site Id';

/* Lucky Orange */
$_['entry_luckyorange_status']		= 'Lucky Orange Tracking';
$_['entry_luckyorange_siteid']		= 'Lucky Orange Site Id';
$_['help_luckyorange_id']           = 'Add your site id ie. 34738';

/* Clarity */
$_['entry_clarity_status']			= 'Microsoft Clarity Heatmap';
$_['entry_clarity_siteid']			= 'Microsoft Clarity Tracking Id';

/* Bing */
$_['entry_bing_status']				= 'Bing Tracking';
$_['entry_bing_uetid']				= 'Bing UET Id';
$_['help_bing_id']                  = 'Add UET id ie. xxxxxx';

/* Skroutz */
$_['entry_skroutz_status']			= 'Skroutz Tracking';
$_['entry_skroutz_siteid']			= 'Skroutz Shop Account ID ';
$_['entry_skroutz_manual_tax']		= 'Skroutz send manual tax  ';
$_['entry_skroutz_manual_tax_value']= 'Skroutz tax value i.e 24 without % sign';
$_['entry_skroutz_payment_fee']		= 'Skroutz Payment Processing fee ';
$_['help_skroutz_id']               = 'Add your account id ie. 34738';


/* Yandex */
$_['entry_yandex_status']			= 'Yandex Metrika';
$_['entry_yandex_code']				= 'Yandex Tag ';

/* Snapchat */
$_['entry_snap_pixel_status']		= 'Snapchat Pixel Status';
$_['entry_snap_pixel_id']		    = 'Snapchat Pixel ID';
$_['entry_snap_pixel_api']			= 'Snap Chat API';
$_['entry_snap_pixel_token']		= 'Snap Chat API Token';
$_['entry_snap_pixel_api_debug']    = 'Snap Chat API Debug / Testing';
$_['help_snap_id']                  = 'Add the Pixel tracking id, only numbers ie. 34893747234332';
$_['help_snap_pixel_api']           = 'Enable Snap Chat Pixel Conversion API';
$_['help_snap_pixel_token']         = 'Snap Chat Pixel Conversion API Access-Token';
$_['help_snap_pixel_api_debug']     = 'Run Snap Chat API in debug mode for validation only for production keep it disabled';

/* Zopim / Zen / Fresh chat*/
$_['entry_zopimchat_status']		= 'Zopim Chat Status';
$_['entry_zopimchat_code']			= 'Zopim ID';
$_['entry_zenchat_status']			= 'Zen Chat Status';
$_['entry_zenchat_code']			= 'Zen Chat ID';
$_['entry_freshchat_code']			= 'Fresh Chat Token ID';
$_['entry_freshchat_host']			= 'Fresh Chat Host';
$_['entry_hubspot_status']			= 'Hubspot Chat Status';
$_['entry_hubspot_code']			= 'Hubspot Chat ID';
$_['entry_smartsupp_status']		= 'Smartsupp Chat Status';
$_['entry_smartsupp_code']			= 'Smartsupp Chat ID';
$_['entry_chat_widget_status']		= 'Custom Chat Widget';
$_['entry_chat_widget']		        = 'Insert Complete Chat Widget Code';
$_['help_chat_widget']		        = 'Add any chat widget by adding the provided code from the chat widget provider. Make sure the code is enclosed in <script> </script> tags.';

/* Affiliate Gateway */
$_['entry_affgateway_status']		= 'Affiliate Gateway Status';
$_['entry_affgateway_code']			= 'Affiliate Gateway Campaign Code';

/* 2Performant */
$_['entry_performant_status']		= '2Performant Status';
$_['entry_performant_code']			= '2Performant Campaign Code';
$_['entry_performant_confirm']		= '2Performant Confirm Code';
$_['entry_performant_tax']			= '2Performant manual tax  ';
$_['entry_performant_tax_value']	= '2Performant tax value i.e 24 without % sign';
$_['entry_performant_currency']		= 'Alternate Currency';

/* Admit Ad */
$_['entry_admitad_status']			= 'AdmitAd Status';
$_['entry_admitad_code']			= 'AdmitAd Campaign Code';
$_['entry_admitad_category']		= 'Tariff code';
$_['entry_admitad_additional_type']	= 'Sales (default)';
$_['entry_admitad_invoice_broker']	= 'Invoice Broker (adm default)';
$_['entry_admitad_invoice_category']= 'Invoice Category (action.code)';
$_['entry_admitad_retag_status']	= 'ReTag Status';
$_['entry_admitad_retag_code1']		= 'ReTag Code For Homepage Page i.e. 9xx0931xb';
$_['entry_admitad_retag_code2']		= 'ReTag Code For Category Pages i.e. 9xx0931xb';
$_['entry_admitad_retag_code3']		= 'ReTag Code For Product Pages i.e. 9xx0931xb';
$_['entry_admitad_retag_code4']		= 'ReTag Code For Cart Page i.e. 9xx0931xb';
$_['entry_admitad_retag_code5']		= 'ReTag Code For Thankyou Page i.e. 9xx0931xb';

/* Sendinblue */
$_['entry_sendinblue_status']		= 'SendinBlue Status';
$_['entry_sendinblue_code']			= 'SendinBlue Client Key';
$_['entry_freshchat_status']		= 'Fresh Chat Status';

/* Paypal */
$_['entry_paypal_status']			= 'Paypal Analytics Status';
$_['entry_paypal_code']			    = 'Paypal Analytics ID';

/* Tiktok */
$_['entry_tiktok_status']			= 'Tiktok Pixel Status';
$_['entry_tiktok_code']			    = 'Tiktok Pixel Code';
$_['entry_tiktok_api']			    = 'Tiktok API';
$_['entry_tiktok_token']			= 'Tiktok API Token';
$_['entry_tiktok_api_debug']		= 'Tiktok API Debug / Testing';
$_['entry_tiktok_api_testcode']		= 'Tiktok API Test Code';
$_['entry_tiktok_alt_currency']		= 'Alternate Currency';
$_['entry_tiktok_alt_currency_status']		= 'Use Alternate Currency';
$_['help_tiktok_id']                = 'Add tracking code only ie. xxxxxxxxx';
$_['help_tiktok_api']               = 'Enable Tiktok Pixel Conversion API';
$_['help_tiktok_token']             = 'Tiktok Pixel Conversion API Access-Token';
$_['help_tiktok_api_debug']         = 'Run Tiktok API in debug mode for validation only for production keep it disabled';
$_['help_tiktok_api_testcode']      = 'Tiktok Conversion API Test Code for testing conversion events';

/* Linkwise */
$_['entry_linkwise_status']		    = 'Enable Linkwise Marketing';
$_['entry_linkwise_code']		    = 'Linkwise Tracking ID';
$_['entry_linkwise_decimal']		= 'Linkwise Decimal';

/* Matomo */
$_['entry_matomo_status']			= 'Matomo Analytics Status';
$_['entry_matomo_code']			    = 'Matomo Analytics Tracking Code';
$_['help_matomo_code']              = 'Matomo Tracking code, you can get it from Matomo Integration for Opencart. Paste the complete javascript code including < script > < / script >';

/* CJ */
$_['entry_cj_status']			    = 'CJ.COM Status';
$_['entry_cj_code']			        = 'CJ Enterprise ID';
$_['entry_cj_actionid']			    = 'CJ Action Tracker ID' ;
$_['entry_cj_currency']			    = 'CJ Currency';
$_['entry_cj_currency_value']		= 'CJ Currency Conversion Value';
$_['help_cj_code']              = 'This value is set to your CJ Enterprise ID, which is a static value provided by CJ.';
$_['help_cj_actionid']          = 'This is a static value provided by CJ. action will have a unique actionTrackerId value.';
$_['help_cj_currency']          = 'ISO Currency Code only if your store currency is not supported by CJ. Leave blank for default';
$_['help_cj_currency_value']    = 'Currency Conversion value if currency code is added in Currency Field above. Leave blank for default';

/* DA */
$_['entry_da_status']			    = 'Data Audience Status';
$_['entry_da_code']			        = 'Campaign ID';

/* CONSENT */
$_['entry_consent_external']        = 'Use External Consent setup';
$_['entry_eu_cookie']				= 'Show EU Cookie Consent';
$_['entry_eu_cookie_enforce']		= 'Enforce Cookie Consent';
$_['entry_cookie_layout']			= 'Cookie Layout';
$_['entry_cookie_position']	        = 'Cookie Position';
$_['entry_cookie_badge']            = 'Show Cookie Preference Icon';
$_['entry_cookie_badge_position']   = 'Icon Position';
$_['entry_cookie_badge_color']      = 'Icon colour';
$_['entry_consent_theme']           = 'Consent Theme';
$_['entry_consent_layout']          = 'Consent Modal Layout';
$_['entry_consent_position']        = 'Consent Modal Position';
$_['entry_pref_layout']             = 'Preferences Modal Layout';
$_['entry_pref_position']           = 'Preferences Modal Position';
$_['entry_disable_interaction']     = 'Disable Page Interaction when modal is active';
$_['entry_consent_modal_title']     = 'Consent Modal Title';
$_['entry_consent_modal_desc']      = 'Consent Modal Description';
$_['entry_consent_modal_accept']    = 'Consent Modal Accept All Button Label';
$_['entry_consent_modal_reject']    = 'Consent Modal Reject All Button Label';
$_['entry_consent_modal_setting']   = 'Consent Modal Manage preferences Button Label';  
$_['entry_consent_modal_link']      = 'Consent Modal Footer Link';  
$_['entry_pref_modal_title']        = 'Consent Preference Heading';  
$_['entry_pref_modal_accept']       = 'Consent preferences Accept Button Label';  
$_['entry_pref_modal_reject']       = 'Consent preferences Reject Button Label';  
$_['entry_pref_modal_save']         = 'Consent preferences Save Button Label';  
$_['entry_pref_modal_heading']      = 'Consent preferences Sub Heading';  
$_['entry_pref_modal_text']         = 'Consent preferences Text';  
$_['entry_pref_modal_necessary_title']  = 'Necessary Cookie Title';  
$_['entry_pref_modal_necessary_desc']   = 'Necessary Cookie Description';  
$_['entry_pref_modal_analytics_title']  = 'Analytics Cookie Title';  
$_['entry_pref_modal_analytics_desc']   = 'Analytics Cookie Description';  
$_['entry_pref_modal_marketing_title']  = 'Marketing Cookie Title';  
$_['entry_pref_modal_marketing_desc']   = 'Marketing Cookie Description';  
$_['entry_pref_modal_moreinfo_title']   = 'More Info Heading';  
$_['entry_pref_modal_moreinfo_desc']    = 'More info Text';  
$_['entry_cookie_custom']	        = 'Use Custom Colours for Buttons';
$_['entry_cookie_b1_background']	= 'Accept Button Background Colour i.e. #56cbdb';
$_['entry_cookie_b1_color']	        = 'Text Colour';
$_['entry_cookie_b2_background']	= 'Reject Button Background Colour i.e. #56cbdb';
$_['entry_cookie_b2_color']	        = 'Text Colour';
$_['entry_cookie_b3_background']	= 'Preference Button Background Colour i.e. #56cbdb';
$_['entry_cookie_b3_color']	        = 'Text Colour';
$_['entry_cookie_reject']	        = 'Disable Reject Button';
$_['help_consent']		        = 'Enable/Disable Built in Cookie Consent';
$_['help_cenforce']		        = 'Enforce mode will block all tracking until user accept the cookie consent';
$_['help_ctitle']   		    = 'Heading for Cookie box, leave black to remove the title';
$_['help_ctext']		        = 'Text that appear when user click on Settings or Customize';
$_['help_cookie_button1']   	= 'Accept Button Text';
$_['help_cookie_button2']   	= 'More Info Button Text';
$_['help_cenforce']		        = 'Enforce mode will block all tracking until user accept the cookie consent';
$_['help_ctitle']   		    = 'Heading for Cookie box, leave black to remove the title';
$_['help_ctext2']   		    = 'Text for Cookie Acceptance';
$_['help_ctext']		        = 'Text that appear when user click on Settings or Customize';
$_['help_clink']		        = 'Link URL for cookie policy or privacy policy ';
$_['help_clinktext']		    = 'Link text for privacy policy link ';
$_['help_customcodecookie']		= 'Custom code or script to load after Consent is Accpted for header placement';
$_['help_cookie_essential']		= 'Essential Cookies description text, visible on hover while in advanced mode, leave blank for default';
$_['help_cookie_analytics']		= 'Analytics Cookies description text, visible on hover while in advanced mode, leave blank for default';
$_['help_cookie_marketing']		= 'Marketing Cookies description text, visible on hover while in advanced mode, leave blank for default';
$_['help_cookie_button3']		= 'Accept Selected Button Text, visible in advanced mode';
$_['help_button_color']         = 'You can change the button colours above, leave blank for theme default';
$_['help_consent_external']     = 'If you already have a Google V2 Consent Setup on your site enable this option, if enable Consent V2 gtag(); should be sent from your script';
$_['help_cookie_reject']        = 'Hide the Reject All button from the consent popup.';

// column
$_['column_date']				= 'Date';
$_['column_oid']				= 'Order ID';
$_['column_status']				= 'Event Standard hit';
$_['column_ads']			    = 'Ad Sale';
$_['column_total']			    = 'Total';
$_['column_payment']			= 'Payment Method';
$_['column_payment_code']		= 'Method Code';
$_['column_astatus']			= 'Analytics';
$_['column_api']			    = 'Event API hit';
$_['column_action']				= 'Available Action';

// button
$_['button_send']				= 'Send via APIs';
$_['button_refund']				= 'Refund';

// Error
$_['error_permission']			= 'Warning: You do not have permission to modify Google Tag Manager!';
$_['error_primary']				= 'Google Tag Manager Container ID required!';
$_['error_warning']             = 'Warning: Your error log file %s is %s!';