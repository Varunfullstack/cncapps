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
    /** @var BUPassword */
    public $buPassword;

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
            case 'archive':
                $this->archive();
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
                $customerID = $dsSearchForm->getValue(DBEPassword::customerID);
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

        $urlSubmit = $this->buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );


        $this->setPageTitle('Passwords');

        if ($dsSearchForm->getValue(DBEPassword::customerID)) {
            $buCustomer = new BUCustomer ($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(DBEPassword::customerID),
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
                'customerID'        => $dsSearchForm->getValue(DBEPassword::customerID),
                'customerIDMessage' => $dsSearchForm->getMessage(DBEPassword::customerID),
                'customerString'    => $customerString,
                'urlCustomerPopup'  => $urlCustomerPopup,
                'urlSubmit'         => $urlSubmit
            )
        );

        $this->template->parse(
            'CONTENTS',
            'PasswordSearch',
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

        $this->setPageTitle('Passwords');

        $this->setTemplateFiles(
            array('PasswordList' => 'PasswordList.inc')
        );

        $dbeCustomer->getRow($customerID);


        $this->buPassword->getRowsByCustomerIDAndPasswordLevel(
            $customerID,
            $this->dbeUser->getValue(DBEUser::passwordLevel),
            $dsPassword
        );

        if ($dsPassword) {


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
                'PasswordList',
                'passwordBlock',
                'passwords'
            );
            while ($dsPassword->fetchNext()) {

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'edit',
                            'passwordID' => $dsPassword->getValue(DBEPassword::passwordID)
                        )
                    );
                $urlArchive =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'archive',
                            'passwordID' => $dsPassword->getValue(DBEPassword::passwordID)
                        )
                    );

                $decryptedNotes = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes));

                if (strpos(
                        $decryptedNotes,
                        'http'
                    ) !== false) {
                    $notes = '<A href="' .$decryptedNotes. '" target="_blank">' . $decryptedNotes . '</a>';
                } else {
                    $notes = $decryptedNotes;
                }


                $decryptedURL = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL));
                $URL = strlen(
                    $decryptedURL
                ) ? '<a href="' . $decryptedURL . '" target="_blank">' . $decryptedURL . '</a>' : '';


                $this->template->set_var(
                    array(
                        'passwordID'          => $dsPassword->getValue(DBEPassword::passwordID),
                        'customerID'          => $dsPassword->getValue(DBEPassword::customerID),
                        DBEPassword::username => $this->buPassword->decrypt(
                            $dsPassword->getValue(DBEPassword::username)
                        ),
                        'password'            => $this->buPassword->decrypt(
                            $dsPassword->getValue(DBEPassword::password)
                        ),
                        DBEPassword::notes    => $notes,
                        'urlEdit'             => $urlEdit,
                        'urlArchive'          => $urlArchive,
                        'level'               => $dsPassword->getValue(DBEPassword::level),
                        'URL'                 => $URL

                    )
                );
                $this->template->parse(
                    'passwords',
                    'passwordBlock',
                    true
                );
            }//while $dsPassword->fetchNext()
            $this->template->parse(
                'CONTENTS',
                'PasswordList',
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

        $dsPassword = new DSForm($this);
        $dbePassword = new dbePassword($this);
        $dsPassword->copyColumnsFrom($dbePassword);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            $formError = (!$dsPassword->populateFromArray($_REQUEST['password']));
            if (!$formError) {
                $passwordID = $_REQUEST['password'][1]['passwordID'];
                if ($passwordID) {

                    $dbePassword->getRow($passwordID);

                    $dsPassword->setValue(
                        DBEPassword::encrypted,
                        $dbePassword->getValue(DBEPassword::encrypted)
                    );

                    $previousPassword = $dbePassword->getValue(DBEPassword::password);

                    $previousPasswordDecrypted = $this->buPassword->decrypt($previousPassword);

                    $newPassword = $dsPassword->getValue(DBEPassword::password);

                    if ($previousPasswordDecrypted != $newPassword) {
                        $this->buPassword->archive(
                            $passwordID,
                            $this->dbeUser
                        );

                        $dsPassword->setValue(
                            DBEPassword::passwordID,
                            0
                        );
                    }
                }


                $dsPassword->setValue(
                    DBEPassword::username,
                    $this->buPassword->encrypt($dsPassword->getValue(DBEPassword::username))
                );
                $dsPassword->setValue(
                    DBEPassword::password,
                    $this->buPassword->encrypt($dsPassword->getValue(DBEPassword::password))
                );
                $dsPassword->setValue(
                    DBEPassword::serviceID,
                    $dsPassword->getValue(DBEPassword::serviceID)
                );
                $dsPassword->setValue(
                    DBEPassword::notes,
                    $this->buPassword->encrypt($dsPassword->getValue(DBEPassword::notes))
                );
                $dsPassword->setValue(
                    DBEPassword::URL,
                    $this->buPassword->encrypt($dsPassword->getValue(DBEPassword::URL))
                );


                $this->buPassword->updatePassword($dsPassword);

                $urlNext =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'     => 'list',
                            'customerID' => $dsPassword->getValue(DBEPassword::customerID)
                        )
                    );

                header('Location: ' . $urlNext);
                exit;
            }
        } else {
            if ($_REQUEST['passwordID']) {                      // editing
                $this->buPassword->getPasswordByID(
                    $_REQUEST['passwordID'],
                    $dsPassword
                );
            } else {                                               // create new record
                $dsPassword->setValue(
                    DBEPassword::passwordID,
                    0
                );
                $dsPassword->setValue(
                    DBEPassword::customerID,
                    $_REQUEST['customerID']
                );
            }
        }

        $urlEdit =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'edit',
                    'ordheadID'  => $passwordID,
                    'customerID' => $customerID
                )
            );
        $this->setPageTitle('Edit Password');

        $this->setTemplateFiles(array('PasswordEdit' => 'PasswordEdit.inc'));

        $this->template->set_block(
            'PasswordEdit',
            'levelBlock',
            'levels'
        );

        $maxLevel = $this->dbeUser->getValue(DBEUser::passwordLevel);

        if ($maxLevel) {
            echo 'You cannot edit this password';
            exit;
        }
        for ($level = 1; $level <= $maxLevel; $level++) {

            $this->template->set_var(
                array(
                    'level'         => $level,
                    'levelSelected' => $dsPassword->getValue(DBEPassword::level) == $level ? 'selected' : ''
                )
            );
            $this->template->parse(
                'levels',
                'levelBlock',
                true
            );
        }

        $this->template->set_block(
            'PasswordEdit',
            'passwordServiceBlock',
            'passwordServices'
        );

        $dbePasswordServices = new DBEPasswordService($this);

        $dbePasswordServices->getRows();
        for ($level = 1; $level <= $maxLevel; $level++) {

            $this->template->set_var(
                array(
                    'level'         => $level,
                    'levelSelected' => $dsPassword->getValue(DBEPassword::level) == $level ? 'selected' : ''
                )
            );
            $this->template->parse(
                'levels',
                'levelBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'ActivityTypeList',
            true
        );


        $this->template->set_var(
            array(
                'customerID'          => $dsPassword->getValue(DBEPassword::customerID),
                'passwordID'          => $dsPassword->getValue(DBEPassword::passwordID),
                DBEPassword::username => $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::username)),
                'usernameMessage'     => $dsPassword->getMessage(DBEPassword::username),
                'password'            => $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::password)),
                'passwordMessage'     => $dsPassword->getMessage(DBEPassword::password),
                DBEPassword::notes    => $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes)),
                'notesMessage'        => $dsPassword->getMessage(DBEPassword::notes),
                'urlEdit'             => $urlEdit,
                'URL'                 => $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL))
            )
        );

        $this->template->parse(
            'CONTENTS',
            'PasswordEdit',
            true
        );
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

                if (!$handle = fopen(
                    $_FILES['csvFile']['tmp_name'],
                    'r'
                )) {

                    $this->setFormErrorMessage('Problem reading CSV data');
                    // todo rediect to listy page with error message

                } else {
                    while ($row = fgetcsv($handle)) {
                        $dsPassword->setUpdateModeInsert();
                        $dsPassword->setValue(
                            'customerID',
                            $customerID
                        );
                        $dsPassword->setValue(
                            DBEPassword::username,
                            $row[0]
                        );
                        $dsPassword->setValue(
                            DBEPassword::serviceID,
                            $row[1]
                        );
                        $dsPassword->setValue(
                            'password',
                            $row[2]
                        );
                        $dsPassword->setValue(
                            DBEPassword::notes,
                            $row[3]
                        );
                        $dsPassword->post();
                    }
                    $this->buPassword->updatePassword($dsPassword);

                    $urlNext =
                        $this->buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'     => 'list',
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

    function archive()
    {
        $this->setMethodName('archive');

        if (!$this->buPassword->getPasswordByID(
            $_REQUEST['passwordID'],
            $dsPassword
        )) {
            $this->raiseError('PasswordID ' . $_REQUEST['passwordID'] . ' not found');
            exit;
        }

        $this->buPassword->archive(
            $_REQUEST['passwordID'],
            $this->dbeUser
        );
        $urlNext =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'list',
                    'customerID' => $dsPassword->getValue(DBEPassword::customerID)
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

        $this->template->parse(
            'CONTENTS',
            'PasswordGenerate',
            true
        );
        $this->parsePage();

    }
}// end of class
?>