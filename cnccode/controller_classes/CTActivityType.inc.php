<?php
/**
 * Activity Type controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUActivityType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTACTIVITYTYPE_ACT_DISPLAY_LIST', 'activityTypeList');
define('CTACTIVITYTYPE_ACT_CREATE', 'createActivityType');
define('CTACTIVITYTYPE_ACT_EDIT', 'editActivityType');
define('CTACTIVITYTYPE_ACT_DELETE', 'deleteActivityType');
define('CTACTIVITYTYPE_ACT_UPDATE', 'updateActivityType');

class CTActivityType extends CTCNC
{
    var $dsCallActType = '';
    var $buActivityType = '';

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $this->buActivityType = new BUActivityType($this);
        $this->dsCallActType = new DSForm($this);
        $this->dsCallActType->copyColumnsFrom($this->buActivityType->dbeCallActType);
        $this->dsCallActType->addColumn('itemDescription', DA_STRING, DA_ALLOW_NULL);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTACTIVITYTYPE_ACT_EDIT:
            case CTACTIVITYTYPE_ACT_CREATE:
                $this->edit();
                break;
            case CTACTIVITYTYPE_ACT_DELETE:
                $this->delete();
                break;
            case CTACTIVITYTYPE_ACT_UPDATE:
                $this->update();
                break;
            case CTACTIVITYTYPE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Display list of types
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Activity Types');
        $this->setTemplateFiles(
            array('ActivityTypeList' => 'ActivityTypeList.inc')
        );

        $this->buActivityType->getAllTypes($dsCallActType);

        $urlCreate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITYTYPE_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsCallActType->rowCount() > 0) {
            $this->template->set_block('ActivityTypeList', 'typeBlock', 'types');
            while ($dsCallActType->fetchNext()) {
                $callActTypeID = $dsCallActType->getValue('callActTypeID');
                $urlEdit =
                    $this->buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTACTIVITYTYPE_ACT_EDIT,
                            'callActTypeID' => $callActTypeID
                        )
                    );
                $txtEdit = '[edit]';
                /*
                                $urlDelete =
                                    $this->buildLink(
                                        $_SERVER['PHP_SELF'],
                                        array(
                                            'action'				=>	CTACTIVITYTYPE_ACT_DELETE,
                                            'callActTypeID'	=>	$callActTypeID
                                        )
                                    );
                                $txtDelete = '[delete]';
                    */
                $this->template->set_var(
                    array(
                        'callActTypeID' => $callActTypeID,
                        'description' => Controller::htmlDisplayText($dsCallActType->getValue('description')),
                        'curValueFlag' => $dsCallActType->getValue('curValueFlag'),
                        'customerEmailFlag' => $dsCallActType->getValue('customerEmailFlag'),
                        'oohMultiplier' => Controller::htmlInputText($dsCallActType->getValue('oohMultiplier')),
                        'maxHours' => Controller::htmlInputText($dsCallActType->getValue('maxHours')),
                        'minHours' => Controller::htmlInputText($dsCallActType->getValue('minHours')),
                        'allowSCRFlag' => $dsCallActType->getValue('allowSCRFlag'),
                        'requireCheckFlag' => $dsCallActType->getValue('requireCheckFlag'),
                        'allowExpensesFlag' => $dsCallActType->getValue('allowExpensesFlag'),
                        'allowReasonFlag' => $dsCallActType->getValue('allowReasonFlag'),
                        'allowActionFlag' => $dsCallActType->getValue('allowActionFlag'),
                        'allowFinalStatusFlag' => $dsCallActType->getValue('allowFinalStatusFlag'),
                        'reqReasonFlag' => $dsCallActType->getValue('reqReasonFlag'),
                        'reqActionFlag' => $dsCallActType->getValue('reqActionFlag'),
                        'reqFinalStatusFlag' => $dsCallActType->getValue('reqFinalStatusFlag'),
                        'activeFlag' => $dsCallActType->getValue('activeFlag'),
                        'showNotChargeableFlag' => $dsCallActType->getValue('showNotChargeableFlag'),
                        'engineerOvertimeFlag' => $dsCallActType->getValue('engineerOvertimeFlag'),
                        'travelFlag' => $dsCallActType->getValue('travelFlag'),
                        'onSiteFlag' => $dsCallActType->getValue('onSiteFlag'),
                        'portalDisplayFlag' => $dsCallActType->getValue('portalDisplayFlag'),
                        'urlEdit' => $urlEdit,
                        'txtEdit' => $txtEdit
                    )
                );
                $this->template->parse('types', 'typeBlock', true);
            }//while $dsCallActType->fetchNext()
        }
        $this->template->parse('CONTENTS', 'ActivityTypeList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Activity
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsCallActType = &$this->dsCallActType; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTACTIVITYTYPE_ACT_EDIT) {
                $this->buActivityType->getActivityTypeByID($_REQUEST['callActTypeID'], $dsCallActType);
                $callActTypeID = $_REQUEST['callActTypeID'];
            } else {                                                                    // creating new
                $dsCallActType->initialise();
                $dsCallActType->setValue('callActTypeID', '0');
                $callActTypeID = '0';
            }
        } else {                                                                        // form validation error
            $dsCallActType->initialise();
            $dsCallActType->fetchNext();
            $callActTypeID = $dsCallActType->getValue('callActTypeID');
        }
        if ($_REQUEST['action'] == CTACTIVITYTYPE_ACT_EDIT && $this->buActivityType->canDeleteActivityType($_REQUEST['callActTypeID'])) {
            $urlDelete =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTACTIVITYTYPE_ACT_DELETE,
                        'callActTypeID' => $callActTypeID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITYTYPE_ACT_UPDATE,
                    'callActTypeID' => $callActTypeID
                )
            );
        $urlItemPopup =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_DISP_ITEM_POPUP,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlItemEdit =
            $this->buildLink(
                CTCNC_PAGE_ITEM,
                array(
                    'action' => CTCNC_ACT_ITEM_EDIT,
                    'htmlFmt' => CT_HTML_FMT_POPUP
                )
            );
        $urlDisplayList =
            $this->buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITYTYPE_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Activity Type');
        $this->setTemplateFiles(
            array('ActivityTypeEdit' => 'ActivityTypeEdit.inc')
        );
        $this->template->set_var(
            array(
                'callActTypeID' => $callActTypeID,
                'itemID' => $dsCallActType->getValue('itemID'),
                'description' => Controller::htmlInputText($dsCallActType->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsCallActType->getMessage('description')),
                'itemDescription' => Controller::htmlInputText($dsCallActType->getValue('itemDescription')),
                'curValueFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('curValueFlag')),
                'customerEmailFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('customerEmailFlag')),
                'oohMultiplier' => Controller::htmlInputText($dsCallActType->getValue('oohMultiplier')),
                'oohMultiplierMessage' => Controller::htmlDisplayText($dsCallActType->getMessage('oohMultiplier')),
                'maxHours' => Controller::htmlInputText($dsCallActType->getValue('maxHours')),
                'maxHoursMessage' => Controller::htmlDisplayText($dsCallActType->getMessage('maxHours')),
                'minHours' => Controller::htmlInputText($dsCallActType->getValue('minHours')),
                'minHoursMessage' => Controller::htmlDisplayText($dsCallActType->getMessage('minHours')),
                'allowSCRFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('allowSCRFlag')),
                'requireCheckFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('requireCheckFlag')),
                'allowExpensesFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('allowExpensesFlag')),
                'allowReasonFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('allowReasonFlag')),
                'allowActionFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('allowActionFlag')),
                'allowFinalStatusFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('allowFinalStatusFlag')),
                'reqReasonFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('reqReasonFlag')),
                'reqActionFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('reqActionFlag')),
                'reqFinalStatusFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('reqFinalStatusFlag')),
                'activeFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('activeFlag')),
                'showNotChargeableFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('showNotChargeableFlag')),
                'engineerOvertimeFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('engineerOvertimeFlag')),
                'portalDisplayFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('portalDisplayFlag')),
                'travelFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('travelFlag')),
                'onSiteFlagChecked' => Controller::htmlChecked($dsCallActType->getValue('onSiteFlag')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList,
                'urlItemEdit' => $urlItemEdit,
                'urlItemPopup' => $urlItemPopup
            )
        );
        $this->template->parse('CONTENTS', 'ActivityTypeEdit', true);
        $this->parsePage();
    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsCallActType = &$this->dsCallActType;
        $this->formError = (!$this->dsCallActType->populateFromArray($_REQUEST['callActType']));
        if ($this->formError) {
            if ($this->dsCallActType->getValue('callActTypeID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTACTIVITYTYPE_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTACTIVITYTYPE_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buActivityType->updateActivityType($this->dsCallActType);

        $urlNext =
            $this->buildLink($_SERVER['PHP_SELF'],
                array(
                    'callActTypeID' => $this->dsCallActType->getValue('callActTypeID'),
                    'action' => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Activity
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buActivityType->deleteActivityType($_REQUEST['callActTypeID'])) {
            $this->displayFatalError('Cannot delete this activity type');
            exit;
        } else {
            $urlNext =
                $this->buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTACTIVITYTYPE_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}// end of class
?>