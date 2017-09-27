<?php
/**
 * Automated process requests from portal
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once("config.inc.php");
require_once($cfg["path_bu"] . "/BURenewal.inc.php");

$buRenewal = new BURenewal($this);

$buRenewal->processRenewalEmailRequests();    // Renewal schedules to be sent

$buRenewal->processTandcEmailRequests();      // T&Cs awaiting to be sent
exit;
?>
