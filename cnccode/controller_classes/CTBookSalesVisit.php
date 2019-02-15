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

class CTBookSalesVisit extends CTCNC
{
    const ACTION_CREATE_BOOK_SALES_VISIT = 'createBookSalesVisit';
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

            case self::ACTION_CREATE_BOOK_SALES_VISIT:
                break;
            default:
                $this->showForm();
                break;
        }
    }

    function showForm()
    {
        $dsSearchForm = &$this->dsSearchForm; // ref to global
        $dsSearchResults = &$this->dsSearchResults; // ref to global

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
                'action' => self::ACTION_CREATE_BOOK_SALES_VISIT
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

        $this->template->set_var(
            array(
                'formError'        => $this->formError,
                'customerID'       => $dsSearchForm->getValue('customerID'),
                'customerString'   => $customerString,
                'urlCustomerPopup' => $urlCustomerPopup,
                'urlSubmit'        => $urlSubmit
            )
        );
        // activity status selector
        $this->template->set_block(
            'BookSalesVisit',
            'statusBlock',
            'statuss'
        ); // ss avoids naming confict!
        if ($this->hasPermissions(PHPLIB_PERM_CUSTOMER)) {
            $statusArray = &$this->statusArrayCustomer;
        } else {
            $statusArray = &$this->statusArray;
        }

        foreach ($statusArray as $key => $value) {
            $statusSelected = ($dsSearchForm->getValue('status') == $key) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'statusSelected'    => $statusSelected,
                    'status'            => $key,
                    'statusDescription' => $value
                )
            );
            $this->template->parse(
                'statuss',
                'statusBlock',
                true
            );
        }


        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->setValue(
            'activeFlag',
            'Y'
        );
        $dbeCallActType->getRowsByColumn(
            'activeFlag',
            'description'
        );

        // activity type selector
        $this->template->set_block(
            'BookSalesVisit',
            'attendeesBlock',
            'attendees'
        );

        $dbeUser = new DBEUser($this);
        $dbeUser->getActiveUsers();

        while ($dbeUser->fetchNext()) {

            $this->template->setVar(
                [
                    'attendeeID'   => $dbeUser->getValue(DBEUser::name),
                    'attendeeName' => $dbeUser->getValue(DBEUser::name),
                ]
            );

            $this->template->parse(
                'attendees',
                'attendeesBlock',
                true
            );
        }


        $this->template->set_block(
            'BookSalesVisit',
            'typeOfMeetingBlock',
            'typeOfMeetings'
        );

        $dbeStandardTextType = new DBEStandardTextType($this);

        $dbeStandardTextType->setValue(
            DBEStandardTextType::description,
            'Sales Meeting Type'
        );
        $dbeStandardTextType->getRowsByColumn(DBEStandardTextType::description);

        $standardTextTypeID = $dbeStandardTextType->getValue(DBEStandardTextType::standardTextTypeID);

        $DBEStandardText = new DBEStandardText($this);
        $DBEStandardText->getRowsByTypeID($standardTextTypeID);

        while ($DBEStandardText->fetchNext()) {
            $this->template->setVar(
                [
                    'typeOfMeetingID'          => $dbeUser->getValue(DBEUser::name),
                    'typeOfMeetingDescription' => $dbeUser->getValue(DBEUser::name),
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