<?php
/**
 * Activity Type controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global

use CNCLTD\Exceptions\JsonHttpException;

$cfg;
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
    /** @var DSForm */
    public $dsCallActType;
    /** @var BUActivityType */
    public $buActivityType;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles            = MAINTENANCE_PERMISSION;
        $noPermissionList = ["getCallActTypes", "getAllDetails", "updateActivityTypeOrder"];
        $key              = array_search(@$_REQUEST["action"], $noPermissionList);
        if ($key === false && !self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(801);
        $this->buActivityType = new BUActivityType($this);
        $this->dsCallActType  = new DSForm($this);
        $this->dsCallActType->copyColumnsFrom($this->buActivityType->dbeCallActType);
        $this->dsCallActType->addColumn(DBEJCallActType::itemDescription, DA_STRING, DA_ALLOW_NULL);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
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
            case "getCallActTypes":
                echo json_encode($this->getCallActTypes());
                exit;
            case "getAllDetails":
                echo json_encode($this->getActTypeList());
                exit;
            case CTACTIVITYTYPE_ACT_DISPLAY_LIST:
            case "updateActivityTypeOrder":
                $this->updateActivityTypeOrder();
                echo json_encode(["status" => "ok"]);
                exit;
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * Edit/Add Activity
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsCallActType = &$this->dsCallActType; // ref to class var
        if (!$this->getFormError()) {
            if ($this->getAction() == CTACTIVITYTYPE_ACT_EDIT) {
                $this->buActivityType->getActivityTypeByID($this->getParam('callActTypeID'), $dsCallActType);
                $callActTypeID = $this->getParam('callActTypeID');
            } else {                                                                    // creating new
                $dsCallActType->initialise();
                $dsCallActType->setValue(DBECallActType::callActTypeID, '0');
                $callActTypeID = '0';
            }
        } else {                                                                        // form validation error
            $dsCallActType->initialise();
            $dsCallActType->fetchNext();
            $callActTypeID = $dsCallActType->getValue(DBECallActType::callActTypeID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTACTIVITYTYPE_ACT_EDIT && $this->buActivityType->canDeleteActivityType(
                $this->getParam('callActTypeID')
            )) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'        => CTACTIVITYTYPE_ACT_DELETE,
                    'callActTypeID' => $callActTypeID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate      = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'        => CTACTIVITYTYPE_ACT_UPDATE,
                'callActTypeID' => $callActTypeID
            )
        );
        $urlItemPopup   = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_DISP_ITEM_POPUP,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlItemEdit    = Controller::buildLink(
            CTCNC_PAGE_ITEM,
            array(
                'action'  => CTCNC_ACT_ITEM_EDIT,
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );
        $urlDisplayList = Controller::buildLink(
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
                'callActTypeID'                    => $callActTypeID,
                'itemID'                           => $dsCallActType->getValue(DBECallActType::itemID),
                'description'                      => Controller::htmlInputText(
                    $dsCallActType->getValue(DBECallActType::description)
                ),
                'descriptionMessage'               => Controller::htmlDisplayText(
                    $dsCallActType->getMessage(DBECallActType::description)
                ),
                'itemDescription'                  => Controller::htmlInputText(
                    $dsCallActType->getValue(DBEJCallActType::itemDescription)
                ),
                'curValueFlagChecked'              => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::curValueFlag)
                ),
                'customerEmailFlagChecked'         => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::customerEmailFlag)
                ),
                'oohMultiplier'                    => Controller::htmlInputText(
                    $dsCallActType->getValue(DBECallActType::oohMultiplier)
                ),
                'oohMultiplierMessage'             => Controller::htmlDisplayText(
                    $dsCallActType->getMessage(DBECallActType::oohMultiplier)
                ),
                'maxHours'                         => Controller::htmlInputText(
                    $dsCallActType->getValue(DBECallActType::maxHours)
                ),
                'maxHoursMessage'                  => Controller::htmlDisplayText(
                    $dsCallActType->getMessage(DBECallActType::maxHours)
                ),
                'minHours'                         => Controller::htmlInputText(
                    $dsCallActType->getValue(DBECallActType::minHours)
                ),
                'minHoursMessage'                  => Controller::htmlDisplayText(
                    $dsCallActType->getMessage(DBECallActType::minHours)
                ),
                'requireCheckFlagChecked'          => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::requireCheckFlag)
                ),
                'activeFlagChecked'                => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::activeFlag)
                ),
                'engineerOvertimeFlagChecked'      => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::engineerOvertimeFlag)
                ),
                'portalDisplayFlagChecked'         => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::portalDisplayFlag)
                ),
                'travelFlagChecked'                => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::travelFlag)
                ),
                'onSiteFlagChecked'                => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::onSiteFlag)
                ),
                'visibleInSRFlagChecked'           => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::visibleInSRFlag)
                ),
                'activityNotesRequiredChecked'     => Controller::htmlChecked(
                    $dsCallActType->getValue(DBECallActType::activityNotesRequired)
                ),
                'urlUpdate'                        => $urlUpdate,
                'urlDelete'                        => $urlDelete,
                'txtDelete'                        => $txtDelete,
                'urlDisplayList'                   => $urlDisplayList,
                'urlItemEdit'                      => $urlItemEdit,
                'urlItemPopup'                     => $urlItemPopup,
                'catRequireCNCNextActionCNCAction' => $dsCallActType->getValue(
                    DBECallActType::catRequireCNCNextActionCNCAction
                ),
                'catRequireCNCNextActionOnHold'    => $dsCallActType->getValue(
                    DBECallActType::catRequireCNCNextActionOnHold
                ),
                'catRequireCustomerNoteCNCAction'  => $dsCallActType->getValue(
                    DBECallActType::catRequireCustomerNoteCNCAction
                ),
                'catRequireCustomerNoteOnHold'     => $dsCallActType->getValue(
                    DBECallActType::catRequireCustomerNoteOnHold
                ),
                'minMinutesAllowed'                => $dsCallActType->getValue(DBECallActType::minMinutesAllowed),
            )
        );
        $this->template->parse('CONTENTS', 'ActivityTypeEdit', true);
        $this->parsePage();
    }

    /**
     * Delete Activity
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buActivityType->deleteActivityType($this->getParam('callActTypeID'))) {
            $this->displayFatalError('Cannot delete this activity type');
            exit;
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTACTIVITYTYPE_ACT_DISPLAY_LIST
                )
            );
            header('Location: ' . $urlNext);
            exit;
        }
    }// end function editActivity()

    /**
     * Update call activity type details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsCallActType->populateFromArray($this->getParam('callActType')));
        if ($this->formError) {
            if (!$this->dsCallActType->getValue(DBECallActType::callActTypeID)) {
                $this->setAction(CTACTIVITYTYPE_ACT_EDIT);
            } else {
                $this->setAction(CTACTIVITYTYPE_ACT_CREATE);
            }
            $this->edit();
            exit;
        }
        $this->buActivityType->updateActivityType($this->dsCallActType);
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'callActTypeID' => $this->dsCallActType->getValue(DBECallActType::callActTypeID),
                'action'        => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }

    function getCallActTypes()
    {
        $dbeCallActType = new DBECallActType($this);
        $dbeCallActType->setValue(
            DBEJCallActType::activeFlag,
            'Y'
        );
        $dbeCallActType->getRowsByColumn(
            DBEJCallActType::activeFlag,
            'description'
        );
        $types = array();
        while ($dbeCallActType->fetchNext()) {
            array_push(
                $types,
                [
                    'id'                               => $dbeCallActType->getValue(DBECallActType::callActTypeID),
                    'description'                      => $dbeCallActType->getValue(DBECallActType::description),
                    'allowOvertime'                    => $dbeCallActType->getValue(
                        DBECallActType::engineerOvertimeFlag
                    ) == 'Y' ? 1 : 0,
                    "curValueFlag"                     => $dbeCallActType->getValue(DBECallActType::curValueFlag),
                    "requireCheckFlag"                 => $dbeCallActType->getValue(DBECallActType::requireCheckFlag),
                    'onSiteFlag'                       => $dbeCallActType->getValue(DBECallActType::onSiteFlag),
                    'catRequireCNCNextActionCNCAction' => $dbeCallActType->getValue(
                        DBECallActType::catRequireCNCNextActionCNCAction
                    ),
                    'catRequireCustomerNoteCNCAction'  => $dbeCallActType->getValue(
                        DBECallActType::catRequireCustomerNoteCNCAction
                    ),
                    'catRequireCNCNextActionOnHold'    => $dbeCallActType->getValue(
                        DBECallActType::catRequireCNCNextActionOnHold
                    ),
                    'catRequireCustomerNoteOnHold'     => $dbeCallActType->getValue(
                        DBECallActType::catRequireCustomerNoteOnHold
                    ),
                    'visibleInSRFlag'                  => $dbeCallActType->getValue(DBECallActType::visibleInSRFlag),
                    'minMinutesAllowed'                => $dbeCallActType->getValue(DBECallActType::minMinutesAllowed),
                    'order'                            => $dbeCallActType->getValue(DBECallActType::orderNum),
                    "activityNotesRequired"            => $dbeCallActType->getValue(
                        DBECallActType::activityNotesRequired
                    )
                ]
            );
        }
        return $types;
    }

    /**
     * @return array
     */
    function getActTypeList(): array
    {
        $dsCallActType = new DataSet($this);
        $this->buActivityType->getAllTypes($dsCallActType);
        $types = array();
        if ($dsCallActType->rowCount()) {
            while ($dsCallActType->fetchNext()) {
                $callActTypeID = $dsCallActType->getValue(DBECallActType::callActTypeID);
                array_push(
                    $types,
                    array(
                        'callActTypeID'                    => $callActTypeID,
                        'description'                      => Controller::htmlDisplayText(
                            $dsCallActType->getValue(DBECallActType::description)
                        ),
                        'curValueFlag'                     => $dsCallActType->getValue(DBECallActType::curValueFlag),
                        'customerEmailFlag'                => $dsCallActType->getValue(
                            DBECallActType::customerEmailFlag
                        ),
                        'oohMultiplier'                    => Controller::htmlInputText(
                            $dsCallActType->getValue(DBECallActType::oohMultiplier)
                        ),
                        'maxHours'                         => Controller::htmlInputText(
                            $dsCallActType->getValue(DBECallActType::maxHours)
                        ),
                        'minHours'                         => Controller::htmlInputText(
                            $dsCallActType->getValue(DBECallActType::minHours)
                        ),
                        'requireCheckFlag'                 => $dsCallActType->getValue(
                            DBECallActType::requireCheckFlag
                        ),
                        'activeFlag'                       => $dsCallActType->getValue(DBECallActType::activeFlag),
                        'engineerOvertimeFlag'             => $dsCallActType->getValue(
                            DBECallActType::engineerOvertimeFlag
                        ),
                        'travelFlag'                       => $dsCallActType->getValue(DBECallActType::travelFlag),
                        'onSiteFlag'                       => $dsCallActType->getValue(DBECallActType::onSiteFlag),
                        'portalDisplayFlag'                => $dsCallActType->getValue(
                            DBECallActType::portalDisplayFlag
                        ),
                        'visibleInSRFlag'                  => $dsCallActType->getValue(DBECallActType::visibleInSRFlag),
                        'catRequireCNCNextActionCNCAction' => $this->getCatRequireTitle(
                            $dsCallActType->getValue(DBECallActType::catRequireCNCNextActionCNCAction)
                        ),
                        'catRequireCustomerNoteCNCAction'  => $this->getCatRequireTitle(
                            $dsCallActType->getValue(DBECallActType::catRequireCustomerNoteCNCAction)
                        ),
                        'catRequireCNCNextActionOnHold'    => $this->getCatRequireTitle(
                            $dsCallActType->getValue(DBECallActType::catRequireCNCNextActionOnHold)
                        ),
                        'catRequireCustomerNoteOnHold'     => $this->getCatRequireTitle(
                            $dsCallActType->getValue(DBECallActType::catRequireCustomerNoteOnHold)
                        ),
                        "minMinutesAllowed"                => $dsCallActType->getValue(
                            DBECallActType::minMinutesAllowed
                        ),
                        "order"                            => $dsCallActType->getValue(DBECallActType::orderNum),
                        "activityNotesRequired"            => $dsCallActType->getValue(
                            DBECallActType::activityNotesRequired
                        )
                    )
                );
            }
        }
        return $types;
    }

    function getCatRequireTitle($value)
    {
        if ($value == 0) return "Off";
        if ($value == 1) return "On";
        if ($value == 2) return "Optional";
    }

    function updateActivityTypeOrder()
    {
        $data = $this->getJSONData();
        if (!isset($data['fromActivityTypeId'])) {
            throw new JsonHttpException(400, 'fromActivityTypeId is required');
        }
        $dbeActivityTypeFrom = new DBECallActType($this);
        $dbeActivityTypeFrom->getRow($data['fromActivityTypeId']);
        if (!isset($data['toActivityTypeId'])) {
            $dbeActivityTypeFrom->moveItemToBottom($data['fromActivityTypeId']);
            return;
        }
        $dbeActivityTypeTo = new DBECallActType($this);
        $dbeActivityTypeTo->getRow($data['toActivityTypeId']);
        $dbeActivityTypeFrom->swapPlaces(
            $dbeActivityTypeFrom->getValue(DBECallActType::orderNum),
            $dbeActivityTypeTo->getValue(DBECallActType::orderNum)
        );
    }

    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Activity Types');
        $this->setTemplateFiles(
            array('ActivityTypeList' => 'ActivityTypeList.rct')
        );
        $this->loadReactScript('ActivityTypeComponent.js');
        $this->loadReactCSS('ActivityTypeComponent.css');
        $this->template->parse('CONTENTS', 'ActivityTypeList', true);
        $this->parsePage();
    }
}
