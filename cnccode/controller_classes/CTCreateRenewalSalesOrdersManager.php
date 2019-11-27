<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/11/2018
 * Time: 9:19
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBEItemBillingCategory.php');


class CTCreateRenewalSalesOrdersManager extends CTCNC
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
            "sales",
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
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function displayList()
    {
        $this->setPageTitle('Create Renewal Sales Order');
        $this->setTemplateFiles(
            array('CreateRenewalSalesOrderManager' => 'CreateRenewalSalesOrdersManager')
        );

        $this->template->set_block('CreateRenewalSalesOrderManager', 'actionsBlock', 'actions');
        $dbeItemBillingCategory = new DBEItemBillingCategory($this);
        $dbeItemBillingCategory->getRows(DBEItemBillingCategory::name);
        while ($dbeItemBillingCategory->fetchNext()) {
            $this->template->setVar(
                [
                    "actionURL"   => "CreateRenewalsSalesOrders.php?itemBillingCategory=" . $dbeItemBillingCategory->getValue(
                            DBEItemBillingCategory::id
                        ),
                    "description" => $dbeItemBillingCategory->getValue(DBEItemBillingCategory::name)
                ]
            );
            $this->template->parse('actions', 'actionsBlock', true);
        }


        $this->template->parse(
            'CONTENTS',
            'CreateRenewalSalesOrderManager',
            true
        );
        $this->parsePage();
    }
}