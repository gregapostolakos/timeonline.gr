<?php
$_['heading_title']      = 'Import/Export Προϊόντων';

$_['text_extension']     = 'Επεκτάσεις';
$_['text_success']       = 'Επιτυχία: Το Import/Export Προϊόντων ενημερώθηκε!';
$_['text_edit']          = 'Επεξεργασία Import/Export Προϊόντων';
$_['text_enabled']       = 'Ενεργό';
$_['text_disabled']      = 'Ανενεργό';
$_['text_info_title']    = 'Πώς λειτουργεί';
$_['text_info']          = 'Όταν είναι ενεργό, εμφανίζονται τα κουμπιά Import και Export δίπλα στο κουμπί προσθήκης νέου προϊόντος στη λίστα προϊόντων. Το Export κατεβάζει CSV με τα προϊόντα που ταιριάζουν στα ενεργά φίλτρα της λίστας. Το Import ανεβάζει CSV: οι γραμμές με product_id ενημερώνουν το υπάρχον προϊόν, οι γραμμές χωρίς product_id δημιουργούν νέο προϊόν.';
$_['text_import_title']  = 'Import Προϊόντων';
$_['text_import_help']   = 'Το CSV πρέπει να έχει γραμμή επικεφαλίδων. Οι γραμμές με product_id ενημερώνουν το υπάρχον προϊόν, οι γραμμές χωρίς product_id δημιουργούν νέο προϊόν (απαιτείται name). Υποστηριζόμενες στήλες: product_id, name, model, sku, ean, quantity, price, status (1/0), weight.';
$_['text_import_result'] = 'Το import ολοκληρώθηκε: %s ενημερώθηκαν, %s δημιουργήθηκαν, %s παραλείφθηκαν.';
$_['text_row_not_found'] = 'Γραμμή %s: το product_id %s δεν βρέθηκε - παραλείφθηκε.';
$_['text_row_no_name']   = 'Γραμμή %s: λείπει το name για νέο προϊόν - παραλείφθηκε.';
$_['text_product_list']  = 'Μετάβαση στη λίστα προϊόντων';

$_['entry_status']       = 'Κατάσταση';
$_['entry_import_file']  = 'Αρχείο CSV';

$_['button_save']        = 'Αποθήκευση';
$_['button_cancel']      = 'Ακύρωση';
$_['button_import']      = 'Import προϊόντων (CSV)';
$_['button_export']      = 'Export προϊόντων (CSV, με τα ενεργά φίλτρα)';
$_['button_import_run']  = 'Import';
$_['button_close']       = 'Κλείσιμο';

$_['error_permission']   = 'Προσοχή: Δεν έχετε δικαίωμα τροποποίησης του Import/Export Προϊόντων!';
$_['error_upload']       = 'Το αρχείο δεν μπόρεσε να ανέβει, δοκιμάστε ξανά!';
$_['error_filetype']     = 'Μη έγκυρος τύπος αρχείου, επιτρέπονται μόνο αρχεία CSV!';
$_['error_empty']        = 'Το αρχείο είναι κενό!';
$_['error_header']       = 'Μη έγκυρη γραμμή επικεφαλίδων: απαιτείται στήλη product_id ή name!';
$_['error_file']         = 'Επιλέξτε πρώτα ένα αρχείο CSV!';
