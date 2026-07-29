<?php
/* Digital Marketing Tools v:13.0 15-04-2025*/
$tmanalytics .= '<script platform="admitad" category="marketing" nitro-exclude="" src="https://www.artfut.com/static/tagtag.min.js?campaign_code=' . $dmt['admitad_code'] . '" 
				async onerror=\'var self = this;window.ADMITAD=window.ADMITAD||{},ADMITAD.Helpers=ADMITAD.Helpers||{},
				ADMITAD.Helpers.generateDomains=function(){for(var e=new Date,	n=Math.floor(new Date(2020,e.getMonth(),e.getDate()).setUTCHours(0,0,0,0)/1e3),
				t=parseInt(1e12*(Math.sin(n)+1)).toString(30),i=["de"],o=[],a=0;a<i.length;++a)o.push({domain:t+"."+i[a],name:t});return o},
				ADMITAD.Helpers.findTodaysDomain=function(e){function n(){	var o=new XMLHttpRequest,a=i[t].domain,D="https://"+a+"/";	o.open("HEAD",D,!0),o.onload=function(){setTimeout(e,0,i[t])},o.onerror=function(){
				++t<i.length?setTimeout(n,0):setTimeout(e,0,void 0)},o.send()}var t=0,	i=ADMITAD.Helpers.generateDomains();n()},window.ADMITAD=window.ADMITAD||{},ADMITAD.Helpers.findTodaysDomain(function(e){
				if(window.ADMITAD.dynamic=e,window.ADMITAD.dynamic){var n=function(){	return function(){return self.src?self:""}}(),t=n(),i=(/campaign_code=([^&]+)/.exec(t.src)||[])[1]||"";
				t.parentNode.removeChild(t);var o=document.getElementsByTagName("head")[0],	a=document.createElement("script");	a.src="https://www."+window.ADMITAD.dynamic.domain+"/static/"+window.ADMITAD.dynamic.name.slice(1)+
				window.ADMITAD.dynamic.name.slice(0,1)+".min.js?campaign_code="+i,o.appendChild(a)}});\'></script>';
$tmanalytics .= "<script type=\"text/javascript\" platform=\"admitad\" category=\"marketing\"  nitro-exclude=\"\">
				var cookie_name = 'deduplication_cookie';var days_to_store = 90;var deduplication_cookie_value = 'admitad';
				var channel_name = 'utm_source';getSourceParamFromUri = function () {var pattern = channel_name + '=([^&]+)';
				var re = new RegExp(pattern);return (re.exec(document.location.search) || [])[1] || '';	};getSourceCookie = function () {var matches = document.cookie.match(new RegExp('(?:^|; )' + cookie_name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + '=([^;]*)'
				));	return matches ? decodeURIComponent(matches[1]) : undefined;};setSourceCookie = function () {	var param = getSourceParamFromUri();if (!param) { return; }	var period = days_to_store * 60 * 60 * 24 * 1000;	
				var expiresDate = new Date((period) + +new Date);var cookieString = cookie_name + '=' + param + '; path=/; expires=' + expiresDate.toGMTString();
				document.cookie = cookieString;	document.cookie = cookieString + '; domain=.' + location.host;};setSourceCookie();
				</script>" . "\n";
