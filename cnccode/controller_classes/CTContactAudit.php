<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 25/07/2018
 * Time: 12:33
 */

require_once($cfg['path_bu'] . '/BUContact.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once $cfg['path_dbe'] . '/DBEJContactAudit.php';

class CTContactAudit extends CTCNC
{
    /**
     * Dataset for contact record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsContact = '';

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
            "technical"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buContact = new BUContact($this);
        $this->dsContact = new DSForm($this);    // new specialised dataset with form message support
        $this->dsContact->copyColumnsFrom($this->buContact->dbeContact);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'doSearch':
                echo json_encode(
                    $this->searchContactAudit(
                        $_REQUEST['customerId'],
                        $_REQUEST['startDate'],
                        $_REQUEST['endDate']
                    )
                );
                break;
            default:
                $this->displaySearchForm();
        }
    }

    /**
     * Display the initial form that prompts the employee for details
     * @access private
     */
    function displaySearchForm()
    {
        $this->setMethodName('displaySearchForm');
        $this->setTemplateFiles(
            'CustomerSearch',
            'ContactAuditSearch'
        );
// Parameters
        $this->setPageTitle("Customer");
        $submitURL = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array('action' => CTCUSTOMER_ACT_SEARCH)
        );
        $customerPopupURL =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->template->set_var(
            array(
                'contactString'           => "",
                'phoneString'             => "",
                'customerString'          => "",
                'address'                 => "",
                'customerStringMessage'   => "",
                'newCustomerFromDate'     => "",
                'newCustomerToDate'       => "",
                'droppedCustomerFromDate' => "",
                'droppedCustomerToDate'   => "",
                'submitURL'               => $submitURL,
                'customerPopupURL'        => $customerPopupURL,
            )
        );
        $this->template->parse(
            'CONTENTS',
            'CustomerSearch',
            true
        );
        $this->parsePage();
    }


    private function searchContactAudit($customerID,
                                        $startDate,
                                        $endDate
    )
    {
        $test = new DBEJContactAudit($this);

        if ($startDate) {
            $startDate = DateTime::createFromFormat(
                'd/m/Y',
                $startDate
            );
        }

        if ($endDate) {
            $endDate = DateTime::createFromFormat(
                'd/m/Y',
                $endDate
            );
        }

        $test->search(
            $customerID,
            $startDate,
            $endDate
        );

        $result = [];

        while ($test->fetchNext()) {
            $row = [];
            foreach (DBEJContactAudit::getConstants() as $constant) {
                $row[$constant] = $test->getValue($constant);
            }
            $result[] = $row;
        }

        return $result;
    }
}// end of class
