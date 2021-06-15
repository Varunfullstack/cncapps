<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 19/02/2019
 * Time: 10:05
 */

use CNCLTD\Business\BUActivity;

global $cfg;
require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTLeadStatusReport.inc.php");
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
$thing          = null;
$dsRenBroadband = new DataSet($thing);
$template       = new Template (
    $GLOBALS ["cfg"] ["path_templates"], "remove"
);
$template->setFile(
    'RenBroadbandList',
    'LeasedLinesContractExpiryList.html'
);
$buRenBroadband = new BURenBroadband($thing);
$lowerBoundDays = 59;
$upperBoundDays = 67;
if (isset($_GET['lowerBoundDays'])) {
    $lowerBoundDays = $_GET['lowerBoundDays'];
}
if (isset($_GET['upperBoundDays'])) {
    $upperBoundDays = $_GET['upperBoundDays'];
}
$onScreen = isset($_GET['onScreen']);
$buRenBroadband->resetContractExpireNotified();
$buRenBroadband->getLeasedLinesToExpire(
    $dsRenBroadband,
    $lowerBoundDays,
    $upperBoundDays
);
if ($dsRenBroadband->rowCount()) {
    $template->set_block(
        'RenBroadbandList',
        'rowBlock',
        'rows'
    );
    while ($dsRenBroadband->fetchNext()) {
        $customerItemID = $dsRenBroadband->getValue(DBEJRenBroadband::customerItemID);
        if ($dsRenBroadband->getValue(DBEJRenBroadband::contractExpireNotified) == 0) {
            // Create New SR and send Email
            $buActivity = new BUActivity($thing);
            $buActivity->createActivityLeasedLineExpire(
                $dsRenBroadband->getValue(DBEJRenBroadband::customerID),
                $customerItemID,
                $dsRenBroadband->getValue(DBEJRenBroadband::itemDescription),
                Controller::dateYMDtoDMY(
                    $dsRenBroadband->getValue(DBEJRenBroadband::contractExpiryDate)
                )
            );
            // mark contractExpireNotified @ custitem  to 1
            global $db;
            $sql = "UPDATE custitem
                SET contractExpireNotified = 1 where cui_cuino=$customerItemID";
            $db->query($sql);
        }
        $template->set_var(
            array(
                'customerItemID'     => $customerItemID,
                'customerName'       => $dsRenBroadband->getValue(DBEJRenBroadband::customerName),
                'itemDescription'    => $dsRenBroadband->getValue(DBEJRenBroadband::itemDescription),
                'ispID'              => $dsRenBroadband->getValue(DBEJRenBroadband::ispID),
                'adslPhone'          => $dsRenBroadband->getValue(DBEJRenBroadband::adslPhone),
                'salePricePerMonth'  => $dsRenBroadband->getValue(DBEJRenBroadband::salePricePerMonth),
                'costPricePerMonth'  => $dsRenBroadband->getValue(DBEJRenBroadband::costPricePerMonth),
                'invoiceFromDate'    => Controller::dateYMDtoDMY(
                    $dsRenBroadband->getValue(DBEJRenBroadband::invoiceFromDate)
                ),
                'invoiceToDate'      => Controller::dateYMDtoDMY(
                    $dsRenBroadband->getValue(DBEJRenBroadband::invoiceToDate)
                ),
                'contractExpiryDate' => Controller::dateYMDtoDMY(
                    $dsRenBroadband->getValue(DBEJRenBroadband::contractExpiryDate)
                ),
            )
        );
        $template->parse(
            'rows',
            'rowBlock',
            true
        );
    }
    $template->parse(
        'OUTPUT',
        'RenBroadbandList'
    );
    $result = $template->getVar('OUTPUT');
    if ($onScreen) {
        echo $result;
    }
}
