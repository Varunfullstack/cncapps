<?php
/**
* Populate SLAHours field on historic Requests
* CNC Ltd
*
* @access public
* @authors Karim Ahmed - Sweet Code Limited
*/
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUInvoice.inc.php');
$buInvoice = new BUInvoice($this);
$buInvoice->populate2010PdfField();
echo "Finished setting 2010 pdf fields";
?>