import 'https://cdn.jsdelivr.net/gh/orestbida/cookieconsent@3.0.1/dist/cookieconsent.umd.js';

		CookieConsent.run({
		cookie: {
        	name: "_consents",
    	},
		guiOptions: {
			consentModal: {
				layout: "bar",
				position: "bottom center",
				equalWeightButtons: true,
				flipButtons: false
			},
			preferencesModal: {
				layout: "box",
				position: "left",
				equalWeightButtons: true,
				flipButtons: false
			}
		},
		onFirstConsent: () => {
	       var c_functionality = "granted";
			var c_security = "granted";
			var c_analytics = "denied";
			var c_marketing = "denied";
			var c_personalization = "denied";
			var c_url_passthrough = "true";
			var c_ads_data_redaction = "true";
			var c_consent = "revoke";
			
		gtag('consent', 'update', {
					'security_storage': 'granted',
					'functionality_storage' : 'granted',
					'analytics_storage': 'granted',
					'ad_storage': 'granted',
					'ad_user_data':'granted',
					'ad_personalization':'granted',
					'personalization_storage': 'granted'					
				});
				gtag('set', 'ads_data_redaction', false);
				gtag('set', 'url_passthrough', false);
		 
	    },
	
	    onConsent: () => {
			
		},
	
	    onChange: () => {
			var c_functionality = "granted";
			var c_security = "granted";
			var c_analytics = "denied";
			var c_marketing = "denied";
			var c_personalization = "denied";
			var c_url_passthrough = "true";
			var c_ads_data_redaction = "true";
			var c_consent = "revoke";
			
		gtag('consent', 'update', {
					'security_storage': 'granted',
					'functionality_storage' : 'granted',
					'analytics_storage': 'granted',
					'ad_storage': 'granted',
					'ad_user_data':'granted',
					'ad_personalization':'granted',
					'personalization_storage': 'granted'
				});
				gtag('set', 'ads_data_redaction', false);
				gtag('set', 'url_passthrough', false);},
	    
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
	        	
	        },
	    },
		language: {
			default: "en",
			autoDetect: "document",
			translations: {el: {
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
				,en: {
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
		
		