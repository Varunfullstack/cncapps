<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 19/02/2019
 * Time: 10:05
 */


GLOBAL $cfg;
require_once("config.inc.php");
require_once($cfg["path_ct"] . "/CTLeadStatusReport.inc.php");
require_once($cfg['path_bu'] . '/BURenBroadband.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerItem.inc.php');
$thing = null;
$dsRenBroadband = new DataSet($thing);

$template = new Template (
    $GLOBALS ["cfg"] ["path_templates"],
    "remove"
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
    } else {
        $buMail = new BUMail($thing);

        $senderEmail = CONFIG_SUPPORT_EMAIL;
        $senderName = 'CNC Support Department';

        $toEmail = "leasedlinecontractexpirations@cnc-ltd.co.uk";

        $body = $result;

        $hdrs = array(
            'From'         => $senderEmail,
            'To'           => $toEmail,
            'Subject'      => 'Leased line contract expiry notification',
            'Date'         => date("r"),
            'Content-Type' => 'text/html; charset=UTF-8'
        );


        $buMail->mime->setHTMLBody($body);

        $mime_params = array(
            'text_encoding' => '7bit',
            'text_charset'  => 'UTF-8',
            'html_charset'  => 'UTF-8',
            'head_charset'  => 'UTF-8'
        );
        $body = $buMail->mime->get($mime_params);

        $hdrs = $buMail->mime->headers($hdrs);

        $buMail->putInQueue(
            $senderEmail,
            $toEmail,
            $hdrs,
            $body
        );
    }
}
