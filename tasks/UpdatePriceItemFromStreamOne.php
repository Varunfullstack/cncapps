<?php

/**
 * Created by Mustafa Taha. *
 * Date: 20/07/2020
 */

use CNCLTD\LoggerCLI;

require_once(__DIR__ . "/../htdocs/config.inc.php");
global $cfg;
global $db;

require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUMail.inc.php');
require_once($cfg['path_bu'] . '/BUTechDataApi.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomerItem.inc.php');
require_once($cfg['path_dbe'] . '/DBEStreamOneCustomers.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');


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
//**************************************************update item prices */
if (true) {
//------ get all ccna items
    $query = "SELECT  itm_itemno,itm_unit_of_sale, itm_sstk_cost ,itm_desc , partNoOld FROM  item WHERE isStreamOne=1";
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
        array_push($search["lines"], ["sku" => $item["itm_unit_of_sale"], "quantity" => 1]);
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
                        round($cncItem['itm_sstk_cost'], 2) != round($streamOneItem['unitResellerCost'], 2)
                    ) {
                        //compare price of item
                        array_push(
                            $updatedItems,
                            [
                                "id"       => $cncItem['itm_itemno'],
                                "sku"      => $streamOneItem['sku'],
                                "oldPrice" => $cncItem['itm_sstk_cost'],
                                "newPrice" => $streamOneItem['unitResellerCost'],
                                "desc"     => $cncItem['itm_desc']
                            ]
                        );
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
            $updatedItems[$key]["newPrice"] = $newValue;
            echo $db->query("update item set itm_sstk_cost=$newValue where itm_itemno=$item[id]");
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
//**************************************get all customers */
// fetch all stream one customers


    $allSubscriptions = [];
    $allCustomers = json_decode($buStreamOneApi->searchCustomers(json_encode(["noOfRecords" => 500])));
    if ($allCustomers->Result == "Success") {
        //BodyText.endCustomersDetails
        // now we have all streamOne Customers
        $streamOneCustomers = array_map(
            function ($item) {
                $item->name = $item->firstName . ' ' . $item->lastName;
                $item->endCustomerPO = $item->companyName;
                return $item;
            },
            $allCustomers->BodyText->endCustomersDetails
        );
        // get all subscriptions

        $firstSubscription = json_decode($buStreamOneApi->getAllSubscriptions(1));
        if ($firstSubscription->Result == "Success") {
            //"totalRecords":457,"totalPages":23,"page":1,"recordsPerPage":20,"subscriptions":
            $totalPages = $firstSubscription->BodyText->totalPages;
            $pages = array();
            for ($i = 2; $i < $totalPages; $i++)
                array_push($pages, $i);

            $subscriptions = $firstSubscription->BodyText->subscriptions;
            $allSubscriptions = array_merge($allSubscriptions, $subscriptions);
            //$totalPages=1;
            $allRequests = $buStreamOneApi->getAllSubscriptionsSync($pages);
            //echo  "167".count($allRequests);
            for ($i = 0; $i < count($allRequests); $i++) {

                $temp = $allRequests[$i];
                $allSubscriptions = array_merge($allSubscriptions, $temp["BodyText"]["subscriptions"]);
                //echo count($allSubscriptions)."\n";
            }

            //----------------------------end update customer items seats from stream one
            //now we have all subscription and we need to map it to customers
            $subscriptionsContacts = [];
            foreach ($allSubscriptions as $subscription) {
                foreach ($subscription as $key => $value) {
                    $value = (object)$value;
                    $contact = [
                        "companyName"   => $value->company,
                        "email"         => $value->endCustomerEmail,
                        "name"          => $value->endCustomerName,
                        "endCustomerPO" => isset($value->endCustomerPO) ? $value->endCustomerPO : null,
                        "MsDomain"      => isset($value->additionalData) ? [$value->additionalData] : null,
                    ];
                    $found = false;
                    foreach ($subscriptionsContacts as $inContact) {
                        if ($inContact->email == $contact["email"])
                            $found = true;
                    }
                    if (!$found) {
                        //echo  $contact["email"]."\n";
                        array_push($subscriptionsContacts, (object)$contact);
                    }
                }
            }

            // now we have all subscription contacts and then we need to merge it with customers
            $subscriptionsContacts = $subscriptionsContacts;
            foreach ($subscriptionsContacts as $orderContact) {
                $found = false;
                foreach ($streamOneCustomers as $customer) {
                    if ($customer->email == $orderContact->email) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    array_push($streamOneCustomers, $orderContact);
                    //echo   json_decode($orderContact);
                }
            }
            // now we have all customers and need to insert into db
            //if(false)
            {
                $inserted = 0;
                $db->query("delete from streamonecustomers");
                $i = 1;
                foreach ($streamOneCustomers as $customer) {
                    $dbeStreamOneCustomers = new DBEStreamOneCustomers($thing);
                    $dbeStreamOneCustomers->setPKValue($i);
                    if (isset($customer->addressLine1))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::addressLine1, $customer->addressLine1);
                    if (isset($customer->addressLine2))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::addressLine2, $customer->addressLine2);
                    if (isset($customer->city))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::city, $customer->city);
                    if (isset($customer->companyName))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::companyName, $customer->companyName);
                    if (isset($customer->country))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::country, $customer->country);
                    if (isset($customer->createdOn))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::createdOn, $customer->createdOn);
                    if (isset($customer->email))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::email, $customer->email);
                    if (isset($customer->endCustomerId))
                        $dbeStreamOneCustomers->setValue(
                            DBEStreamOneCustomers::endCustomerId,
                            $customer->endCustomerId
                        );
                    if (isset($customer->endCustomerPO))
                        $dbeStreamOneCustomers->setValue(
                            DBEStreamOneCustomers::endCustomerPO,
                            $customer->endCustomerPO
                        );
                    if (isset($customer->MsDomain))
                        $dbeStreamOneCustomers->setValue(
                            DBEStreamOneCustomers::MsDomain,
                            json_encode($customer->MsDomain)
                        );
                    if (isset($customer->name))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::name, $customer->name);
                    if (isset($customer->phone1))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::phone1, $customer->phone1);
                    if (isset($customer->postalCode))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::postalCode, $customer->postalCode);
                    if (isset($customer->title))
                        $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::title, $customer->title);
                    $dbeStreamOneCustomers->insertRow();
                    $inserted++;
                    $i = $i + 1;
                }
                $logger->info('inserted customers = ' . $inserted);
            }
        }

    }
}
//******************************* update customer licences number and status */
// now we have all subscriptions,  streamone customers and cnc items
//----------------------------start update customer items seats from stream one
//1- get all cnc customers
$db->query("SELECT `cus_custno` as id,streamOneEmail as email FROM `customer` WHERE `streamOneEmail` IS NOT NULL");
$cncCustomers = $db->fetchAll(MYSQLI_ASSOC);
//2- get all subscriptions details
$count = 0;
$orderIds = array();
foreach ($allSubscriptions as $item) {
    foreach ($item as $key => $sub) {
        $sub = (object)$sub;
        array_push($orderIds, $sub->orderNumber);
    }
}

