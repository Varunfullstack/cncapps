<?php
/**
 * Action Alert Email controller
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once("config.inc.php");
global $cfg;
require_once($cfg['path_bu'] . '/BUProblemSLA.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
$thing = null;

$dryRun = false;
if (isset($_REQUEST['dryRun'])) {
    $dryRun = true;
}

$problemID = null;
if (isset($_REQUEST['problemID'])) {
    $problemID = $_REQUEST['problemID'];
}

$debug = null;
if (isset($_REQUEST['debug'])) {
    $debug = $_REQUEST['debug'];
}

$buProblemSLA = new BUProblemSLA($thing);
$buProblemSLA->monitor($dryRun, $problemID, $debug);
echo "Service Desk Monitor Routine Finished";

echo 'Start processing future SR\n';
$buActivity = new BUActivity($thing);
$dsProblems = $buActivity->getAlarmReachedProblems();
while ($dsProblems->fetchNext()) {
    $update = new DBEProblem($thing);
    $update->getRow($dsProblems->getValue(DBEJProblem::problemID));
    echo "<br>{$dsProblems->getValue(DBEJProblem::problemID)} is in breach resetting it to Awaiting CNC <br>";
    $buActivity->logOperationalActivity(
        $dsProblems->getValue(DBEJProblem::problemID),
        "Future alarm has been reached, resetting to Awaiting CNC",
        true
    );
    $update->setValue(DBEProblem::alarmDate, null);
    $update->setValue(DBEProblem::alarmTime, null);
    $update->setValue(DBEProblem::awaitingCustomerResponseFlag, 'N');

    $update->updateRow();
}
echo 'Finished processing future breached SR\'s';

?>