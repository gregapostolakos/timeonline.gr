<?php
// Heading
$_['heading_title']            = 'Skroutz XML Feed';

// Text
$_['text_extension']           = 'Extensions';
$_['text_home']                = 'Home';
$_['text_success']             = 'Success: You have modified the Skroutz feed!';
$_['text_edit']                = 'Edit Skroutz XML Feed';
$_['text_enabled']             = 'Enabled';
$_['text_disabled']            = 'Disabled';
$_['text_yes']                 = 'Yes';
$_['text_no']                  = 'No';
$_['text_select']              = '--- Select ---';
$_['text_autocomplete']        = 'Search to check… (autocomplete)';
$_['text_none']                = '--- None ---';
$_['text_exclude_skip']        = '--- Exclude product ---';
$_['text_default']             = 'Default';

$_['text_weight_g']            = 'Grams (g)';
$_['text_weight_kg']           = 'Kilograms (kg)';

$_['text_source_option']       = 'Product Option';
$_['text_source_attribute']    = 'Attribute';
$_['text_source_none']         = 'None';
$_['text_source_name']         = 'Parse from product name';

$_['text_desc_description']    = 'Description';
$_['text_desc_meta']           = 'Meta Description';

$_['text_empty_ean']           = 'Missing EAN / barcode';
$_['text_empty_mpn']           = 'Missing MPN';
$_['text_empty_image']         = 'Missing image';
$_['text_empty_manufacturer']  = 'Missing manufacturer';
$_['text_empty_price']         = 'Zero / empty price';

$_['text_percent']             = 'Percent (%)';
$_['text_fixed']               = 'Fixed (€)';

$_['text_feed_url']            = 'Your feed is available at';

// Tabs
$_['tab_general']              = 'General';
$_['tab_mapping']              = 'Field Mapping';
$_['tab_availability']         = 'Availability & Stock';
$_['tab_exclude']              = 'Excludes';
$_['tab_buffer']               = 'Price Buffer';
$_['tab_support']              = 'Support';

// Support tab
$_['copyright_title']          = 'Copyright';
$_['copyright_body']           = 'Skroutz XML Feed for OpenCart, developed by Webartstudio. This extension is licensed for use on the store it was installed on.';
$_['text_rights']              = 'All rights reserved.';
$_['support_title']            = 'Support';
$_['support_body']             = 'Need help with configuration or have a question about the feed? Contact our team — we are happy to help.';
$_['support_hours']            = 'Support hours: Mon–Fri 09:00–17:00';
$_['support_note']             = 'Please have your store URL and the feed URL ready when contacting support.';
$_['text_about']               = 'About';
$_['text_version']             = 'Version';

// Entry
$_['entry_status']             = 'Status';
$_['entry_store']              = 'Store';
$_['entry_language']           = 'Language';
$_['entry_customer_group']     = 'Customer Group';
$_['entry_mask']               = 'Feed URL mask';
$_['entry_weight_unit']        = 'Weight output unit';
$_['entry_desc_source']        = 'Description source';
$_['entry_vat_default']        = 'Default VAT rate';
$_['entry_strip_html']         = 'Strip HTML';
$_['entry_shipping']           = 'Shipping cost';

$_['entry_mpn_field']          = 'MPN source field';
$_['entry_ean_field']          = 'EAN source field';
$_['entry_variations']         = 'Size variations';
$_['entry_size_source']        = 'Size source';
$_['entry_color_source']       = 'Color source';
$_['entry_additional_images']  = 'Additional images';

$_['entry_availability_instock'] = 'Availability when in stock';
$_['entry_availability_map']     = 'Out-of-stock availability mapping';
$_['entry_exclude_stock']        = 'Exclude these stock statuses';

$_['entry_exclude_product']      = 'Exclude products';
$_['entry_exclude_category']     = 'Exclude categories';
$_['entry_exclude_manufacturer'] = 'Exclude manufacturers';
$_['entry_exclude_empty']        = 'Exclude products with';

$_['entry_buffer_general']     = 'General buffer';
$_['entry_buffer_op']          = 'Operation';
$_['entry_buffer_val']         = 'Value';
$_['entry_buffer_round']       = 'Round to .99';
$_['entry_buffer_product']     = 'Per-product buffers';
$_['entry_buffer_category']    = 'Per-category buffers';
$_['entry_buffer_manufacturer']= 'Per-manufacturer buffers';

// Column
$_['column_stock_status']      = 'Stock status';
$_['column_availability']      = 'Skroutz availability';
$_['column_name']              = 'Name';
$_['column_operation']         = 'Operation';
$_['column_value']             = 'Value';
$_['column_action']            = 'Action';

// Help
$_['help_mask']                = 'Clean filename/path after the domain (e.g. "skroutz.xml"). Leave empty to use the default route URL.';
$_['help_weight_unit']         = 'Output unit for the &lt;weight&gt; field. Product weight is converted from its weight class.';
$_['help_customer_group']      = 'Prices (specials/discounts) are calculated for this customer group.';
$_['help_shipping']            = 'Fixed shipping cost for all products. Leave empty if shipping has conditions (Skroutz requires it empty then).';
$_['help_vat_default']         = 'Used when a product has no tax class (price is then treated as VAT-inclusive and this rate is reported).';
$_['help_strip_html']          = 'Skroutz does not allow HTML in any field. Recommended: Yes.';
$_['help_variations']          = 'Emit a &lt;variations&gt; block with per-size stock. Required for fashion categories.';
$_['help_size_source']         = 'Where product sizes come from. Choose the option/attribute that holds sizes.';
$_['help_color_source']        = 'Where product color comes from. Mandatory for fashion categories.';
$_['help_availability_instock'] = 'Expression sent when a product (or size) has quantity &gt; 0.';
$_['help_availability_map']    = 'For out-of-stock items, map each OC stock status to a Skroutz expression (or exclude the product).';
$_['help_buffer']              = 'Adjust the final price (with VAT). Precedence: product &gt; category &gt; manufacturer &gt; general.';

// Button
$_['button_save']              = 'Save';
$_['button_cancel']            = 'Cancel';
$_['button_add']               = 'Add';
$_['button_remove']            = 'Remove';
$_['button_check_all']         = 'Check all';
$_['button_uncheck_all']       = 'Uncheck all';

// Error
$_['error_permission']         = 'Warning: You do not have permission to modify the Skroutz feed!';
$_['error_warning']            = 'Warning: Please check the form carefully for errors!';