if (isset($dmt['email']) && !empty($dmt['email'])) {				
$tmanalytics .= "<script type=\"text/javascript\">  ADMITAD = window.ADMITAD || {}; ADMITAD.Invoice = ADMITAD.Invoice || {};
   ADMITAD.Invoice.accountId = '" . $dmt['email'] . "';  </script>";
}
switch ($page_type) {
	
    case "success":

            $tmanalytics .= "<script type=\"text/javascript\" platform=\"admitad\" category=\"marketing\" nitro-exclude=\"\">
				ADMITAD = window.ADMITAD || {};	ADMITAD.Invoice = ADMITAD.Invoice || {};if (!getSourceCookie(cookie_name)) {ADMITAD.Invoice.broker = 'na';	} else if (getSourceCookie(cookie_name) != deduplication_cookie_value) {ADMITAD.Invoice.broker = getSourceCookie(cookie_name);
				} else {ADMITAD.Invoice.broker = '" . $dmt['admitad_invoice_broker'] . "';}ADMITAD.Invoice.category = '" . $dmt['admitad_invoice_category'] . "';var orderedItem = [];  ";
            foreach ($_data['admitad_items'] as $item) { 
            $tmanalytics .= "orderedItem.push({	Product: {productID: '" . $item['product_id'] ."',category: '" . $item['category'] ."',  price: '" . $item['price'] . "',priceCurrency: '" . $item['currency'] . "',},orderQuantity: '" . $item['quantity'] . "',additionalType: '" . $item['type'] . "' });";
            }	
            $tmanalytics .= "ADMITAD.Invoice.referencesOrder = ADMITAD.Invoice.referencesOrder || [];ADMITAD.Invoice.referencesOrder.push({	orderNumber: '" . $_data['order_id'] ."',  orderedItem: orderedItem });</script>" . "\n";
            if (isset($dmt['admitad_retag_code5']) && !empty($dmt['admitad_retag_code5']) && $dmt['admitad_retag_status']) {    
                $tmanalytics .= '<script type="text/javascript" platform="retag" category="marketing" nitro-exclude="">
                window.ad_order = "' . $_data['order_id'] .'"; 
                window.ad_amount = "' . $_data['value'] .'";
                window.ad_products = [';
                if (isset($_data['items'])) {
                    foreach($_data['items'] as $items) {
                    $tmanalytics .= '{"id": "' . $items['item_id'] . '", "number": "' . $items['quantity'] .'"  },';
                    }
                }
                $tmanalytics .= ' ];  window._retag = window._retag || [];	window._retag.push({code: "' . $dmt['admitad_retag_code5'] . '", level: 4});
                (function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s = document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
                })()	</script>'. "\n";
            }
        break;

    case "checkout":    

        break;

    
    case "product":

        if (isset($dmt['admitad_retag_code3']) && !empty($dmt['admitad_retag_code3']) && $dmt['admitad_retag_status']) {
            $tmanalytics .= '<script type="text/javascript" platform="retag" category="marketing" nitro-exclude="">
            window.ad_product = {';
            if (isset($_data['items'])) {
            foreach($_data['items'] as $items) {
            $tmanalytics .= '"id": "' . $items['item_id'] . '","vendor": "' . $items['item_brand'] .'","price": "' . $items['price'] . '","url": "' . (isset($items['item_url']) ? $items['item_url'] : '') . '","picture": "' . (isset($items['item_image']) ? $items['item_image'] : '') . '","name": "' . $items['item_name'] . '","category": "' . $items['item_list_id']. '"';
            }
            }
            $tmanalytics .= '}; window._retag = window._retag || [];
            window._retag.push({code: "' . $dmt['admitad_retag_code3'] . '", level: 2});
            (function () {
            var id = "admitad-retag";
            if (document.getElementById(id)) {return;}
            var s = document.createElement("script");
            s.async = true; s.id = id;
            var r = (new Date).getDate();
            s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
            var a = document.getElementsByTagName("script")[0]
            a.parentNode.insertBefore(s, a);
            })()
            </script>' ."\n";
        }

        break;

    case "listing":    

        if (isset($dmt['admitad_retag_code2']) && !empty($dmt['admitad_retag_code2']) && $dmt['admitad_retag_status']) {
            if (isset($this->request->get['path'])) {
                $path = '';
                $parts = explode('_', (string)$this->request->get['path']);
                $category_id = (int)array_pop($parts);
                $tmanalytics .= '<script type="text/javascript" platform="retag" category="marketing" nitro-exclude="">
                window.ad_category = "' . (isset($category_id) ? $category_id : '') . '"; 
                window._retag = window._retag || [];window._retag.push({code: "' . $dmt['admitad_retag_code2'] . '", level: 1});
                (function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s=document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
                })()</script>'."\n";
            }	
        }

    case "cart":

        if (isset($dmt['admitad_retag_code4']) && !empty($dmt['admitad_retag_code4']) && $dmt['admitad_retag_status']) {
            $tmanalytics .= '<script type="text/javascript" platform="retag" category="marketing" nitro-exclude="">
            window.ad_products = [';
            if (isset($_data['items'])) {
            foreach($_data['items'] as $items) {
            $tmanalytics .= '{"id": "' . $items['item_id'] . '", "number": "' . $items['quantity'] .'"  },';
            }
            }
            $tmanalytics .= ' ]; window._retag = window._retag || [];window._retag.push({code: "' . $dmt['admitad_retag_code4'] . '", level: 3});
            (function () {
            var id = "admitad-retag";
            if (document.getElementById(id)) {return;}
            var s = document.createElement("script");
            s.async = true; s.id = id;
            var r = (new Date).getDate();
            s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
            var a = document.getElementsByTagName("script")[0]
            a.parentNode.insertBefore(s, a);
            })()	</script>' . "\n";
        }

        break;

    case "home": 
        
        if (isset($dmt['admitad_retag_code1']) && !empty($dmt['admitad_retag_code1']) && $dmt['admitad_retag_status']) {
            $tmanalytics .= '<script type="text/javascript" platform="retag" category="marketing" nitro-exclude="">
            window._retag = window._retag || [];window._retag.push({code: "' . $dmt['admitad_retag_code1'] . '", level: 0});
            (function () {
            var id = "admitad-retag";
            if (document.getElementById(id)) {return;}
            var s = document.createElement("script");
            s.async = true; s.id = id;
            var r = (new Date).getDate();
            s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
            var a = document.getElementsByTagName("script")[0]
            a.parentNode.insertBefore(s, a);
            })()</script>' . "\n";
        }

        break;
}