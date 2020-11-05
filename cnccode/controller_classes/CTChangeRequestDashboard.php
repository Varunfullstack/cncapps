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

class CTChangeRequestDashboard extends CTCNC
{
    const GET_DATA = 'getData';

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
        $this->setMenuId(203);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case self::GET_DATA:
            {
                $showHelpDesk = isset($_REQUEST['HD']);
                $showEscalation = isset($_REQUEST['ES']);
                $showSmallProjects = isset($_REQUEST['SP']);
                $showProjects = isset($_REQUEST['P']);

                $dbejCallActivity = new DBEJCallActivity($this);
                $dbejCallActivity->getPendingChangeRequestRows(
                    $showHelpDesk,
                    $showEscalation,
                    $showSmallProjects,
                    $showProjects
                );

                $result = [];
                while ($dbejCallActivity->fetchNext()) {
                    $result[] = [
                        'customerName'     => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                        'serviceRequestId' => $dbejCallActivity->getValue(DBEJCallActivity::problemID),
                        'requestBody'      => $dbejCallActivity->getValue(DBEJCallActivity::reason),
                        'requesterName'    => $dbejCallActivity->getValue(DBEJCallActivity::userAccount),
                        'requestedAt'      => $dbejCallActivity->getValue(
                                DBEJCallActivity::date
                            ) . ' ' . $dbejCallActivity->getValue(DBEJCallActivity::startTime) . ':00',
                        'activityId'       => $dbejCallActivity->getValue(DBEJCallActivity::callActivityID)
                    ];
                }
                echo json_encode(["status" => "ok", "data" => $result]);

                exit;
            }
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
        $this->loadReactScript('SpinnerHolderComponent.js');
        $this->loadReactCSS('SpinnerHolderComponent.css');
        $this->setPageTitle('Change Request Dashboard');

        $this->template->parse(
            'CONTENTS',
            'ChangeRequestDashboard',
            true
        );
        $this->parsePage();


    }
}