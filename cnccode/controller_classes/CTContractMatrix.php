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
require_once($cfg['path_bu'] . '/BURenewal.inc.php');

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
        $roles = RENEWALS_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(606);
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

                    $urlEncodedCustomerName = urlencode($dsCustomers->getValue(DBECustomer::name));
                    $customerId = $dsCustomers->getValue(DBECustomer::customerID);
                    $customerRenewalReportURL = "RenewalReport.php?action=search&searchForm[1][customerID]=$customerId&customerString=$urlEncodedCustomerName&Search=Generate";
                    $row = [
                        "Customer Name" => "<a href='$customerRenewalReportURL' target='_blank'>{$dsCustomers->getValue(DBECustomer::name)}</a>"
                    ];

                    foreach ($itemTypes as $itemType) {
                        $row[$itemType] = null;
                    }

                    $items = $buRenewal->getRenewalsAndExternalItemsByCustomer(
                        $dsCustomers->getValue(DBECustomer::customerID),
                        $this,
                        true
                    );
                    foreach ($items as $item) {
                        if (in_array($item['itemTypeDescription'], $itemTypes)) {
                            $row[$item['itemTypeDescription']] = "<a href='{$item['linkURL']}' target='_blank' >Yes</a>";
                        }
                    }
                    $data[] = $row;
                }


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
        $this->loadReactScript('SpinnerHolderComponent.js');
        $this->loadReactCSS('SpinnerHolderComponent.css');
        $this->template->parse(
            'CONTENTS',
            'ContractMatrix',
            true
        );
        $this->parsePage();
    }
}// end of class
