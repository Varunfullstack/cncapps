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
require_once($cfg['path_dbe'] . '/DBEJPassword.php');
require_once($cfg['path_dbe'] . '/DBEPasswordService.inc.php');

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
                if ($_REQUEST['customerID']) {
                    $customerID = $_REQUEST['customerID'];
                }
                $this->displayList($customerID);
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
        /** @var DSForm $dsSearchForm */
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
    function displayList($customerID)
    {
        $dbeCustomer = new DBECustomer($this);
        $showArchived = isset($_REQUEST['archived']);

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
        $dsPassword = new DBEJPassword($this);
        if ($showArchived) {
            $dsPassword->getRowsByCustomerIDAndPasswordLevel(
                $customerID,
                null,
                true
            );
        } else {
            $dsPassword->getRowsByCustomerIDAndPasswordLevel(
                $customerID,
                $this->dbeUser->getValue(DBEUser::passwordLevel)
            );
        }

        if (!$showArchived) {

            $urlAdd =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'edit',
                        'customerID' => $customerID
                    )
                );

            $urlSubmit = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'displayList'
                )
            );


        }


        $this->template->set_var(
            array(
                'urlSubmit'          => $urlSubmit,
                'urlAdd'             => $urlAdd,
                'customerName'       => $dbeCustomer->getValue(DBECustomer::name),
                'customerID'         => $customerID,
                'formError'          => $this->getFormErrorMessage(),
                'hideOnArchived'     => $showArchived ? "hidden" : '',
                'showOnArchived'     => $showArchived ? '' : 'hidden',
                "weirdColumnHeaders" => $showArchived ? '<th>Archived By</th><th>Archived At</th>' : '<th colspan="2">&nbsp;</th>',
                'archived'           => $showArchived ? '(Archived Passwords)' : ''
            )
        );

        $this->template->set_block(
            'PasswordList',
            'passwordBlock',
            'passwords'
        );
        $passwords = [];

        while ($dsPassword->fetchNext()) {

            $urlArchive = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'archive',
                    'passwordID' => $dsPassword->getValue(DBEPassword::passwordID)
                )
            );

            $urlEdit = $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'edit',
                    'passwordID' => $dsPassword->getValue(DBEPassword::passwordID)
                )
            );
            if ($showArchived) {
                $weirdFields = "<td class=\"contentLeftAlign\">" . $dsPassword->getValue(DBEPassword::archivedBy) . "</td>
        <td class=\"contentLeftAlign\">" . $dsPassword->getValue(DBEPassword::archivedAt) . "</td>";
            } else {
                $weirdFields = "<td class=\"contentLeftAlign\"><A href=\"$urlEdit\">edit</a></td>
        <td class=\"contentLeftAlign\"><A href=\"$urlArchive\" onClick=\"if(!confirm('Are you sure you want to archive this password?')) return(false)\">archive</a></td>";
            }

            $notes = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes));
            $decryptedURL = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL));
            $URL = strlen(
                $decryptedURL
            ) ? '<a href="' . $decryptedURL . '" target="_blank">' . $decryptedURL . '</a>' : '';


            $passwords[] = [
                "notes"               => $notes,
                "URL"                 => $URL,
                "serviceName"         => $dsPassword->getValue(DBEJPassword::serviceName),
                "serviceID"           => $dsPassword->getValue(DBEJPassword::serviceID),
                'passwordID'          => $dsPassword->getValue(DBEPassword::passwordID),
                'customerID'          => $dsPassword->getValue(DBEPassword::customerID),
                DBEPassword::username => $this->buPassword->decrypt(
                    $dsPassword->getValue(DBEPassword::username)
                ),
                'password'            => $this->buPassword->decrypt(
                    $dsPassword->getValue(DBEPassword::password)
                ),
                "weirdFields"         => $weirdFields,
                'level'               => $dsPassword->getValue(DBEPassword::level),
            ];
        }

        usort(
            $passwords,
            function ($a,
                      $b
            ) {

                if (!$a[DBEJPassword::serviceID] && $b[DBEJPassword::serviceID]) {
                    return 1;
                }

                if (!$b[DBEJPassword::serviceID] && $a[DBEJPassword::serviceID]) {
                    return -1;
                }

                if ($a[DBEJPassword::serviceName] < $b[DBEJPassword::serviceName]) {
                    return -1;
                }

                if ($a[DBEJPassword::serviceName] > $b[DBEJPassword::serviceName]) {
                    return 1;
                }

                if ($a[DBEJPassword::notes] < $b[DBEJPassword::notes]) {
                    return -1;
                }

                if ($a[DBEJPassword::notes] > $b[DBEJPassword::notes]) {
                    return 1;
                }
                return 0;
            }
        );

        foreach ($passwords as $password) {
            $this->template->set_var(
                $password
            );
            $this->template->parse(
                'passwords',
                'passwordBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'PasswordList',
            true
        );
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


            $_REQUEST['password'][1]['encrypted'] = 1;
            $passwordID = $_REQUEST['password'][1]['passwordID'];
            if ($passwordID) {

                $dbePassword->getRow($passwordID);

                $previousPassword = $dbePassword->getValue(DBEPassword::password);

                $previousPasswordDecrypted = $this->buPassword->decrypt($previousPassword);

                $newPassword = $_REQUEST['password'][1]['password'];

                if ($previousPassword && $previousPasswordDecrypted != $newPassword) {
                    $this->buPassword->archive(
                        $passwordID,
                        $this->dbeUser
                    );

                    $_REQUEST['password'][1]['passwordID'] = "0";
                }
            }

            $_REQUEST['password'][1][DBEPassword::username] = $this->buPassword->encrypt(
                $_REQUEST['password'][1][DBEPassword::username]
            );


            $_REQUEST['password'][1][DBEPassword::password] = $this->buPassword->encrypt(
                $_REQUEST['password'][1][DBEPassword::password]
            );


            $_REQUEST['password'][1][DBEPassword::notes] = $this->buPassword->encrypt(
                $_REQUEST['password'][1][DBEPassword::notes]
            );


            $_REQUEST['password'][1][DBEPassword::URL] = $this->buPassword->encrypt(
                $_REQUEST['password'][1][DBEPassword::URL]
            );


            $formError = (!$dsPassword->populateFromArray($_REQUEST['password']));

            if (!$formError) {
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
            $passwordID = null;
            if ($_REQUEST['passwordID']) {                      // editing
                $passwordID = $_REQUEST['passwordID'];
                $this->buPassword->getPasswordByID(
                    $_REQUEST['passwordID'],
                    $dsPassword
                );
                $customerID = $dsPassword->getValue(DBEPassword::customerID);
            } else {                                               // create new record
                $dsPassword->setValue(
                    DBEPassword::passwordID,
                    0
                );
                $dsPassword->setValue(
                    DBEPassword::customerID,
                    $_REQUEST['customerID']
                );
                $customerID = $_REQUEST['customerID'];
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

        if (!$maxLevel) {
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
        $dbePasswordServices->getNotInUseServices(
            $customerID,
            $passwordID
        );

        while ($dbePasswordServices->fetchNext()) {
            $passwordServiceID = $dbePasswordServices->getValue(DBEPasswordService::passwordServiceID);
            $this->template->setVar(
                [
                    "passwordServiceID"          => $passwordServiceID,
                    "selected"                   => $dsPassword->getValue(
                        DBEPassword::serviceID
                    ) == $passwordServiceID ? 'selected' : '',
                    "passwordServiceDescription" => $dbePasswordServices->getValue(DBEPasswordService::description),
                ]
            );

            $this->template->parse(
                'passwordServices',
                'passwordServiceBlock',
                true
            );
        }

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