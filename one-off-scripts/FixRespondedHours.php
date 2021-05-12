<?php
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once $cfg['path_bu'] . '/BUProblemSLA.inc.php';
$thing        = null;
$buProblemSLA = new BUProblemSLA($thing);
/** @var $db dbSweetcode */
global $db;
$statement = $db->query("select distinct problemID from contact_callback");
while ($row = $statement->fetch_assoc()) {
    $serviceRequestId = $row['problemID'];
    $respondedHours   = $buProblemSLA->getRespondedHours($serviceRequestId);
    $serviceRequest   = new DBEProblem($thing);
    $serviceRequest->getRow($serviceRequestId);
    $serviceRequest->setValue(DBEProblem::respondedHours, $respondedHours);
    $serviceRequest->updateRow();
}
