<?php
require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
require_once $cfg['path_bu'] . '/BUProblemSLA.inc.php';
$thing        = null;
$buProblemSLA = new BUProblemSLA($thing);
/** @var $db dbSweetcode */
global $db;
$statement          = $db->query("select distinct problemID from contact_callback");
$allServiceRequests = $statement->fetch_all(MYSQLI_ASSOC);
foreach ($allServiceRequests as $serviceRequestInfo) {
    $serviceRequestId = $serviceRequestInfo['problemID'];
    $respondedHours   = $buProblemSLA->getRespondedHours($serviceRequestId);
    $serviceRequest   = new DBEProblem($thing);
    echo $serviceRequestId . ": " . $respondedHours . PHP_EOL;
    $serviceRequest->getRow($serviceRequestId);
    $serviceRequest->setValue(DBEProblem::respondedHours, $respondedHours);
    $serviceRequest->updateRow();
}
