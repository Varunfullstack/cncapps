<?php
/**
 * Domain renewal controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPassword.inc.php');
require_once($cfg['path_bu'] . '/BUCustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEJPassword.php');
require_once($cfg['path_dbe'] . '/DBEPasswordService.inc.php');

class CTPassword extends CTCNC
{
    public static $passwordLevels = [
        ["level" => 0, "description" => "No Access"],
        ["level" => 1, "description" => "Helpdesk Access"],
        ["level" => 2, "description" => "Engineer Access"],
        ["level" => 3, "description" => "Senior Engineer Access"],
        ["level" => 4, "description" => "Team Lead Access"],
        ["level" => 5, "description" => "Management Access"]
    ];
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
            "technical",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(104);
        $this->buPassword = new BUPassword($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case 'edit':
                $this->edit();
                break;
            case 'archive':
                $this->archive();
                break;
            case 'generate':
                $this->generate();
                break;
            case 'list':

                if (!$this->getParam('customerID')) {
                    $this->displayFatalError('Customer ID is not provided');
                    exit;
                }
                $customerID = $this->getParam('customerID');
                $this->displayList($customerID);
                break;

            case 'search':
            default:
                $this->search();
                break;
        }
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
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');

        $dsPassword = new DSForm($this);
        $dbePassword = new dbePassword($this);
        $dsPassword->copyColumnsFrom($dbePassword);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $passwordForm = $this->getParam('password')[1];
            $passwordForm['encrypted'] = 1;
            $passwordID = $passwordForm['passwordID'];


            if ($passwordID) {
                $dbePassword->getRow($passwordID);
                if ($this->dbeUser->getValue(DBEUser::passwordLevel) < $dbePassword->getValue(DBEPassword::level)) {
                    return;
                }
                if (!$this->dbeUser->getValue(DBEUser::salesPasswordAccess)) {
                    $passwordForm[DBEPassword::salesPassword] = $dbePassword->getValue(DBEPassword::salesPassword);
                }
                $previousPassword = $dbePassword->getValue(DBEPassword::password);
                $previousPasswordDecrypted = $this->buPassword->decrypt($previousPassword);
                $newPassword = $passwordForm['password'];
                if ($previousPassword && $previousPasswordDecrypted != $newPassword) {
                    $this->buPassword->archive(
                        $passwordID,
                        $this->dbeUser
                    );
                    $passwordForm['passwordID'] = null;
                }
            }
            $passwordForm[DBEPassword::username] = $this->buPassword->encrypt($passwordForm[DBEPassword::username]);
            $passwordForm[DBEPassword::password] = $this->buPassword->encrypt($passwordForm[DBEPassword::password]);
            $passwordForm[DBEPassword::notes] = $this->buPassword->encrypt($passwordForm[DBEPassword::notes]);
            $passwordForm[DBEPassword::URL] = $this->buPassword->encrypt($passwordForm[DBEPassword::URL]);
            $formError = (!$dsPassword->populateFromArray([$passwordForm]));

            if (!$formError) {
                $this->buPassword->updatePassword($dsPassword);

                $urlNext =
                    Controller::buildLink(
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
            if ($this->getParam('passwordID')) {                      // editing
                $passwordID = $this->getParam('passwordID');
                $this->buPassword->getPasswordByID(
                    $this->getParam('passwordID'),
                    $dsPassword
                );
                if ($this->dbeUser->getValue(DBEUser::passwordLevel) < $dsPassword->getValue(DBEPassword::level)) {
                    return;
                }
                $customerID = $dsPassword->getValue(DBEPassword::customerID);
            } else {                                               // create new record
                $dsPassword->setValue(
                    DBEPassword::passwordID,
                    null
                );
                $dsPassword->setValue(
                    DBEPassword::customerID,
                    $this->getParam('customerID')
                );
                $customerID = $this->getParam('customerID');
            }
        }


        $urlEdit =
            Controller::buildLink(
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

        $minLevel = 1;
        $userLevel = $this->dbeUser->getValue(DBEUser::passwordLevel);

        if (!$userLevel) {
            echo 'You cannot edit this password';
            exit;
        }

        for ($level = $minLevel; $level <= count(self::$passwordLevels) - 1; $level++) {

            $this->template->set_var(
                array(
                    'level'            => $level,
                    'levelSelected'    => $dsPassword->getValue(DBEPassword::level) == $level ? 'selected' : '',
                    'levelDescription' => self::$passwordLevels[$level]['description']
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
                'customerID'             => $dsPassword->getValue(DBEPassword::customerID),
                'passwordID'             => $dsPassword->getValue(DBEPassword::passwordID),
                DBEPassword::username    => $this->replaceQuatos($this->buPassword->decrypt($dsPassword->getValue(DBEPassword::username))),
                'usernameMessage'        => $dsPassword->getMessage(DBEPassword::username),
                'password'               => $this->replaceQuatos($this->buPassword->decrypt($dsPassword->getValue(DBEPassword::password))),
                'passwordMessage'        => $dsPassword->getMessage(DBEPassword::password),
                DBEPassword::notes       => $this->replaceQuatos($this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes))),
                'notesMessage'           => $dsPassword->getMessage(DBEPassword::notes),
                'urlEdit'                => $urlEdit,
                'URL'                    => $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL)),
                'hasSalesPasswordAccess' => $this->dbeUser->getValue(DBEUser::salesPasswordAccess) ? 1 : 0,
                'salesPassword'          => $dsPassword->getValue(DBEPassword::salesPassword) ? 1 : 0,
                'salesPasswordChecked'   => $dsPassword->getValue(DBEPassword::salesPassword) ? 'checked' : 0,
                'error'                  => $this->getParam('error')
            )
        );

        $this->template->parse(
            'CONTENTS',
            'PasswordEdit',
            true
        );
        $this->parsePage();

    } // end search
    function replaceQuatos($str)
    {
        return str_replace("\"","&quot;",$str);
    }
    function archive()
    {
        $this->setMethodName('archive');

        if (!$this->buPassword->getPasswordByID(
            $this->getParam('passwordID'),
            $dsPassword
        )) {
            $this->raiseError('PasswordID ' . $this->getParam('passwordID') . ' not found');
            exit;
        }
        $urlArray = [
            'action'     => 'list',
            'customerID' => $dsPassword->getValue(DBEPassword::customerID),
        ];

        if ($dsPassword->getValue(DBEPassword::level) > $this->getDbeUser()->getValue(DBEUser::passwordLevel)) {
            $urlArray['error'] = "Not enough level";
        } else {
            $this->buPassword->archive(
                $this->getParam('passwordID'),
                $this->dbeUser
            );
        }

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                $urlArray
            );

        header('Location: ' . $urlNext);
        exit;
    }

    /**
     * generate a password
     *
     * @throws Exception
     */
    function generate()
    {
        $this->setMethodName('generate');

        $this->setPageTitle('New Password');

        $this->setTemplateFiles(array('PasswordGenerate' => 'PasswordGenerate.inc'));

        $this->template->parse(
            'CONTENTS',
            'PasswordGenerate',
            true
        );
        $this->parsePage();

    }

    /**
     * Display list of types
     * @access private
     * @param $customerID
     * @throws Exception
     */
    function displayList($customerID)
    {
        $dbeCustomer = new DBECustomer($this);
        $showArchived = $this->getParam('archived');
        $showHigherLevel = $this->getParam('higherLevel');
        if (empty($customerID)) {
            $this->raiseError('Please search for a customer by typing and then pressing tab');
            exit;
        }

        $this->setMethodName('displayList');

        $this->setPageTitle('Passwords');
        $dbeCustomer->getRow($customerID);

        if ($dbeCustomer->getValue(DBECustomer::referredFlag) == 'Y') {
            $this->setTemplateFiles('PasswordReferred', 'PasswordReferred.inc');
            $this->template->parse(
                'CONTENTS',
                'PasswordReferred',
                true
            );
            $this->parsePage();
            return;
        }

        $this->setTemplateFiles(
            array('PasswordList' => 'PasswordList.inc')
        );

        $dsPassword = new DBEJPassword($this);
        $passwordLevel = $this->dbeUser->getValue(DBEUser::passwordLevel);
        if ($showHigherLevel) {
            $passwordLevel = 5;
        }
        $dsPassword->getRowsByCustomerIDAndPasswordLevel(
            $customerID,
            $passwordLevel,
            $showArchived,
            $this->dbeUser->getValue(DBEUser::salesPasswordAccess)
        );

        $urlSubmit = null;
        $urlAdd = null;

        if (!$showArchived) {

            $urlAdd =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'     => 'edit',
                        'customerID' => $customerID
                    )
                );

            $urlSubmit = Controller::buildLink(
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
                'showOnHigherLevel'  => $showHigherLevel ? '' : 'hidden',
                'hideOnHigherLevel'  => $showHigherLevel ? 'hidden' : '',
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

            $urlArchive = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'archive',
                    'passwordID' => $dsPassword->getValue(DBEPassword::passwordID)
                )
            );


            $urlEdit = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'     => 'edit',
                    'passwordID' => $dsPassword->getValue(DBEPassword::passwordID)
                )
            );
            $weirdFields = "<td class=\"contentLeftAlign\"><A href=\"$urlEdit\">edit</a></td>
        <td class=\"contentLeftAlign\"><A href=\"$urlArchive\" onClick=\"if(!confirm('Are you sure you want to archive this password?')) return(false)\">archive</a></td>";

            if ($showArchived) {
                $weirdFields = "<td class=\"contentLeftAlign\">" . $dsPassword->getValue(DBEPassword::archivedBy) . "</td>
        <td class=\"contentLeftAlign\">" . $dsPassword->getValue(DBEPassword::archivedAt) . "</td>";
            }

            $notes = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::notes));
            $decryptedURL = $this->buPassword->decrypt($dsPassword->getValue(DBEPassword::URL));
            $URL = strlen(
                $decryptedURL
            ) ? '<a href="' . $decryptedURL . '" target="_blank">' . $decryptedURL . '</a>' : '';

            $userName = $this->buPassword->decrypt(
                $dsPassword->getValue(DBEPassword::username)
            );
            $password = $this->buPassword->decrypt(
                $dsPassword->getValue(DBEPassword::password)
            );

            if ($dsPassword->getValue(DBEPassword::level) > $this->dbeUser->getValue(DBEUser::passwordLevel)) {
                $userName = null;
                $password = null;
                $weirdFields = null;
            }

            $passwords[] = [
                "notes"               => $notes,
                "URL"                 => $URL,
                "serviceName"         => $dsPassword->getValue(DBEJPassword::serviceName),
                "serviceID"           => $dsPassword->getValue(DBEJPassword::serviceID),
                'passwordID'          => $dsPassword->getValue(DBEPassword::passwordID),
                'customerID'          => $dsPassword->getValue(DBEPassword::customerID),
                DBEPassword::username => $userName,
                'password'            => $password,
                "weirdFields"         => $weirdFields,
                'level'               => $dsPassword->getValue(DBEPassword::level),
                'sortOrder'           => $dsPassword->getValue(DBEJPassword::sortOrder)
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

                if ($a[DBEJPassword::sortOrder] != $b[DBEJPassword::sortOrder]) {
                    return $a[DBEJPassword::sortOrder] - $b[DBEJPassword::sortOrder];
                }

                return strcmp(
                    $a[DBEJPassword::notes],
                    $b[DBEJPassword::notes]
                );
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

    function search()
    {
        $this->template->setVar("menuId", 104);
        $this->setMethodName('search');
        /** @var DSForm $dsSearchForm */
        $this->buPassword->initialiseSearchForm($dsSearchForm);
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_REQUEST['searchForm'])) {
                if (!$dsSearchForm->populateFromArray($_REQUEST ['searchForm'])) {
                    $this->setFormErrorOn();
                } else {
                    $customerID = $dsSearchForm->getValue(DBEPassword::customerID);
                    header("Location: Password.php?action=list&customerID=$customerID");
                    exit;
                }
            }

        }

        $this->setMethodName('displaySearchForm');

        $this->setTemplateFiles(
            array(
                'PasswordSearch' => 'PasswordSearch.inc'
            )
        );

        $urlSubmit = Controller::buildLink(
            $_SERVER ['PHP_SELF'],
            array('action' => CTCNC_ACT_SEARCH)
        );


        $this->setPageTitle('Passwords');
        $customerString = null;
        if ($dsSearchForm->getValue(DBEPassword::customerID)) {
            $buCustomer = new BUCustomer ($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(DBEPassword::customerID),
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

    }

    /**
     * @param $a
     * @param $b
     * @return int|lt
     */
    function weirdStringComparison($a,
                                   $b
    )
    {
        $lenA = strlen($a);
        $lenB = strlen($b);

        if (!$lenA && $lenB) {
            return -1;
        }

        if ($lenA && !$lenB) {
            return 1;
        }

        if (!$lenA && !$lenB) {
            return 0;
        }

        $len = $lenA > $lenB ? $lenA : $lenB;
        $currentIdx = 0;
        while ($currentIdx < $len) {

            if (!isset($a[$currentIdx])) {
                return -1;
            }

            if (!isset($b[$currentIdx])) {
                return 1;
            }

            if ($comparison = $this->compareCharacter(
                $a[$currentIdx],
                $b[$currentIdx]
            )) {
                return $comparison;
            };
            $currentIdx++;
        }
        return 0;
    }

    function compareCharacter($ch1,
                              $ch2
    )
    {
        if (ctype_lower($ch1) && !ctype_lower($ch2)) {
            return -1;
        }

        if (!ctype_lower($ch1) && ctype_lower($ch2)) {
            return 1;
        }
        return strcmp(
            $ch1,
            $ch2
        );
    }

}// end of class
