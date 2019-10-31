<?php
/**
 * Password service controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
global $cfg;
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
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);

        switch ($this->getAction()) {
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
            /** @noinspection PhpMissingBreakStatementInspection */
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
        if (!$this->getParam('sortOrder')) {
            return;
        }

        foreach ($this->getParam('sortOrder') as $passwordServiceID => $value) {

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
     * @throws Exception
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
                    'sortOrderUp'       => $up ? null : 'disabled',
                    'sortOrderDown'     => $down ? null : 'disabled',
                    'sortOrderTop'      => $top ? null : 'disabled',
                    'sortOrderBottom'   => $bottom ? null : 'disabled',
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
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsPasswordService = &$this->dsPasswordService; // ref to class var

        if (!$this->getFormError()) {

            if ($this->getAction() == CTPasswordService_ACT_EDIT) {
                $this->buPasswordService->getPasswordServiceByID(
                    $this->getParam('passwordServiceID'),
                    $dsPasswordService
                );
                $passwordServiceID = $this->getParam('passwordServiceID');
            } else {                                                                    // creating new
                $dsPasswordService->initialise();
                $dsPasswordService->setValue(
                    DBEPasswordService::passwordServiceID,
                    0
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
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() != CTPasswordService_ACT_EDIT) {
        } else {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'            => CTPasswordService_ACT_DELETE,
                        'passwordServiceID' => $passwordServiceID
                    )
                );
            $txtDelete = 'Delete';
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
                ) ? 'checked' : null,
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
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsPasswordService->populateFromArray($this->getParam('passwordService')));

        if ($this->formError) {
            if (!$this->dsPasswordService->getValue(
                DBEPasswordService::passwordServiceID
            )) {                    // attempt to insert
                $this->setAction(CTPasswordService_ACT_EDIT);
            } else {
                $this->setAction(CTPasswordService_ACT_CREATE);
            }
            $this->edit();
            exit;
        }

        $this->buPasswordService->updatePasswordService($this->dsPasswordService);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'passwordServiceID' => $this->dsPasswordService->getValue(DBEPasswordService::passwordServiceID),
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
            $this->buPasswordService->deletePasswordService($this->getParam('passwordServiceID'));
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
}
