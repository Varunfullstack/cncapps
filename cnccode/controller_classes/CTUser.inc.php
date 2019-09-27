<?php
/**
 * User controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Encryption;

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
    const dateOfBirth = "dateOfBirth";
    const startDate = "startDate";
    const pensionAdditionalPayments = "pensionAdditionalPayments";
    const salary = "salary";
    const salarySacrifice = "salarySacrifice";
    const nationalInsuranceNumber = "nationalInsuranceNumber";
    const address1 = "address1";
    const address2 = "address2";
    const address3 = "address3";
    const town = "town";
    const county = "county";
    const postcode = "postcode";

    const absenceFormUserID = "userID";
    const absenceFormStartDate = "startDate";
    const absenceFormDays = "days";


    const DECRYPT = 'decrypt';
    const GetAge = 'getAge';
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
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buUser = new BUUser($this);
        $this->dsUser = new DSForm($this);
        $this->dsUser->copyColumnsFrom($this->buUser->dbeUser, false);
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
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
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
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTUSER_ACT_DELETE,
                        'userID' => $userID
                    )
                );
            $txtDelete = 'Delete';
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


        $this->template->setVar(
            array(
                'userID'                                     => $dsUser->getValue(DBEJUser::userID),
                'name'                                       => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::name)
                ),
                'nameMessage'                                => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::name)
                ),
                'salutation'                                 => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::salutation)
                ),
                'salutationMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::salutation)
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
                'add1'                                       => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::add1)
                ),
                'add1Message'                                => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::add1)
                ),
                'add2'                                       => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::add2)
                ),
                'add3'                                       => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::add3)
                ),
                'town'                                       => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::town)
                ),
                'townMessage'                                => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::town)
                ),
                'county'                                     => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::county)
                ),
                'postcode'                                   => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::postcode)
                ),
                'postcodeMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::postcode)
                ),
                'username'                                   => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::username)
                ),
                'usernameMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::username)
                ),
                'employeeNo'                                 => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::employeeNo)
                ),
                'employeeNoMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::employeeNo)
                ),
                'jobTitle'                                   => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::jobTitle)
                ),
                'jobTitleMessage'                            => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::jobTitle)
                ),
                'petrolRate'                                 => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::petrolRate)
                ),
                'petrolRateMessage'                          => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::petrolRate)
                ),
                'hourlyPayRate'                              => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::hourlyPayRate)
                ),
                'hourlyPayRateMessage'                       => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::hourlyPayRate)
                ),
                'standardDayHours'                           => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::standardDayHours)
                ),
                'standardDayHoursMessage'                    => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::standardDayHours)
                ),
                'signatureFilename'                          => Controller::htmlInputText(
                    $dsUser->getValue(DBEJUser::signatureFilename)
                ),
                'signatureFilenameMessage'                   => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::signatureFilename)
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
                "isExpenseApproverChecked"                   => $dsUser->getValue(
                    DBEUser::isExpenseApprover
                ) ? 'checked' : null,
                'receiveSdManagerEmailFlagChecked'           => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::receiveSdManagerEmailFlag)
                ),
                'autoApproveExpensesChecked'                 => $dsUser->getValue(
                    DBEJUser::autoApproveExpenses
                ) ? 'checked' : null,
                'appearInQueueFlagChecked'                   => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::appearInQueueFlag)
                ),
                'changePriorityFlagChecked'                  => Controller::htmlChecked(
                    $dsUser->getValue(
                        DBEJUser::changePriorityFlag
                    )
                ),
                'weekdayOvertimeFlagChecked'                 => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::weekdayOvertimeFlag)
                ),
                'helpdeskFlagChecked'                        => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::helpdeskFlag)
                ),

                'salesChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_SALES
                    ) !== FALSE) ? CT_CHECKED : null,

                'accountsChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_ACCOUNTS
                    ) !== FALSE) ? CT_CHECKED : null,

                'technicalChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_TECHNICAL
                    ) !== FALSE) ? CT_CHECKED : null,

                'supervisorChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_SUPERVISOR
                    ) !== FALSE) ? CT_CHECKED : null,

                'maintenanceChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_MAINTENANCE
                    ) !== FALSE) ? CT_CHECKED : null,

                'customerChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_CUSTOMER
                    ) !== FALSE) ? CT_CHECKED : null,

                'renewalsChecked' => (strpos(
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_RENEWALS
                    ) !== FALSE) ? CT_CHECKED : null,

                'changeApproverFlagChecked'                     => Controller::htmlChecked(
                    $dsUser->getValue(DBEJUser::changeApproverFlag)
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
                        $dsUser->getValue(DBEJUser::perms),
                        PHPLIB_PERM_REPORTS
                    ) !== FALSE) ? CT_CHECKED : null,
                'teamMessage'                                   => Controller::htmlDisplayText(
                    $dsUser->getMessage(DBEJUser::teamID)
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

        // customer selection
        $dbeCustomer = new DBECustomer($this);
        $dbeCustomer->getRows(DBECustomer::name);
        $this->template->set_block(
            'UserEdit',
            'customerBlock',
            'customers'
        );
        while ($dbeCustomer->fetchNext()) {
            $customerSelected = ($dsUser->getValue(DBEJUser::customerID) == $dbeCustomer->getValue(
                    DBECustomer::customerID
                )) ? CT_SELECTED : null;
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
        $userData = $this->getParam('user')[1];
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
            $encryptedValue = $this->dsUser->getValue($encryptedKeyName);
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
                    $this->dsAbsence->getValue(self::absenceFormDays)
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

        if ($this->hasPermissions(PHPLIB_PERM_ACCOUNTS)) {
            $urlCreate =
                Controller::buildLink(
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
                $userID = $dsUser->getValue(DBEJUser::userID);

                $urlEdit = null;
                $txtEdit = null;
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

                        'firstName' => Controller::htmlDisplayText($dsUser->getValue(DBEJUser::firstName)),

                        'lastName' => Controller::htmlDisplayText($dsUser->getValue(DBEJUser::lastName)),

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
}
