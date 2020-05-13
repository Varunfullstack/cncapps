<?php
/**
 * notify certain CNC users about outstanding support calls
 *
 * called as scheduled task at given time every day
 *
 * @authors Karim Ahmed - Sweet Code Limited
 */

require_once("config.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");
require_once($cfg["path_bu"] . '/BUItemsNotYetReceived.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
$outputToScreen = isset($_GET['toScreen']);
$thing = null;
$buItemsNotYetReceived = new BUItemsNotYetReceived($thing);


$sender_name = "System";
$sender_email = CONFIG_SALES_EMAIL;
$headers = "From: " . $sender_name . " <" . $sender_email . ">\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html";


$result = $buItemsNotYetReceived->getItemsNotYetReceived();


usort(
    $result,
    function (\CNCLTD\ItemNotYetReceived $a,
              \CNCLTD\ItemNotYetReceived $b
    ) {

        if ($a->getCustomerName() > $b->getCustomerName()) {
            return 1;
        }

        if ($a->getCustomerName() < $b->getCustomerName()) {
            return -1;
        }

        if ($a->getPurchaseOrderRequiredBy() > $b->getPurchaseOrderRequiredBy()) {
            return -1;
        }

        if ($a->getPurchaseOrderRequiredBy() < $b->getPurchaseOrderRequiredBy()) {
            return 1;
        }

        return 0;
    }
);

global $twig;
$body = $twig->render('@internal/itemsNotYetReceivedEmail.html.twig', ["itemsNotYetReceived" => $result]);

if (!$outputToScreen) {

    $buMail = new BUMail($thing);

    $toEmail = 'unreceivedpo@' . CONFIG_PUBLIC_DOMAIN;

    $hdrs = array(
        'From'         => CONFIG_SALES_EMAIL,
        'To'           => $toEmail,
        'Subject'      => 'Purchase Order Status Report',
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
        CONFIG_SALES_EMAIL,
        $toEmail,
        $hdrs,
        $body
    );
} else {
    echo $body;
}
?>