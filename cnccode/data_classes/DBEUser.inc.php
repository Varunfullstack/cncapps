<?php
/*
* User table
* @authors Karim Ahmed
* @access public
*/
global $cfg;
require_once($cfg["path_gc"] . "/DBEntity.inc.php");

class DBEUser extends DBEntity
{
    CONST userID = "userID";
    CONST managerID = "managerID";
    CONST name = "name";
    CONST salutation = "salutation";
    CONST add1 = "add1";
    CONST add2 = "add2";
    CONST add3 = "add3";
    CONST town = "town";
    CONST county = "county";
    CONST postcode = "postcode";
    CONST username = "username";
    CONST employeeNo = "employeeNo";
    CONST petrolRate = "petrolRate";
    CONST perms = "perms";
    CONST signatureFilename = "signatureFilename";
    CONST jobTitle = "jobTitle";
    CONST firstName = "firstName";
    CONST lastName = "lastName";
    CONST activeFlag = "activeFlag";
    CONST weekdayOvertimeFlag = "weekdayOvertimeFlag";
    CONST helpdeskFlag = "helpdeskFlag";
    CONST customerID = "customerID";
    CONST hourlyPayRate = "hourlyPayRate";
    CONST teamID = "teamID";
    CONST receiveSdManagerEmailFlag = "receiveSdManagerEmailFlag";
    CONST changePriorityFlag = "changePriorityFlag";
    CONST appearInQueueFlag = "appearInQueueFlag";
    CONST standardDayHours = "standardDayHours";
    CONST changeApproverFlag = "changeApproverFlag";
    const admin = 'admin';
    const excludeFromStatsFlag = "excludeFromStatsFlag";
    const changeInitialDateAndTimeFlag = 'changeInitialDateAndTimeFlag';
    const projectManagementFlag = 'projectManagementFlag';

    const encryptedDateOfBirth = "encryptedDateOfBirth";
    const startDate = "startDate";
    const companyHealthcareStartDate = "companyHealthcareStartDate";
    const enhancedCNC2YearPensionStartDate = "enhancedCNC2YearPensionStartDate";
    const encryptedPensionAdditionalPayments = "encryptedPensionAdditionalPayments";
    const encryptedSalary = "encryptedSalary";
    const encryptedSalarySacrifice = "encryptedSalarySacrifice";
    const hoursWorkedInAWeek = "hoursWorkedInAWeek";
    const encryptedNationalInsuranceNumber = "encryptedNationalInsuranceNumber";
    const encryptedAddress1 = "encryptedAddress1";
    const encryptedAddress2 = "encryptedAddress2";
    const encryptedAddress3 = "encryptedAddress3";
    const encryptedTown = "encryptedTown";
    const encryptedCounty = "encryptedCounty";
    const encryptedPostcode = "encryptedPostcode";
    const staffAppraiserFlag = 'staffAppraiserFlag';
    const passwordLevel = 'passwordLevel';
    const changeSRContractsFlag = 'changeSRContractsFlag';
    const starterLeaverQuestionManagementFlag = 'starterLeaverQuestionManagementFlag';
    const offsiteBackupAdditionalPermissionsFlag = 'offsiteBackupAdditionalPermissionsFlag';
    const salesPasswordAccess = 'salesPasswordAccess';

