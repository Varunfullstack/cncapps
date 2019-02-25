<?php
/**
 * Password service controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUPasswordService.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define(
    'CTPASSWORDSERVICE_ACT_DISPLAY_LIST',
    'passwordServiceList'
);
define(
    'CTPasswordService_ACT_CREATE',
    'createPasswordService'
);
define(
    'CTPasswordService_ACT_EDIT',
    'editPasswordService'
);
define(
    'CTPasswordService_ACT_DELETE',
    'deletePasswordService'
);
define(
    'CTPasswordService_ACT_UPDATE',
    'updatePasswordService'
);

define(
    'CT_PASSWORD_SERVICE_ACT_CHANGE_ORDER',
    'changeOrder'
);

class CTPasswordServices extends CTCNC
{
    public $dsPasswordService;
    /** @var BUPasswordService */
    public $buPasswordService;

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
        $this->buPasswordService = new BUPasswordService($this);
        $this->dsPasswordService = new DSForm($this);
        $this->dsPasswordService->copyColumnsFrom($this->buPasswordService->dbePasswordService);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTPasswordService_ACT_EDIT:
            case CTPasswordService_ACT_CREATE:
                $this->edit();
                break;
            case CTPasswordService_ACT_DELETE:
                $this->delete();
                break;
            case CTPasswordService_ACT_UPDATE:
                $this->update();
                break;

            case CT_PASSWORD_SERVICE_ACT_CHANGE_ORDER:
                $this->changeOrder();
            case CTPASSWORDSERVICE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }


    function changeOrder()
    {
        if (!isset($_REQUEST['sortOrder'])) {
            return;
        }

        foreach ($_REQUEST['sortOrder'] as $passwordServiceID => $value) {

            $dbePasswordService = new DBEPasswordService($this);

            switch ($value) {
                case 'top':
                    $dbePasswordService->moveItemToTop($passwordServiceID);
                    break;
                case 'bottom':
                    $dbePasswordService->moveItemToBottom($passwordServiceID);
                    break;
                case 'down':
                    $dbePasswordService->moveItemDown($passwordServiceID);
                    break;
                case 'up':
                    $dbePasswordService->moveItemUp($passwordServiceID);
                    break;
            }

        }
    }

    /**
     * Display list of types
     * @access private
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Password service');
        $this->setTemplateFiles(
            array('PasswordServiceList' => 'PasswordServiceList.inc')
        );


        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPasswordService_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );


        $dbePasswordService = new DBEPasswordService($this);
        $dbePasswordService->getRows(DBEPasswordService::sortOrder);

        $this->template->set_block(
            'PasswordServiceList',
            'passwordServiceBlock',
            'passwordServices'
        );
        $count = 0;
        $totalCount = $dbePasswordService->rowCount();

        while ($dbePasswordService->fetchNext()) {

            $passwordServiceID = $dbePasswordService->getValue(DBEPasswordService::passwordServiceID);

            $urlEdit =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'            => CTPasswordService_ACT_EDIT,
                        'passwordServiceID' => $passwordServiceID
                    )
                );
            $txtEdit = '[edit]';

            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'            => CTPasswordService_ACT_DELETE,
                        'passwordServiceID' => $passwordServiceID
                    )
                );
            $txtDelete = '[delete]';

            $up = true;
            $down = true;
            $top = true;
            $bottom = true;

            if (!$count) {
                $top = false;
                $up = false;
            }

            $count++;

            if ($count == $totalCount) {
                $down = false;
                $bottom = false;
            }


            $this->template->set_var(
                array(
                    'passwordServiceID' => $passwordServiceID,
                    'description'       => Controller::htmlDisplayText(
                        $dbePasswordService->getValue(DBEPasswordService::description)
                    ),
                    'onePerCustomer'    => Controller::htmlDisplayText(
                        $dbePasswordService->getValue(DBEPasswordService::onePerCustomer) ? 'Yes' : 'No'
                    ),
                    'urlEdit'           => $urlEdit,
                    'urlDelete'         => $urlDelete,
                    'txtEdit'           => $txtEdit,
                    'txtDelete'         => $txtDelete,
                    'sortOrderUp'       => $up ? '' : 'disabled',
                    'sortOrderDown'     => $down ? '' : 'disabled',
                    'sortOrderTop'      => $top ? '' : 'disabled',
                    'sortOrderBottom'   => $bottom ? '' : 'disabled',
                )
            );
            $this->template->parse(
                'passwordServices',
                'passwordServiceBlock',
                true
            );
        }
        $this->template->parse(
            'CONTENTS',
            'PasswordServiceList',
            true
        );
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsPasswordService = &$this->dsPasswordService; // ref to class var

        if (!$this->getFormError()) {

            if ($_REQUEST['action'] == CTPasswordService_ACT_EDIT) {
                $this->buPasswordService->getPasswordServiceByID(
                    $_REQUEST['passwordServiceID'],
                    $dsPasswordService
                );
                $passwordServiceID = $_REQUEST['passwordServiceID'];
            } else {                                                                    // creating new
                $dsPasswordService->initialise();
                $dsPasswordService->setValue(
                    'passwordServiceID',
                    '0'
                );
                $dbePasswordService = new DBEPasswordService($this);
                $dsPasswordService->setValue(
                    DBEPasswordService::sortOrder,
                    $dbePasswordService->getNextSortOrder()
                );
                $passwordServiceID = 0;
            }
        } else {                                                                        // form validation error
            $dsPasswordService->initialise();
            $dsPasswordService->fetchNext();
            $passwordServiceID = $dsPasswordService->getValue(DBEPasswordService::passwordServiceID);
        }
        if ($_REQUEST['action'] == CTPasswordService_ACT_EDIT) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'            => CTPasswordService_ACT_DELETE,
                        'passwordServiceID' => $passwordServiceID
                    )
                );
            $txtDelete = 'Delete';
        } else {
            $urlDelete = '';
            $txtDelete = '';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'            => CTPasswordService_ACT_UPDATE,
                    'passwordServiceID' => $passwordServiceID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTPASSWORDSERVICE_ACT_DISPLAY_LIST
                )
            );
        $title = 'Edit Password Service';
        if (!$passwordServiceID) {
            $title = "Create Password Service";
        }
        $this->setPageTitle($title);
        $this->setTemplateFiles(
            array('PasswordServiceEdit' => 'PasswordServiceEdit.inc')
        );
        $this->template->set_var(
            array(
                'passwordServiceID'     => $passwordServiceID,
                'sortOrder'             => Controller::htmlInputText(
                    $dsPasswordService->getValue(DBEPasswordService::sortOrder)
                ),
                'sortOrderMessage'      => Controller::htmlDisplayText(
                    $dsPasswordService->getMessage(DBEPasswordService::sortOrder)
                ),
                'description'           => Controller::htmlInputText(
                    $dsPasswordService->getValue(DBEPasswordService::description)
                ),
                'descriptionMessage'    => Controller::htmlDisplayText(
                    $dsPasswordService->getMessage(DBEPasswordService::description)
                ),
                'onePerCustomerChecked' => $dsPasswordService->getValue(
                    DBEPasswordService::onePerCustomer
                ) ? 'checked' : '',
                'onePerCustomerMessage' => Controller::htmlDisplayText(
                    $dsPasswordService->getMessage(DBEPasswordService::onePerCustomer)
                ),
                'updateOrCreate'        => !$passwordServiceID ? 'Create' : 'Update',
                'urlUpdate'             => $urlUpdate,
                'urlDelete'             => $urlDelete,
                'txtDelete'             => $txtDelete,
                'urlDisplayList'        => $urlDisplayList
            )
        );

        $this->template->parse(
            'CONTENTS',
            'PasswordServiceEdit',
            true
        );
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsPasswordService = &$this->dsPasswordService;

        $this->formError = (!$this->dsPasswordService->populateFromArray($_REQUEST['passwordService']));

        if ($this->formError) {
            if ($this->dsPasswordService->getValue(
                    'passwordServiceID'
                ) == '') {                    // attempt to insert
                $_REQUEST['action'] = CTPasswordService_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTPasswordService_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buPasswordService->updatePasswordService($this->dsPasswordService);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'passwordServiceID' => $this->dsPasswordService->getValue('passwordServiceID'),
                    'action'            => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function delete()
    {
        $this->setMethodName('delete');
        try {
            $this->buPasswordService->deletePasswordService($_REQUEST['passwordServiceID']);
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTPASSWORDSERVICE_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        } catch (Exception $exception) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        }
    }
}// end of class
?>