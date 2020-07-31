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
if(true)
{
//------ get all ccna items
$query  = "SELECT  itm_itemno,itm_unit_of_sale, itm_sstk_cost ,itm_desc , partNoOld FROM  item WHERE isStreamOne=1";
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
                    round($cncItem['itm_sstk_cost'],2) != round($streamOneItem['unitResellerCost'],2)
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
    echo json_encode($updatedItems);
    // start update item with new prices
    foreach ($updatedItems as $key => $item) {
        $newValue = round($item["newPrice"], 2);
        $updatedItems[$key]["newPrice"] =  $newValue;
      echo  $db->query("update item set itm_sstk_cost=$newValue where itm_itemno=$item[id]");
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
    $streamOneCustomers = array_map(function ($item) {
        $item->name = $item->firstName . ' ' . $item->lastName;
        $item->endCustomerPO = $item->companyName;
        return $item;
    }, $allCustomers->BodyText->endCustomersDetails);
    // get all subscriptions
    
    $firstSubscription = json_decode($buStreamOneApi->getAllSubscriptions(1));
    if ($firstSubscription->Result == "Success") {
        //"totalRecords":457,"totalPages":23,"page":1,"recordsPerPage":20,"subscriptions":
        $totalPages = $firstSubscription->BodyText->totalPages;
        $subscriptions = $firstSubscription->BodyText->subscriptions;
        $allSubscriptions = array_merge($allSubscriptions, $firstSubscription->BodyText->subscriptions);
        //$totalPages=1;
        for ($i = 2; $i <= $totalPages; $i++) {
            $temp = json_decode($buStreamOneApi->getAllSubscriptions($i));
            $allSubscriptions = array_merge($allSubscriptions, $temp->BodyText->subscriptions);
            //echo count($allSubscriptions)."\n";  
        }
        
        //----------------------------end update customer items seats from stream one
        //now we have all subscription and we need to map it to customers
        $subscriptionsContacts = [];
        foreach ($allSubscriptions as $subscription) {
            foreach ($subscription as $key => $value) {

                $contact = [
                    "companyName" => $value->company,
                    "email" => $value->endCustomerEmail,
                    "name" => $value->endCustomerName,
                    "endCustomerPO" => isset($value->endCustomerPO) ? $value->endCustomerPO : null,
                    "MsDomain" => isset($value->additionalData) ? [$value->additionalData] : null,
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
        $i=1;
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
                $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::endCustomerId, $customer->endCustomerId);
            if (isset($customer->endCustomerPO))
                $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::endCustomerPO, $customer->endCustomerPO);
            if (isset($customer->MsDomain))
                $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::MsDomain, json_encode($customer->MsDomain));
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
            $i=$i+1;
        }
        $logger->info('inserted customers = '.$inserted);
        }
    }
    
}
}
//******************************* update customer licences number and status */
// now we have all subscriptions,  streamone customers and cnc items
//----------------------------start update customer items seats from stream one
//1- get all cnc customers
$db->query("SELECT `cus_custno` as id,streamOneEmail as email FROM `customer` WHERE `streamOneEmail` IS NOT NULL");
$cncCustomers =$db->fetchAll(MYSQLI_ASSOC);
//echo json_encode( $cncCustomers); //cus_custno
echo "\n";
$updatedItems=0;
foreach($cncCustomers as $customer)
{
    //echo $customer["email"]."\n";
    //get all customer subscriptions
    foreach ($allSubscriptions as $item) {
        foreach ($item as $key => $subscription) {
            //echo json_encodesubscription$value);
   
           // echo $subscription->endCustomerEmail."\n";
            if($customer["email"]==$subscription->endCustomerEmail)
            {
                $itemId=getItemId($cncItems,$subscription->sku);
                //echo "\n".$customer["id"]." ".$customer["email"]." ".$subscription->sku." ".$itemId." ".$subscription->quantity;
                if($itemId)
                {
                    $db->query("select cui_users quantity from custitem where   renewalStatus='R'  AND declinedFlag='N'
                    and cui_custno= $customer[id]
                    and cui_itemno=  $itemId");
                    $temp=$db->fetchAll();
                    if(count($temp)>0)
                    {
                      //  echo " ".$temp[0]["quantity"];
                        if((int)$subscription->quantity!=(int)$temp[0]["quantity"]&&$subscription->lineStatus=="active")
                        {
                            //echo  $customer["id"]." ".$itemId." ".(int)$subscription->quantity." ".(int)$temp[0]["quantity"]."\n";
                            $db->query("update custitem set cui_users=$subscription->quantity where   renewalStatus='R'  AND declinedFlag='N'
                            and cui_custno= $customer[id]
                            and cui_itemno=  $itemId");
                            $updatedItems++;
                        }
                    }
                    else $logger->info("Customer $customer[email] does not have license $subscription->sku in CNCAPPS");
                }
                else $logger->info("Customer $customer[email] does not have license $subscription->sku in CNCAPPS");

            }
             
        }
    }
}
$logger->info('updated customers items  '.$updatedItems);
function getItemId($cncItems,$sku)
{
   foreach($cncItems as $item)
   {
    if($item['itm_unit_of_sale'] ==$sku||$item['partNoOld']  == $sku)
        return $item['itm_itemno'];
   } 
   return null;
}

exit;