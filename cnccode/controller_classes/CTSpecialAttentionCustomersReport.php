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
require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');

class CTSpecialAttentionCustomersReport extends CTCNC
{
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            TECHNICAL_PERMISSION
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
        $this->displaySpecialAttentionCustomers();
    }

    /**
     * Displays list of customers with Special Attention flag set
     *
     * @throws Exception
     */
    function displaySpecialAttentionCustomers()
    {
        $this->setMethodName('displaySpecialAttentionCustomers');
        $this->setMenuId(112);
        $this->setPageTitle("Special Attention Customers");
        global $cfg;
        $customerTemplate = new Template (
            $cfg["path_templates"],
            "remove"
        );

        $contactTemplate = new Template(
            $cfg["path_templates"],
            "remove"
        );


        $this->setTemplateFiles(
            'SpecialAttention',
            'SpecialAttention'
        );

        $buContact = new BUContact($this);
        $dsContact = new DataSet($this);
        if ($buContact->getSpecialAttentionContacts($dsContact)) {
            $contactTemplate->setFile(
                'ContactSpecialAttention',
                'ContactSpecialAttention.html'
            );

            $contactTemplate->set_block(
                'ContactSpecialAttention',
                'contactBlock',
                'contacts'
            );
            $dbeCustomer = new DBECustomer($this);
            while ($dsContact->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsContact->getValue(DBEContact::customerID)
                        )
                    );

                if ($dbeCustomer->getValue(DBECustomer::customerID) != $dsContact->getValue(DBEContact::customerID)) {
                    $dbeCustomer->getRow($dsContact->getValue(DBEContact::customerID));
                }

                $contactTemplate->set_var(
                    array(
                        'contactName'  => ($dsContact->getValue(DBEContact::firstName) . " " . $dsContact->getValue(
                                DBEContact::lastName
                            )),
                        'linkURL'      => $linkURL,
                        'customerName' => $dbeCustomer->getValue(DBECustomer::name)
                    )
                );

                $contactTemplate->parse(
                    'contacts',
                    'contactBlock',
                    true
                );

            }

            $contactTemplate->parse(
                'OUTPUT',
                'ContactSpecialAttention',
                true
            );


        } else {
            $contactTemplate->setFile(
                'SimpleMessage',
                'SimpleMessage.inc.html'
            );

            $contactTemplate->set_var(array('message' => 'There are no special attention contacts'));

            $contactTemplate->parse(
                'OUTPUT',
                'SimpleMessage',
                true
            );
        }
        $dsCustomer = new DataSet($this);
        $buCustomer = new BUCustomer($this);
        if ($buCustomer->getSpecialAttentionCustomers($dsCustomer)) {


            $customerTemplate->setFile(
                'CustomerSpecialAttention',
                'CustomerSpecialAttention.inc.html'
            );

            $customerTemplate->set_block(
                'CustomerSpecialAttention',
                'customerBlock',
                'customers'
            );

            while ($dsCustomer->fetchNext()) {

                $linkURL =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'dispEdit',
                            'customerID' => $dsCustomer->getValue(DBECustomer::customerID)
                        )
                    );


                $customerTemplate->set_var(
                    array(
                        'customerName'            => $dsCustomer->getValue(DBECustomer::name),
                        'specialAttentionEndDate' => $dsCustomer->getValue(DBECustomer::specialAttentionEndDate),
                        'linkURL'                 => $linkURL
                    )
                );

                $customerTemplate->parse(
                    'customers',
                    'customerBlock',
                    true
                );

            }

            $customerTemplate->parse(
                'OUTPUT',
                'CustomerSpecialAttention',
                true
            );

        } else {

            $customerTemplate->setFile(
                'SimpleMessage',
                'SimpleMessage.inc.html'
            );

            $customerTemplate->set_var(array('message' => 'There are no special attention customers'));

            $customerTemplate->parse(
                'OUTPUT',
                'SimpleMessage',
                true
            );
        }

        $this->template->setVar(
            [
                "customerSpecialAttention" => $customerTemplate->getVar('OUTPUT'),
                "contactSpecialAttention"  => $contactTemplate->getVar('OUTPUT')
            ]
        );

        $this->template->parse(
            'CONTENTS',
            'SpecialAttention'
        );

        $this->parsePage();

        exit;
    }

}
