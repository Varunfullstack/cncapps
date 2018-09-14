<?php
/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUThirdPartyContact.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEThirdPartyContact.inc.php');

class CTThirdPartyContact extends CTCNC
{
    var $buThirdPartyContact = '';

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
            "technical",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buThirdPartyContact = new BUThirdPartyContact($this);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case 'edit':
                $this->edit();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'list':
                $this->displayList();
                break;
            case 'search':
            default:
                $this->search();
                break;
        }
    }

    function search()
    {

        $this->setMethodName('search');

        $this->buThirdPartyContact->initialiseSearchForm($dsSearchForm);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $customerID = $dsSearchForm->getValue('customerID');
                $report = $this->displayList($customerID);
                exit;
            }

        }

        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'ThirdPartyContactSearch' => 'ThirdPartyContactSearch.inc'
            )
        );

        $urlSubmit = $this->buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );


        $this->setPageTitle('Third Party Contacts');

        if ($dsSearchForm->getValue('customerID')) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue('customerID'),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $urlCustomerPopup =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $this->template->set_var(
            array(
                'formError'         => $this->formError,
                'customerID'        => $dsSearchForm->getValue('customerID'),
                'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
                'customerString'    => $customerString,
                'urlCustomerPopup'  => $urlCustomerPopup,
                'urlSubmit'         => $urlSubmit
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ThirdPartyContactSearch',
            true
        );

        $this->parsePage();

    } // end search

    /**
     * Display list of types
     * @access private
     */
    function displayList($customerID = false)
    {
        $dbeCustomer = new DBECustomer($this);

        if ($_REQUEST['customerID']) {
            $customerID = $_REQUEST['customerID'];
        }
        if (empty($customerID)) {
            $this->raiseError('Please search for a customer by typing and then pressing tab');
            exit;
        }

        $this->setMethodName('displayList');

        $this->setPageTitle('Third Party Contacts');

        $this->setTemplateFiles(
            array('ThirdPartyContactList' => 'ThirdPartyContactList.inc')
        );

        $dbeCustomer->getRow($customerID);

        $this->buThirdPartyContact->getRowsByCustomerID(
            $customerID,
            $dsThirdPartyContact
        );

        if ($dsThirdPartyContact) {
            $urlAdd =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'edit',
                        'customerID' => $customerID
                    )
                );
            $urlLoadFromCsv =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'loadFromCsv',
                        'customerID' => $customerID
                    )
                );

            $urlSubmit = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'displayList'
                )
            );

            $this->template->set_var(
                array(
                    'urlSubmit'      => $urlSubmit,
                    'urlAdd'         => $urlAdd,
                    'urlLoadFromCsv' => $urlLoadFromCsv,
                    'customerName'   => $dbeCustomer->getValue(DBECustomer::name),
                    'customerID'     => $customerID,
                    'formError'      => $this->getFormErrorMessage()
                )
            );

            $urlCustomerPopup = $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );


            $this->template->set_block(
                'ThirdPartyContactList',
                'thirdPartyContactBlock',
                'thirdPartyContacts'
            );
            while ($dsThirdPartyContact->fetchNext()) {

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'              => 'edit',
                            'thirdPartyContactID' => $dsThirdPartyContact->getValue('thirdPartyContactID')
                        )
                    );
                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'              => 'delete',
                            'thirdPartyContactID' => $dsThirdPartyContact->getValue('thirdPartyContactID')
                        )
                    );

                if (strpos(
                        $dsThirdPartyContact->getValue(DBEThirdPartyContact::notes),
                        'http'
                    ) !== false) {
                    $notes = '<A href="' . $dsThirdPartyContact->getValue(
                            DBEThirdPartyContact::notes
                        ) . '" target="_blank">' . $dsThirdPartyContact->getValue(DBEThirdPartyContact::notes) . '</a>';
                } else {
                    $notes = $dsThirdPartyContact->getValue(DBEThirdPartyContact::notes);
                }

                $this->template->set_var(
                    array(
                        'thirdPartyContactID' => $dsThirdPartyContact->getValue(
                            DBEThirdPartyContact::thirdPartyContactID
                        ),
                        'customerID'          => $dsThirdPartyContact->getValue(DBEThirdPartyContact::customerID),
                        'software'            => $dsThirdPartyContact->getValue(DBEThirdPartyContact::software),
                        'vendor'              => $dsThirdPartyContact->getValue(DBEThirdPartyContact::vendor),
                        'phoneLink'           => getPhoneLink(
                            $dsThirdPartyContact->getValue(DBEThirdPartyContact::phone)
                        ),
                        'emailLink'           => getMailToLink(
                            $dsThirdPartyContact->getValue(DBEThirdPartyContact::email)
                        ),
                        'notes'               => $notes,
                        'urlEdit'             => $urlEdit,
                        'urlDelete'           => $urlDelete

                    )
                );
                $this->template->parse(
                    'thirdPartyContacts',
                    'thirdPartyContactBlock',
                    true
                );

            }//while $dsThirdPartyContact->fetchNext()
            $this->template->parse(
                'CONTENTS',
                'ThirdPartyContactList',
                true
            );
        }

        $this->parsePage();
    }

    /**
     * Called from sales order line to edit a renewal.
     * The page passes
     * ordheadID
     * sequenceNo (line)
     * renewalCustomerItemID (blank if renewal not created yet
     *
     *
     */
    function edit()
    {
        $this->setMethodName('edit');

        $dsThirdPartyContact = new DSForm($this);
        $dbeThirdPartyContact = new dbeThirdPartyContact($this);
        $dsThirdPartyContact->copyColumnsFrom($dbeThirdPartyContact);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formError = (!$dsThirdPartyContact->populateFromArray($_REQUEST['thirdPartyContact']));
            if (!$formError) {

                $this->buThirdPartyContact->updateThirdPartyContact($dsThirdPartyContact);

                $urlNext =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'list',
                            'customerID' => $dsThirdPartyContact->getValue('customerID')
                        )
                    );

                header('Location: ' . $urlNext);
                exit;
            }
        } else {
            if ($_REQUEST['thirdPartyContactID']) {                      // editing
                $this->buThirdPartyContact->getThirdPartyContactByID(
                    $_REQUEST['thirdPartyContactID'],
                    $dsThirdPartyContact
                );
            } else {                                               // create new record
                $dsThirdPartyContact->setValue(
                    'thirdPartyContactID',
                    0
                );
                $dsThirdPartyContact->setValue(
                    'customerID',
                    $_REQUEST['customerID']
                );
            }
        }

        $urlEdit =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'edit',
                    'ordheadID'  => $thirdPartyContactID,
                    'customerID' => $customerID
                )
            );
        $this->setPageTitle('Edit ThirdPartyContact');

        $this->setTemplateFiles(array('ThirdPartyContactEdit' => 'ThirdPartyContactEdit.inc'));

        $this->template->set_var(
            array(
                'customerID'          => $dsThirdPartyContact->getValue(DBEThirdPartyContact::customerID),
                'thirdPartyContactID' => $dsThirdPartyContact->getValue(DBEThirdPartyContact::thirdPartyContactID),
                'software'            => $dsThirdPartyContact->getValue(DBEThirdPartyContact::software),
                'vendor'              => $dsThirdPartyContact->getValue(DBEThirdPartyContact::vendor),
                'phone'               => $dsThirdPartyContact->getValue(DBEThirdPartyContact::phone),
                'email'               => $dsThirdPartyContact->getValue(DBEThirdPartyContact::email),
                'notes'               => $dsThirdPartyContact->getValue(DBEThirdPartyContact::notes),
                'urlEdit'             => $urlEdit
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ThirdPartyContactEdit',
            true
        );
        $this->parsePage();

    }


    function delete()
    {
        $this->setMethodName('delete');

        if (!$this->buThirdPartyContact->getThirdPartyContactByID(
            $_REQUEST['thirdPartyContactID'],
            $dsThirdPartyContact
        )) {
            $this->raiseError('ThirdPartyContactID ' . $_REQUEST['thirdPartyContactID'] . ' not found');
            exit;
        }

        $this->buThirdPartyContact->delete($_REQUEST['thirdPartyContactID']);
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'list',
                    'customerID' => $dsThirdPartyContact->getValue('customerID')
                )
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * generate a thirdPartyContact
     *
     */
    function generate()
    {
        $this->setMethodName('generate');

        $this->setPageTitle('New ThirdPartyContact');

        $thirdPartyContact = $this->buThirdPartyContact->generateThirdPartyContact();

        $this->setTemplateFiles(array('ThirdPartyContactGenerate' => 'ThirdPartyContactGenerate.inc'));

        $this->template->set_var(
            array(
                'thirdPartyContact' => $thirdPartyContact
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ThirdPartyContactGenerate',
            true
        );
        $this->parsePage();

    }

}// end of class
?>