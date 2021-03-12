<?php
/**
 * Customer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
require_once($cfg['path_bu'] . '/BUCustomerNote.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTCustomerNote extends CTCNC
{

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "sales",
            "accounts",
            "technical",
            "supervisor",
            "reports",
            "maintenance",
            "renewals"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {

            case 'getCustomerNote':
                $this->getCustomerNote();
                break;

            case 'updateNote':
                $this->updateNote();
                break;

            case 'customerNotePopup':
                $this->customerNotePopup();
                break;
            case 'customerNoteHistoryPopup':
                $this->customerNoteHistoryPopup();
                break;

            case 'deleteCustomerNote':
                $this->deleteCustomerNote();
                break;

            default:

                break;

        }
    }

    function getCustomerNote()
    {

        if (!$this->getParam('identifier')) {
            $this->raiseError('No identifier Passed');
        }
        if (!$this->getParam('customerID')) {
            $this->raiseError('No customerID Passed');
        }

        $buCustomerNote = new BUCustomerNote($this);

        if ($record = $buCustomerNote->getNote(
            $this->getParam('customerID'),
            $this->getParam('created'),
            $this->getParam('identifier')
        )) {

            $noteHistory = $this->getTextStringOfHistory($record->cno_custno);

            echo $this->createReturnJavascriptString($record, $noteHistory);
        }
    }

    function getTextStringOfHistory($customerID)
    {

        $buCustomerNote = new BUCustomerNote($this);

        if ($results = $buCustomerNote->getNotesByCustomerID($customerID)) {

            $returnString = '';

            while ($row = $results->fetch_object()) {

                if ($returnString != '') {
                    $returnString .= "\\n\\n";
                }
                if (substr($row->cno_modified, 0, 10) != '2010-09-28') {
                    $returnString .=
                        Controller::dateYMDtoDMY(
                            $row->cno_modified
                        ) . ' - ' . $row->cns_name . " ####################################################################\\n\\n";
                }

                $returnString .= $row->cno_details;

            }

            return $returnString;
        }

    }

    function createReturnJavascriptString($record, $history)
    {
        $details = str_replace(array("\r", "\n"), array('\r', '\n'), $record->cno_details);
        $details = addcslashes($details, "'\"");

        $history = str_replace(array("\r", "\n"), array('\r', '\n'), $history);
        $history = addcslashes($history, "'\"");

        $javascript = '
        var im = document.getElementById(\'customerNoteDetails\');
        im.value = "' . $details . '";
        var im = document.getElementById(\'customerNoteHistory\');
        im.value = "' . $history . '";
        var im = document.getElementById(\'customerNoteCreated\');
        im.value = "' . $record->cno_created . '";
        var im = document.getElementById(\'customerNoteModified\');
        im.value = "' . $record->cno_modified . '";
        var im = document.getElementById(\'customerNoteModifiedText\');
        im.innerHTML = "' . Controller::dateYMDtoDMY($record->cno_modified) . ' by ' . $record->cns_logname . '";
        var im = document.getElementById(\'customerNoteID\');
        im.value = "' . $record->cno_customernoteno . '";
        var im = document.getElementById(\'customerNoteOrdheadID\');
        im.value = "' . $record->cno_ordno . '";';

        /*
              im.value = "' . $history . '";
        */
        return $javascript;
    }

    function updateNote()
    {

        if (!$this->getParam('customerID')) {
            $this->raiseError('No customerID Passed');
        }

        $buCustomerNote = new BUCustomerNote($this);

        if ($record = $buCustomerNote->updateNote(
            $this->getParam('customerID'),
            $this->getParam('customerNoteID'),
            $this->getParam('details'),
            $this->getParam('ordheadID')
        )) {

            $noteHistory = $this->getTextStringOfHistory($record->cno_custno);

            echo $this->createReturnJavascriptString($record, $noteHistory);
        }

    }

    /**
     * Form to create a new customer note
     *
     * @throws Exception
     * @throws Exception
     */
    function customerNotePopup()
    {
        $this->setTemplateFiles('CustomerNotePopup', 'CustomerNotePopup.inc');

        $this->setPageTitle('Customer Note');

        $buCustomerNote = new BUCustomerNote($this);

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {

            if (!$this->getParam('customerID')) {
                $this->raiseError('No customerID Passed');
            }

            if (!$this->getParam('details')) {
                $this->raiseError('No details Passed');
            }

            $buCustomerNote = new BUCustomerNote($this);

            $buCustomerNote->updateNote(
                $this->getParam('customerID'),
                $this->getParam('customerNoteID'),
                $this->getParam('details'),
                $this->getParam('ordheadID')
            );

            echo '<script language="javascript">window.close()</script>;';

        } else {
            if ($_REQUEST ['customerID']) {
                $record =
                    $buCustomerNote->getNote(
                        $_REQUEST ['customerID'],
                        false,
                        'salesOrder',
                        false,
                        $_REQUEST ['ordheadID']
                    );
                if ($record) {
                    $this->setParam('customerID', $record->cno_custno);
                    $this->setParam('customerNoteID', $record->cno_customernoteno);
                    $this->setParam('ordheadID', $record->cno_ordno);
                    $this->setParam('details', $record->cno_details);
                }
            }
        }


        $urlSubmit =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'customerNotePopup'
                )
            );

        $this->template->set_var(
            array(
                'customerID'     => $this->getParam('customerID'),
                'ordheadID'      => $this->getParam('ordheadID'),
                'customerNoteID' => $this->getParam('customerNoteID'),
                'details'        => $this->getParam('details'),
                'urlSubmit'      => $urlSubmit
            )
        );
        $this->template->parse('CONTENTS', 'CustomerNotePopup', true);
        $this->parsePage();

    }

    function customerNoteHistoryPopup()
    {

        if (!$this->getParam('customerID')) {
            $this->raiseError('No customerID Passed');
            return;
        }
        $customerID = $this->getParam('customerID');
        $this->setTemplateFiles('CustomerNoteHistoryPopup', 'CustomerNoteHistoryPopup.inc');

        $buCustomerNote = new BUCustomerNote($this);

        if ($results = $buCustomerNote->getNotesByCustomerID($customerID)) {

            $this->template->set_block('CustomerNoteHistoryPopup', 'notesBlock', 'rows');

            while ($row = $results->fetch_object()) {

                $this->template->set_var(
                    array(
                        'details' => Controller::formatForHTML($row->cno_details),
                        'date'    => Controller::dateYMDtoDMY($row->cno_modified),
                        'name'    => $row->cns_name
                    )
                );

                $this->template->parse('rows', 'notesBlock', true);

            }

            $this->template->parse('CONTENTS', 'CustomerNoteHistoryPopup', true);

            $this->parsePage();

        }

        exit;
    }

    function deleteCustomerNote()
    {

        if (!$this->getParam('customerNoteID')) {
            $this->raiseError('No customerNoteID Passed');
        }

        $buCustomerNote = new BUCustomerNote($this);

        if ($record = $buCustomerNote->deleteNote(
            $this->getParam('customerNoteID')
        )) {

            $noteHistory = $this->getTextStringOfHistory($record->cno_custno);

            echo $this->createReturnJavascriptString($record, $noteHistory);

    }
}

}// end of class
