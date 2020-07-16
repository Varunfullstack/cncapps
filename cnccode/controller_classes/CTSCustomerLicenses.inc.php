<?php

/**
 * My Account controller class
 * CNC Ltd
 *
 * @access public
 * @authors Mustafa Taha
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg ['path_dbe'] . '/DSForm.inc.php');
require_once($cfg ['path_dbe'] . '/DBECustomer.inc.php');

require_once($cfg['path_bu'] . '/BUTechDataApi.inc.php');

class CTSCustomerLicenses extends CTCNC
{
    /**
     * @var BUTechDataApi
     */
    private $buTechDataApi;
    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
        parent::__construct(
            $requestMethod,
            $postVars,
            $getVars,
            $cookieVars,
            $cfg
        );        
        
        $this->buTechDataApi= new BUTechDataApi($this);
     }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $page=1;
        if(isset($_GET['page']))
            $page=$_GET['page'];
           
        switch ($this->getAction()) {
            case "getProductList":
                echo $this->buTechDataApi->getProductList($page);
                exit;
            case "getAllSubscriptions":
                echo $this->buTechDataApi->getAllSubscriptions($page);
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
            break;          
            default:
                $this->setTemplate();
        }
    }


    function setTemplate()
    {
        $this->setMethodName('setTemplate');

        $action = $this->getAction();

        switch ($action) {
            case 'searchOrders':
                $this->setMenuId(312);
                $this->setPageTitle('TechData Orders');
                break;
            case 'newOrder':
                $this->setMenuId(312);
                $this->setPageTitle('TechData Place New Order');
                break;
            case "editOrder":
                $this->setMenuId(312);
                $this->setPageTitle('TechData Edit Order');
                break;
            case 'searchCustomers':
                $this->setMenuId(312);
                $this->setPageTitle('TechData Customers');
                break;
            case 'addNewCustomer';
                $this->setMenuId(312);
                $this->setPageTitle('TechData Add New Customer');
                break;
            case 'editCustomer':
                $this->setMenuId(312);
                $this->setPageTitle('TechData Edit Customer detials');
                break;
                
        }

        $this->setTemplateFiles(
            'CustomerLicenses',
            'CustomerLicenses.inc'
        );

        $this->template->parse(
            'CONTENTS',
            'CustomerLicenses',
            true
        );
        $this->parsePage();
    }

    function getCustomerById()
    {
        $this->setMethodName('getCustomerById');
        $endCustomerId = $_GET['endCustomerId'];
        $endCustomer = null;
        if (isset($endCustomerId)) {
            //get techdata end customer             
            $endCustomer = $this->buTechDataApi->getCustomerById($endCustomerId);
            $obj = json_decode($endCustomer);
            //get cnc customer by endcustomerId
            if ($obj->Result == 'Success') {
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->setValue(DBECustomer::techDataCustomerId,  $endCustomerId);
                $dbeCustomer->getRowsByColumn(DBECustomer::techDataCustomerId);
                $dbeCustomer->fetchNext();
                $obj->BodyText->endCustomerDetails->cncCustomerId = $dbeCustomer->getValue(DBECustomer::customerID);
                $obj->BodyText->endCustomerDetails->cncCustomerName = $dbeCustomer->getValue(DBECustomer::name);
               
            }
            return json_encode($obj);
        }
    }
    function updateTechDataCustomer()
    {
        $body = file_get_contents('php://input');                     
        if(isset($_GET['endCustomerId']) && isset($body))
        {
            $result = $this->buTechDataApi->updateCustomer($_GET['endCustomerId'],$body);
            $obj=json_decode($result);
            $bodyObj=json_decode($body);
            if($obj->Result=='Success'&&$bodyObj->cncCustomerId!=null)
            {
                //remove  old customer
                $dbeCustomer = new DBECustomer($this);
                $dbeCustomer->setValue(DBECustomer::techDataCustomerId,  $_GET['endCustomerId']);
                $dbeCustomer->getRowsByColumn(DBECustomer::techDataCustomerId);
                $dbeCustomer->fetchNext();
                
                if( $dbeCustomer->getPKValue()!=$bodyObj->cncCustomerId)
                {
                    $dbeCustomer->setPKValue($dbeCustomer->getValue(DBECustomer::customerID));
                    $dbeCustomer->getRow();
                    $dbeCustomer->setValue(DBECustomer::techDataCustomerId,null);
                    $dbeCustomer->updateRow();
  
                    // set new customer
                    $dbeCustomer->setPKValue($bodyObj->cncCustomerId);
                    $dbeCustomer->getRow();
                    $dbeCustomer->setValue(DBECustomer::techDataCustomerId,$_GET['endCustomerId']);
                    $dbeCustomer->updateRow();
                }
                 
            }
            return $result;
        }
        else return json_encode(['Result'=>'Failed']);

    }
     
   
}
