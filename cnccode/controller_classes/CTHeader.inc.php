<?php
/**
 * System header controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUHeader.inc.php');
require_once($cfg['path_bu'] . '/BUPortalDocument.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTHEADER_ACT_EDIT', 'editHeader');
define('CTHEADER_ACT_UPDATE', 'updateHeader');

class CTHeader extends CTCNC
{
    var $dsHeader = '';
    var $buHeader = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buHeader = new BUHeader($this);
        $this->dsHeader = new DSForm($this);
        $this->dsHeader->copyColumnsFrom($this->buHeader->dbeJHeader);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTHEADER_ACT_UPDATE:
                $this->update();
                break;
            case 'editHelpDesk':
                $this->editHelpDesk();
                break;
            case 'updateHelpDesk':
                $this->updateHelpDesk();
                break;
            case CTHEADER_ACT_EDIT:
            default:
                $this->edit();
                break;
        }
    }

    /**
     * Edit/Add Header
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsHeader = &$this->dsHeader; // ref to class var

        if (!$this->getFormError()) {
            $this->buHeader->getHeader($dsHeader);
        } else {                                                                        // form validation error
            $dsHeader->initialise();
            $dsHeader->fetchNext();
        }
        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTHEADER_ACT_UPDATE
                )
            );
        $this->setPageTitle('Edit Header');
        $this->setTemplateFiles(
            array('HeaderEdit' => 'HeaderEdit.inc')
        );
        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $this->template->set_var(
            array(
                'headerID' => Controller::htmlInputText($dsHeader->getValue('headerID')),
                'name' => Controller::htmlInputText($dsHeader->getValue('name')),
                'nameMessage' => Controller::htmlDisplayText($dsHeader->getMessage('name')),
                'add1' => Controller::htmlInputText($dsHeader->getValue('add1')),
                'add1Message' => Controller::htmlDisplayText($dsHeader->getMessage('add1')),
                'add2' => Controller::htmlInputText($dsHeader->getValue('add2')),
                'add3' => Controller::htmlInputText($dsHeader->getValue('add3')),
                'town' => Controller::htmlInputText($dsHeader->getValue('town')),
                'townMessage' => Controller::htmlDisplayText($dsHeader->getMessage('town')),
                'county' => Controller::htmlInputText($dsHeader->getValue('county')),
                'postcode' => Controller::htmlInputText($dsHeader->getValue('postcode')),
                'postcodeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('postcode')),
                'phone' => Controller::htmlInputText($dsHeader->getValue('phone')),
                'phoneMessage' => Controller::htmlDisplayText($dsHeader->getMessage('phone')),
                'fax' => Controller::htmlInputText($dsHeader->getValue('fax')),
                'faxMessage' => Controller::htmlDisplayText($dsHeader->getMessage('fax')),
                'goodsContact' => Controller::htmlInputText($dsHeader->getValue('goodsContact')),
                'goodsContactMessage' => Controller::htmlDisplayText($dsHeader->getMessage('goodsContact')),
                'billingStartTime' => Controller::htmlInputText($dsHeader->getValue('billingStartTime')),
                'billingStartTimeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('billingStartTime')),
                'billingEndTime' => Controller::htmlInputText($dsHeader->getValue('billingEndTime')),
                'billingEndTimeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('billingEndTime')),
                'projectStartTime' => Controller::htmlInputText($dsHeader->getValue('projectStartTime')),
                'projectStartTimeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('projectStartTime')),
                'projectEndTime' => Controller::htmlInputText($dsHeader->getValue('projectEndTime')),
                'projectEndTimeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('projectEndTime')),
                'helpdeskStartTime' => Controller::htmlInputText($dsHeader->getValue('helpdeskStartTime')),
                'helpdeskStartTimeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('helpdeskStartTime')),
                'helpdeskEndTime' => Controller::htmlInputText($dsHeader->getValue('helpdeskEndTime')),
                'helpdeskEndTimeMessage' => Controller::htmlDisplayText($dsHeader->getMessage('helpdeskEndTime')),
                'hourlyLabourCost' => Controller::htmlInputText($dsHeader->getValue('hourlyLabourCost')),
                'hourlyLabourCostMessage' => Controller::htmlDisplayText($dsHeader->getMessage('hourlyLabourCost')),
                'portalPin' => Controller::htmlInputText($dsHeader->getValue('portalPin')),
                'portalPinMessage' => Controller::htmlInputText($dsHeader->getMessage('portalPin')),
                'portal24HourPin' => Controller::htmlInputText($dsHeader->getValue('portal24HourPin')),
                'portal24HourPinMessage' => Controller::htmlInputText($dsHeader->getMessage('portal24HourPin')),
                'gscItemID' => Controller::htmlInputText($dsHeader->getValue('gscItemID')),
                'gscItemDescription' => Controller::htmlInputText($dsHeader->getValue('gscItemDescription')),
                'highActivityAlertCount' => Controller::htmlInputText($dsHeader->getValue('highActivityAlertCount')),
                'highActivityAlertCountMessage' => $dsHeader->getMessage('highActivityAlertCount'),
                'mailshot1FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot1FlagDesc')),
                'mailshot1FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot1FlagDef')),
                'mailshot2FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot2FlagDesc')),
                'mailshot2FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot2FlagDef')),
                'mailshot3FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot3FlagDesc')),
                'mailshot3FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot3FlagDef')),
                'mailshot4FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot4FlagDesc')),
                'mailshot4FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot4FlagDef')),
                'mailshot5FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot5FlagDesc')),
                'mailshot5FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot5FlagDef')),
                'mailshot6FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot6FlagDesc')),
                'mailshot6FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot6FlagDef')),
                'mailshot7FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot7FlagDesc')),
                'mailshot7FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot7FlagDef')),
                'mailshot8FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot8FlagDesc')),
                'mailshot8FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot8FlagDef')),
                'mailshot9FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot9FlagDesc')),
                'mailshot9FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot9FlagDef')),
                'mailshot10FlagDesc' => Controller::htmlInputText($dsHeader->getValue('mailshot10FlagDesc')),
                'mailshot10FlagDefChecked' => $this->getChecked($dsHeader->getValue('mailshot10FlagDef')),
                'priority1Desc' => Controller::htmlInputText($dsHeader->getValue('priority1Desc')),
                'priority1DescMessage' => Controller::htmlDisplayText($dsHeader->getMessage('priority1Desc')),
                'priority2Desc' => Controller::htmlInputText($dsHeader->getValue('priority2Desc')),
                'priority2DescMessage' => Controller::htmlDisplayText($dsHeader->getMessage('priority2Desc')),
                'priority3Desc' => Controller::htmlInputText($dsHeader->getValue('priority3Desc')),
                'priority3DescMessage' => Controller::htmlDisplayText($dsHeader->getMessage('priority3Desc')),
                'priority4Desc' => Controller::htmlInputText($dsHeader->getValue('priority4Desc')),
                'priority4DescMessage' => Controller::htmlDisplayText($dsHeader->getMessage('priority4Desc')),
                'priority5Desc' => Controller::htmlInputText($dsHeader->getValue('priority5Desc')),
                'priority5DescMessage' => Controller::htmlDisplayText($dsHeader->getMessage('priority5Desc')),
                'allowedClientIpPattern' => Controller::htmlInputText($dsHeader->getValue('allowedClientIpPattern')),
                'allowedClientIpPatternMessage' => Controller::htmlDisplayText($dsHeader->getMessage('allowedClientIpPattern')),

                'hdTeamLimitHours' => Controller::htmlInputText($dsHeader->getValue('hdTeamLimitHours')),

                'hdTeamLimitHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('hdTeamLimitHours')),

                'esTeamLimitHours' => Controller::htmlInputText($dsHeader->getValue('esTeamLimitHours')),

                'esTeamLimitHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('esTeamLimitHours')),

                'imTeamLimitHours' => Controller::htmlInputText($dsHeader->getValue('imTeamLimitHours')),

                'imTeamLimitHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('imTeamLimitHours')),

                'hdTeamTargetLogPercentage' => Controller::htmlInputText($dsHeader->getValue('hdTeamTargetLogPercentage')),

                'hdTeamTargetSlaPercentage' => Controller::htmlInputText($dsHeader->getValue('hdTeamTargetSlaPercentage')),

                'hdTeamTargetSlaPercentageMessage' => Controller::htmlDisplayText($dsHeader->getMessage('hdTeamTargetSlaPercentage')),

                'hdTeamTargetFixHours' => Controller::htmlInputText($dsHeader->getValue('hdTeamTargetFixHours')),

                'hdTeamTargetFixHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('hdTeamTargetFixHours')),

                'hdTeamTargetFixQtyPerMonth' => Controller::htmlInputText($dsHeader->getValue('hdTeamTargetFixQtyPerMonth')),

                'hdTeamTargetFixQtyPerMonthMessage' => Controller::htmlDisplayText($dsHeader->getMessage('hdTeamTargetFixQtyPerMonth')),

                'esTeamTargetLogPercentage' => Controller::htmlInputText($dsHeader->getValue('esTeamTargetLogPercentage')),

                'esTeamTargetSlaPercentage' => Controller::htmlInputText($dsHeader->getValue('esTeamTargetSlaPercentage')),

                'esTeamTargetSlaPercentageMessage' => Controller::htmlDisplayText($dsHeader->getMessage('esTeamTargetSlaPercentage')),

                'esTeamTargetFixHours' => Controller::htmlInputText($dsHeader->getValue('esTeamTargetFixHours')),

                'esTeamTargetFixHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('esTeamTargetFixHours')),

                'esTeamTargetFixQtyPerMonth' => Controller::htmlInputText($dsHeader->getValue('esTeamTargetFixQtyPerMonth')),

                'esTeamTargetFixQtyPerMonthMessage' => Controller::htmlDisplayText($dsHeader->getMessage('esTeamTargetFixQtyPerMonth')),

                'imTeamTargetLogPercentage' => Controller::htmlInputText($dsHeader->getValue('imTeamTargetLogPercentage')),

                'imTeamTargetSlaPercentage' => Controller::htmlInputText($dsHeader->getValue('imTeamTargetSlaPercentage')),

                'imTeamTargetSlaPercentageMessage' => Controller::htmlDisplayText($dsHeader->getMessage('imTeamTargetSlaPercentage')),

                'imTeamTargetFixHours' => Controller::htmlInputText($dsHeader->getValue('imTeamTargetFixHours')),

                'imTeamMinutesInADay' => Controller::htmlDisplayText($dsHeader->getValue(DBEHeader::ImplementationTeamMinutesInADay)),

                'imTeamTargetFixHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('imTeamTargetFixHours')),

                'imTeamTargetFixQtyPerMonth' => Controller::htmlInputText($dsHeader->getValue('imTeamTargetFixQtyPerMonth')),

                'imTeamTargetFixQtyPerMonthMessage' => Controller::htmlDisplayText($dsHeader->getMessage('imTeamTargetFixQtyPerMonth')),

                'srAutocompleteThresholdHours' => Controller::htmlInputText($dsHeader->getValue('srAutocompleteThresholdHours')),

                'srAutocompleteThresholdHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('srAutocompleteThresholdHours')),

                'srPromptContractThresholdHours' => Controller::htmlInputText($dsHeader->getValue('srPromptContractThresholdHours')),

                'srPromptContractThresholdHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('srPromptContractThresholdHours')),

                'customerContactWarnHours' => Controller::htmlInputText($dsHeader->getValue('customerContactWarnHours')),

                'customerContactWarnHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('customerContactWarnHours')),

                'remoteSupportWarnHours' => Controller::htmlInputText($dsHeader->getValue('remoteSupportWarnHours')),

                'remoteSupportWarnHoursMessage' => Controller::htmlDisplayText($dsHeader->getMessage('remoteSupportWarnHours')),
                DBEHeader::RemoteSupportMinWarnHours => Controller::htmlInputText($dsHeader->getValue(DBEHeader::RemoteSupportMinWarnHours)),

                DBEHeader::RemoteSupportMinWarnHours . 'Message' => Controller::htmlDisplayText($dsHeader->getMessage(DBEHeader::RemoteSupportMinWarnHours)),

                DBEHeader::backupTargetSuccessRate => Controller::htmlInputText($dsHeader->getValue(DBEHeader::backupTargetSuccessRate)),

                'urlItemPopup' => $urlItemPopup,
                'urlUpdate' => $urlUpdate
            )
        );

        // VAT code
        $this->template->set_block('HeaderEdit', 'vatCodeBlock', 'vatCodes');
        for ($i = 0; $i < 10; $i++) {
            $vatCode = 'T' . $i;
            $vatCodeSelected = ($dsHeader->getValue("stdVATCode") == $vatCode) ? CT_SELECTED : '';
            $this->template->set_var(
                array(
                    'vatCodeSelected' => $vatCodeSelected,
                    'vatCode' => $vatCode,
                )
            );
            $this->template->parse('vatCodes', 'vatCodeBlock', true);
        }

        $this->documents('HeaderEdit');


        $this->template->parse('CONTENTS', 'HeaderEdit', true);
        $this->parsePage();
    }// end function editHeader()

    /**
     * Update
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsHeader = &$this->dsHeader;
        $this->formError = (!$this->dsHeader->populateFromArray($_REQUEST['header']));

        if ($this->formError) {
            $_REQUEST['action'] = CTHEADER_ACT_EDIT;
            $this->edit();
            exit;
        }

        $this->buHeader->updateHeader($this->dsHeader);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                array(
                    'action' => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Edit Helpdesk
     * @access private
     */
    function editHelpDesk()
    {
        $this->setMethodName('editHelpDesk');
        $dsHeader = &$this->dsHeader; // ref to class var

        if (!$this->getFormError()) {
            $this->buHeader->getHeader($dsHeader);
        } else {                                                                        // form validation error
            $dsHeader->initialise();
            $dsHeader->fetchNext();
        }

        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => 'updateHelpDesk'
                )
            );
        $this->setPageTitle('Edit HelpDesk Details');

        $this->setTemplateFiles(
            array('HeaderHelpDeskEdit' => 'HeaderHelpDeskEdit.inc')
        );

        $this->template->set_var(
            array(
                'headerID' => Controller::htmlInputText($dsHeader->getValue('headerID')),
                'helpDeskProblems' => Controller::htmlInputText($dsHeader->getValue('helpDeskProblems')),
                'helpDeskProblemsMessage' => Controller::htmlDisplayText($dsHeader->getMessage('helpDeskProblems')),
                'helpDeskOSCount' => Controller::htmlInputText($dsHeader->getValue('helpDeskOSCount')),
                'helpDeskOSCountMessage' => Controller::htmlDisplayText($dsHeader->getMessage('helpDeskOSCount')),
                'urlUpdate' => $urlUpdate
            )
        );

        $this->template->parse('CONTENTS', 'HeaderHelpDeskEdit', true);
        $this->parsePage();
    }// end function editHelpDesk()

    /**
     * Update
     * @access private
     */
    function updateHelpDesk()
    {
        $this->setMethodName('updateHelpDesk');
        $dsHeader = &$this->dsHeader;
        $this->formError = (!$this->dsHeader->populateFromArray($_REQUEST['header']));

        if ($this->formError) {
            $_REQUEST['action'] = CTHEADER_ACT_EDIT;
            $this->edit();
            exit;
        }

        $this->buHeader->updateHelpDesk($this->dsHeader);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                array(
                    'action' => 'editHelpDesk'
                )
            );
        header('Location: ' . $urlNext);
    }

    function documents($templateName)
    {
        $this->template->set_block($templateName, 'portalDocumentBlock', 'portalDocuments');

        if ($this->getAction() != CTCUSTOMER_ACT_ADDCUSTOMER) {

            $buPortalDocument = new BUPortalDocument($this);
            $buPortalDocument->getDocuments($dsPortalDocument);

            $urlAddDocument =
                $this->buildLink(
                    'PortalDocument.php',
                    array(
                        'action' => 'add'
                    )
                );


            $this->template->set_var(
                array(
                    'txtAddDocument' => 'Add document',
                    'urlAddDocument' => $urlAddDocument
                )
            );

            while ($dsPortalDocument->fetchNext()) {

                $urlEditDocument =
                    $this->buildLink(
                        'PortalDocument.php',
                        array(
                            'action' => 'edit',
                            'portalDocumentID' => $dsPortalDocument->getValue('portalDocumentID')
                        )
                    );

                $urlViewFile =
                    $this->buildLink(
                        'PortalDocument.php',
                        array(
                            'action' => 'viewFile',
                            'portalDocumentID' => $dsPortalDocument->getValue('portalDocumentID')
                        )
                    );

                $urlDeleteDocument =
                    $this->buildLink(
                        'PortalDocument.php',
                        array(
                            'action' => 'delete',
                            'portalDocumentID' => $dsPortalDocument->getValue('portalDocumentID')
                        )
                    );

                $this->template->set_var(
                    array(
                        'description' => $dsPortalDocument->getValue("description"),
                        'filename' => $dsPortalDocument->getValue("filename"),
                        'mainContactOnlyFlag' => $dsPortalDocument->getValue("mainContactOnlyFlag"),
                        'requiresAcceptanceFlag' => $dsPortalDocument->getValue("requiresAcceptanceFlag"),
                        'createDate' => $dsPortalDocument->getValue("createdDate"),
                        'urlViewFile' => $urlViewFile,
                        'urlEditDocument' => $urlEditDocument,
                        'urlDeleteDocument' => $urlDeleteDocument
                    )
                );
                $this->template->parse('portalDocuments', 'portalDocumentBlock', true);
            } // end while

        } // end if

    } // end function documents

}// end of class
?>