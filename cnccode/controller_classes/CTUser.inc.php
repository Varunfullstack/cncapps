<?php
/**
 * User controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;

global $cfg;
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
    const dateOfBirth               = "dateOfBirth";
    const startDate                 = "startDate";
    const pensionAdditionalPayments = "pensionAdditionalPayments";
    const salary                    = "salary";
    const salarySacrifice           = "salarySacrifice";
    const nationalInsuranceNumber   = "nationalInsuranceNumber";
    const address1                  = "address1";
    const address2                  = "address2";
    const address3                  = "address3";
    const town                      = "town";
    const county                    = "county";
    const postcode                  = "postcode";

    const absenceFormUserID    = "userID";
    const absenceFormStartDate = "startDate";
    const absenceFormDays      = "days";
    const sickTime             = 'sickTime';


    const DECRYPT                = 'decrypt';
    const GetAge                 = 'getAge';
    const REGISTER_HALF_HOLIDAYS = 'REGISTER_HALF_HOLIDAYS';
    const REQ_SETTINGS           = 'settings';
    const CONST_MY_FEEDBACK      = 'myFeedback';
    /** @var DSForm */
    public $dsUser;
    /** @var DSForm */
    public $dsAbsence;
    /** @var BUUser */
    public $buUser;

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
        $noPermissionList = [
            'myFeedback',
            "all",
            "active",
            "getCurrentUser",
            "getUsersByTeamLevel",
            self::REQ_SETTINGS
        ];
        $roles            = SENIOR_MANAGEMENT_PERMISSION;
        $key              = array_search(@$_REQUEST["action"], $noPermissionList);
        if (false === $key) if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(903);
        $this->buUser = new BUUser($this);
        $this->dsUser = new DSForm($this);
        $this->dsUser->copyColumnsFrom($this->buUser->dbeUser);
        $this->dsUser->setNull(DBEUser::userID, DA_ALLOW_NULL);
        $this->dsUser->setAddColumnsOn();
        $this->dsUser->addColumn(
            self::dateOfBirth,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::startDate,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::pensionAdditionalPayments,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::salary,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::salarySacrifice,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::nationalInsuranceNumber,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::address1,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::address2,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::address3,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::town,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::county,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->addColumn(
            self::postcode,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->dsUser->setAddColumnsOff();
        $this->dsAbsence = new DSForm($this);
        $this->dsAbsence->addColumn(
            self::absenceFormUserID,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->dsAbsence->addColumn(
            self::absenceFormStartDate,
            DA_DATE,
            DA_NOT_NULL
        );
        $this->dsAbsence->addColumn(
            self::absenceFormDays,
            DA_INTEGER,
            DA_NOT_NULL
        );
        $this->dsAbsence->addColumn(
            self::sickTime,
            DA_TEXT,
            DA_NOT_NULL
        );
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        switch ($this->getAction()) {
            case CTUSER_ACT_EDIT:
            case CTUSER_ACT_CREATE:
                $this->edit();
                break;
            case CTUSER_ACT_DELETE:
                $this->delete();
                break;
            case CTUSER_ACT_UPDATE:
                $this->update();
                break;
            case self::REGISTER_HALF_HOLIDAYS:
                $this->registerHalfHolidays();
                break;
            case CTUSER_ACT_ABSENCE_EDIT:
                $this->absenceEdit();
                break;
            case self::DECRYPT:
                $response = ["status" => "ok"];
                try {
                    $response['decryptedData'] = Encryption::decrypt(
                        USER_ENCRYPTION_PRIVATE_KEY,
                        @$this->getParam('passphrase'),
                        @$this->getParam('encryptedData')
                    );
                    if ($this->getParam('extraData') && $response['decryptedData']) {
                        switch ($this->getParam('extraData')) {
                            case 'age':
                                $response['extraData'] = (new DateTime())->diff(
                                        DateTime::createFromFormat(
                                            'd/m/Y',
                                            $response['decryptedData']
                                        )
                                    )->y . " years old";
                                break;
                            case 'lengthOfService':
                                $difference            = (new DateTime())->diff(
                                    DateTime::createFromFormat(
                                        'd/m/Y',
                                        $response['decryptedData']
                                    )
                                );
                                $differenceTotal       = $difference->y + $difference->m / 12;
                                $response['extraData'] = "Length Of Service : " . number_format(
                                        $differenceTotal,
                                        1
                                    ) . " years";
                                break;
                        }
                    }

                } catch (Exception $exception) {
                    $response['status'] = "error";
                    $response['error']  = $exception->getMessage();
                    http_response_code(400);
                }
                echo json_encode($response);
                break;
            case 'getApprovalSubordinates':
                $superiorId = $_REQUEST['superiorId'];
                $dbeUser    = new DBEUser($this);
                $dbeUser->getApprovalSubordinates($superiorId);
                $users = [];
                while ($dbeUser->fetchNext()) {
                    $users[] = $dbeUser->getRowAsAssocArray();
                }
                echo json_encode(
                    [
                        "status" => "ok",
                        "data"   => $users
                    ]
                );
                break;
            case "getCurrentUser":
                echo $this->getCurrentUser();
                exit;
            case "all":
                echo json_encode($this->getAllUsers());
                exit;
            case "active":
                echo json_encode($this->getActiveUsers());
                exit;
            case "getUsersByTeamLevel":
                echo json_encode($this->getUsersByTeamLevel());
                exit;
            case self::REQ_SETTINGS:
                if ($method == 'POST') echo json_encode(
                    $this->saveSettings()
                ); else if ($method == 'GET') echo json_encode($this->getSettings());
                exit;
            case self::CONST_MY_FEEDBACK:
                echo json_encode($this->getMyFeedback(), JSON_NUMERIC_CHECK);
                exit;
            case CTUSER_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Edit/Add Expense Type
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsUser = &$this->dsUser; // ref to class var
        if (!$this->getFormError()) {
            if ($this->getAction() == CTUSER_ACT_EDIT) {
                $this->buUser->getUserByID(
                    $this->getParam('userID'),
                    $dsUser
                );
                $userID = $this->getParam('userID');
            } else {                                                                    // creating new
                $dsUser->initialise();
                $dsUser->setValue(
                    DBEJUser::userID,
                    null
                );
                $userID = null;
            }
        } else {                                                                        // form validation error
            $dsUser->initialise();
            $dsUser->fetchNext();
            $userID = $dsUser->getValue(DBEJUser::userID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTUSER_ACT_EDIT && $this->buUser->canDeleteUser($this->getParam('userID'))) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTUSER_ACT_DELETE,
                    'userID' => $userID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate      = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTUSER_ACT_UPDATE,
                'userID' => $userID
            )
        );
        $urlDisplayList = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTUSER_ACT_DISPLAY_LIST
            )
        );
        $this->setPageTitle('Edit User');
        $this->setTemplateFiles(
            array('UserEdit' => 'UserEdit.inc')
        );
        $this->template->setVar('javaScript', "<link rel='stylesheet' href='components/shared/ToolTip.css'>");
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
                    'levelSelected'    => $dsUser->getValue(
                        DBEUser::passwordLevel
                    ) == $level['level'] ? 'selected' : null
                )
            );
            $this->template->parse(
                'levels',
                'levelBlock',
                true
            );
        }
        $siteCustomerString = '';
        $siteCustomerId     = $dsUser->getValue(DBEJUser::siteCustId);
        if (isset($siteCustomerId)) {
            $dbeCustomer = new DBECustomer($this);
            $dbeCustomer->setPKValue($siteCustomerId);
            $dbeCustomer->getRow();
            $siteCustomerString = $dbeCustomer->getValue(DBECustomer::name);
        }
        $this->template->setVar(
            array(
                'userID'                                        => $dsUser->getValue(DBEJUser::userID),
                'name'                                          => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::name)
                ),
                'nameMessage'                                   => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::name)
                ),
                'salutation'                                    => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::salutation)
                ),
                'salutationMessage'                             => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::salutation)
                ),
                'address1PencilColor'                           => $this->dsUser->getValue(
                    DBEUser::encryptedAddress1
                ) ? "greenPencil" : "redPencil",
                'encryptedAddress1'                             => $this->dsUser->getValue(
                    DBEUser::encryptedAddress1
                ),
                "dateOfBirthPencilColor"                        => $this->dsUser->getValue(
                    DBEUser::encryptedDateOfBirth
                ) ? 'greenPencil' : 'redPencil',
                "encryptedDateOfBirth"                          => $this->dsUser->getValue(
                    DBEUser::encryptedDateOfBirth
                ),
                "startDate"                                     => $this->dsUser->getValue(DBEUser::startDate),
                "companyHealthcareStartDate"                    => $this->dsUser->getValue(
                    DBEUser::companyHealthcareStartDate
                ),
                "enhancedCNC2YearPensionStartDate"              => $this->dsUser->getValue(
                    DBEUser::enhancedCNC2YearPensionStartDate
                ),
                "pensionAdditionalPaymentsPencilColor"          => $this->dsUser->getValue(
                    DBEUser::encryptedPensionAdditionalPayments
                ) ? 'greenPencil' : 'redPencil',
                "encryptedPensionAdditionalPayments"            => $this->dsUser->getValue(
                    DBEUser::encryptedPensionAdditionalPayments
                ),
                "salaryPencilColor"                             => $this->dsUser->getValue(
                    DBEUser::encryptedSalary
                ) ? 'greenPencil' : 'redPencil',
                "encryptedSalary"                               => $this->dsUser->getValue(DBEUser::encryptedSalary),
                "salarySacrificePencilColor"                    => $this->dsUser->getValue(
                    DBEUser::encryptedSalarySacrifice
                ) ? 'greenPencil' : 'redPencil',
                "encryptedSalarySacrifice"                      => $this->dsUser->getValue(
                    DBEUser::encryptedSalarySacrifice
                ),
                "nationalInsuranceNumberPencilColor"            => $this->dsUser->getValue(
                    DBEUser::encryptedNationalInsuranceNumber
                ) ? 'greenPencil' : 'redPencil',
                "encryptedNationalInsuranceNumber"              => $this->dsUser->getValue(
                    DBEUser::encryptedNationalInsuranceNumber
                ),
                "address2PencilColor"                           => $this->dsUser->getValue(
                    DBEUser::encryptedAddress2
                ) ? 'greenPencil' : 'redPencil',
                "encryptedAddress2"                             => $this->dsUser->getValue(DBEUser::encryptedAddress2),
                "address3PencilColor"                           => $this->dsUser->getValue(
                    DBEUser::encryptedAddress3
                ) ? 'greenPencil' : 'redPencil',
                "encryptedAddress3"                             => $this->dsUser->getValue(DBEUser::encryptedAddress3),
                "townPencilColor"                               => $this->dsUser->getValue(
                    DBEUser::encryptedTown
                ) ? 'greenPencil' : 'redPencil',
                "encryptedTown"                                 => $this->dsUser->getValue(DBEUser::encryptedTown),
                "countyPencilColor"                             => $this->dsUser->getValue(
                    DBEUser::encryptedCounty
                ) ? 'greenPencil' : 'redPencil',
                "encryptedCounty"                               => $this->dsUser->getValue(DBEUser::encryptedCounty),
                "postcodePencilColor"                           => $this->dsUser->getValue(
                    DBEUser::encryptedPostcode
                ) ? 'greenPencil' : 'redPencil',
                "encryptedPostcode"                             => $this->dsUser->getValue(DBEUser::encryptedPostcode),
                'add1'                                          => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::add1)
                ),
                'add1Message'                                   => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::add1)
                ),
                'add2'                                          => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::add2)
                ),
                'add3'                                          => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::add3)
                ),
                'town'                                          => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::town)
                ),
                'townMessage'                                   => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::town)
                ),
                'county'                                        => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::county)
                ),
                'postcode'                                      => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::postcode)
                ),
                'postcodeMessage'                               => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::postcode)
                ),
                'username'                                      => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::username)
                ),
                'usernameMessage'                               => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::username)
                ),
                'employeeNo'                                    => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::employeeNo)
                ),
                'employeeNoMessage'                             => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::employeeNo)
                ),
                'jobTitle'                                      => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::jobTitle)
                ),
                'jobTitleMessage'                               => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::jobTitle)
                ),
                'petrolRate'                                    => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::petrolRate)
                ),
                'petrolRateMessage'                             => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::petrolRate)
                ),
                'hourlyPayRate'                                 => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::hourlyPayRate)
                ),
                'hourlyPayRateMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::hourlyPayRate)
                ),
                'standardDayHours'                              => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::standardDayHours)
                ),
                'standardDayHoursMessage'                       => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::standardDayHours)
                ),
                'signatureFilename'                             => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::signatureFilename)
                ),
                'signatureFilenameMessage'                      => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::signatureFilename)
                ),
                'firstName'                                     => Controller::htmlInputText(
                    $dsUser->getValue(DBEUser::firstName)
                ),
                'firstNameMessage'                              => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEUser::firstName)
                ),
                'lastName'                                      => Controller::htmlInputText(
                    $dsUser->getValue(DBEUser::lastName)
                ),
                'lastNameMessage'                               => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEUser::lastName)
                ),
                'activeFlagChecked'                             => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::activeFlag)
                ),
                "bccOnCustomerEmailsChecked"                    => $dsUser->getValue(
                    DBEUser::bccOnCustomerEmails
                ) ? "checked" : "",
                'globalExpenseApproverChecked'                  => $dsUser->getValue(
                    DBEUser::globalExpenseApprover
                ) ? 'checked' : null,
                'salesPasswordAccessChecked'                    => $dsUser->getValue(
                    DBEUser::salesPasswordAccess
                ) ? 'checked' : null,
                'starterLeaverQuestionManagementFlagChecked'    => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::starterLeaverQuestionManagementFlag)
                ),
                'changeSRContractsFlagChecked'                  => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::changeSRContractsFlag)
                ),
                'staffAppraiserFlagChecked'                     => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::staffAppraiserFlag)
                ),
                "isExpenseApproverChecked"                      => $dsUser->getValue(
                    DBEUser::isExpenseApprover
                ) ? 'checked' : null,
                'receiveSdManagerEmailFlagChecked'              => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::receiveSdManagerEmailFlag)
                ),
                'autoApproveExpensesChecked'                    => $dsUser->getValue(
                    DBEJUser::autoApproveExpenses
                ) ? 'checked' : null,
                'appearInQueueFlagChecked'                      => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::appearInQueueFlag)
                ),
                'changePriorityFlagChecked'                     => Controller::htmlChecked(
                    $dsUser->getValue(
                        DBEJUser::changePriorityFlag
                    )
                ),
                'helpdeskFlagChecked'                           => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::helpdeskFlag)
                ),
                'createRenewalSalesOrdersFlagChecked'           => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::createRenewalSalesOrdersFlag)
                ),
                'salesChecked'                                  => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        SALES_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'accountManagementChecked'                      => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        ACCOUNT_MANAGEMENT_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'seniorManagementChecked'                       => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        SENIOR_MANAGEMENT_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'accountsChecked'                               => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        ACCOUNTS_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'technicalChecked'                              => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        TECHNICAL_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'supervisorChecked'                             => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        SUPERVISOR_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'maintenanceChecked'                            => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        MAINTENANCE_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'renewalsChecked'                               => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        RENEWALS_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'queueManagerChecked'                           => $dsUser->getValue(
                    DBEUser::queueManager
                ) ? 'checked' : '',
                'excludeFromStatsFlagChecked'                   => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::excludeFromStatsFlag)
                ),
                'projectManagementFlagChecked'                  => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::projectManagementFlag)
                ),
                'offsiteBackupAdditionalPermissionsFlagChecked' => Controller::htmlChecked(
                    $dsUser->getValue(DBEUser::offsiteBackupAdditionalPermissionsFlag)
                ),
                'additionalTimeLevelApproverChecked'            => $dsUser->getValue(
                    DBEUser::additionalTimeLevelApprover
                ) ? 'checked' : null,
                'reportsChecked'                                => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        REPORTS_PERMISSION
                    ) !== FALSE) ? CT_CHECKED : null,
                'teamMessage'                                   => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::teamID)
                ),
                'urlUpdate'                                     => $urlUpdate,
                'urlDelete'                                     => $urlDelete,
                'txtDelete'                                     => $txtDelete,
                'urlDisplayList'                                => $urlDisplayList,
                "basedAtCustomerSiteChecked"                    => $this->dsUser->getValue(
                    DBEUser::basedAtCustomerSite
                ) ? 'checked' : null,
                "basedAtCustomerSiteChecked"                    => $this->dsUser->getValue(
                    DBEUser::basedAtCustomerSite
                ) ? 'checked' : null,
                'siteCustId'                                    => $siteCustomerId,
                'siteCustomerString'                            => $siteCustomerString,
                'streamOneLicenseManagementChecked'             => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::streamOneLicenseManagement)
                ),
                'excludeFromSDManagerDashboardChecked'          => $dsUser->getValue(
                    DBEUser::excludeFromSDManagerDashboard
                ) ? 'checked' : null,
                'holdAllSRsforQAReviewChecked'                  => $dsUser->getValue(
                    DBEUser::holdAllSRsforQAReview
                ) ? 'checked' : null,
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
            if ($dbeManager->getValue(DBEJUser::userID) != $dsUser->getValue(
                    DBEJUser::userID
                )) {                // exclude this user
                $managerSelected = ($dsUser->getValue(DBEJUser::managerID) == $dbeManager->getValue(
                        DBEJUser::userID
                    )) ? CT_SELECTED : null;
                $this->template->set_var(
                    array(
                        'managerSelected' => $managerSelected,
                        'managerID'       => $dbeManager->getValue(DBEJUser::userID),
                        'managerName'     => $dbeManager->getValue(DBEJUser::name)
                    )
                );
                $this->template->parse(
                    'managers',
                    'managerBlock',
                    true
                );
            }
        }
        $dbeExpenseApprover = new DBEUser($this);
        $dbeExpenseApprover->getApproverUsers();
        $this->template->set_block(
            'UserEdit',
            'expenseApproverBlock',
            'expenseApprovers'
        );
        while ($dbeExpenseApprover->fetchNext()) {
            $expenseApproverSelected = ($dsUser->getValue(
                    DBEJUser::expenseApproverID
                ) == $dbeExpenseApprover->getValue(
                    DBEJUser::userID
                )) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'expenseApproverSelected' => $expenseApproverSelected,
                    'expenseApproverID'       => $dbeExpenseApprover->getValue(DBEJUser::userID),
                    'expenseApproverName'     => $dbeExpenseApprover->getValue(DBEJUser::name)
                )
            );
            $this->template->parse(
                'expenseApprovers',
                'expenseApproverBlock',
                true
            );
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

            $teamSelected = ($dsUser->getValue(DBEJUser::teamID) == $dbeTeam->getValue(
                    DBETeam::teamID
                )) ? CT_SELECTED : null;
            $this->template->set_var(
                array(
                    'teamSelected' => $teamSelected,
                    'teamID'       => $dbeTeam->getValue(DBETeam::teamID),
                    'teamName'     => $dbeTeam->getValue(DBETeam::name)
                )
            );
            $this->template->parse(
                'teams',
                'teamBlock',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'UserEdit',
            true
        );
        $this->parsePage();
    }

    /**
     * Delete Expense Type
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buUser->deleteUser($this->getParam('userID'))) {
            $this->displayFatalError('Cannot delete this user');
            exit;
        }
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTUSER_ACT_DISPLAY_LIST
            )
        );
        header('Location: ' . $urlNext);
        exit;

    }// end function edit()

    /**
     * Update call user details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsUser->populateFromArray($this->getParam('user')));
        $userData        = $this->getParam('user')[1];
        $this->updateEncryptedData($userData, $this->dsUser);
        if ($this->getParam('perms')) {
            $this->dsUser->setUpdateModeUpdate();
            $this->dsUser->setValue(
                DBEJUser::perms,
                implode(
                    ',',
                    $this->getParam('perms')
                )
            );
            $this->dsUser->post();
        }
        if ($this->formError) {
            if (!$this->dsUser->getValue(DBEJUser::userID)) {
                $this->setAction(CTUSER_ACT_CREATE);
            } else {
                $this->setAction(CTUSER_ACT_EDIT);
            }
            $this->edit();
            exit;
        }
        $this->buUser->updateUser($this->dsUser);
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'userID' => $this->dsUser->getValue(DBEJUser::userID),
                'action' => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }

    private function updateEncryptedData(array $userData, DataAccess $dsUser)
    {
        $dsUser->setUpdateModeUpdate();
        $keys = [
            'dateOfBirth',
            'pensionAdditionalPayments',
            'salary',
            'salarySacrifice',
            'nationalInsuranceNumber',
            'address1',
            'address2',
            'address3',
            'town',
            'county',
            'postcode',
        ];
        foreach ($keys as $key) {
            $encryptedKeyName = 'encrypted' . ucfirst($key);
            $encryptedValue   = $this->dsUser->getValue($encryptedKeyName);
            if (isset($userData[$key]) && $userData[$key]) {
                $encryptedValue = Encryption::encrypt(
                    USER_ENCRYPTION_PUBLIC_KEY,
                    $userData[$key]
                );

            }
            $this->dsUser->setValue($encryptedKeyName, $encryptedValue);
        }
        $dsUser->post();
    }

    function registerHalfHolidays()
    {
        $this->setPageTitle('Register Half Holidays');
        $this->setTemplateFiles(array('UserHalfHolidays' => 'UserHalfHoliday'));
        $userId = $this->getParam('userID');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $date = $this->getParam('date');
            $this->buUser->logHalfHoliday($userId, $date);
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTUSER_ACT_DISPLAY_LIST
                )
            );
            header('Location: ' . $urlNext);
            exit;

        }
        $dsUser = new DataSet($this);
        $this->buUser->getUserByID(
            $userId,
            $dsUser
        );
        $this->template->setVar(
            array(
                'userID'   => $this->dsAbsence->getValue(self::absenceFormUserID),
                'userName' => $dsUser->getValue(DBEJUser::name),
            )
        );
        $this->template->parse(
            'CONTENTS',
            'UserHalfHolidays',
            true
        );
        $this->parsePage();
    }

    /**
     * @throws Exception
     */
    function absenceEdit()
    {
        $this->setPageTitle('Record Absence');
        $this->setTemplateFiles(array('UserAbsenceEdit' => 'UserAbsenceEdit.inc'));
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!$this->formError = (!$this->dsAbsence->populateFromArray($this->getParam('absence')))) {

                $this->buUser->setUserAbsent(
                    $this->dsAbsence->getValue(self::absenceFormUserID),
                    $this->dsAbsence->getValue(self::absenceFormStartDate),
                    $this->dsAbsence->getValue(self::absenceFormDays),
                    $this->dsAbsence->getValue(self::sickTime),
                    $this->dbeUser
                );
                $urlNext = Controller::buildLink(
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
                self::absenceFormUserID,
                $this->getParam('userID')
            );
            $this->dsAbsence->setValue(
                self::absenceFormStartDate,
                date(DATE_MYSQL_DATE)
            );
            $this->dsAbsence->setValue(
                self::absenceFormDays,
                1
            );
        }
        $dsUser = new DataSet($this);
        $this->buUser->getUserByID(
            $this->dsAbsence->getValue(self::absenceFormUserID),
            $dsUser
        );
        $this->template->setVar(
            array(
                'userID'           => $this->dsAbsence->getValue(self::absenceFormUserID),
                'startDate'        => Controller::dateYMDtoDMY($this->dsAbsence->getValue(self::absenceFormStartDate)),
                'startDateMessage' => $this->dsAbsence->getMessage(self::absenceFormStartDate),
                'daysMessage'      => $this->dsAbsence->getMessage(self::absenceFormDays),
                'userName'         => $dsUser->getValue(DBEJUser::name),
            )
        );
        $this->template->set_block(
            'UserAbsenceEdit',
            'daysBlock',
            'records'
        );
        for ($days = 1; $days <= 30; $days++) {

            $daySelected = ($this->dsAbsence->getValue(self::absenceFormDays) == $days) ? CT_SELECTED : null;
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

    function getCurrentUser()
    {
        $dbeJUser = new DBEJUser($this);
        $dbeJUser->setValue(
            DBEJUser::userID,
            $this->dbeUser->getValue(DBEUser::userID)
        );
        $dbeJUser->getRow();
        return json_encode(
            [
                'firstName'                  => $dbeJUser->getValue(DBEJUser::firstName),
                'lastName'                   => $dbeJUser->getValue(DBEJUser::lastName),
                'id'                         => $dbeJUser->getValue(DBEJUser::userID),
                'email'                      => $dbeJUser->getEmail(),
                'isSDManager'                => $this->isSdManager(),
                'isExpenseApprover'          => $dbeJUser->getValue(DBEJUser::isExpenseApprover),
                'globalExpenseApprover'      => $dbeJUser->getValue(DBEJUser::globalExpenseApprover),
                'teamID'                     => $dbeJUser->getValue(DBEJUser::teamID),
                'teamLevel'                  => $dbeJUser->getValue(DBEJUser::teamLevel),
                'serviceRequestQueueManager' => $dbeJUser->getValue(DBEJUser::queueManager),
                'isProjectManager'           => $dbeJUser->getValue(DBEJUser::projectManagementFlag) == 'Y',
            ]
        );
    }

    function getAllUsers()
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows(false);  // include inActive users
        $users = array();
        while ($dbeUser->fetchNext()) {
            array_push(
                $users,
                array(
                    'id'     => $dbeUser->getValue(DBEUser::userID),
                    'name'   => $dbeUser->getValue(DBEUser::name),
                    'teamId' => $dbeUser->getValue(DBEUser::teamID),
                    'active' => $dbeUser->getValue(DBEUser::activeFlag) === 'Y'
                )
            );
        }
        return $users;
    }

    function getActiveUsers()
    {
        $dbeUser = new DBEUser($this);
        $dbeUser->getRows(true);  // include inActive users
        $users = array();
        while ($dbeUser->fetchNext()) {
            array_push(
                $users,
                array(
                    'id'     => $dbeUser->getValue(DBEUser::userID),
                    'name'   => $dbeUser->getValue(DBEUser::name),
                    'teamId' => $dbeUser->getValue(DBEUser::teamID),
                )
            );
        }
        return $users;
    }

    function getUsersByTeamLevel()
    {
        $teamLevel = $_REQUEST["teamLevel"];
        if (isset($teamLevel)) {
            $hdUsers = (new BUUser($this))->getUsersByTeamLevel($teamLevel);
            $users   = array();
            foreach ($hdUsers as $user) {
                array_push(
                    $users,
                    array(
                        'userName' => $user['userName'],
                        'userID'   => $user['cns_consno']
                    )
                );

            }
            return $users;
        } else return [];
    }

    /**
     * Display list of users
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Users');
        $this->setTemplateFiles(
            array('UserList' => 'UserList.inc')
        );
        $dsUser = new DataSet($this);
        $this->buUser->getAllUsers($dsUser);
        $txtCreate = null;
        $urlCreate = null;
        if ($this->hasPermissions(SENIOR_MANAGEMENT_PERMISSION)) {
            $urlCreate = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTUSER_ACT_CREATE
                )
            );
            $txtCreate = 'Create new user';
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
                $userID  = $dsUser->getValue(DBEJUser::userID);
                $urlEdit = null;
                $txtEdit = null;
                if ($this->hasPermissions(SENIOR_MANAGEMENT_PERMISSION)) {

                    $urlEdit = Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTUSER_ACT_EDIT,
                            'userID' => $userID
                        )
                    );
                    $txtEdit = '[Edit]';
                }
                $urlHalfHolidays = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => self::REGISTER_HALF_HOLIDAYS,
                        'userID' => $userID
                    )
                );
                $urlReportAbsent = Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTUSER_ACT_ABSENCE_EDIT,
                        'userID' => $userID
                    )
                );
                $txtReportAbsent = '[Record Absence]';
                $this->template->set_var(
                    array(
                        'userID'          => $userID,
                        'firstName'       => Controller::htmlDisplayText($dsUser->getValue(DBEJUser::firstName)),
                        'lastName'        => Controller::htmlDisplayText($dsUser->getValue(DBEJUser::lastName)),
                        'urlReportAbsent' => $urlReportAbsent,
                        'txtReportAbsent' => $txtReportAbsent,
                        'urlHalfHolidays' => $urlHalfHolidays,
                        'urlEdit'         => $urlEdit,
                        'txtEdit'         => $txtEdit
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

    function saveSettings()
    {
        $body = json_decode(file_get_contents('php://input'));
        if (!isset($body->consID) || !isset($body->type) || !isset($body->settings)) return [
            'status' => false,
            'error'  => 'missed data'
        ];
        //get data first
        $consultant = DBConnect::fetchOne(
            "select * from cons_settings where consno=:id and type=:type",
            ['id' => $body->consID, 'type' => $body->type]
        );
        $result     = false;
        if (!$consultant) // insert new recored
        {
            $result = DBConnect::execute(
                "insert into cons_settings(consno,type,settings) values(:consID,:type,:settings)",
                ['consID' => $body->consID, 'settings' => $body->settings, 'type' => $body->type]
            );
        } else { // update one
            $result = DBConnect::execute(
                "update cons_settings set settings=:settings where consno=:consID and type=:type",
                ['consID' => $body->consID, 'settings' => $body->settings, 'type' => $body->type]
            );
        }
        return ['status' => $result];
    }

    function getSettings()
    {
        $type = $_REQUEST['type'];
        if (!isset($type)) return ['status' => false];
        $userId = $this->dbeUser->getValue(DBEUser::userID);
        $result = DBConnect::fetchOne(
            "select * from cons_settings where type=:type and consno=:userId",
            ['type' => $type, 'userId' => $userId]
        );
        return ['status' => true, 'data' => json_decode($result['settings'])];
    }

    function getMyFeedback()
    {
        $from  = @$_REQUEST['from'] ?? null;
        $to    = @$_REQUEST['to'] ?? null;
        $query = "SELECT       
                    f.id,
                    f.value,     
                    customer.`cus_name`,
                    f.`comments`,
                    DATE_FORMAT(f.`createdAt` , '%d/%m/%Y')   createdAt  ,
                    serviceRequestId problemID    
                FROM `customerfeedback` f 
                    JOIN problem ON problem.`pro_problemno`=f.serviceRequestId
                    JOIN callactivity cal ON cal.caa_problemno=f.serviceRequestId     
                    JOIN customer ON customer.`cus_custno`=problem.`pro_custno`

                WHERE cal.caa_callacttypeno=57
                    AND cal.`caa_consno`=:consID
                    AND (:from is null or f.`createdAt` >= :from )
                    AND (:to is null or f.`createdAt` <= :to)
                order by f.`createdAt` desc";
        return DBConnect::fetchAll($query, ['from' => $from, 'to' => $to, 'consID' => $this->dbeUser->getPKValue()]);
    }
}
