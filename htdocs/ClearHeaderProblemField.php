<?php
/**
 * Clear header problem field
 *
 * Timed task called once a day to clear the help desk problems field on the system header
 * table
 *
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
GLOBAL $cfg;
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
$thing = null;
$buHeader = new BUHeader($thing);
$buHeader->clearActivityProblemField();
echo "Cleared help-desk problem field on system header"
?>