<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 15/02/2019
 * Time: 10:37
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardTextType.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardText.inc.php');


class CTBookSalesVisit extends CTCNC
{

    const searchFormCustomerID      = 'customerID';
    const searchFormAttendees       = 'attendees';
    const searchFormTypeOfMeetingID = 'typeOfMeetingID';
    const searchFormContactID       = 'contactID';
    const searchFormMeetingDate     = 'meetingDate';
    const searchFormMeetingTime     = 'meetingTime';

    const ACTION_BOOK_SALES_VISIT = 'bookSalesVisit';
    /** @var DSForm */
    private $dsSearchForm;

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
        $roles = ACCOUNT_MANAGEMENT_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(407);
        $this->dsSearchForm = new DSForm($this);
        $this->dsSearchForm->addColumn(
            self::searchFormCustomerID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormAttendees,
            DA_ARRAY,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormTypeOfMeetingID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormContactID,
            DA_ID,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormMeetingDate,
            DA_DATE,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            self::searchFormMeetingTime,
            DA_TIME,
            DA_NOT_NULL
        );


    }

    /**
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case self::ACTION_BOOK_SALES_VISIT:
                $this->bookSalesVisit();
                break;
            default:
                $this->showForm();
                break;
        }
    }

    /**
     * @throws Exception
     */
    private function bookSalesVisit()
    {

        if ($this->getParam('form')[0]['attendees']) {
            $this->getParam('form')[0]['attendees'] = json_encode($this->getParam('form')[0]['attendees']);
        }
        if (!$this->dsSearchForm->populateFromArray($this->getParam('form'))) {
            $this->formError = true;
            return $this->showForm();
        }
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        if (!$dsSearchForm->getValue(self::searchFormCustomerID)) {
            $dsSearchForm->setMessage(
                self::searchFormCustomerID,
                'Customer ID is missing, please select a customer'
            );
            $this->formError = true;
        }
        if (!$dsSearchForm->getValue(self::searchFormAttendees)) {
            $dsSearchForm->setMessage(
                self::searchFormAttendees,
                'Attendees is mandatory'
            );
            $this->formError = true;
        }
        if (!$dsSearchForm->getValue(self::searchFormTypeOfMeetingID)) {
            $dsSearchForm->setMessage(
                self::searchFormTypeOfMeetingID,
                'Type of Meeting is mandatory'
            );
            $this->formError = true;
        }
        if (!$dsSearchForm->getValue(self::searchFormContactID)) {
            $dsSearchForm->setMessage(
                self::searchFormContactID,
                'Contact is mandatory, please select a contact'
            );
            $this->formError = true;
        }
        if (!$dsSearchForm->getValue(self::searchFormMeetingDate)) {
            $dsSearchForm->setMessage(
                self::searchFormMeetingDate,
                'Meeting date is mandatory'
            );
            $this->formError = true;
        }
        if (!$dsSearchForm->getValue(self::searchFormMeetingTime)) {
            $dsSearchForm->setMessage(
                self::searchFormMeetingTime,
                'meeting time is mandatory'
            );
            $this->formError = true;
        }
        if ($this->formError) {
            return $this->showForm();
        }
        $buActivity = new BUActivity($this);
        $dbeContact = new DBEContact($this);
        $dbeContact->getRow($dsSearchForm->getValue(self::searchFormContactID));
        $dbeProblem = new DBEProblem($this);
        $customerID = $dsSearchForm->getValue(self::searchFormCustomerID);
        $siteNo = $dbeContact->getValue(DBEContact::siteNo);
        $dbeProblem->setValue(DBEProblem::emailSubjectSummary, "Account Review Meeting");
        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::smallProjectsTeamLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::smallProjectsTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::projectTeamLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::projectTeamLimitMinutes)
        );
        $dbeProblem->setValue(DBEProblem::raiseTypeId, 7);
        $buStandardText = new BUStandardText($this);
        $buStandardText->getStandardTextByID(129, $dbeStandardText);
        $dbeProblem->setValue(DBEProblem::emptyAssetReason, $dbeStandardText->getValue(DBEStandardText::stt_text));
        $dbeProblem->setValue(
            DBEProblem::slaResponseHours,
            $buActivity->getSlaResponseHours(
                5,
                $customerID,
                $dbeContact->getValue(DBEContact::contactID)
            )
        );
        $dbeProblem->setValue(
            DBEProblem::customerID,
            $customerID
        );
        $dbeProblem->setValue(
            DBEProblem::status,
            'P'
        );
        $dbeProblem->setValue(
            DBEProblem::priority,
            5
        );
        $dbeProblem->setValue(
            DBEProblem::dateRaised,
            date(DATE_MYSQL_DATETIME)
        );
        $dbeProblem->setValue(
            DBEProblem::alarmDate,
            $dsSearchForm->getValue(self::searchFormMeetingDate)
        );
        $dbeProblem->setValue(
            DBEProblem::alarmTime,
            $dsSearchForm->getValue(self::searchFormMeetingTime)
        );
        $dbeProblem->setValue(
            DBEProblem::contactID,
            $dbeContact->getValue(DBEContact::contactID)
        );
        $dbeProblem->setValue(
            DBEJProblem::hideFromCustomerFlag,
            'Y'
        );
        $dbeProblem->setValue(
            DBEJProblem::queueNo,
            4
        );
        $dbeProblem->setValue(
            DBEJProblem::rootCauseID,
            59
        );
        $dbeProblem->setValue(
            DBEJProblem::awaitingCustomerResponseFlag,
            'N'
        );
        $dbeProblem->setValue(
            DBEJProblem::userID,
            $this->dbeUser->getValue(DBEUser::userID)
        );
        $dbeProblem->insertRow();
        $dbeCallActivity = new DBECallActivity($this);
        $dbeCallActivity->setValue(DBEJCallActivity::curValue, '0.00');
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActivityID,
            0
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::siteNo,
            $siteNo
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::contactID,
            $dbeContact->getValue(DBEContact::contactID)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::callActTypeID,
            CONFIG_INITIAL_ACTIVITY_TYPE_ID
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::date,
            date(DATE_MYSQL_DATE)
        );
        $startTime = date('H:i');
        $dbeCallActivity->setValue(
            DBEJCallActivity::startTime,
            $startTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::endTime,
            $startTime
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::status,
            'C'
        );
        $standardText = new DBEStandardText($this);
        $standardText->getRow($this->dsSearchForm->getValue(self::searchFormTypeOfMeetingID));
        $dbeCallActivity->setValue(
            DBEJCallActivity::reason,
            $standardText->getValue(DBEStandardText::stt_text)
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::problemID,
            $dbeProblem->getPKValue()
        );
        $dbeCallActivity->setValue(
            DBEJCallActivity::userID,
            $this->dbeUser->getValue(DBEUser::userID)
        );
        $dbeCallActivity->insertRow();
        $attendeesJSON = $dsSearchForm->getValue(self::searchFormAttendees);
        $attendees     = $attendeesJSON;
        if (!is_array($attendeesJSON)) {
            $attendees = json_decode($attendeesJSON);
        }
        $firstActivityCreated = null;
        foreach ($attendees as $attendee) {
            $dbeCallActivity->setValue(
                DBEJCallActivity::callActivityID,
                0
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::siteNo,
                $siteNo
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::contactID,
                $dbeContact->getValue(DBEContact::contactID)
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::callActTypeID,
                7
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::date,
                $dsSearchForm->getValue(self::searchFormMeetingDate)
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::startTime,
                $dsSearchForm->getValue(self::searchFormMeetingTime)
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::endTime,
                null
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::status,
                'O'
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::awaitingCustomerResponseFlag,
                'N'
            );
            $standardText = new DBEStandardText($this);
            $standardText->getRow($this->dsSearchForm->getValue(self::searchFormTypeOfMeetingID));
            $dbeCallActivity->setValue(
                DBEJCallActivity::reason,
                $standardText->getValue(DBEStandardText::stt_text)
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::problemID,
                $dbeProblem->getPKValue()
            );
            $dbeCallActivity->setValue(
                DBEJCallActivity::userID,
                $attendee
            );
            $dbeCallActivity->insertRow();
            if (!$firstActivityCreated) {
                $firstActivityCreated = $dbeCallActivity->getValue(DBECallActivity::callActivityID);
            }
        }
        $this->dsSearchForm->clear();
        $this->setParam('booked', $firstActivityCreated);
        return $this->showForm();
    }

    /**
     * @throws Exception
     */
    function showForm()
    {
        $customerString   = null;
        $urlCustomerPopup = null;
        $dsSearchForm     = &$this->dsSearchForm; // ref to global
        $bookedActivity   = null;
        if ($this->getParam('booked')) {
            $bookedActivity = $this->getParam('booked');
        }
        $this->setTemplateFiles(
            'BookSalesVisit',
            'BookSalesVisit'
        );
        $urlSubmit = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => self::ACTION_BOOK_SALES_VISIT
            )
        );
        $this->setPageTitle('Create Sales Meeting Booking ');
        if ($dsSearchForm->getValue(self::searchFormCustomerID) != 0) {
            $buCustomer = new BUCustomer($this);
            $dsCustomer = new DataSet($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue(self::searchFormCustomerID),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $contactString = '';
        if ($dsSearchForm->getValue(self::searchFormContactID)) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($dsSearchForm->getValue(self::searchFormContactID));
            $contactString = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
        }
        $bookedActivityURL = '';
        if ($bookedActivity) {
            $bookedActivityURL = Controller::buildLink(
                'SRActivity.php',
                array(
                    'action'         => 'displayActivity',
                    'callActivityID' => $bookedActivity
                )
            );
        }
        $this->template->set_var(
            array(
                'formError'              => $this->formError,
                'customerID'             => $this->dsSearchForm->getValue(self::searchFormCustomerID),
                'customerIDMessage'      => $this->dsSearchForm->getMessage(self::searchFormCustomerID),
                'attendees'              => $this->dsSearchForm->getValue(self::searchFormAttendees),
                'attendeesMessage'       => $this->dsSearchForm->getMessage(self::searchFormAttendees),
                'typeOfMeetingIDMessage' => $this->dsSearchForm->getMessage(self::searchFormTypeOfMeetingID),
                'contactID'              => $this->dsSearchForm->getValue(self::searchFormContactID),
                'contactIDMessage'       => $this->dsSearchForm->getMessage(self::searchFormContactID),
                'contactString'          => $contactString,
                'meetingDate'            => $this->dsSearchForm->getValue(self::searchFormMeetingDate),
                'meetingDateMessage'     => $this->dsSearchForm->getMessage(self::searchFormMeetingDate),
                'meetingTime'            => $this->dsSearchForm->getValue(self::searchFormMeetingTime),
                'meetingTimeMessage'     => $this->dsSearchForm->getMessage(self::searchFormMeetingTime),
                'customerString'         => $customerString,
                'urlCustomerPopup'       => $urlCustomerPopup,
                'urlSubmit'              => $urlSubmit,
                'bookedActivityURL'      => $bookedActivityURL
            )
        );
        // activity type selector
        $this->template->set_block(
            'BookSalesVisit',
            'attendeesBlock',
            'attendees'
        );
        $dbeUser = new DBEUser($this);
        $dbeUser->getActiveWithPermission(ACCOUNT_MANAGEMENT_PERMISSION);
        $selectedAttendees = [];
        if ($this->dsSearchForm->getValue(self::searchFormAttendees)) {
            $selectedAttendees = json_decode(
                $this->dsSearchForm->getValue(self::searchFormAttendees)
            );
        }
        while ($dbeUser->fetchNext()) {
            $selected = in_array(
                $dbeUser->getValue(DBEUser::userID),
                $selectedAttendees
            );
            $this->template->setVar(
                [
                    'attendeeID'       => $dbeUser->getValue(DBEUser::userID),
                    'attendeeName'     => $dbeUser->getValue(DBEUser::name),
                    'attendeeSelected' => $selected ? 'selected' : ''
                ]
            );
            $this->template->parse(
                'attendees',
                'attendeesBlock',
                true
            );
        }
        $dbeStandardTextType = new DBEStandardTextType($this);
        $dbeStandardTextType->setValue(
            DBEStandardTextType::description,
            'Sales Meeting Type'
        );
        $dbeStandardTextType->getRowsByColumn(DBEStandardTextType::description);
        $dbeStandardTextType->fetchNext();
        $standardTextTypeID = $dbeStandardTextType->getValue(DBEStandardTextType::standardTextTypeID);
        $DBEStandardText = new DBEStandardText($this);
        $DBEStandardText->getRowsByTypeID($standardTextTypeID);
        $this->template->set_block(
            'BookSalesVisit',
            'typeOfMeetingBlock',
            'typeOfMeetings'
        );
        while ($DBEStandardText->fetchNext()) {

            $selected = $this->dsSearchForm->getValue(self::searchFormTypeOfMeetingID) == $DBEStandardText->getValue(
                    DBEStandardText::stt_standardtextno
                );
            $this->template->setVar(
                [
                    'typeOfMeetingID'          => $DBEStandardText->getValue(DBEStandardText::stt_standardtextno),
                    'typeOfMeetingDescription' => $DBEStandardText->getValue(DBEStandardText::stt_desc),
                    'typeOfMeetingSelected'    => $selected ? 'selected' : ''
                ]
            );
            $this->template->parse(
                'typeOfMeetings',
                'typeOfMeetingBlock',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'BookSalesVisit',
            true
        );
        $this->parsePage();
    }
}