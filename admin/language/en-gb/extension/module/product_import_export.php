<?php
$_['heading_title']      = 'Product Import/Export';

$_['text_extension']     = 'Extensions';
$_['text_success']       = 'Success: You have modified Product Import/Export!';
$_['text_edit']          = 'Edit Product Import/Export';
$_['text_enabled']       = 'Enabled';
$_['text_disabled']      = 'Disabled';
$_['text_info_title']    = 'How it works';
$_['text_info']          = 'When enabled, an Import and an Export button appear next to the Add New button on the product list. The Export button downloads a CSV with the products that match the currently applied list filters. The Import button uploads a CSV: rows with a product_id update the existing product, rows without product_id create a new product.';
$_['text_import_title']  = 'Import Products';
$_['text_import_help']   = 'The CSV must have a header row. Rows with a product_id update the existing product, rows without product_id create a new product (name required). Supported columns: product_id, name, model, sku, ean, quantity, price, status (1/0), weight.';
$_['text_import_result'] = 'Import completed: %s updated, %s created, %s skipped.';
$_['text_row_not_found'] = 'Row %s: product_id %s not found - skipped.';
$_['text_row_no_name']   = 'Row %s: missing name for new product - skipped.';
$_['text_product_list']  = 'Go to product list';

$_['entry_status']       = 'Status';
$_['entry_import_file']  = 'CSV file';

$_['button_save']        = 'Save';
$_['button_cancel']      = 'Cancel';
$_['button_import']      = 'Import products (CSV)';
$_['button_export']      = 'Export products (CSV, respects active filters)';
$_['button_import_run']  = 'Import';
$_['button_close']       = 'Close';

$_['error_permission']   = 'Warning: You do not have permission to modify Product Import/Export!';
$_['error_upload']       = 'The file could not be uploaded, please try again!';
$_['error_filetype']     = 'Invalid file type, only CSV files are allowed!';
$_['error_empty']        = 'The file is empty!';
$_['error_header']       = 'Invalid header row: a product_id or name column is required!';
$_['error_file']         = 'Please select a CSV file first!';
