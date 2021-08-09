<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 05/12/2018
 * Time: 12:43
 */

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerReviewMeeting.inc.php');

class CTCustomerReviewMeetingsReport extends CTCNC
{
    /**
     * Dataset for item record storage.
     *
     * @var     DSForm
     * @access  private
     */
    var $dsItem;
    /** @var BUCustomerReviewMeeting */
    private $buCustomerReviewMeetings;

    function __construct(
        $requestMethod,
        $postVars,
        $getVars,
        $cookieVars,
        $cfg
    ) {
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
        $this->setMenuId(404);
        $this->buCustomerReviewMeetings = new BUCustomerReviewMeeting($this);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            default:
                $this->display();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function display()
    {
        $this->setPageTitle("Customer Review Meetings");
        $this->setTemplateFiles(
            'CustomerReviewMeetings',
            'CustomerReviewMeetingsReport'
        );

        $dbeCustomer = new DBECustomer($this);

        $dbeCustomer->setValue(DBECustomer::referredFlag, false);

        $dbeCustomer->getReviewMeetingCustomers();

        $customerReviewMeetings = [];

        while ($dbeCustomer->fetchNext()) {
            $BUSite = new BUSite($this);
            $activeCustomerSites = new DataSet($this);
            $BUSite->getActiveSitesByCustomer($dbeCustomer->getValue(DBECustomer::customerID), $activeCustomerSites);
            $activeCustomerSites->fetchNext();


            $lastReviewMeetingDate = DateTime::createFromFormat(
                'Y-m-d',
                $dbeCustomer->getValue(
                    DBECustomer::lastReviewMeetingDate
                )
            );
            $nextReviewMeetingDate = (clone $lastReviewMeetingDate)->add(
                new DateInterval('P' . $dbeCustomer->getValue(DBECustomer::reviewMeetingFrequencyMonths) . 'M')
            );

            $buCustomer = new BUCustomer($this);
            $reviewContacts = $buCustomer->getReviewContacts($dbeCustomer->getValue(DBECustomer::customerID));

            $reviewContactsString = array_reduce(
                $reviewContacts,
                function (
                    $acc,
                    $item
                ) {
                    if (strlen($acc)) {
                        $acc .= ", ";
                    }
                    $acc .= $item["firstName"] . " " . $item["lastName"];
                    return $acc;
                },
                ''
            );

            $now = new DateTime();
            $style = null;
            if ($dbeCustomer->getValue(
                DBECustomer::reviewMeetingBooked
            )) {
                $style = 'style="background-color: #B2FFB2"';
            } elseif ($nextReviewMeetingDate < $now) {
                $style = 'style="background-color: #F5AEBD"';
            }

            $locationString = $activeCustomerSites->getValue(DBESite::town) . ', ' . $activeCustomerSites->getValue(
                    DBESite::postcode
                );

            $customerURL = Controller::buildLink(
                'Customer.php',
                [
                    'action'     => 'dispEdit',
                    'customerID' => $dbeCustomer->getValue(DBECustomer::customerID)
                ]
            );

            $customerLink = "<a href='" . $customerURL . "' target='_blank'>" . $dbeCustomer->getValue(
                    DBECustomer::name
                ) . "</a>";

            $customerReviewMeetings[] = [
                "class"             => $style,
                "customerLink"      => $customerLink,
                "mainLocation"      => $locationString,
                "lastReviewMeeting" => $lastReviewMeetingDate->format('d/m/Y'),
                "nextReviewMeeting" => $nextReviewMeetingDate->format('d/m/Y'),
                "frequency"         => $dbeCustomer->getValue(DBECustomer::reviewMeetingFrequencyMonths),
                "contact"           => $reviewContactsString,
            ];
        }


        $this->template->set_block(
            'CustomerReviewMeetings',
            'reviewMeetingsBlock',
            'customerReviewMeetingItems'
        );

        foreach ($customerReviewMeetings as $item) {
            $this->template->set_var(
                $item
            );

            $this->template->parse(
                'customerReviewMeetingItems',
                'reviewMeetingsBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'CustomerReviewMeetings',
            true
        );


        $this->parsePage();
    }
}// end of class
