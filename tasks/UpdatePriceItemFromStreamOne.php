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
$shortopts = "df";
$longopts = [];
$options = getopt($shortopts, $longopts);
$debugMode = false;
if (isset($options['d'])) {
    $debugMode = true;
}
$forcedMode = isset($options['f']);
$thing = null;
//**************************************************update item prices */
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
    $streamOneItems = json_decode($buStreamOneApi->getProductsPrices($encodedSearch), true);
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
if (!empty($updatedItems)) {
    // start update item with new prices
    foreach ($updatedItems as $key => $item) {
        $newValue = round($item["newPrice"], 2);
        $updatedItems[$key]["newPrice"] = $newValue;
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
        $allRequests = $buStreamOneApi->getAllSubscriptionsSync($pages);
        for ($i = 0; $i < count($allRequests); $i++) {

            $temp = $allRequests[$i];
            $allSubscriptions = array_merge($allSubscriptions, $temp["BodyText"]["subscriptions"]);
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
                    array_push($subscriptionsContacts, (object)$contact);
                }
            }
        }

        // now we have all subscription contacts and then we need to merge it with customers
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

//******************************* update customer licences number and status */
// now we have all subscriptions,  streamone customers and cnc items
//----------------------------start update customer items seats from stream one
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

/**
 * @param $cncItems
 * @param $sku
 * @param $units
 * @param $unitPrice
 * @param $licenseStatus
 * @param $forcedMode
 * @param $licenseEmail
 * @throws Exception
 */
function updateContracts($cncItems,
                         $sku,
                         $units,
                         $unitPrice,
                         $licenseStatus,
                         $forcedMode,
                         $licenseEmail
)
{
    $customer = getCustomerFromLicenseEmail($licenseEmail);

    if (!$customer) {
        throw new Exception(
            "Could not find a customer that matches the SKU: {$sku} and email {$licenseEmail} in CNCAPPS"
        );
    }

    $customerId = $customer['customerID'];
    $customerName = $customer['name'];

    $itemId = getItemId($cncItems, $sku);
    if (!$itemId) {
        if ($licenseStatus == 'active') {
            throw new Exception(
                "Customer {$customerName}({$customerId}) {$licenseEmail}  does not have license for SKU {$sku} in CNCAPPS"
            );
        }
        return;
    }
    global $db;
    $db->query(
        "select cui_users as units, salePricePerMonth as salePrice from custitem where renewalStatus='R'  AND declinedFlag='N'
                                and cui_custno = {$customerId}
                                and cui_itemno =  {$itemId}"
    );

    $temp = $db->fetchAll();
    if (empty($temp)) {
        if ($licenseStatus == 'active') {
            throw new Exception(
                "Customer {$customerName}({$customerId}) {$licenseEmail}  does not have license for SKU {$sku} in CNCAPPS"
            );
        }
        return;
    }

    if (((int)$units != (int)$temp[0]["units"] || $forcedMode) && $licenseStatus == "active") {
        $salePriceAnnum = ($temp[0]['salePrice'] * $units) * 12;
        $costAnnum = ($unitPrice * $units) * 12;

        $params = [
            [
                "type"  => "i",
                "value" => $units
            ],
            [
                "type"  => "d",
                "value" => $unitPrice
            ],
            [
                "type"  => "d",
                "value" => $costAnnum
            ],
            [
                "type"  => "d",
                "value" => $salePriceAnnum
            ],
            [
                "type"  => "i",
                "value" => $customerId
            ],
            [
                "type"  => "i",
                "value" => $itemId
            ],
        ];

        $result = $db->preparedQuery(
            "update custitem set cui_users = ? , costPricePerMonth = ?, cui_cost_price = ?, cui_sale_price = ? where   renewalStatus='R'  AND declinedFlag='N'
                            and cui_custno = ?
                            and cui_itemno = ?",
            $params
        );
    }
    if ($licenseStatus == "inactive") {
        $db->query(
            "update custitem set  renewalStatus='D'  , declinedFlag='Y' where   
                                    cui_custno= $customerId
                                    and cui_itemno=  $itemId"
        );
    }
}

syncAddons($orderDetails, $cncItems, $forcedMode, $logger);
$updatedItems = 0;
$updatedItemsAddOns = 0;

$subscription = null;
$logger->info("All subscriptions number :" . count($allSubscriptions));

//get all customer subscriptions
foreach ($allSubscriptions as $item) {
    foreach ($item as $key => $subscription) {
        $subscription = (object)$subscription;
        try {
            updateContracts(
                $cncItems,
                $subscription->sku,
                $subscription->quantity,
                $subscription->unitPrice,
                $subscription->lineStatus,
                $forcedMode,
                $subscription->endCustomerEmail
            );
        } catch (Exception $exception) {
            $logger->error($exception->getMessage());
        }
    }
}

$logger->info('updated customers items  ' . $updatedItems);
$logger->info('updated customers items addOns  ' . $updatedItemsAddOns);
function syncAddons($orderDetails, $cncItems, $forcedMode, LoggerCLI $logger)
{
    global $db;
    $updatedItemsAddOns = 0;
    $allAddons = array();
    $orderUnique = array();
    foreach ($orderDetails as $order) {
        $order = $order["BodyText"]["orderInfo"];
        if (!in_array($order['orderNumber'], $orderUnique)) {
            array_push($orderUnique, $order["orderNumber"]);
            foreach ($order["lines"] as $line) {
                if (isset($line["addOns"]) && $line["lineStatus"] == 'active') {
                    foreach ($line["addOns"] as $addon) {
                        if ($addon["addOnStatus"] == "active") {
                            $compositeKey = "{$order['endUserEmail']}-{$addon["sku"]}";
                            if (!isset($allAddons[$compositeKey])) {
                                $allAddons[$compositeKey] = (object)[
                                    "orderNumber" => $order["orderNumber"],
                                    "email"       => $order["endUserEmail"],
                                    "sku"         => $addon["sku"],
                                    "quantity"    => 0,
                                    "addOnStatus" => $addon["addOnStatus"],
                                    "unitPrice"   => $addon['unitPrice']
                                ];
                            }
                            $allAddons[$compositeKey]->quantity += $addon['quantity'];
                        }
                    }
                }
            }
        }
    }
    foreach ($allAddons as $addOn) {
        try {
            updateContracts(
                $cncItems,
                $addOn->sku,
                $addOn->quantity,
                $addOn->unitPrice,
                $addOn->addOnStatus,
                $forcedMode,
                $addOn->email
            );
        } catch (\Exception $exception) {
            $logger->error($exception->getMessage());
        }
    }
}

/**
 * @param $email
 * @return array|mixed|null
 * @throws Exception
 */
function getCustomerFromLicenseEmail($email)
{

    if (!$email) {
        throw new Exception('Email is mandatory');
    }
    $that = null;
    global $customerCache;
    if (!$customerCache) {
        $customerCache = [];
    }

    if (!array_key_exists($email, $customerCache)) {
        $dbeCustomer = new DBECustomer($that);
        $customerCache[$email] = null;
        if ($dbeCustomer->getCustomerByStreamOneEmail($email)) {
            $customerCache[$email] = $dbeCustomer->getRowAsAssocArray();
        }
    }

    return $customerCache[$email];
}

function getItemId($cncItems, $sku)
{
    foreach ($cncItems as $item) {
        if ($item['itm_unit_of_sale'] == $sku || $item['partNoOld'] == $sku)
            return $item['itm_itemno'];
    }
    return null;
}

// *************************** get all subscriptions addons and update customer items

exit;