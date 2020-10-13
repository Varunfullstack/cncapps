<?php
/**
 * Created by PhpStorm.
 * User: fizdalf
 * Date: 22/08/2018
 * Time: 10:39
 */
global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DBECallDocumentWithoutFile.php');
require_once($cfg ["path_dbe"] . "/DBEJCallActivity.php");
require_once($cfg['path_bu'] . '/BUActivity.inc.php');

class CTSalesRequestDashboard extends CTCNC
{
    const GET_ALLOCATION_USERS = "getAllocationUsers";
    const GET_DATA = 'getData';
    private $allocatedUser;

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
        $this->setMenuId(204);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {

        switch ($this->getAction()) {
            case 'assignUser':
                $data = json_decode(file_get_contents('php://input'), true);

                if (!array_key_exists('userId', $data)) {
                    throw new Exception('user ID Field required');
                }
                if (!array_key_exists('problemId', $data) || !isset($data['problemId'])) {
                    throw new Exception('Problem ID required');
                }

                $dbeProblem = new DBEProblem($this);
                $dbeProblem->getRow($data['problemId']);
                $dbeProblem->setValue(DBEProblem::salesRequestAssignedUserId, $data['userId']);
                $dbeProblem->updateRow();
                echo json_encode(["status" => "ok"]);
                break;
            case self::GET_ALLOCATION_USERS:
            {
                $dbeUser = new DBEUser($this);
                $dbeUser->getRows('firstName');
                $result = [];
                while ($dbeUser->fetchNext()) {
                    $result[] =
                        [
                            'id'       => $dbeUser->getValue(DBEUser::userID),
                            'userName' => $dbeUser->getValue(DBEUser::name),
                            'fullName' => $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(
                                    DBEUser::lastName
                                )
                        ];
                }
                echo json_encode(["status" => "ok", "data" => $result]);
                exit;
            }
            case self::GET_DATA:
            {
                global $db;
                $query = "
SELECT
  callactivity.`caa_callactivityno` AS activityId,
  callactivity.`caa_problemno` AS serviceRequestId,
  standardtext.`stt_desc` AS `type`,
  customer.cus_name AS customerName,
  callactivity.`reason` AS requestBody,
  CONCAT(callactivity.`caa_date`,' ',callactivity.`caa_starttime`,':00') AS requestedAt,
   consultant.cns_name AS requesterName,
   problem.`salesRequestAssignedUserId`
FROM
  callactivity 
  LEFT JOIN standardtext ON callactivity.`requestType` = standardtext.`stt_standardtextno`
  LEFT JOIN problem ON callactivity.`caa_problemno` = problem.`pro_problemno`
  LEFT JOIN customer ON problem.`pro_custno` = customer.`cus_custno`
  LEFT JOIN consultant ON callactivity.`caa_consno` = consultant.cns_consno
WHERE callactivity.salesRequestStatus = 'O'
  AND caa_callacttypeno = 43";
                $statement = $db->preparedQuery($query, []);
                $requests = $statement->fetch_all(MYSQLI_ASSOC);
                $requests = array_map(
                    function ($request) {
                        $dbeJCallDocument = new DBECallDocumentWithoutFile($this);
                        $dbeJCallDocument->setValue(
                            DBECallDocumentWithoutFile::callActivityID,
                            $request['activityId']
                        );
                        $dbeJCallDocument->getRowsByColumn(DBECallDocumentWithoutFile::callActivityID);
                        $request['attachments'] = [];
                        while ($dbeJCallDocument->fetchNext()) {
                            $request['attachments'][] = [
                                "documentId" => $dbeJCallDocument->getValue(DBECallDocumentWithoutFile::callDocumentID)
                            ];
                        }
                        return $request;
                    },
                    $requests
                );
                echo json_encode(["status" => "ok", "data" => $requests]);
                exit;
            }
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

        $this->loadReactScript('SpinnerHolderComponent.js');
        $this->loadReactCSS('SpinnerHolderComponent.css');

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
                'SRActivity.php',
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
            $dbeProblem = new DBEProblem($this);
            $dbeProblem->getRow($dbejCallActivity->getValue(DBECallActivity::problemID));

            $dbeUser = new DBEUser($this);

            $dbeUser->getRows('firstName');

            while ($dbeUser->fetchNext()) {

                $userRow =
                    array(
                        'userID'   => $dbeUser->getValue(DBEUser::userID),
                        'userName' => $dbeUser->getValue(DBEUser::name),
                        'fullName' => $dbeUser->getValue(DBEUser::firstName) . ' ' . $dbeUser->getValue(
                                DBEUser::lastName
                            )
                    );

                $this->allocatedUser[$dbeUser->getValue(DBEUser::userID)] = $userRow;

            }

            $this->template->set_var(
                [
                    'customerName'      => $dbejCallActivity->getValue(DBEJCallActivity::customerName),
                    'srLink'            => $srLink,
                    'engineerDropDown'  => $this->getAllocatedUserDropdown(
                        $dbeProblem->getValue(DBEProblem::salesRequestAssignedUserId)
                    ),
                    'problemId'         => $dbeProblem->getValue(DBEProblem::problemID),
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

    /**
     * return list of user options for dropdown
     *
     * @param mixed $selectedID
     * @return string
     * @throws Exception
     */
    function getAllocatedUserDropdown($selectedID
    )
    {

        // user selection
        $userSelected = !$selectedID ? CT_SELECTED : null;
        $string = '<option ' . $userSelected . ' value=""></option>';

        foreach ($this->allocatedUser as $value) {

            $userSelected = ($selectedID == $value['userID']) ? CT_SELECTED : null;

            $string .= "<option {$userSelected} value='{$value['userID']}'>{$value['userName']}</option>";

        }

        return $string;

    }
}