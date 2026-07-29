<?php
if (version_compare(VERSION, '2.1.0.3', '>')) {
$this->registry->set('dmt', new dmt\dmt($this->registry));

} elseif (version_compare(VERSION, '2', '>=')) {
$registry->set('dmt', new dmt\dmt($registry));
} else {

include_once(DIR_SYSTEM . 'library/dmt.php');
$registry->set('dmt', new dmt\dmt($registry));

}