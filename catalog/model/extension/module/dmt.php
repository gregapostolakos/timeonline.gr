<?php
/******************************************************
 * @package Digital Marketing Tools for OC1.5x, OC2x,3x
 * @version 13.6
 * @author Muhammad Akram
 * @link https://aits.xyz
 * @copyright Copyright (C)2017-2026 aits.xyz All rights reserved.
 * @email:info@aits.pk. 
 * $date: 08-FEB-2026
 * CATALOG/MODEL
*******************************************************/

class ModelExtensionModuleDMT extends Model {
	
	public function getProductInfo($product_id = 0) {
		
		if (!$product_id) {
			return false;
		}
	
		$cacheKey = 'dmt.product_info.' . (int)$product_id;
	
		return $this->gtm->fetchWithCache($cacheKey, function() use ($product_id) {
			$query = $this->db->query("
				SELECT 
					p.product_id,
					p.sku,
					p.ean,
					p.upc,
					p.model,
					p.price,
					p.status,
					p.image,
					p.tax_class_id,
					m.name AS manufacturer,
					(
						SELECT ps.price 
						FROM " . DB_PREFIX . "product_special ps 
						WHERE ps.product_id = p.product_id 
						AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' 
						AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) 
						AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))
						ORDER BY ps.priority ASC, ps.price ASC 
						LIMIT 1
					) AS special
				FROM " . DB_PREFIX . "product p
				LEFT JOIN " . DB_PREFIX . "manufacturer m ON p.manufacturer_id = m.manufacturer_id
				WHERE p.product_id = '" . (int)$product_id . "'
				LIMIT 1
			");
	
			return $query->num_rows ? $query->row : false;
		});
	}

    public function getCartProducts() {
		
        $products = $this->cart->getProducts();
		$dmt = $this->gtm->settings;
        $data = [];

		$data['ec_shipping_total'] = isset($this->session->data['shipping_method']['cost']) ? $this->session->data['shipping_method']['cost'] : 0;
		$data['ec_coupon'] = isset($this->session->data['coupon']) ? $this->session->data['coupon'] : false;

        $data['ecom_prodid'] = [];
        $data['fb_contents'] = [];
        $data['remarketing_ids'] = [];
        $data['sendinblue_products'] = [];
        $data['ecom_pagetype'] = 'purchase';
        $data['ecom_totalvalue'] = 0;
        $data['dynx_itemid'] = '';
        $data['dynx_itemid2'] = '';
		$data['ftotal'] = 0;
        $data['tiktok_value'] = 0;
		$data['fb_items'] = 0;
		$data['twitter_items'] = [];
		$data['matomo_items'] = [];
		$data['tiktok_items'] = [];
		$data['bing_items'] = [];
		$data['pinterest_items'] = [];

        $i = 1;

		foreach ($products as $product) {
		
			$optext = '';

			foreach ($product['option'] as $option) {
				if (isset($option['type']) && $option['type'] != 'file') { 
					$value = (isset($option['value']) ? $option['value'] : ''); 
					if (substr(VERSION, 0, 1) == '1') {
						$value = (isset($option['option_value']) ? $option['option_value'] : ''); 
					}	
                }
                else {
					$value = ''; 
				}
                $optext .= $option['name'] . ': ' . (mb_strlen($value) > 50 ? mb_substr($value, 0, 50) . '..' : $value) . ' ';
			} 

            $optext = mb_substr($optext, 0, 499);

			$model = $product['model'];
            $sku = (isset($product['sku']) ? $product['sku'] : false);

            $pid = $this->gtm->tagmangerPmap($model, $sku, $product['product_id']);
			$brand = $this->gtm->getProductBrandName($product['product_id']);
			$cat_data = $this->gtm->getProductCatName($product['product_id']);
	
			if (isset($cat_data)) {
				$category_name = $cat_data['category'];
                $item_list_id = $cat_data['item_list_id'];
				$item_list_name = $cat_data['item_list_name'];
                $item_category = $cat_data['item_category'];
				$item_category2 = $cat_data['item_category2'];
				$item_category3 = $cat_data['item_category3'];
				$item_category4 = $cat_data['item_category4'];
				$item_category5 = $cat_data['item_category5'];
			} 
            $title = $this->gtm->tagmangerPtitle($product['name'], $brand, $model, $product['product_id']);
			$item_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));
			$total_price = $item_price * $product['quantity'];
            $total_price = $this->currency->format($total_price, $this->session->data['currency'], 0, false);

			if ($dmt['alt_currency_status'] && $dmt['alt_currency'] != $dmt['currency']) {
				$fprice = $this->currency->format($total_price, $dmt['alt_currency'], 0, false);
			} else {
				$fprice = $total_price;
			}
			if (isset($dmt['fb_tax_exclude']) && $dmt['fb_tax_exclude']) {
				$ftotal_price = $product['price'] * $product['quantity'];
				$ftotal_price = $this->currency->format($ftotal_price, $this->session->data['currency'], 0, false);
				if ($dmt['alt_currency_status'] && $dmt['alt_currency'] != $dmt['currency']) {
					$fprice = $this->currency->format($ftotal_price, $dmt['alt_currency'], 0, false);
				} else {
					$fprice = $ftotal_price;
				}
			}
            
            $pprice = number_format((float)$this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) , $this->session->data['currency'], 0, false) , 2, '.', '');
		
			$data['ftotal'] = $data['ftotal'] + $fprice;
			$data['ecom_prodid'][] = $pid;
            $data['remarketing_ids'][] = [
                'id' => (string)$pid,
                'google_business_vertical' => 'retail'
			];
            $data['ecom_totalvalue'] += number_format((float)$total_price, 2, '.', '');
            $data['fb_contents'][] = [
                'id' => $pid,
                'quantity' => $product['quantity']
			];
			$data['fb_items'] = $data['fb_items'] + $product['quantity'];

			if ($i == 1) {
				$data['dynx_itemid'] = $pid;
            }
            elseif ($i == 2) {
				$data['dynx_itemid2'] = $pid;
			}
			
            $data['ec_cartproducts'][] = [
                'id' => (string)$pid,
				'product_id' => $product['product_id'],
                'name' => $title,
				'category' => $category_name,
                'brand' => $brand,
                'variant' => $optext,
				'quantity' => $product['quantity'],
                'price' => $pprice,
                'ex_price' => number_format((float)$product['price'], 2, '.', '') ,
				'currency' => $this->session->data['currency']
			];

			if ($dmt['pinterest_status']) {
				$data['pinterest_items'][] = [
					'product_id' 				=> $pid,
					'product_name' 				=> $title,
					'product_category' 			=> $category_name,
					'product_variant'			=> $optext,
					'product_brand' 			=> $brand,
					'product_quantity' 			=> $product['quantity'],
					'product_price' 			=> $pprice,
				];
			}

			$data['matomo_items'][] = [
				'sku'	=> $pid,
				'name'	=> $title,
				'category'	=> $category_name,
				'price'	=> $pprice,
				'quantity'	=> $product['quantity'],
			];

			if ($dmt['bing_status']) {
				$data['bing_items'][] = [
					'id' 		=> (string)$pid,
					'price' 	=> number_format((float)$product['price'], 2, '.', '') ,
					'quantity' 	=> $product['quantity'],
				];
			}
			
			if (isset($dmt['sendinblue_status']) && $dmt['sendinblue_status']) {
			
                $data['sendinblue_products'][] = [
	    			'id' => (string)$pid,
	    			'name' => $title,
	    			'quantity' => $product['quantity'],
                    'price' => number_format((float)$product['price'], 2, '.', '') ,
	    			'url' => str_replace('&amp;', '&', $this->url->link('product/product', 'product_id=' . $product['product_id']))
				];
			}

			if (isset($dmt['twitter_status']) && $dmt['twitter_status']) {

				$data['twitter_items'][] = [
					'content_id' => (string)$pid,
					'content_type' => 'product',
					'content_name' => $title,
					'num_items' => $product['quantity'],
					'content_price' => $pprice, 
					'content_group_id' => '',
				];
			}

            if ($dmt['tiktok_status']) {

				$tiktok_price = $this->currency->format($item_price, $this->session->data['currency'], 0, false);
                $tiktok_value = $item_price * $product['quantity'];
                $tiktok_value = $this->currency->format($tiktok_value, $this->session->data['currency'], 0, false);
				
				if ($dmt['tiktok_alt_currency_status'] && $dmt['tiktok_alt_currency'] != $dmt['currency']) {
                    $tiktok_value = $item_price * $product['quantity'];
                    $tiktok_value = $this->currency->format($tiktok_value, $dmt['tiktok_alt_currency'], 0, false);
					$tiktok_price = $this->currency->format($item_price, $dmt['tiktok_alt_currency'], 0, false);
				} 

                $data['tiktok_items'][] = [
                    'content_category' 	=> (isset($item_list_name) ? $item_list_name : '') ,
                    'content_name' 		=> $title,
                    'price' 			=> number_format((float)$tiktok_price, 2, '.', ''),
                    'content_id' 		=> $pid,
                    'quantity'			=> $product['quantity'],
                    'brand'				=> $brand,
                ];

                $data['tiktok_value'] = $data['tiktok_value'] + $tiktok_value;

            }
			
            $data['ga4_items'][] = [
                'item_id' => (isset($pid) ? (string)$pid : '') ,
                'item_name' => (isset($title) ? $title : '') ,
                'item_brand' => (isset($brand) ? $brand : '') ,
                'item_category' => (isset($item_category) ? $item_category : '') ,
                'item_category2' => (isset($item_category2) ? $item_category2 : '') ,
                'item_category3' => (isset($item_category3) ? $item_category3 : '') ,
                'item_category4' => (isset($item_category4) ? $item_category4 : '') ,
                'item_category5' => (isset($item_category5) ? $item_category5 : '') ,
                'item_list_id' => (isset($item_list_id) ? $item_list_id : '') ,
                'item_list_name' => (isset($item_list_name) ? $item_list_name : '') ,
                'item_variant' => $optext,
                'affiliation' => '',
                'discount' => 0,
                'coupon' => $data['ec_coupon'],
                'price' => $pprice,
                'currency' => $dmt['currency'],
                'quantity' => $product['quantity']
			];
			$i++;
			
		}

		$data['number_of_items'] = $data['fb_items'];

		return $data;
	}


	public function getProductCost($product_id) {
		$dmt = $this->gtm->settings;
		$cost = 0;
		if (!isset($product_id) && empty($product_id)) {
			return $cost;

		}
		if (isset($dmt['cache']) && $dmt['cache'] == '1') {
			$cost = $this->cache->get('dmt.cost.' . $product_id);
		}
		if (!$cost) {
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_cost WHERE product_id = '" . (int)$product_id . "' LIMIT 1");
			if ($query->num_rows == 1) {
				$cost = $query->row['cost'];
				if (isset($dmt['cache']) && $dmt['cache'] == '1') {
					$this->cache->set('dmt.cost.' . $product_id, $cost);
				}
			}
		} 

		return $cost;
	}
    
}
?>