$logger->info("Loading all subscriptions and there addOns from streamOne.....");
$orderDetails = $buStreamOneApi->getProductsDetails($orderIds, 40);
syncAddons($orderDetails, $cncItems, $cncCustomers, $logger);
$updatedItems = 0;
$updatedItemsAddOns = 0;

$subscription = null;
$logger->info("All subscriptions number :" . count($allSubscriptions));
foreach ($cncCustomers as $customer) {
    //get all customer subscriptions
    foreach ($allSubscriptions as $item) {

        foreach ($item as $key => $subscription) {
            $subscription = (object)$subscription;
            if (strtolower($customer["email"]) == strtolower($subscription->endCustomerEmail)) {
                $itemId = getItemId($cncItems, $subscription->sku);

                if ($itemId) {
                    $db->query(
                        "select cui_users quantity, costPricePerMonth as salePrice from custitem where   renewalStatus='R'  AND declinedFlag='N'
                                and cui_custno= $customer[id]
                                and cui_itemno=  $itemId"
                    );
                    $temp = $db->fetchAll();
                    if (count($temp) > 0) {

                        if ((int)$subscription->quantity != (int)$temp[0]["quantity"] && $subscription->lineStatus == "active") {
                            $params = [
                                [
                                    "type"  => "i",
                                    "value" => $subscription->quantity
                                ],
                                [
                                    "type"  => "d",
                                    "value" => $subscription->unitPrice
                                ],
                                [
                                    "type"  => "d",
                                    "value" => ($subscription->unitPrice * $subscription->quantity) * 12
                                ],
                                [
                                    "type"  => "d",
                                    "value" => ($temp[0]['salePrice'] * $subscription->quantity) * 12
                                ],
                                [
                                    "type"  => "i",
                                    "value" => $customer['id']
                                ],
                                [
                                    "type"  => "i",
                                    "value" => $itemId
                                ],
                            ];

                            $result = $db->preparedQuery(
                                "update custitem set cui_users = ? , costPricePerMonth = ?, cui_cost_price = ?, cui_sales_price = ? where   renewalStatus='R'  AND declinedFlag='N'
                            and cui_custno = ?
                            and cui_itemno = ?",
                                $params
                            );
                            $updatedItems++;
                        }
                    } else if ($subscription->lineStatus == "active")
                        $logger->info("Customer $customer[email] does not have license $subscription->sku in CNCAPPS");
                    if ($subscription->lineStatus == "inactive") {
                        $db->query(
                            "update custitem set  renewalStatus='D'  , declinedFlag='Y' where   
                                    cui_custno= $customer[id]
                                    and cui_itemno=  $itemId"
                        );
                    }
                } else if ($subscription->lineStatus == "active")
                    $logger->info("Customer $customer[email] does not have license $subscription->sku in CNCAPPS");

            }

        }
    }
}
$logger->info('updated customers items  ' . $updatedItems);
$logger->info('updated customers items addOns  ' . $updatedItemsAddOns);
function syncAddons($orderDetails, $cncItems, $cncCustomers, $logger)
{
    global $db;
    $updatedItemsAddOns = 0;
    // need to group addOns as email, sku and sum(seats);
    $allAddons = array();
    $orderUnique = array();
    foreach ($orderDetails as $order) {
        $order = $order["BodyText"]["orderInfo"];
        $found = false;
        foreach ($orderUnique as $o) {
            if ($o == $order["orderNumber"])
                $found = true;
        }
        if (!$found) {
            array_push($orderUnique, $order["orderNumber"]);
            foreach ($order["lines"] as $line) {
                if (isset($line["addOns"]) && $line["lineStatus"] == 'active') {
                    foreach ($line["addOns"] as $addon) {
                        if ($addon["addOnStatus"] == "active")
                            array_push(
                                $allAddons,
                                (object)[
                                    "orderNumber" => $order["orderNumber"],
                                    "email"       => $order["endUserEmail"],
                                    "sku"         => $addon["sku"],
                                    "quantity"    => $addon["quantity"],
                                    "addOnStatus" => $addon["addOnStatus"]
                                ]
                            );
                    }
                }
            }
        }
    }
    // echo "addons Count".count($allAddons)."\n";
    $items = array();
    foreach ($allAddons as $addon) {
        //if($addon->email=="Chris.Andrewartha@equity.co.uk")
        // echo "chris ".$addon->orderNumber." ".$addon->quantity." ".$addon->sku." ".$addon->addOnStatus."\n";

        $found = false;
        for ($i = 0; $i < count($items); $i++) {
            if ($items[$i]->email == $addon->email && $items[$i]->sku == $addon->sku && $addon->addOnStatus == 'active') {
                $found = true;
                //if($items[$i]->email=="Chris.Andrewartha@equity.co.uk")
                // echo "chris ".$addon->quantity." ".$items[$i]->quantity." ".$items[$i]->sku."\n";
                $items[$i]->quantity += $addon->quantity;

                break;
            }
        }
        if (!$found)
            array_push($items, $addon);
    }
    foreach ($items as $addOn) {
        $sku = $addOn->sku;
        $quantity = $addOn->quantity;
        $status = $addOn->addOnStatus;
        $itemId = getItemId($cncItems, $sku);
        $customerId = getCustomerId($cncCustomers, $addOn->email);
        //echo "\n".$customer["id"]." ".$customer["email"]." ".$sku." ".$itemId." ".$subscription->quantity;
        if ($itemId && $customerId) {
            $db->query(
                "select cui_users quantity,cui_cuino from custitem 
                where   
                    renewalStatus='R'  
                    and declinedFlag='N'
                    and cui_custno= $customerId
                    and cui_itemno=  $itemId"
            );
            $temp = $db->fetchAll();
            if (count($temp) > 0) {
                //  echo " ".$temp[0]["quantity"];
                if ((int)$quantity != (int)$temp[0]["quantity"] && $status == "active") {
                    $cui_cuino = $temp[0]["cui_cuino"];
                    //echo  $customer["id"]." ".$itemId." ".(int)$subscription->quantity." ".(int)$temp[0]["quantity"]."\n";
                    $db->query(
                        "update custitem set cui_users=$quantity 
                                    where   
                                    cui_cuino=$cui_cuino"
                    );

                    $updatedItemsAddOns++;
                }
                if ($status == "inactive") {
                    $db->query(
                        "update custitem set  renewalStatus='D'  , declinedFlag='Y' where   
                         cui_custno= $customerId
                        and cui_itemno=  $itemId"
                    );
                }

            } else if ($status == "active")
                $logger->info("Customer $addOn->email does not have license $sku in CNCAPPS");
        } else if ($status == "active")
            $logger->info("Customer $addOn->email does not have license $sku in CNCAPPS");

    }
    // echo "addons groups".count($items)."\n";    

    // echo json_encode($allAddons)."\n";

}

function getCustomerId($customers, $email)
{
    foreach ($customers as $customer) {
        if (trim(strtolower($customer["email"])) == trim(strtolower($email)))
            return $customer["id"];
    }
}

function getItemId($cncItems, $sku)
{
    foreach ($cncItems as $item) {
        if ($item['itm_unit_of_sale'] == $sku || $item['partNoOld'] == $sku)
            return $item['itm_itemno'];
    }
    return null;
}

function getAddOns($orderDetails, $subscription)
{
    //echo count($orderDetails);
    foreach ($orderDetails as $order) {
        $order = $order["BodyText"]["orderInfo"];
        foreach ($order["lines"] as $line) {
            //echo $subscription->sku." ".$line["sku"]."\n";
            if ($subscription->orderNumber == $order["orderNumber"]
                && $subscription->sku == $line["sku"]
                && isset($line["addOns"])
            ) {
                //echo 'Addons'."\n";
                return $line["addOns"];
            }
        }
    }
    return [];
}

// *************************** get all subscriptions addons and update customer items

exit;