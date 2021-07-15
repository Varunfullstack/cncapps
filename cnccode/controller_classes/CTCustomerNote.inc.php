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

            case 'getCustomerNotes':
                $this->getCustomerNotes();
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

            case 'deleteNote':
                $this->deleteCustomerNote();
                break;

            default:

                break;

        }
    }

    function getCustomerNotes()
    {
        if (!$this->getParam('customerId')) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Customer Id is missing"]);
            exit;
        }
        $buCustomerNote = new BUCustomerNote($this);
        $notes = $buCustomerNote->getNotesByCustomerID($this->getParam('customerId'));
        echo json_encode(["status" => "ok", "data" => $notes]);
    }

    function updateNote()
    {
        $data = $this->getJSONData();

        $buCustomerNote = new BUCustomerNote($this);

        $customerNoteArray = $buCustomerNote->updateNote(
            @$data['note'],
            @$data['id'],
            @$data['customerId'],
            @$data['lastUpdatedDateTime']
        );

        if (!$customerNoteArray) {
            echo json_encode(["status" => "error", "message" => "Failed to save note"]);
            http_response_code(400);
            exit;
        }

        echo json_encode(["status" => "ok", "data" => $customerNoteArray]);
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
                $this->getParam('details'),
                $this->getParam('customerNoteID'),
                $this->getParam('customerID')
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
        if (!isset($_REQUEST['noteId'])) {
            echo json_encode(["status" => "error", "message" => "Id of the note to be deleted not provided"]);
            http_response_code(400);
            exit;
        }

        $buCustomerNote = new BUCustomerNote($this);
        $buCustomerNote->deleteNote($_REQUEST['noteId']);
        echo json_encode(["status" => "ok"]);
    }
}
