<?php

use CNCLTD\Data\DBEItem;
use PhpParser\Node\Expr\Isset_;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg ['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg ['path_dbe'] . '/DBECustomerItem.inc.php');
require_once($cfg ['path_dbe'] . '/DBEStreamOneCustomers.inc.php');
require_once($cfg['path_bu'] . '/BUTechDataApi.inc.php');

class CTSCustomerLicenses extends CTCNC
{

    /**
     * @var BUTechDataApi
     */
    private $buTechDataApi;

    function __construct($requestMethod,
                         $postVars,
                         $getVars,
                         $cookieVars,
                         $cfg
    )
    {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );
        if (!$this->dbeUser->getValue(DBEUser::streamOneLicenseManagement)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buTechDataApi = new BUTechDataApi($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $page = 1;
        if (isset($_GET['page'])) $page = $_GET['page'];
        switch ($this->getAction()) {
            case "getProductList":
                echo $this->buTechDataApi->getProductList($page);
                exit;
            case "getAllSubscriptions":
                echo $this->buTechDataApi->getAllSubscriptionsForPage($page);
                exit;
            case "getSubscriptionsByEmail":
                echo $this->buTechDataApi->getSubscriptionsByEmail($page);
                exit;
            case "getSubscriptionsByDateRange":
                echo $this->buTechDataApi->getSubscriptionsByDateRange($page);
                exit;
            case "searchTechDataCustomers":
                echo $this->buTechDataApi->searchCustomers($page);
                exit;
            case "addTechDataCustomer":
                echo $this->buTechDataApi->createCustomer();
                exit;
            case "getEndCustomerById":
                echo $this->getCustomerById();
                exit;
            case "updateTechDataCustomer":
                echo $this->updateTechDataCustomer();
                exit;
            case "getSubscriptionsByEndCustomerId":
                echo $this->buTechDataApi->getSubscriptionsByEndCustomerId($page);
                exit;
            case "getVendors":
                echo $this->buTechDataApi->getVendors($page);
                exit;
            case "getProductsByVendor":
                echo $this->buTechDataApi->getProductsByVendor($page);
                exit;
            case "getOrderDetials":
                echo $this->buTechDataApi->getOrderDetials($page);
                exit;
            case "getProductBySKU":
                echo $this->buTechDataApi->getProductBySKU();
                exit;
            case "updateSubscription":
                echo $this->buTechDataApi->updateSubscription();
                exit;
            case "updateSubscriptionAddOns":
                echo $this->buTechDataApi->updateSubscriptionAddOns();
                exit;
            case "purchaseSubscriptionAddOns":
                echo $this->buTechDataApi->purchaseSubscriptionAddOns();
                exit;
            case "getProductsPrices":
                echo $this->getProductsPrices();
                exit;
            case "activeCncItem":
                echo $this->activeCncItem();
                exit;
            case "addSubscription":
                echo $this->buTechDataApi->addOrder();
                exit;
            case "getLocalProducts":
                echo $this->getLocalProducts();
                exit;
            case "getStreamOneCustomerByEmail":
                echo $this->buTechDataApi->getStreamOneCustomerByEmail();
                exit;
            case "getStreamOneCustomersLocal":
                echo $this->getStreamOneCustomersLocal();
                exit;
            case "checkLicenseExistAtCNC":
                echo $this->checkLicenseExistAtCNC();
                exit;
            default:
                $this->setTemplate();
        }
    }

    function getCustomerById()
    {
        $this->setMethodName('getCustomerById');
        $endCustomerId = $_GET['endCustomerId'];
        $endCustomer   = null;
        if (isset($endCustomerId)) {
            //get techdata end customer
            $endCustomer = $this->buTechDataApi->getCustomerById($endCustomerId);
            $obj         = json_decode($endCustomer);
            //get cnc customer by endcustomerId
            if ($obj->Result == 'Success') {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->setValue(DBECustomer::streamOneEmail, $endCustomerId);
                $dbeCustomer->getRowsByColumn(DBECustomer::streamOneEmail);
                $dbeCustomer->fetchNext();
                $obj->BodyText->endCustomerDetails->cncCustomerId   = $dbeCustomer->getValue(DBECustomer::customerID);
                $obj->BodyText->endCustomerDetails->cncCustomerName = $dbeCustomer->getValue(DBECustomer::name);

            }
            return json_encode($obj);
        }
    }

    function updateTechDataCustomer()
    {
        $body = file_get_contents('php://input');
        if (isset($_GET['endCustomerId']) && isset($body)) {
            $bodyObj = json_decode($body);
            $result  = $this->buTechDataApi->updateCustomer($_GET['endCustomerId'], $body);
            $obj     = json_decode($result);
            if ($obj->Result == 'Success') {
                if ($bodyObj->cncCustId != null) {
                    $dbeCustomer = new DBECustomer($this);
                    $dbeCustomer->setPKValue($bodyObj->cncCustId);
                    $dbeCustomer->getRow();
                    $dbeCustomer->setValue(DBECustomer::streamOneEmail, null);
                    $dbeCustomer->updateRow();
                }
                if ($bodyObj->newCustomerId != null) {
                    // set new
                    $dbeCustomer = new DBECustomer($this);
                    $dbeCustomer->setPKValue($bodyObj->newCustomerId);
                    $dbeCustomer->getRow();
                    $dbeCustomer->setValue(DBECustomer::streamOneEmail, $bodyObj->email);
                    $dbeCustomer->updateRow();
                }
            }
            if ($obj->Result == 'Success' && $bodyObj->cncCustId != null) {
                // update local item
                $dbeStreamOneCustomers = new DBEStreamOneCustomers($this);
                $dbeStreamOneCustomers->setPKValue($bodyObj->id);
                $dbeStreamOneCustomers->getRow();
                if (isset($bodyObj->addressLine1)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::addressLine1,
                    $bodyObj->addressLine1
                );
                if (isset($bodyObj->addressLine2)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::addressLine2,
                    $bodyObj->addressLine2
                );
                if (isset($bodyObj->city)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::city,
                    $bodyObj->city
                );
                if (isset($bodyObj->companyName)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::companyName,
                    $bodyObj->companyName
                );
                if (isset($bodyObj->country)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::country,
                    $bodyObj->country
                );
                if (isset($bodyObj->createdOn)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::createdOn,
                    $bodyObj->createdOn
                );
                if (isset($bodyObj->email)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::email,
                    $bodyObj->email
                );
                if (isset($bodyObj->endCustomerId)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::endCustomerId,
                    $bodyObj->endCustomerId
                );
                if (isset($bodyObj->endCustomerPO)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::endCustomerPO,
                    $bodyObj->endCustomerPO
                );
                if (isset($bodyObj->name)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::name,
                    $bodyObj->name
                );
                if (isset($bodyObj->phone1)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::phone1,
                    $bodyObj->phone1
                );
                if (isset($bodyObj->postalCode)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::postalCode,
                    $bodyObj->postalCode
                );
                if (isset($bodyObj->title)) $dbeStreamOneCustomers->setValue(
                    DBEStreamOneCustomers::title,
                    $bodyObj->title
                );
                $dbeStreamOneCustomers->updateRow();
            }
            return $result;
        } else return json_encode(['Result' => 'Failed']);
    }

    function getProductsPrices()
    {
        $body = file_get_contents('php://input');
        return $this->buTechDataApi->getProductsPrices($body);
    }

    function activeCncItem()
    {
        global $db;
        $customerId = null;
        $sku        = null;
        $seats      = null;
        if (isset($_GET['customerId'])) $customerId = $_GET['customerId'];
        if (isset($_GET['sku'])) $sku = $_GET['sku'];
        //get item id
        $dbeItem = new DBEItem($this);
        $dbeItem->setValue(DBEItem::partNo, $sku);
        $dbeItem->getRowByColumn(DBEItem::partNo);
        $itemId = $dbeItem->getValue(DBEItem::itemID);
        // update customer item
        if ($customerId && $itemId && $seats) {
            $result = $db->query(
                "update custitem set
            renewalStatus ='R',
            declinedFlag ='N',
            cui_users =$seats
            where                 
                cui_custno=$customerId and
                cui_itemno=  $itemId  and
                renewalStatus ='D' and
                declinedFlag  ='Y'
            "
            );
            if ($result) {
                return $this->success("");
            }
        }
        return $this->failed();

    }

    function success($message = "")
    {
        return json_encode(['Result' => 'Success', 'message' => $message]);
    }

    function failed($message = "")
    {
        return json_encode(['Result' => 'Failed', 'message' => $message]);
    }

    function getLocalProducts()
    {
        global $db;
        $results = $db->query(
            "SELECT itm_itemno,itm_desc as description, itm_unit_of_sale as sku, itm_sstk_cost as cost FROM  item WHERE isStreamOne=1"
        )->fetch_all(MYSQLI_ASSOC);
        return json_encode($results);
    }

    function getStreamOneCustomersLocal()
    {
        $dbeStreamOneCustomers = new DBEStreamOneCustomers($this);
        $dbeStreamOneCustomers->getRows();
        $rows = $dbeStreamOneCustomers->fetchArray();
        global $db;
        $db->query(
            "SELECT   s.`id`,
       s.`name`,
       s.`email`,
       s.`createdOn`,
       s.`country`,
       s.`companyName`,
       s.`city`,
       s.`addressLine1`,
       s.`addressLine2`,
       s.`MsDomain`,
       s.`phone1`,
       s.`postalCode`,
       s.`title`,
       s.`endCustomerId`,
       s.`endCustomerPO`,
        `cus_name` cncCustName ,
     `cus_custno` cncCustId   
        FROM  streamOneCustomers s LEFT JOIN `customer` c ON s.email=c.streamOneEmail"
        );
        return json_encode($db->fetchAll(MYSQLI_ASSOC));
    }

    function checkLicenseExistAtCNC()
    {
        global $db;
        $email = $_GET["email"] ?? null;
        $sku   = $_GET["sku"] ?? null;
        if ($email != null && $sku != null) {
            //get customer Id
            $dbeCustomer = new DBECustomer($this);
            $dbeCustomer->setValue(DBECustomer::streamOneEmail, $email);
            $dbeCustomer->getRowByColumn(DBECustomer::streamOneEmail);
            $custId = $dbeCustomer->getPKValue();
            if ($custId) {
                // get item no by sku
                $dbeItem = new DBEItem($this);
                if ($dbeItem->getItemsByPartNoOrOldPartNo($sku)) {
                    $itemId       = $dbeItem->getPKValue();
                    $customerItem = new DBECustomerItem($this);
                    $count        = $customerItem->getCountByCustomerAndItemID($custId, $itemId);
                    if ($count) {
                        return json_encode(["status" => true, "custId" => $custId, "itemId" => $itemId]);
                    }
                    return json_encode(["status" => false, "custId" => $custId, "itemId" => $itemId]);
                }
            }
        }
        return json_encode(["status" => false]);
    }

    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $email = null;
        $name  = "";
        if (isset($_GET["email"])) {
            $email                 = $_GET["email"];
            $dbeStreamOneCustomers = new DBEStreamOneCustomers($this);
            $dbeStreamOneCustomers->setValue(DBEStreamOneCustomers::email, $email);
            $dbeStreamOneCustomers->getRowByColumn(DBEStreamOneCustomers::email);
            $name    = $dbeStreamOneCustomers->getValue(DBEStreamOneCustomers::name);
            $company = $dbeStreamOneCustomers->getValue(DBEStreamOneCustomers::companyName);

        }
        $action = $this->getAction();
        $this->setMenuId(313);
        switch ($action) {
            case 'searchOrders':
                $this->setPageTitle('StreamOne Orders For ' . $company);
                break;
            case 'newOrder':
                $this->setPageTitle('StreamOne Place New Order');
                break;
            case "editOrder":
                $this->setPageTitle('StreamOne Edit Order');
                break;
            case 'searchCustomers':
                $this->setPageTitle('StreamOne Customers');
                break;
            case 'addNewCustomer';
                $this->setPageTitle('StreamOne Add New Customer');
                break;
            case 'editCustomer':
                $this->setPageTitle('StreamOne Edit Customer Details');
                break;
        }
        $this->setTemplateFiles(
            'CustomerLicenses',
            'CustomerLicenses.inc'
        );
        $this->loadReactScript('CustomerLicensesComponent.js');
        $this->loadReactCSS('CustomerLicensesComponent.css');
        $this->template->parse(
            'CONTENTS',
            'CustomerLicenses',
            true
        );
        $this->parsePage();
    }


}
