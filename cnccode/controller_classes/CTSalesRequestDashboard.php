<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_dbe'] . '/DBECallDocumentWithoutFile.php');

class CTSalesRequestDashboard extends CTCNC
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
        $roles = [
            "sales",
        ];
        if (!self::hasPermissions($roles)) {
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

    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles(
            'SalesRequestDashboard',
            'SalesRequestDashboard'
        );

        $this->setPageTitle('Sales Request Dashboard');

        $dbejCallActivity = new DBEJCallActivity($this);
        $dbejCallActivity->getPendingSalesRequestRows();

        $this->template->set_block(
            'SalesRequestDashboard',
            'SalesRequestsBlock',
            'salesRequests'
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

            $srLink = "<a href='$srLink' target='_blank'>SR</a>";

            $processCRLink = Controller::buildLink(
                'Activity.php',
                [
                    "callActivityID" => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID),
                    "action"         => "salesRequestReview"
                ]
            );

            $processCRLink = "<a href='$processCRLink' target='_blank'>Process Sales Request</a>";

            $attachments = "";

            $dbeJCallDocument = new DBECallDocumentWithoutFile($this);
            $dbeJCallDocument->setValue(
                DBECallDocumentWithoutFile::callActivityID,
                $dbejCallActivity->getValue(DBECallActivity::callActivityID)
            );
            $dbeJCallDocument->getRowsByColumn(DBECallDocumentWithoutFile::callActivityID);

            while ($dbeJCallDocument->fetchNext()) {
                $attachments .= "<a href=\"/Activity.php?action=viewFile&callDocumentID=" . $dbeJCallDocument->getValue(
                        DBECallDocumentWithoutFile::callDocumentID
                    ) . "\"
                            target=\"_blank\"
        ><i class=\"fa fa-paperclip\"></i></a>";
            }

            $dbeStandardText = new DBEStandardText($this);
            $dbeStandardText->getRow($dbejCallActivity->getValue(DBEJCallActivity::requestType));


            $this->template->set_var(
                [
                    'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    'srLink'            => $srLink,
                    'salesRequest'      => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                    'requestedBy'       => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                    'requestedDateTime' => $dbejCallActivity->getValue(
                            DBEJCallActivity::date
                        ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime),
                    'processCRLink'     => $processCRLink,
                    'attachments'       => $attachments,
                    'type'              => $dbeStandardText->getValue(DBEStandardText::stt_desc)
                ]
            );

            $this->template->parse(
                'salesRequests',
                'SalesRequestsBlock',
                true
            );
        }


        $this->template->parse(
            'CONTENTS',
            'SalesRequestDashboard',
            true
        );
        $this->parsePage();


    }
}