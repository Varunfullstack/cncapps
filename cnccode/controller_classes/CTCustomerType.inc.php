<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUCustomerType.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTCUSTOMERTYPE_ACT_DISPLAY_LIST', 'sectorList');
define('CTCUSTOMERTYPE_ACT_CREATE', 'createCustomerType');
define('CTCUSTOMERTYPE_ACT_EDIT', 'editCustomerType');
define('CTCUSTOMERTYPE_ACT_DELETE', 'deleteCustomerType');
define('CTCUSTOMERTYPE_ACT_UPDATE', 'updateCustomerType');

class CTCustomerType extends CTCNC
{
    /** @var DSForm */
    public $dsCustomerType;
    /** @var BUCustomerType */
    public $buCustomerType;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "maintenance",
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buCustomerType = new BUCustomerType($this);
        $this->dsCustomerType = new DSForm($this);
        $this->dsCustomerType->copyColumnsFrom($this->buCustomerType->dbeCustomerType);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
        switch ($_REQUEST['action']) {
            case CTCUSTOMERTYPE_ACT_EDIT:
            case CTCUSTOMERTYPE_ACT_CREATE:
                $this->edit();
                break;
            case CTCUSTOMERTYPE_ACT_DELETE:
                $this->delete();
                break;
            case CTCUSTOMERTYPE_ACT_UPDATE:
                $this->update();
                break;
            case CTCUSTOMERTYPE_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
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
        $this->setPageTitle('Referral Types');
        $this->setTemplateFiles(
            array('CustomerTypeList' => 'CustomerTypeList.inc')
        );
        $dsCustomerType = new DataSet($this);
        $this->buCustomerType->getAll($dsCustomerType);

        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCUSTOMERTYPE_ACT_CREATE
            )
        );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsCustomerType->rowCount() > 0) {

            $this->template->set_block(
                'CustomerTypeList',
                'CustomerTypeBlock',
                'customerTypes'
            );

            while ($dsCustomerType->fetchNext()) {

                $customerTypeID = $dsCustomerType->getValue(DBECustomerType::customerTypeID);

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => CTCUSTOMERTYPE_ACT_EDIT,
                            'customerTypeID' => $customerTypeID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete = null;
                $txtDelete = null;
                if ($this->buCustomerType->canDelete($customerTypeID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'         => CTCUSTOMERTYPE_ACT_DELETE,
                                'customerTypeID' => $customerTypeID
                            )
                        );
                    $txtDelete = '[delete]';
                }

                $this->template->set_var(
                    array(
                        'customerTypeID' => $customerTypeID,
                        'description'    => Controller::htmlDisplayText(
                            $dsCustomerType->getValue(DBECustomerType::description)
                        ),
                        'urlEdit'        => $urlEdit,
                        'urlDelete'      => $urlDelete,
                        'txtEdit'        => $txtEdit,
                        'txtDelete'      => $txtDelete
                    )
                );

                $this->template->parse('customerTypes', 'CustomerTypeBlock', true);

            }//while $dsCustomerType->fetchNext()
        }
        $this->template->parse('CONTENTS', 'CustomerTypeList', true);
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
        $dsCustomerType = &$this->dsCustomerType; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTCUSTOMERTYPE_ACT_EDIT) {
                $this->buCustomerType->getCustomerTypeByID($_REQUEST['customerTypeID'], $dsCustomerType);
                $customerTypeID = $_REQUEST['customerTypeID'];
            } else {                                                                    // creating new
                $dsCustomerType->initialise();
                $dsCustomerType->setValue(DBECustomerType::customerTypeID, '0');
                $customerTypeID = '0';
            }
        } else {                                                                        // form validation error
            $dsCustomerType->initialise();
            $dsCustomerType->fetchNext();
            $customerTypeID = $dsCustomerType->getValue(DBECustomerType::customerTypeID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($_REQUEST['action'] == CTCUSTOMERTYPE_ACT_EDIT && $this->buCustomerType->canDelete(
                $_REQUEST['customerTypeID']
            )) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTCUSTOMERTYPE_ACT_DELETE,
                    'customerTypeID' => $customerTypeID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => CTCUSTOMERTYPE_ACT_UPDATE,
                    'customerTypeID' => $customerTypeID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTCUSTOMERTYPE_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Referral Type');
        $this->setTemplateFiles(
            array('CustomerTypeEdit' => 'CustomerTypeEdit.inc')
        );
        $this->template->set_var(
            array(
                'customerTypeID'     => $customerTypeID,
                'description'        => Controller::htmlInputText(
                    $dsCustomerType->getValue(DBECustomerType::description)
                ),
                'descriptionMessage' => Controller::htmlDisplayText(
                    $dsCustomerType->getMessage(DBECustomerType::description)
                ),
                'urlUpdate'          => $urlUpdate,
                'urlDelete'          => $urlDelete,
                'txtDelete'          => $txtDelete,
                'urlDisplayList'     => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'CustomerTypeEdit', true);
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
        $this->formError = (!$this->dsCustomerType->populateFromArray($_REQUEST['customerType']));
        if ($this->formError) {
            if (!$this->dsCustomerType->getValue(DBECustomerType::customerTypeID)) {
                $_REQUEST['action'] = CTCUSTOMERTYPE_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTCUSTOMERTYPE_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buCustomerType->updateCustomerType($this->dsCustomerType);

        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'customerTypeID' => $this->dsCustomerType->getValue(DBECustomerType::customerTypeID),
                'action'         => CTCNC_ACT_VIEW
            )
        );
        header('Location: ' . $urlNext);
    }

    /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buCustomerType->deleteCustomerType($_REQUEST['customerTypeID'])) {
            $this->displayFatalError('Cannot delete this row');
            exit;
        }
        $urlNext = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTCUSTOMERTYPE_ACT_DISPLAY_LIST
            )
        );
        header('Location: ' . $urlNext);
        exit;
    }
}
