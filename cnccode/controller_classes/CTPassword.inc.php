<?php
/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEPassword.inc.php');

class CTPassword extends CTCNC
{
    var $buPassword = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buPassword = new BUPassword($this);
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
            case 'generate':
                $this->generate();
                break;
            case 'loadFromCsv':
                $this->loadFromCsv();
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

        $this->buPassword->initialiseSearchForm($dsSearchForm);
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
                'PasswordSearch' => 'PasswordSearch.inc'
            )
        );

        $urlSubmit = $this->buildLink($_SERVER ['PHP_SELF'], array('action' => CTCNC_ACT_SEARCH));


        $this->setPageTitle('Passwords');

        if ($dsSearchForm->getValue('customerID')) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID($dsSearchForm->getValue('customerID'), $dsCustomer);
            $customerString = $dsCustomer->getValue('name');
        }

        $urlCustomerPopup =
            $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP)
            );

        $this->template->set_var(
            array(
                'formError' => $this->formError,
                'customerID' => $dsSearchForm->getValue('customerID'),
                'customerIDMessage' => $dsSearchForm->getMessage('customerID'),
                'customerString' => $customerString,
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit' => $urlSubmit
            )
        );

        $this->template->parse('CONTENTS', 'PasswordSearch', true);

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

        $this->setPageTitle('Passwords');

        $this->setTemplateFiles(
            array('PasswordList' => 'PasswordList.inc')
        );

        $dbeCustomer->getRow($customerID);

        $this->buPassword->getRowsByCustomerID($customerID, $dsPassword);

        if ($dsPassword) {


            $urlAdd =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'edit',
                        'customerID' => $_REQUEST['customerID']
                    )
                );
            $urlLoadFromCsv =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'loadFromCsv',
                        'customerID' => $_REQUEST['customerID']
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
                    'urlSubmit' => $urlSubmit,
                    'urlAdd' => $urlAdd,
                    'urlLoadFromCsv' => $urlLoadFromCsv,
                    'customerName' => $dbeCustomer->getValue('Name'),
                    'customerID' => $_REQUEST['customerID'],
                    'formError' => $this->getFormErrorMessage()
                )
            );

            $urlCustomerPopup = $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action' => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );


            $this->template->set_block('PasswordList', 'passwordBlock', 'passwords');
            while ($dsPassword->fetchNext()) {

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'passwordID' => $dsPassword->getValue('passwordID')
                        )
                    );
                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'delete',
                            'passwordID' => $dsPassword->getValue('passwordID')
                        )
                    );

                if (strpos($dsPassword->getValue('notes'), 'http') !== false) {
                    $notes = '<A href="' . $dsPassword->getValue('notes') . '" target="_blank">' . $dsPassword->getValue('notes') . '</a>';
                } else {
                    $notes = $dsPassword->getValue('notes');
                }

                $this->template->set_var(
                    array(
                        'passwordID' => $dsPassword->getValue('passwordID'),
                        'customerID' => $dsPassword->getValue('customerID'),
                        'username' => $dsPassword->getValue('username'),
                        'service' => $dsPassword->getValue('service'),
                        'password' => $dsPassword->getValue('password'),
                        'notes' => $notes,
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete

                    )
                );
                $this->template->parse('passwords', 'passwordBlock', true);
            }//while $dsPassword->fetchNext()
            $this->template->parse('CONTENTS', 'PasswordList', true);
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

        $dsPassword = new DSForm($this);
        $dbePassword = new dbePassword($this);
        $dsPassword->copyColumnsFrom($dbePassword);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formError = (!$dsPassword->populateFromArray($_REQUEST['password']));
            if (!$formError) {

                $this->buPassword->updatePassword($dsPassword);

                $urlNext =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'list',
                            'customerID' => $dsPassword->getValue('customerID')
                        )
                    );

                header('Location: ' . $urlNext);
                exit;
            }
        } else {
            if ($_REQUEST['passwordID']) {                      // editing
                $this->buPassword->getPasswordByID($_REQUEST['passwordID'], $dsPassword);
            } else {                                               // create new record
                $dsPassword->setValue('passwordID', 0);
                $dsPassword->setValue('customerID', $_REQUEST['customerID']);
            }
        }

        $urlEdit =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'edit',
                    'ordheadID' => $passwordID,
                    'customerID' => $customerID
                )
            );
        $this->setPageTitle('Edit Password');

        $this->setTemplateFiles(array('PasswordEdit' => 'PasswordEdit.inc'));

        $this->template->set_var(
            array(
                'customerID' => $dsPassword->getValue('customerID'),
                'passwordID' => $dsPassword->getValue('passwordID'),
                'username' => $dsPassword->getValue('username'),
                'usernameMessage' => $dsPassword->getMessage('username'),
                'service' => $dsPassword->getValue('service'),
                'serviceMessage' => $dsPassword->getMessage('service'),
                'password' => $dsPassword->getValue('password'),
                'passwordMessage' => $dsPassword->getMessage('password'),
                'notes' => $dsPassword->getValue('notes'),
                'notesMessage' => $dsPassword->getMessage('notes'),
                'urlEdit' => $urlEdit
            )
        );

        $this->template->parse('CONTENTS', 'PasswordEdit', true);
        $this->parsePage();

    }

    function loadFromCsv()
    {
        $this->setMethodName('loadFromCsv');

        $dsPassword = new DSForm($this);
        $dbePassword = new DBEPassword($this);
        $dsPassword->copyColumnsFrom($dbePassword);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $formError = false;

            if (!$customerID = $_REQUEST['customerID']) {
                $this->raiseError('No customerID');
                exit;
            }

            if (!file_exists($_FILES['csvFile']['tmp_name'])) {

                $this->setFormErrorMessage('Problem uploading file');
                // todo rediect to listy page with error message
            } else {

                if (!$handle = fopen($_FILES['csvFile']['tmp_name'], 'r')) {

                    $this->setFormErrorMessage('Problem reading CSV data');
                    // todo rediect to listy page with error message

                } else {
                    while ($row = fgetcsv($handle)) {
                        $dsPassword->setUpdateModeInsert();
                        $dsPassword->setValue('customerID', $customerID);
                        $dsPassword->setValue('username', $row[0]);
                        $dsPassword->setValue('service', $row[1]);
                        $dsPassword->setValue('password', $row[2]);
                        $dsPassword->setValue('notes', $row[3]);
                        $dsPassword->post();
                    }
                    $this->buPassword->updatePassword($dsPassword);

                    $urlNext =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => 'list',
                                'customerID' => $customerID
                            )
                        );

                    header('Location: ' . $urlNext);
                    exit;
                }
            }
        }
        $this->displayList();
    } // end LoadFromCSV

    function delete()
    {
        $this->setMethodName('delete');

        if (!$this->buPassword->getPasswordByID($_REQUEST['passwordID'], $dsPassword)) {
            $this->raiseError('PasswordID ' . $_REQUEST['passwordID'] . ' not found');
            exit;
        }

        $this->buPassword->delete($_REQUEST['passwordID']);
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'list',
                    'customerID' => $dsPassword->getValue('customerID')
                )
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * generate a password
     *
     */
    function generate()
    {
        $this->setMethodName('generate');

        $this->setPageTitle('New Password');

        $password = $this->buPassword->generatePassword();

        $this->setTemplateFiles(array('PasswordGenerate' => 'PasswordGenerate.inc'));

        $this->template->set_var(
            array(
                'password' => $password
            )
        );

        $this->template->parse('CONTENTS', 'PasswordGenerate', true);
        $this->parsePage();

    }

}// end of class
?>