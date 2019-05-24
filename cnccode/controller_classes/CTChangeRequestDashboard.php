<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");

class CTChangeRequestDashboard extends CTCNC
{
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
        if (!self::isSdManager()) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {

        switch ($this->getAction()) {

            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * @throws Exception
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles(
            'ChangeRequestDashboard',
            'ChangeRequestDashboard'
        );

        $this->setPageTitle('Change Request Dashboard');

        $dbejCallActivity = new DBEJCallActivity($this);
        $dbejCallActivity->getPendingChangeRequestRows();

        $this->template->set_block(
            'ChangeRequestDashboard',
            'ChangeRequestsBlock',
            'changeRequests'
        );

        $buActivity = new BUActivity($this);

        while ($dbejCallActivity->fetchNext()) {

            $lastActivity = $buActivity->getLastActivityInProblem(
                $dbejCallActivity->getValue(DBEJCallActivity::problemID)
            );
            $srLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $lastActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "displayActivity"
                ]
            );

            $srLink = "<a href='$srLink'>SR</a>";

//            http://cncdev7:85/Activity.php?action=changeRequestReview&callActivityID=1813051&fromEmail=true

            $processCRLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "changeRequestReview"
                ]
            );

            $processCRLink = "<a href='$processCRLink'>Process Change Request</a>";

            $this->template->set_var(
                [
                    'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    'srLink'            => $srLink,
                    'changeRequested'   => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                    'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                    'requestedDateTime' => $dbejCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime),
                    'processCRLink'     => $processCRLink,
                ]
            );

            $this->template->parse(
                'changeRequests',
                'ChangeRequestsBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'ChangeRequestDashboard',
            true
        );
        $this->parsePage();


    }
}