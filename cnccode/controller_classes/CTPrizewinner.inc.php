<?php
/**
 * Prizewinner controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPrizewinner.inc.php');
require_once($cfg['path_bu'] . '/BUQuestionnaireReport.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');

// Actions

class CTPrizewinner extends CTCNC
{
    var $dsPrizewinner = '';
    var $buPrizewinner = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "accounts",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buPrizewinner = new BUPrizewinner($this);
        $this->dsPrizewinner = new DSForm($this);
        $this->dsPrizewinner->copyColumnsFrom($this->buPrizewinner->dbePrizewinner);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case 'create':
            case 'edit':
                $this->edit();
                break;
            case 'delete':
                $this->delete();
                break;
            case 'update':
                $this->update();
                break;
            case 'displayList':
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of prizewinners
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Prizewinners');
        $this->setTemplateFiles(
            array('PrizewinnerList' => 'PrizewinnerList.inc')
        );

        $this->buPrizewinner->getAll($dsPrizewinner);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'create'
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsPrizewinner->rowCount() > 0) {

            $this->template->set_block(
                'PrizewinnerList',
                'PrizewinnerBlock',
                'rows'
            );

            while ($dsPrizewinner->fetchNext()) {

                $prizewinnerID = $dsPrizewinner->getValue('prizewinnerID');

                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'edit',
                            'prizewinnerID' => $prizewinnerID
                        )
                    );
                $txtEdit = '[edit]';
                $urlDelete =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => 'delete',
                            'prizewinnerID' => $prizewinnerID
                        )
                    );
                $txtDelete = '[delete]';

                $this->template->set_var(
                    array(
                        'prizewinnerID' => $prizewinnerID,
                        'customerName' => Controller::htmlDisplayText($dsPrizewinner->getValue('customerName')),
                        'approvedFlag' => Controller::htmlDisplayText($dsPrizewinner->getValue('approvedFlag')),
                        'yearMonth' => Controller::htmlDisplayText($dsPrizewinner->getValue('yearMonth')),
                        'contactFirstName' => Controller::htmlDisplayText($dsPrizewinner->getValue('contactFirstName')),
                        'contactLastName' => Controller::htmlDisplayText($dsPrizewinner->getValue('contactLastName')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );

                $this->template->parse('rows', 'PrizewinnerBlock', true);

            }//while $dsPrizewinner->fetchNext()
        }
        $this->template->parse('CONTENTS', 'PrizewinnerList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsPrizewinner = &$this->dsPrizewinner; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'edit') {
                $this->buPrizewinner->getPrizewinnerByID($_REQUEST['prizewinnerID'], $dsPrizewinner);
                $prizewinnerID = $_REQUEST['prizewinnerID'];
            } else {                                                                    // creating new
                $dsPrizewinner->initialise();
                $dsPrizewinner->setValue('prizewinnerID', '0');
                $prizewinnerID = '0';
            }
        } else {                                                                        // form validation error
            $dsPrizewinner->initialise();
            $dsPrizewinner->fetchNext();
            $prizewinnerID = $dsPrizewinner->getValue('prizewinnerID');
        }

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'update',
                    'prizewinnerID' => $prizewinnerID
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'displayList'
                )
            );
        $this->setPageTitle('Edit Prizewinner');
        $this->setTemplateFiles(
            array('PrizewinnerEdit' => 'PrizewinnerEdit.inc')
        );
        $this->template->set_var(
            array(
                'prizewinnerID' => $prizewinnerID,
                'yearMonth' => Controller::htmlInputText($dsPrizewinner->getValue('yearMonth')),
                'yearMonthMessage' => Controller::htmlDisplayText($dsPrizewinner->getMessage('yearMonth')),
                'approvedFlagChecked' => Controller::htmlChecked($dsPrizewinner->getValue('approvedFlag')),
                'approvedFlagMessage' => Controller::htmlDisplayText($dsPrizewinner->getMessage('approvedFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );


        $this->parseContactSelector($dsPrizewinner->getValue('contactID'));

        $this->template->parse('CONTENTS', 'PrizewinnerEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    function parseContactSelector($contactID)
    {
        $buQuestionnaireReport = new BUQuestionnaireReport($this);
        $buQuestionnaireReport->questionnaireID = 1;
        $buQuestionnaireReport->setPeriod(date('Y-m', strtotime('last month')));

        $respondants = $buQuestionnaireReport->getRespondantsUniqueContact();

        $this->template->set_block('PrizewinnerEdit', 'contactBlock', 'contacts');

        while ($row = $respondants->fetch_object()) {

            $this->template->set_var(
                array(
                    'requestName' => $row->requestContact,
                    'customerName' => $row->customer,
                    'contactID' => $row->contactID,
                    'contactSelected' => ($contactID == $row->contactID) ? CT_SELECTED : ''
                )
            );
            $this->template->parse('contacts', 'contactBlock', true);
        }

    }  // end function

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsPrizewinner = &$this->dsPrizewinner;
        $this->formError = (!$this->dsPrizewinner->populateFromArray($_REQUEST['prizewinner']));
        if ($this->formError) {
            if ($this->dsPrizewinner->getValue('prizewinnerID') == '') {                    // attempt to insert
                $_REQUEST['action'] = 'edit';
            } else {
                $_REQUEST['action'] = 'create';
            }
            $this->edit();
            exit;
        }

        $this->buPrizewinner->updatePrizewinner($this->dsPrizewinner);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'prizewinnerID' => $this->dsPrizewinner->getValue('prizewinnerID'),
                                 'action' => 'view'
                             )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Prizewinner
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buPrizewinner->deletePrizewinner($_REQUEST['prizewinnerID'])) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => 'displayList'
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>
