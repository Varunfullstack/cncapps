<?php
/**
 * Standard Text controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');

class CT24HoursSupportCustomersReport extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        if (!self::hasPermissions(TECHNICAL_PERMISSION)) {
            Header("Location: /NotAllowed.php");
            exit;
        }

        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->display24HourSupportCustomers();
    }

    /**
     * Displays list of customers with 24 Hour Support
     *
     * @throws Exception
     */
    function display24HourSupportCustomers()
    {
        $this->setMethodName('display24HourSupportCustomers');
        $this->setMenuId(111);
        $this->setPageTitle("24 Hour Support Customers");
        $dsCustomer = new DataSet($this);
        $buCustomer = new BUCustomer($this);
        if ($buCustomer->get24HourSupportCustomers($dsCustomer)) {

            $this->setTemplateFiles(
                'Customer24HourSupport',
                'Customer24HourSupport.inc'
            );

            $this->template->set_block(
                'Customer24HourSupport',
                'customerBlock',
                'customers'
            );

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        "Customer.php",
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );


                $this->template->set_var(
                    array(
                        'customerName' => $dsCustomer->getValue(DBECustomer::name),
                        'linkURL'      => $linkURL
                    )
                );

                $this->template->parse(
                    'customers',
                    'customerBlock',
                    true
                );

            }

            $this->template->parse(
                'CONTENTS',
                'Customer24HourSupport',
                true
            );

        } else {

            $this->setTemplateFiles(
                'SimpleMessage',
                'SimpleMessage.inc'
            );
            $this->template->set_var(
                'message',
                'There are no 24 Hour Support customers'
            );
        }

        $this->parsePage();

        exit;
    }

}