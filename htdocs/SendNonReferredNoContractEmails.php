<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/01/2019
 * Time: 14:02
 */


require_once("config.inc.php");
require_once($cfg ["path_gc"] . "/Controller.inc.php");
require_once($cfg ["path_bu"] . "/BUMail.inc.php");

$thing = null;
global $db;

$db->query(
    "SELECT 
  customer.`cus_name` as name,
  customer.`cus_custno` as id,
  customer.`cus_create_date` as creationDate 
FROM
  customer 
WHERE customer.`cus_referred` = 'N'
AND
(SELECT 
    COUNT(*) 
  FROM
    custitem 
  WHERE cui_custno = customer.`cus_custno`
AND (
    renewalStatus = 'R'
    OR declinedFlag <> 'Y'
)) = 0 "
);

if ($db->num_rows()) {


    $template = new Template(EMAIL_TEMPLATE_DIR);

    $template->setFile(
        'NonReferredNoContractEmail',
        'NonReferredNoContractEmail.html'
    );
    $content = "";

    $template->setBlock(
        'NonReferredNoContractEmail',
        'customersBlock',
        'customers'
    );

    while ($db->next_record(MYSQLI_ASSOC)) {


        $customerURL = "http://" . $_SERVER['HTTP_HOST'] . Controller::formatForHTML(
                '/Customer.php?action=dispEdit&customerID=' . $db->Record['id'],
                1
            );

        $nameLink = "<a href='" . $customerURL . "'>" . $db->Record['name'] . "</a>";

        $template->setVar(
            [
                "customerID"           => $db->Record['id'],
                "customerNameLink"     => $nameLink,
                "customerCreationDate" => $db->Record['creationDate']
            ]
        );

        $template->parse(
            'customers',
            'customersBlock',
            true
        );

    }


    $subject = 'Active Customers With No Contracts';

    $template->parse(
        'OUTPUT',
        "NonReferredNoContractEmail"
    );

    $body = $template->getVar('OUTPUT');


    echo $body;

    $emailTo = "customersnocontracts@cnc-ltd.co.uk";

    $hdrs = array(
        'From'         => CONFIG_SUPPORT_EMAIL,
        'To'           => $emailTo,
        'Subject'      => $subject,
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    $mime = new Mail_mime();

    $mime->setHTMLBody($body);

    $mime_params = array(
        'text_encoding' => '7bit',
        'text_charset'  => 'UTF-8',
        'html_charset'  => 'UTF-8',
        'head_charset'  => 'UTF-8'
    );

    $body = $mime->get($mime_params);

    $hdrs = $mime->headers($hdrs);

    $buMail = new BUMail($thing);

    $buMail->putInQueue(
        CONFIG_SUPPORT_EMAIL,
        $emailTo,
        $hdrs,
        $body
    );

}