<?php
/**
 * User controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUUser.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBECustomer.inc.php');
require_once($cfg['path_dbe'] . '/DBETeam.inc.php');
require_once($cfg['path_ct'] . '/CTPassword.inc.php');
// Actions
define(
    'CTUSER_ACT_DISPLAY_LIST',
    'userList'
);
define(
    'CTUSER_ACT_CREATE',
    'createUser'
);
define(
    'CTUSER_ACT_EDIT',
    'editUser'
);
define(
    'CTUSER_ACT_DELETE',
    'deleteUser'
);
define(
    'CTUSER_ACT_UPDATE',
    'updateUser'
);
define(
    'CTUSER_ACT_ABSENCE_EDIT',
    'absenceEdit'
);

class CTUser extends CTCNC
{
    const DECRYPT = 'decrypt';
    const GetAge = 'getAge';
    var $dsUser = '';
    var $dsAbsence = '';
    var $buUser = '';

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
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buUser = new BUUser($this);
        $this->dsUser = new DSForm($this);
        $this->dsUser->copyColumnsFrom($this->buUser->dbeUser);

        $this->dsUser->setAddColumnsOn();
        $this->dsUser->addColumn(
            "dateOfBirth",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "startDate",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "pensionAdditionalPayments",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "salary",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "salarySacrifice",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "nationalInsuranceNumber",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "address1",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "address2",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "address3",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "town",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "county",
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            "postcode",
            DA_TEXT,
            DA_ALLOW_NULL
        );

        $this->dsUser->setAddColumnsOff();

        $this->dsAbsence = new DSForm($this);
        $this->dsAbsence->addColumn(
            'userID',
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->dsAbsence->addColumn(
            'startDate',
            DA_DATE,
            DA_NOT_NULL
        );
        $this->dsAbsence->addColumn(
            'days',
            DA_INTEGER,
            DA_NOT_NULL
        );
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case CTUSER_ACT_EDIT:
            case CTUSER_ACT_CREATE:
                $this->edit();
                break;
            case CTUSER_ACT_DELETE:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->delete();
                break;
            case CTUSER_ACT_UPDATE:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->update();
                break;
            case CTUSER_ACT_ABSENCE_EDIT:
                $this->checkPermissions(PHPLIB_PERM_ACCOUNTS);
                $this->absenceEdit();
                break;
            case self::DECRYPT:
                $response = ["status" => "ok"];
                try {
                    $response['decryptedData'] = \CNCLTD\Encryption::decrypt(
                        USER_ENCRYPTION_PRIVATE_KEY,
                        @$_REQUEST['passphrase'],
                        @$_REQUEST['encryptedData']
                    );

                    if (isset($_REQUEST['extraData']) && $response['decryptedData']) {
                        switch ($_REQUEST['extraData']) {
                            case 'age':
                                $response['extraData'] = (new DateTime())->diff(
                                        DateTime::createFromFormat(
                                            'd/m/Y',
                                            $response['decryptedData']
                                        )
                                    )->y . " years old";
                                break;
                            case 'lengthOfService':
                                $difference = (new DateTime())->diff(
                                    DateTime::createFromFormat(
                                        'd/m/Y',
                                        $response['decryptedData']
                                    )
                                );

                                $differenceTotal = $difference->y + $difference->m / 12;

                                $response['extraData'] = "Length Of Service : " . number_format(
                                        $differenceTotal,
                                        1
                                    ) . " years";
                                break;
                        }
                    }

                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error'] = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            case CTUSER_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of users
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Users');
        $this->setTemplateFiles(
            array('UserList' => 'UserList.inc')
        );

        $this->buUser->getAllUsers($dsUser);

        if ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {
            $urlCreate =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTUSER_ACT_CREATE
                    )
                );
            $txtCreate = 'Create new user';
        } else {
            $txtCreate = '';
            $urlCreate = '';
        }

        $this->template->set_var(
            array(
                'urlCreate' => $urlCreate,
                'txtCreate' => $txtCreate
            )
        );

        if ($dsUser->rowCount() > 0) {
            $this->template->set_block(
                'UserList',
                'userBlock',
                'users'
            );
            while ($dsUser->fetchNext()) {
                $userID = $dsUser->getValue('userID');

                if ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {

                    $urlEdit =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTUSER_ACT_EDIT,
                                'userID' => $userID
                            )
                        );

                    $txtEdit = '[edit]';
                } else {
                    $urlEdit = '';
                    $txtEdit = '';
                }

                $urlReportAbsent =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTUSER_ACT_ABSENCE_EDIT,
                            'userID' => $userID
                        )
                    );
                $txtReportAbsent = '[record absence]';

                $this->template->set_var(
                    array(
                        'userID' => $userID,

                        'firstName' => Controller::htmlDisplayText($dsUser->getValue('firstName')),

                        'lastName' => Controller::htmlDisplayText($dsUser->getValue('lastName')),

                        'urlReportAbsent'
                        => $urlReportAbsent,

                        'txtReportAbsent'
                        => $txtReportAbsent,

                        'urlEdit' => $urlEdit,
                        'txtEdit' => $txtEdit
                    )
                );
                $this->template->parse(
                    'users',
                    'userBlock',
                    true
                );
            }//while $dsUser->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'UserList',
            true
        );
        $this->parsePage();
    }

    /**
     * Edit/Add Expense Type
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsUser = &$this->dsUser; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTUSER_ACT_EDIT) {
                $this->buUser->getUserByID(
                    $_REQUEST['userID'],
                    $dsUser
                );
                $userID = $_REQUEST['userID'];
            } else {                                                                    // creating new
                $dsUser->initialise();
                $dsUser->setValue(
                    'userID',
                    '0'
                );
                $userID = '0';
            }
        } else {                                                                        // form validation error
            $dsUser->initialise();
            $dsUser->fetchNext();
            $userID = $dsUser->getValue('userID');
        }
        if ($_REQUEST['action'] == CTUSER_ACT_EDIT && $this->buUser->canDeleteUser($_REQUEST['userID'])) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTUSER_ACT_DELETE,
                        'userID' => $userID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTUSER_ACT_UPDATE,
                    'userID' => $userID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTUSER_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit User');
        $this->setTemplateFiles(
            array('UserEdit' => 'UserEdit.inc')
        );

        $this->template->set_block(
            'UserEdit',
            'levelBlock',
            'levels'
        );
        $passwordLevels = CTPassword::$passwordLevels;

        foreach ($passwordLevels as $level) {

            $this->template->set_var(
                array(
                    'level'            => $level['level'],
                    'levelDescription' => $level['description'],
                    'levelSelected'    => $dsUser->getValue(DBEUser::passwordLevel) == $level['level'] ? 'selected' : ''
                )
            );
            $this->template->parse(
                'levels',
                'levelBlock',
                true
            );
        }


        $this->template->setVar(
            array(
                'userID'                                     => $dsUser->getValue('userID'),
                'name'                                       => Controller::htmlInputText($dsUser->getValue('name')),
                'nameMessage'                                => Controller::htmlDisplayText(
                    $dsUser->getMessage('name')
                ),
                'salutation'                                 => Controller::htmlInputText(
                    $dsUser->getValue('salutation')
                ),
                'salutationMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage('salutation')
                ),
                'address1PencilColor'                        => $this->dsUser->getValue(
                    DBEUser::encryptedAddress1
                ) ? "greenPencil" : "redPencil",
                'encryptedAddress1'                          => $this->dsUser->getValue(
                    DBEUser::encryptedAddress1
                ),
                "dateOfBirthPencilColor"                     => $this->dsUser->getValue(
                    DBEUser::encryptedDateOfBirth
                ) ? 'greenPencil' : 'redPencil',
                "encryptedDateOfBirth"                       => $this->dsUser->getValue(DBEUser::encryptedDateOfBirth),
                "startDate"                                  => Controller::dateYMDtoDMY(
                    $this->dsUser->getValue(DBEUser::startDate)
                ),
                "companyHealthcareStartDate"                 => Controller::dateYMDtoDMY(
                    $this->dsUser->getValue(DBEUser::companyHealthcareStartDate)
                ),
                "enhancedCNC2YearPensionStartDate"           => Controller::dateYMDtoDMY(
                    $this->dsUser->getValue(DBEUser::enhancedCNC2YearPensionStartDate)
                ),
                "pensionAdditionalPaymentsPencilColor"       => $this->dsUser->getValue(
                    DBEUser::encryptedPensionAdditionalPayments
                ) ? 'greenPencil' : 'redPencil',
                "encryptedPensionAdditionalPayments"         => $this->dsUser->getValue(
                    DBEUser::encryptedPensionAdditionalPayments
                ),
                "salaryPencilColor"                          => $this->dsUser->getValue(
                    DBEUser::encryptedSalary
                ) ? 'greenPencil' : 'redPencil',
                "encryptedSalary"                            => $this->dsUser->getValue(DBEUser::encryptedSalary),
                "salarySacrificePencilColor"                 => $this->dsUser->getValue(
                    DBEUser::encryptedSalarySacrifice
                ) ? 'greenPencil' : 'redPencil',
                "encryptedSalarySacrifice"                   => $this->dsUser->getValue(
                    DBEUser::encryptedSalarySacrifice
                ),
                "nationalInsuranceNumberPencilColor"         => $this->dsUser->getValue(
                    DBEUser::encryptedNationalInsuranceNumber
                ) ? 'greenPencil' : 'redPencil',
                "encryptedNationalInsuranceNumber"           => $this->dsUser->getValue(
                    DBEUser::encryptedNationalInsuranceNumber
                ),
                "address2PencilColor"                        => $this->dsUser->getValue(
                    DBEUser::encryptedAddress2
                ) ? 'greenPencil' : 'redPencil',
                "encryptedAddress2"                          => $this->dsUser->getValue(DBEUser::encryptedAddress2),
                "address3PencilColor"                        => $this->dsUser->getValue(
                    DBEUser::encryptedAddress3
                ) ? 'greenPencil' : 'redPencil',
                "encryptedAddress3"                          => $this->dsUser->getValue(DBEUser::encryptedAddress3),
                "townPencilColor"                            => $this->dsUser->getValue(
                    DBEUser::encryptedTown
                ) ? 'greenPencil' : 'redPencil',
                "encryptedTown"                              => $this->dsUser->getValue(DBEUser::encryptedTown),
                "countyPencilColor"                          => $this->dsUser->getValue(
                    DBEUser::encryptedCounty
                ) ? 'greenPencil' : 'redPencil',
                "encryptedCounty"                            => $this->dsUser->getValue(DBEUser::encryptedCounty),
                "postcodePencilColor"                        => $this->dsUser->getValue(
                    DBEUser::encryptedPostcode
                ) ? 'greenPencil' : 'redPencil',
                "encryptedPostcode"                          => $this->dsUser->getValue(DBEUser::encryptedPostcode),
                'add1'                                       => Controller::htmlInputText($dsUser->getValue('add1')),
                'add1Message'                                => Controller::htmlDisplayText(
                    $dsUser->getMessage('add1')
                ),
                'add2'                                       => Controller::htmlInputText($dsUser->getValue('add2')),
                'add3'                                       => Controller::htmlInputText($dsUser->getValue('add3')),
                'town'                                       => Controller::htmlInputText($dsUser->getValue('town')),
                'townMessage'                                => Controller::htmlDisplayText(
                    $dsUser->getMessage('town')
                ),
                'county'                                     => Controller::htmlInputText($dsUser->getValue('county')),
                'postcode'                                   => Controller::htmlInputText(
                    $dsUser->getValue('postcode')
                ),
                'postcodeMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage('postcode')
                ),
                'username'                                   => Controller::htmlInputText(
                    $dsUser->getValue('username')
                ),
                'usernameMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage('username')
                ),
                'employeeNo'                                 => Controller::htmlInputText(
                    $dsUser->getValue('employeeNo')
                ),
                'employeeNoMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage('employeeNo')
                ),
                'jobTitle'                                   => Controller::htmlInputText(
                    $dsUser->getValue('jobTitle')
                ),
                'jobTitleMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage('jobTitle')
                ),
                'petrolRate'                                 => Controller::htmlInputText(
                    $dsUser->getValue('petrolRate')
                ),
                'petrolRateMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage('petrolRate')
                ),
                'hourlyPayRate'                              => Controller::htmlInputText(
                    $dsUser->getValue('hourlyPayRate')
                ),
                'hourlyPayRateMessage'                       => Controller::htmlDisplayText(
                    $dsUser->getMessage('hourlyPayRate')
                ),
                'standardDayHours'                           => Controller::htmlInputText(
                    $dsUser->getValue('standardDayHours')
                ),
                'standardDayHoursMessage'                    => Controller::htmlDisplayText(
                    $dsUser->getMessage('standardDayHours')
                ),
                'signatureFilename'                          => Controller::htmlInputText(
                    $dsUser->getValue('signatureFilename')
                ),
                'signatureFilenameMessage'                   => Controller::htmlDisplayText(
                    $dsUser->getMessage('signatureFilename')
                ),
                'firstName'                                  => Controller::htmlInputText(
                    $dsUser->getValue(DBEUser::firstName)
                ),
                'firstNameMessage'                           => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEUser::firstName)
                ),
                'lastName'                                   => Controller::htmlInputText(
                    $dsUser->getValue(DBEUser::lastName)
                ),
                'lastNameMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEUser::lastName)
                ),
                'activeFlagChecked'                          => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::activeFlag)
                ),
                'starterLeaverQuestionManagementFlagChecked' => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::starterLeaverQuestionManagementFlag)
                ),
                'changeSRContractsFlagChecked'               => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::changeSRContractsFlag)
                ),
                'staffAppraiserFlagChecked'                  => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::staffAppraiserFlag)
                ),
                'receiveSdManagerEmailFlagChecked'           => Controller::htmlChecked(
                    $dsUser->getValue('receiveSdManagerEmailFlag')
                ),

                'appearInQueueFlagChecked' => Controller::htmlChecked($dsUser->getValue('appearInQueueFlag')),

                'changePriorityFlagChecked' => Controller::htmlChecked(
                    $dsUser->getValue(
                        'changePriorityFlag'
                    )
                ),

                'weekdayOvertimeFlagChecked' => Controller::htmlChecked($dsUser->getValue('weekdayOvertimeFlag')),
                'helpdeskFlagChecked'        => Controller::htmlChecked($dsUser->getValue('helpdeskFlag')),

                'salesChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_SALES
                    ) !== FALSE) ? CT_CHECKED : '',

                'accountsChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_ACCOUNTS
                    ) !== FALSE) ? CT_CHECKED : '',

                'technicalChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_TECHNICAL
                    ) !== FALSE) ? CT_CHECKED : '',

                'supervisorChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_SUPERVISOR
                    ) !== FALSE) ? CT_CHECKED : '',

                'maintenanceChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_MAINTENANCE
                    ) !== FALSE) ? CT_CHECKED : '',

                'customerChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_CUSTOMER
                    ) !== FALSE) ? CT_CHECKED : '',

                'renewalsChecked' => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_RENEWALS
                    ) !== FALSE) ? CT_CHECKED : '',

                'changeApproverFlagChecked'                     => Controller::htmlChecked(
                    $dsUser->getValue('changeApproverFlag')
                ),
                'changeInitialDateAndTimeFlagChecked'           => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::changeInitialDateAndTimeFlag)
                ),
                'excludeFromStatsFlagChecked'                   => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::excludeFromStatsFlag)
                ),
                'projectManagementFlagChecked'                  => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::projectManagementFlag)
                ),
                'offsiteBackupAdditionalPermissionsFlagChecked' => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::offsiteBackupAdditionalPermissionsFlag)
                ),
                'reportsChecked'                                => (strpos(
                        $dsUser->getValue('perms'),
                        PHPLIB_PERM_REPORTS
                    ) !== FALSE) ? CT_CHECKED : '',
                'teamMessage'                                   => Controller::htmlDisplayText(
                    $dsUser->getMessage('teamID')
                ),
                'urlUpdate'                                     => $urlUpdate,
                'urlDelete'                                     => $urlDelete,
                'txtDelete'                                     => $txtDelete,
                'urlDisplayList'                                => $urlDisplayList,

            )
        );
        // manager selection
        $dbeManager = new DBEUser($this);
        $dbeManager->getRows();
        $this->template->set_block(
            'UserEdit',
            'managerBlock',
            'managers'
        );
        while ($dbeManager->fetchNext()) {
            if ($dbeManager->getValue("userID") != $dsUser->getValue("userID")) {                // exclude this user
                $managerSelected = ($dsUser->getValue("managerID") == $dbeManager->getValue(
                        "userID"
                    )) ? CT_SELECTED : '';
                $this->template->set_var(
                    array(
                        'managerSelected' => $managerSelected,
                        'managerID'       => $dbeManager->getValue("userID"),
                        'managerName'     => $dbeManager->getValue("name")
                    )
                );
                $this->template->parse(
                    'managers',
                    'managerBlock',
                    true
                );
            }
        }

        // team selection
        $dbeTeam = new DBETeam($this);
        $dbeTeam->getRows();
        $this->template->set_block(
            'UserEdit',
            'teamBlock',
            'teams'
        );
        while ($dbeTeam->fetchNext()) {

            $teamSelected = ($dsUser->getValue("teamID") == $dbeTeam->getValue("teamID")) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'teamSelected' => $teamSelected,
                    'teamID'       => $dbeTeam->getValue("teamID"),
                    'teamName'     => $dbeTeam->getValue("name")
                )
            );
            $this->template->parse(
                'teams',
                'teamBlock',
                true
            );
        }

        // customer selection
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRows(DBECustomer::name);
        $this->template->set_block(
            'UserEdit',
            'customerBlock',
            'customers'
        );
        while ($dbeCustomer->fetchNext()) {
            $customerSelected = ($dsUser->getValue("customerID") == $dbeCustomer->getValue(
                    DBECustomer::customerID
                )) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'customerSelected' => $customerSelected,
                    'customerID'       => $dbeCustomer->getValue(DBECustomer::customerID),
                    'customerName'     => $dbeCustomer->getValue(DBECustomer::name)
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
            'UserEdit',
            true
        );
        $this->parsePage();
    }// end function edit()

    /**
     * Update call user details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsUser = &$this->dsUser;


        if (isset($_REQUEST['user'][1]["dateOfBirth"])) {
            if ($_REQUEST['user'][1]["dateOfBirth"]) {
                $_REQUEST['user'][1]["encryptedDateOfBirth"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["dateOfBirth"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedDateOfBirth"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["pensionAdditionalPayments"])) {

            if ($_REQUEST['user'][1]["pensionAdditionalPayments"]) {
                $_REQUEST['user'][1]["encryptedPensionAdditionalPayments"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["pensionAdditionalPayments"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedPensionAdditionalPayments"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["salary"])) {

            if ($_REQUEST['user'][1]["salary"]) {
                $_REQUEST['user'][1]["encryptedSalary"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["salary"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedSalary"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["salarySacrifice"])) {

            if ($_REQUEST['user'][1]["salarySacrifice"]) {
                $_REQUEST['user'][1]["encryptedSalarySacrifice"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["salarySacrifice"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedSalarySacrifice"] = null;
            }
        }


        if (isset($_REQUEST['user'][1]["nationalInsuranceNumber"])) {

            if ($_REQUEST['user'][1]["nationalInsuranceNumber"]) {
                $_REQUEST['user'][1]["encryptedNationalInsuranceNumber"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["nationalInsuranceNumber"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedNationalInsuranceNumber"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["address1"])) {

            if ($_REQUEST['user'][1]["address1"]) {
                $_REQUEST['user'][1]["encryptedAddress1"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["address1"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedAddress1"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["address2"])) {

            if ($_REQUEST['user'][1]["address2"]) {
                $_REQUEST['user'][1]["encryptedAddress2"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["address2"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedAddress2"] = null;
            }
        }


        if (isset($_REQUEST['user'][1]["address3"])) {

            if ($_REQUEST['user'][1]["address3"]) {
                $_REQUEST['user'][1]["encryptedAddress3"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["address3"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedAddress3"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["town"])) {
            if ($_REQUEST['user'][1]["town"]) {
                $_REQUEST['user'][1]["encryptedTown"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["town"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedTown"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["county"])) {

            if ($_REQUEST['user'][1]["county"]) {
                $_REQUEST['user'][1]["encryptedCounty"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["county"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedCounty"] = null;
            }
        }

        if (isset($_REQUEST['user'][1]["postcode"])) {

            if ($_REQUEST['user'][1]["postcode"]) {
                $_REQUEST['user'][1]["encryptedPostcode"] =
                    \CNCLTD\Encryption::encrypt(
                        USER_ENCRYPTION_PUBLIC_KEY,
                        $_REQUEST['user'][1]["postcode"]
                    );

            } else {
                $_REQUEST['user'][1]["encryptedPostcode"] = null;
            }
        }
        $this->formError = (!$this->dsUser->populateFromArray($_REQUEST['user']));
        if (isset($_REQUEST['perms'])) {
            $this->dsUser->setUpdateModeUpdate();
            $this->dsUser->setValue(
                'perms',
                implode(
                    ',',
                    $_REQUEST['perms']
                )
            );
            $this->dsUser->post();
        }
        if ($this->formError) {
            if ($this->dsUser->getValue('userID') == '0') {                    // attempt to insert
                $_REQUEST['action'] = CTUSER_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTUSER_ACT_CREATE;
            }
            $this->edit();
            exit;
        }


        $this->dsUser->setUpdateModeUpdate();
        $this->dsUser->post();

        $this->buUser->updateUser($this->dsUser);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'userID' => $this->dsUser->getValue('userID'),
                    'action' => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Expense Type
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buUser->deleteUser($_REQUEST['userID'])) {
            $this->displayFatalError('Cannot delete this user');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTUSER_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    function absenceEdit()
    {
        $this->setPageTitle('Record Absence');

        $this->setTemplateFiles(array('UserAbsenceEdit' => 'UserAbsenceEdit.inc'));

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!$this->formError = (!$this->dsAbsence->populateFromArray($_REQUEST['absence']))) {


                $this->buUser->setUserAbsent(
                    $this->dsAbsence->getValue('userID'),
                    $this->dsAbsence->getValue('startDate'),
                    $this->dsAbsence->getValue('days')
                );

                $urlNext =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTUSER_ACT_DISPLAY_LIST
                        )
                    );
                header('Location: ' . $urlNext);
                exit;
            }

        } else {
            /*
            defaults
            */
            $this->dsAbsence->setValue(
                'userID',
                $_REQUEST['userID']
            );
            $this->dsAbsence->setValue(
                'startDate',
                date(CONFIG_MYSQL_DATE)
            );
            $this->dsAbsence->setValue(
                'days',
                1
            );
        }

        $this->buUser->getUserByID(
            $this->dsAbsence->getValue('userID'),
            $dsUser
        );

        $this->template->setVar(
            array(
                'userID'           => $this->dsAbsence->getValue('userID'),
                'startDate'        => Controller::dateYMDtoDMY($this->dsAbsence->getValue('startDate')),
                'startDateMessage' => $this->dsAbsence->getMessage('startDate'),
                'daysMessage'      => $this->dsAbsence->getMessage('days'),
                'userName'         => $dsUser->getValue('name'),
                'urlUpdate'        => $urlUpdate
            )
        );

        $this->template->set_block(
            'UserAbsenceEdit',
            'daysBlock',
            'records'
        );

        for ($days = 1; $days <= 30; $days++) {

            $daySelected = ($this->dsAbsence->getValue("days") == $day) ? CT_SELECTED : '';

            $this->template->set_var(
                array(
                    'daySelected' => $daySelected,
                    'days'        => $days
                )
            );

            $this->template->parse(
                'records',
                'daysBlock',
                true
            );
        }

        $this->template->parse(
            'CONTENTS',
            'UserAbsenceEdit',
            true
        );

        $this->parsePage();
    }

    function reportAbsent()
    {
        $this->setMethodName('reportAbsent');

        $this->buUser->setUserAbsent($_REQUEST['userID']);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTUSER_ACT_DISPLAY_LIST
                )
            );
        header('Location: ' . $urlNext);
        exit;
    }

}// end of class
?>