    /**
     * calls constructor()
     * @access public
     *
     * @param $owner
     * @see constructor()
     */
    function __construct(&$owner)
    {
        parent::__construct($owner);
        $this->setTableName("consultant");
        $this->addColumn(
            self::userID,
            DA_ID,
            DA_NOT_NULL,
            "cns_consno"
        );
        $this->addColumn(
            self::managerID,
            DA_ID,
            DA_ALLOW_NULL,
            "cns_manager"
        );
        $this->addColumn(
            self::name,
            DA_STRING,
            DA_NOT_NULL,
            "cns_name"
        );
        $this->addColumn(
            self::salutation,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_salutation"
        );
        $this->addColumn(
            self::add1,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_add1"
        );
        $this->addColumn(
            self::add2,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_add2"
        );
        $this->addColumn(
            self::add3,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_add3"
        );
        $this->addColumn(
            self::town,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_town"
        );
        $this->addColumn(
            self::county,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_county"
        );
        $this->addColumn(
            self::postcode,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_postcode"
        );
        $this->addColumn(
            self::username,
            DA_STRING,
            DA_NOT_NULL,
            "cns_logname"
        );
        $this->addColumn(
            self::employeeNo,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_employee_no"
        );
        $this->addColumn(
            self::petrolRate,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "cns_petrol_rate"
        );
        $this->addColumn(
            self::perms,
            DA_STRING,
            DA_ALLOW_NULL,
            "cns_perms"
        );
        $this->addColumn(
            self::signatureFilename,
            DA_STRING,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::jobTitle,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::firstName,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::lastName,
            DA_STRING,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::activeFlag,
            DA_YN,
            DA_NOT_NULL,
            'consultant.activeFlag'
        );
        $this->addColumn(
            self::weekdayOvertimeFlag,
            DA_YN,
            DA_NOT_NULL
        ); // does user get overtime in weekdays
        $this->addColumn(
            self::helpdeskFlag,
            DA_YN,
            DA_NOT_NULL,
            'cns_helpdesk_flag'
        ); // does user get overtime in weekdays
        $this->addColumn(
            self::customerID,
            DA_ID,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::hourlyPayRate,
            DA_FLOAT,
            DA_ALLOW_NULL,
            "cns_hourly_pay_rate"
        );
        $this->addColumn(
            self::teamID,
            DA_ID,
            DA_ALLOW_NULL,
            'consultant.teamID'
        );
        $this->addColumn(
            self::receiveSdManagerEmailFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::changePriorityFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::appearInQueueFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::standardDayHours,
            DA_FLOAT,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::changeApproverFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::excludeFromStatsFlag,
            DA_YN,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::projectManagementFlag,
            DA_YN,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::changeInitialDateAndTimeFlag,
            DA_YN,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::encryptedDateOfBirth,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::startDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::companyHealthcareStartDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::enhancedCNC2YearPensionStartDate,
            DA_DATE,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedPensionAdditionalPayments,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedSalary,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedSalarySacrifice,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::hoursWorkedInAWeek,
            DA_INTEGER,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedNationalInsuranceNumber,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedAddress1,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedAddress2,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedAddress3,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedTown,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedCounty,
            DA_TEXT,
            DA_ALLOW_NULL
        );
        $this->addColumn(
            self::encryptedPostcode,
            DA_TEXT,
            DA_ALLOW_NULL
        );

        $this->addColumn(
            self::staffAppraiserFlag,
            DA_YN,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::passwordLevel,
            DA_INTEGER,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::changeSRContractsFlag,
            DA_YN,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::starterLeaverQuestionManagementFlag,
            DA_YN,
            DA_NOT_NULL
        );

        $this->addColumn(
            self::offsiteBackupAdditionalPermissionsFlag,
            DA_YN,
            DA_NOT_NULL
        );
        $this->addColumn(
            self::salesPasswordAccess,
            DA_BOOLEAN,
            DA_NOT_NULL,
            null,
            false
        );

        $this->setPK(0);
        $this->setAddColumnsOff();
    }

    function getRows($activeOnly = true)
    {

        $this->setMethodName("getRows");

        $queryString =
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " JOIN team ON team.teamID = consultant.teamID";

        if ($activeOnly) {
            $queryString .= ' WHERE consultant.activeFlag = "Y"';
        }

        $queryString .= ' ORDER BY firstName, lastName';

        $this->setQueryString($queryString);

        return (parent::getRows());
    }

    function getRowsInGroup($group)
    {

        $this->setMethodName("getRowsInGroup");

        $this->setQueryString(
            "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE activeFlag = 'Y'" .
            " AND cns_perms LIKE '%" . $group . "%'"

        );

        return (parent::getRows());
    }

    function getPermission($page)
    {
        $sql = "select * from permissions inner join " . $this->getTableName() . " on " . $this->getPKName(
            ) . " = permissions.userID where page = '$page'";
        $this->setQueryString($sql);

        return (parent::getRows());
    }

    function getActiveUsers()
    {
        $this->setMethodName("getRowsInGroup");

        $query = "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE activeFlag = 'Y' ORDER BY firstName, lastName";

        $this->setQueryString($query);

        return (parent::getRows());
    }

    public function getAppraisalUsers()
    {
        $ignoredUsers = [67, 97, 111, 115];
        $query = "SELECT " . $this->getDBColumnNamesAsString() .
            " FROM " . $this->getTableName() .
            " WHERE " . $this->getDBColumnName(
                self::userID
            ) . " not in (select distinct managers." . $this->getDBColumnName(
                self::managerID
            ) . " from " . $this->tableName . " managers) and " .
            $this->getDBColumnName(self::activeFlag) . " = 'Y' and " . $this->getDBColumnName(
                self::userID
            ) . " not in (" . implode(
                ',',
                $ignoredUsers
            ) . ")";
        $this->setQueryString($query);
        return parent::getRows();
    }
}

?>