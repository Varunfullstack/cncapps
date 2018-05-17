<?php
/**
 * Requests Awaiting Completion controller class
 * CNC Ltd
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivity.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

class CTAwaitingCompletion extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "maintenance",
            'technical'
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buActivity = new BUActivity($this);

    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {

            default:
                $this->displayReport();
                break;
        }
    }

    /**
     * Display search form
     * @access private
     */
    function displayReport()
    {

        $this->setMethodName('displayReport');

        $this->setTemplateFiles('AwaitingCompletion', 'AwaitingCompletion.inc');

        $this->setPageTitle(CONFIG_SERVICE_REQUEST_DESC . 's Awaiting Completion');

        $this->buActivity->getProblemsByStatus('F', $dsResults);

        $dsResults->sortAscending('completeDate');

        $this->template->set_block('AwaitingCompletion', 'rowBlock', 'rows');

        $count = 0;

        while ($dsResults->fetchNext()) {

            $dbeFirstActivity = $this->buActivity->getFirstActivityInProblem($dsResults->getValue('problemID'));

            $firstActivityID = (string)$dbeFirstActivity->getValue('callActivityID');

            if ($firstActivityID) {

                $count++;

                $urlViewActivity =
                    $this->buildLink(
                        'Activity.php',
                        array(
                            'action' => 'displayActivity',
                            'callActivityID' => $firstActivityID
                        )
                    );


                $this->template->set_var(

                    array(
                        'cTime' => $dsResults->getValue('timeRaised'),
                        'cDate' => $dsResults->getValue('dateRaisedDMY'),
                        'cProblemID' => $dsResults->getValue('problemID'),
                        'cReason' => $this->truncate($dbeFirstActivity->getValue('reason')),
                        'cCustomerName' => $dsResults->getValue('customerName'),
                        'cUrlViewActivity' => $urlViewActivity,
                        'completeDate' => strftime("%d/%m/%Y", strtotime($dsResults->getValue('completeDate'))),
                        'cUrlProblemHistoryPopup' => $this->getProblemHistoryLink($dsResults->getValue('problemID'))
                    )

                );

                $this->template->parse('rows', 'rowBlock', true);
            }

        }

        $this->template->set_var(

            array(
                'fixedCount' => $count
            )

        );

        $this->template->parse('CONTENTS', 'AwaitingCompletion', true);


        $this->parsePage();
    }

    function truncate($reason, $length = 50)
    {
        return substr(common_stripEverything($reason), 0, 50);

    }

    function getProblemHistoryLink($problemID)
    {
        $url = $this->buildLink(
            'Activity.php',
            array(
                'action' => 'problemHistoryPopup',
                'problemID' => $problemID,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        return $url;

    }

}// end of class
?>