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
    /** @var BUThirdPartyContact */
    public $buThirdPartyContact;

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
     * @throws Exception
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

    /**
     * @throws Exception
     */
    function search()
    {
        $this->setMethodName('search');
        $dsSearchForm = new DSForm($this);
        $this->buThirdPartyContact->initialiseSearchForm($dsSearchForm);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                $this->setFormErrorOn();
            } else {
                $customerID = $dsSearchForm->getValue(BUThirdPartyContact::searchFormCustomerID);
                $this->displayList($customerID);
                exit;
            }

        }

        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'ThirdPartyContactSearch' => 'ThirdPartyContactSearch.inc'
            )
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );


        $this->setPageTitle('Third Party Contacts');
        $customerString = null;
        if ($dsSearchForm->getValue(BUThirdPartyContact::searchFormCustomerID)) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(BUThirdPartyContact::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }

        $urlCustomerPopup =
            Controller::buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );

        $this->template->set_var(
            array(
                'formError'         => $this->formError,
                'customerID'        => $dsSearchForm->getValue(BUThirdPartyContact::searchFormCustomerID),
                'customerIDMessage' => $dsSearchForm->getMessage(BUThirdPartyContact::searchFormCustomerID),
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
     * @param bool $customerID
     * @throws Exception
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
        $dsThirdPartyContact = new DataSet($this);
        $this->buThirdPartyContact->getRowsByCustomerID(
            $customerID,
            $dsThirdPartyContact
        );

        if ($dsThirdPartyContact) {
            $urlAdd =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'edit',
                        'customerID' => $customerID
                    )
                );
            $urlLoadFromCsv =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'loadFromCsv',
                        'customerID' => $customerID
                    )
                );

            $urlSubmit = Controller::buildLink(
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

            $this->template->set_block(
                'ThirdPartyContactList',
                'thirdPartyContactBlock',
                'thirdPartyContacts'
            );
            while ($dsThirdPartyContact->fetchNext()) {

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'              => 'edit',
                            'thirdPartyContactID' => $dsThirdPartyContact->getValue(
                                DBEThirdPartyContact::thirdPartyContactID
                            )
                        )
                    );
                $urlDelete =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'              => 'delete',
                            'thirdPartyContactID' => $dsThirdPartyContact->getValue(
                                DBEThirdPartyContact::thirdPartyContactID
                            )
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
     * @throws Exception
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
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'list',
                            'customerID' => $dsThirdPartyContact->getValue(DBEThirdPartyContact::customerID)
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
                    DBEThirdPartyContact::thirdPartyContactID,
                    null
                );
                $dsThirdPartyContact->setValue(
                    DBEThirdPartyContact::customerID,
                    $_REQUEST['customerID']
                );
            }
        }

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
                'notes'               => $dsThirdPartyContact->getValue(DBEThirdPartyContact::notes)
            )
        );

        $this->template->parse(
            'CONTENTS',
            'ThirdPartyContactEdit',
            true
        );
        $this->parsePage();

    }


    /**
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        $dsThirdPartyContact = new DataSet($this);
        if (!$this->buThirdPartyContact->getThirdPartyContactByID(
            $_REQUEST['thirdPartyContactID'],
            $dsThirdPartyContact
        )) {
            $this->raiseError('ThirdPartyContactID ' . $_REQUEST['thirdPartyContactID'] . ' not found');
            exit;
        }

        $this->buThirdPartyContact->delete($_REQUEST['thirdPartyContactID']);
        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'list',
                    'customerID' => $dsThirdPartyContact->getValue(DBEThirdPartyContact::customerID)
                )
            );

        header('Location: ' . $urlNext);
        exit;
    }
}
