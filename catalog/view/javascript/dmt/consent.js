import 'https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.umd.js';

		CookieConsent.run({
		cookie: {
        	name: "_consents",
    	},
		guiOptions: {
			consentModal: {
				layout: "cloud",
				position: "bottom center",
				equalWeightButtons: true,
				flipButtons: false
			},
			preferencesModal: {
				layout: "bar wide",
				position: "right",
				equalWeightButtons: true,
				flipButtons: false
			}
		},
		onFirstConsent: () => {
	        console.log("onFirstAction fired");
	    },
	
	    onConsent: () => {
	        
	        if(CookieConsent.acceptedCategory("analytics")){
            	var gdpr_analytics = "granted";
				var gdpr_pixel = "grant";
				var cc_analytics = 1;
				var url_passthrough = "false";
        	} else {
        		var gdpr_analytics = "denied";
				var gdpr_pixel = "revoke";
				var cc_analytics = 0;	
				var url_passthrough = "true";
        	}
        
	        if(CookieConsent.acceptedCategory("marketing")){
	        	var gdpr_marketing = "granted";
				var cc_marketing=1;
			} else {
				gtag("set", "ads_data_redaction", true);
				var gdpr_marketing = "denied";
				var cc_marketing=0;
			}
			
			gtag('consent', 'update', {'analytics_storage': gdpr_analytics,'ad_storage': gdpr_marketing,'ad_user_data':gdpr_marketing,'ad_personalization':gdpr_marketing});
					whenAvailable('fbq', function(t) {fbq('consent', gdpr_pixel);});console.log("Consent Fired ...");

	    },
	
	    onChange: () => {
	        if(CookieConsent.acceptedCategory("analytics")){
            	var gdpr_analytics = "granted";
				var gdpr_pixel = "grant";
				var cc_analytics = 1;
				var url_passthrough = "false";
        	} else {
        		var gdpr_analytics = "denied";
				var gdpr_pixel = "revoke";
				var cc_analytics = 0;	
				var url_passthrough = "true";
        	}
        
	        if(CookieConsent.acceptedCategory("marketing")){
	        	var gdpr_marketing = "granted";
				var cc_marketing=1;
				gtag("set", "ads_data_redaction", false);
			} else {
				gtag("set", "ads_data_redaction", true);
				var gdpr_marketing = "denied";
				var cc_marketing=0;
			}gtag('consent', 'update', {'analytics_storage': gdpr_analytics,'ad_storage': gdpr_marketing,'ad_user_data':gdpr_marketing,'ad_personalization':gdpr_marketing});
					whenAvailable('fbq', function(t) {fbq('consent', gdpr_pixel);});console.log("Consent Updated ...");
	    },
	    
	    categories: {
	        necessary: {
	            readOnly: true,
	            enabled: true
	        },
	        analytics: {
	            autoClear: {
	                cookies: [
	                    {
	                        name: /^(_ga|_gid)/
	                    }
	                ]
	            }
	        },
	        marketing: {
				autoClear: {
	                cookies: [
	                    {
	                        name: /^(kl_csrftoken|__kla_id)/
	                    }
	                ]
	            }
	        	
	        }
	    },
		language: {
			default: "en",
			autoDetect: "browser",
			translations: {en: {
								consentModal: {
											title: "Cookie Consent",
											description: "We use cookies to give you the best online experience. By using our website you agree to our use of cookies in accordance with our Cookie Policy",
											closeIconLabel: "",
											acceptAllBtn: "Accept all",
											acceptNecessaryBtn: "Reject all",
											showPreferencesBtn: "Manage preferences",
											footer: "<a href=\"#link\">Privacy Policy</a>\n<a href=\"#link\">Terms and conditions</a>"
										},
										preferencesModal: {
											title: "Consent Preferences Center",
											closeIconLabel: "Close modal",
											acceptAllBtn: "Accept All",
											acceptNecessaryBtn: "Reject all",
											savePreferencesBtn: "Save preferences",
											serviceCounterLabel: "Service|Services",
											sections: [
												{
													title: "Cookie Usage",
													description: "We use cookies to give you the best online experience. By using our website you agree to our use of cookies in accordance with our Cookie Policy"
												},
												{
													title: "Necessary Cookies <span class=\"pm__badge\">Always Enabled</span>",
													description: "Necessary cookies are required to enable the basic features of this site, such as providing secure log-in or adjusting your consent preferences. These cookies do not store any personally identifiable data.",
													linkedCategory: "necessary"
												},
												{
													title: "Analytics Cookies",
													description: "Analytical cookies are used to understand how visitors interact with the website. These cookies help provide information on metrics such as the number of visitors, bounce rate, traffic source, etc.",
													linkedCategory: "analytics"
												},
												{
													title: "Marketing Cookies",
													description: "Advertisement cookies are used to provide visitors with customized advertisements based on the pages you visited previously and to analyze the effectiveness of the ad campaigns.",
													linkedCategory: "marketing"
												},
												{
													title: "More information",
													description: "For any query in relation to my policy on cookies and your choices, please <a class=\"cc__link\" href=\"?route=information/contact\">contact us</a>."
												}
											]
										}
									}
				
			}
		},
		disablePageInteraction: 0
	});
	
	