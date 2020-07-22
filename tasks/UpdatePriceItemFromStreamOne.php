<?php

/**
 * Created by Mustafa Taha. * 
 * Date: 20/07/2020 
 */

use CNCLTD\LoggerCLI;
use Twig\Environment;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
global $db;

require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_bu'] . '/BUTechDataApi.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');

$logName = 'UpdatePriceItemFromStreamOne';
$logger = new LoggerCLI($logName);

// increasing execution time to infinity...
ini_set('max_execution_time', 0);
if (!is_cli()) {
    echo 'This script can only be ran from command line';
    exit;
}
// Script example.php
$shortopts = "d";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$thing = null;
//------ get all ccna items
$query  = "SELECT  itm_itemno,itm_unit_of_sale, itm_sstk_cost ,itm_desc FROM  item WHERE isStreamOne=1";
$db->query($query);
$cncItems = $db->fetchAll(MYSQLI_ASSOC);

$search = null;
$search["vendorIds"] = [397];
$search["page"] = 1;
$search["lines"] = [
    ["sku" => "SK4665", "quantity" => 1],
    ["sku" => "SK4663", "quantity" => 1]
];
foreach ($cncItems as $item) {
    array_push($search["lines"],  ["sku" => $item["itm_unit_of_sale"], "quantity" => 1]);
}

$updatedItems = [];

//------ fetch StreamOne Data
$pages = ceil(count($cncItems) / 10);
$buStreamOneApi = new BUTechDataApi($thing);
for ($page = 1; $page <= $pages; $page++) {
    $search['page'] = $page;
    $encodedSearch = json_encode($search);
    //echo json_encode($encodedSearch);     
    $streamOneItems = json_decode($buStreamOneApi->getProductsPrices($encodedSearch), true);
    //echo $streamOneItems["Result"];
    if ($streamOneItems["Result"] === "Success") {
        //start to compare prices        
        foreach ($streamOneItems["BodyText"]['pricingDetails'] as $streamOneItem) {

            foreach ($cncItems as $cncItem) {
                if (
                    $cncItem['itm_unit_of_sale'] == $streamOneItem['sku'] &&
                    $cncItem['itm_sstk_cost'] != $streamOneItem['unitResellerCost']
                ) {
                    //compare price of item
                    array_push($updatedItems, [
                        "id" => $cncItem['itm_itemno'],
                        "sku" => $streamOneItem['sku'],
                        "oldPrice" => $cncItem['itm_sstk_cost'],
                        "newPrice" => $streamOneItem['unitResellerCost'],
                        "desc" => $cncItem['itm_desc']
                    ]);
                }
            }
        }
    }
}
if (count($updatedItems) > 0) {
    //echo json_encode($updatedItems);
    // start update item with new prices
    foreach ($updatedItems as $key => $item) {
        $newValue = round($item["newPrice"], 2);
        $updatedItems[$key]["newPrice"] =  $newValue;        
        $db->query("update item set itm_sstk_cost=$newValue where itm_itemno=$item[id]");
    }

    //send email to sales to tell them there is an update to price  
    $buMail = new BUMail($thing);
    $senderEmail = CONFIG_SUPPORT_EMAIL;
    $toEmail = CONFIG_SALES_EMAIL;
    $hdrs = array(
        'From'         => $senderEmail,
        'To'           => $toEmail,
        'Subject'      => 'The cost price of a StreamOne Office 365 license has changed',
        'Date'         => date("r"),
        'Content-Type' => 'text/html; charset=UTF-8'
    );

    global $twig;
    $html = $twig->render(
        '@internal/streamOnePricesUpdate.html.twig',
        ["items" => $updatedItems]
    );
    $buMail->mime->setHTMLBody($html);

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
    echo "email sent";
}
