<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 15/02/2019
 * Time: 10:37
 */

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardTextType.inc.php');
require_once($cfg['path_dbe'] . '/DBEStandardText.inc.php');


class CTBookSalesVisit extends CTCNC
{
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

        $this->dsSearchForm = new DSForm($this);
        $this->dsSearchForm->addColumn(
            'customerID',
            DA_ID,
            DA_NOT_NULL
        );

        $this->dsSearchForm->addColumn(
            'attendees',
            DA_ARRAY,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            'typeOfMeetingID',
            DA_ID,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            'contactID',
            DA_ID,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            'meetingDate',
            DA_DATE,
            DA_NOT_NULL
        );
        $this->dsSearchForm->addColumn(
            'meetingTime',
            DA_TIME,
            DA_NOT_NULL
        );

        $roles = [
            "sales",
        ];

        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            case self::ACTION_BOOK_SALES_VISIT:
                $this->bookSalesVisit();
                break;
            default:
                $this->showForm();
                break;
        }
    }

    function showForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $bookedActivity = null;
        if (isset($_REQUEST['booked'])) {
            $bookedActivity = $_REQUEST['booked'];
        }

        if (!$this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $urlCustomerPopup = $this->buildLink(
                CTCNC_PAGE_CUSTOMER,
                array(
                    'action'  => CTCNC_ACT_DISP_CUST_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        }


        $this->setTemplateFiles(
            'BookSalesVisit',
            'BookSalesVisit'
        );

        $urlSubmit = $this->buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => self::ACTION_BOOK_SALES_VISIT
            )
        );
        $this->setPageTitle('Create Sales Meeting Booking ');
        if ($dsSearchForm->getValue('customerID') != 0) {
            $buCustomer = new BUCustomer($this);
            $buCustomer->getCustomerByID(
                $dsSearchForm->getValue('customerID'),
                $dsCustomer
            );
            $customerString = $dsCustomer->getValue(DBECustomer::name);
        }
        $contactString = '';
        if ($dsSearchForm->getValue('contactID')) {
            $dbeContact = new DBEContact($this);
            $dbeContact->getRow($dsSearchForm->getValue('contactID'));
            $contactString = $dbeContact->getValue(DBEContact::firstName) . " " . $dbeContact->getValue(
                    DBEContact::lastName
                );
        }
        $bookedActivityURL = '';
        if ($bookedActivity) {
            $bookedActivityURL = $this->buildLink(
                'Activity.php',
                array(
                    'action'         => 'displayActivity',
                    'callActivityID' => $bookedActivity
                )
            );
        }

        $this->template->set_var(
            array(
                'formError'              => $this->formError,
                'customerID'             => $this->dsSearchForm->getValue('customerID'),
                'customerIDMessage'      => $this->dsSearchForm->getMessage('customerID'),
                'attendees'              => $this->dsSearchForm->getValue('attendees'),
                'attendeesMessage'       => $this->dsSearchForm->getMessage('attendees'),
                'typeOfMeetingIDMessage' => $this->dsSearchForm->getMessage('typeOfMeetingID'),
                'contactID'              => $this->dsSearchForm->getValue('contactID'),
                'contactIDMessage'       => $this->dsSearchForm->getMessage('contactID'),
                'contactString'          => $contactString,
                'meetingDate'            => $this->dsSearchForm->getValue('meetingDate'),
                'meetingDateMessage'     => $this->dsSearchForm->getMessage('meetingDate'),
                'meetingTime'            => $this->dsSearchForm->getValue('meetingTime'),
                'meetingTimeMessage'     => $this->dsSearchForm->getMessage('meetingTime'),
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
        $dbeUser->getActiveUsers();

        $selectedAttendees = [];

        if ($this->dsSearchForm->getValue('attendees')) {
            $selectedAttendees = json_decode(
                $this->dsSearchForm->getValue('attendees')
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

            $selected = $this->dsSearchForm->getValue('typeOfMeetingID') == $DBEStandardText->getValue(
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

    private function bookSalesVisit()
    {

        if ($_REQUEST['form'][0]['attendees']) {
            $_REQUEST['form'][0]['attendees'] = json_encode($_REQUEST['form'][0]['attendees']);
        }

        if (!$this->dsSearchForm->populateFromArray($_REQUEST['form'])) {
            $this->formError = true;
            return $this->showForm();
        }

        $dsSearchForm = &$this->dsSearchForm; // ref to global

        if (!$dsSearchForm->getValue('customerID')) {
            $dsSearchForm->setMessage(
                'customerID',
                'Customer ID is missing, please select a customer'
            );
            $this->formError = true;
        }

        if (!$dsSearchForm->getValue('attendees')) {
            $dsSearchForm->setMessage(
                'attendees',
                'Attendees is mandatory'
            );
            $this->formError = true;
        }

        if (!$dsSearchForm->getValue('typeOfMeetingID')) {
            $dsSearchForm->setMessage(
                'typeOfMeetingID',
                'Type of Meeting is mandatory'
            );
            $this->formError = true;
        }

        if (!$dsSearchForm->getValue('contactID')) {
            $dsSearchForm->setMessage(
                'contactID',
                'Contact is mandatory, please select a contact'
            );
            $this->formError = true;
        }

        if (!$dsSearchForm->getValue('meetingDate')) {
            $dsSearchForm->setMessage(
                'meetingDate',
                'Meeting date is mandatory'
            );
            $this->formError = true;
        }

        if (!$dsSearchForm->getValue('meetingTime')) {
            $dsSearchForm->setMessage(
                'meetingTime',
                'meeting time is mandatory'
            );
            $this->formError = true;
        }

        if ($this->formError) {
            return $this->showForm();
        }

        $buActivity = new BUActivity($this);


        $dbeContact = new DBEContact($this);

        $dbeContact->getRow($dsSearchForm->getValue('contactID'));

        $dbeProblem = new DBEProblem($this);
        $customerID = $dsSearchForm->getValue('customerID');

        $siteNo = $dbeContact->getValue(DBEContact::siteNo);


        $dbeProblem->setValue(
            DBEProblem::hdLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::hdTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::esLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::esTeamLimitMinutes)
        );
        $dbeProblem->setValue(
            DBEProblem::imLimitMinutes,
            $buActivity->dsHeader->getValue(DBEHeader::imTeamLimitMinutes)
        );
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
            date(CONFIG_MYSQL_DATETIME)
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
            7
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
            date(CONFIG_MYSQL_DATE)
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

        $standardText->getRow($this->dsSearchForm->getValue('typeOfMeetingID'));

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

        $attendeesJSON = $dsSearchForm->getValue('attendees');

        $attendees = json_decode($attendeesJSON);

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
                $dsSearchForm->getValue('meetingDate')
            );

            $dbeCallActivity->setValue(
                DBEJCallActivity::startTime,
                $dsSearchForm->getValue('meetingTime')
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

            $standardText->getRow($this->dsSearchForm->getValue('typeOfMeetingID'));

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
        $_REQUEST['booked'] = $firstActivityCreated;
        return $this->showForm();
    }
}