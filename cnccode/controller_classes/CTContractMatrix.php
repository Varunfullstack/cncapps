<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:33
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');

class CTContractMatrix extends CTCNC
{


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
        $roles = [
            "reports",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'getData':

                // we have to get a list of all active customers
                $data = [];
                $buCustomer = new BUCustomer($this);
                $dsCustomers = new DataSet($this);
                $buCustomer->getActiveCustomers($dsCustomers);
                $buRenewal = new BURenewal($this);
                $dbeItemType = new DBEItemType($this);
                $dbeItemType->getCustomerReviewRows(true);
                $itemTypes = [];
                while ($dbeItemType->fetchNext()) {
                    $itemTypes[] = $dbeItemType->getValue(DBEItemType::description);
                }

                while ($dsCustomers->fetchNext()) {
                    $data[] = ["Customer Name" => $dsCustomers->getValue(DBECustomer::name)];
                    $items = $buRenewal->getRenewalsAndExternalItemsByCustomer(
                        $dsCustomers->getValue(DBECustomer::customerID),
                        $this,
                        true
                    );
                    foreach ($items as $item){
                        if($item['itemTypeDescription'])
                    }

                }

                $customerItem = new BUCustomerItem($this);

                echo json_encode(
                    $data
                );
                break;
            default:
                $this->showReport();
        }
    }


    /**
     * Display the initial form that prompts the employee for details
     * @access private
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function showReport()
    {
        $this->setTemplateFiles(
            'ContractMatrix',
            'ContractMatrix'
        );
// Parameters
        $this->setPageTitle("Contract Matrix");

        $this->template->parse(
            'CONTENTS',
            'ContractMatrix',
            true
        );
        $this->parsePage();
    }
}// end of class
