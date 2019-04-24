<?php
/**
 * Manufacturer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_bu'] . '/BUManufacturer.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define('CTMANUFACTURER_MSG_NONE_FND', 'No manufacturers found');
define('CTMANUFACTURER_MSG_MANUFACTURER_NOT_FND', 'Manufacturer not found');
define('CTMANUFACTURER_MSG_MANUFACTURERID_NOT_PASSED', 'ManufacturerID not passed');
define('CTMANUFACTURER_MSG_MANUFACTURER_ARRAY_NOT_PASSED', 'Manufacturer array not passed');
// Actions
define('CTMANUFACTURER_ACT_DISPLAY_LIST', 'listManufacturers');
define('CTMANUFACTURER_ACT_DELETE', 'deleteManufacturer');
define('CTMANUFACTURER_ACT_UPDATE', 'updateManufacturer');
// Page text
define('CTMANUFACTURER_TXT_NEW_MANUFACTURER', 'Create Manufacturer');
define('CTMANUFACTURER_TXT_UPDATE_MANUFACTURER', 'Update Manufacturer');


class CTManufacturer extends CTCNC
{
    /** @var DSForm */
    public $dsManufacturer;
    /**
     * @var BUManufacturer
     */
    public $buManufacturer;

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
        $this->buManufacturer = new BUManufacturer($this);
        $this->dsManufacturer = new DSForm($this);
        $this->dsManufacturer->copyColumnsFrom($this->buManufacturer->dbeManufacturer);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->setParentFormFields();
        switch ($_REQUEST['action']) {
            case 'editManufacturer':
            case 'createManufacturer':
                $this->edit();
                break;
            case 'deleteManufacturer':
                $this->delete();
                break;
            case 'updateManufacturer':
                $this->update();
                break;
            case 'displayPopup':
                $this->displayManufacturerSelectPopup();
                break;
            case CTMANUFACTURER_ACT_DISPLAY_LIST:
            default:
                $this->displayList();
                break;
        }
    }

    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if (isset($_REQUEST['parentIDField'])) {
            $_SESSION['manufacturerParentIDField'] = $_REQUEST['parentIDField'];
        }
        if (isset($_REQUEST['parentDescField'])) {
            $_SESSION['manufacturerParentDescField'] = $_REQUEST['parentDescField'];
        }
    }

    /**
     * Display the popup selector form
     * @access private
     * @throws Exception
     */
    function displayManufacturerSelectPopup()
    {
        common_decodeQueryArray($_REQUEST);

        $this->setMethodName('displayManufacturerSelectPopup');
        // this may be required in a number of situations
        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'  => 'createManufacturer',
                'htmlFmt' => CT_HTML_FMT_POPUP
            )
        );

        // A single slash means create new manufacturer
        if ($_REQUEST['manufacturerName']{0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $dsManufacturer = new DataSet($this);
        $this->buManufacturer->getManufacturersByNameMatch($_REQUEST['manufacturerName'], $dsManufacturer);
        $this->template->set_var(
            array(
                'parentIDField'   => $_SESSION['manufacturerParentIDField'],
                'parentDescField' => $_SESSION['manufacturerParentDescField']
            )
        );
        if ($dsManufacturer->rowCount() == 1) {
            $this->setTemplateFiles('ManufacturerSelect', 'ManufacturerSelectOne.inc');
            // This template runs a javascript function NOT inside HTML and so must use stripslashes()
            $this->template->set_var(
                array(
                    'submitDescription' => addslashes($dsManufacturer->getValue(DBEManufacturer::name)),
                    // for javascript
                    'manufacturerID'    => $dsManufacturer->getValue(DBEManufacturer::manufacturerID)
                )
            );
        } else {
            if ($dsManufacturer->rowCount() == 0) {
                $this->template->set_var(
                    array(
                        'manufacturerName' => $_REQUEST['manufacturerName'],
                    )
                );
                $this->setTemplateFiles('ManufacturerSelect', 'ManufacturerSelectNone.inc');
            }
            if ($dsManufacturer->rowCount() > 1) {
                $this->setTemplateFiles('ManufacturerSelect', 'ManufacturerSelectPopup.inc');
            }
            $this->template->set_var(
                array(
                    'urlManufacturerCreate' => $urlCreate
                )
            );
            // Parameters
            $this->setPageTitle('Manufacturer Selection');
            if ($dsManufacturer->rowCount() > 0) {
                $this->template->set_block('ManufacturerSelect', 'manufacturerBlock', 'manufacturers');
                while ($dsManufacturer->fetchNext()) {
                    $this->template->set_var(
                        array(
                            'manufacturerName'  => Controller::htmlDisplayText(
                                $dsManufacturer->getValue(DBEManufacturer::name)
                            ),
                            'submitDescription' => Controller::htmlInputText(
                                addslashes($dsManufacturer->getValue(DBEManufacturer::name))
                            ),
                            'manufacturerID'    => $dsManufacturer->getValue(DBEManufacturer::manufacturerID)
                        )
                    );
                    $this->template->parse('manufacturers', 'manufacturerBlock', true);
                }
            }
        } // not ($dsManufacturer->rowCount()==1)
        $this->template->parse('CONTENTS', 'ManufacturerSelect', true);
        $this->parsePage();
    }

    /**
     * Display list of manufacturers
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');

        $this->setPageTitle('Manufacturers');

        $this->setTemplateFiles(
            array('ManufacturerList' => 'ManufacturerList.inc')
        );
        $dsManufacturer = new DataSet($this);
        $this->buManufacturer->getAll($dsManufacturer);

        $urlCreate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => 'createManufacturer'
            )
        );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsManufacturer->rowCount() > 0) {
            $this->template->set_block('ManufacturerList', 'manufacturerBlock', 'manufacturers');
            while ($dsManufacturer->fetchNext()) {
                $manufacturerID = $dsManufacturer->getValue(DBEManufacturer::manufacturerID);
                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'         => 'editManufacturer',
                            'manufacturerID' => $manufacturerID
                        )
                    );
                $txtEdit = '[edit]';
                $this->template->set_var(
                    array(
                        'manufacturerID' => $manufacturerID,
                        'name'           => Controller::htmlDisplayText(
                            $dsManufacturer->getValue(DBEManufacturer::name)
                        ),
                        'urlEdit'        => $urlEdit,
                        'txtEdit'        => $txtEdit
                    )
                );
                $this->template->parse('manufacturers', 'manufacturerBlock', true);
            }
        }
        $this->template->parse('CONTENTS', 'ManufacturerList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Manufacturer
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsManufacturer = &$this->dsManufacturer; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == 'editManufacturer') {
                $this->buManufacturer->getManufacturerByID($_REQUEST['manufacturerID'], $dsManufacturer);
                $manufacturerID = $_REQUEST['manufacturerID'];
            } else {                                                                    // creating new
                $dsManufacturer->initialise();
                $dsManufacturer->setValue(DBEManufacturer::manufacturerID, '0');
                $manufacturerID = '0';
            }
        } else {                                                                        // form validation error
            $dsManufacturer->initialise();
            $dsManufacturer->fetchNext();
            $manufacturerID = $dsManufacturer->getValue(DBEManufacturer::manufacturerID);
        }

        $urlDelete = null;
        $txtDelete = null;
        if ($_REQUEST['action'] == 'editManufacturer' && $this->buManufacturer->canDeleteManufacturer(
                $_REQUEST['manufacturerID']
            )) {
            $urlDelete = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'         => 'deleteManufacturer',
                    'manufacturerID' => $manufacturerID
                )
            );
            $txtDelete = 'Delete';
        }
        $urlUpdate = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action'         => 'updateManufacturer',
                'manufacturerID' => $manufacturerID
            )
        );
        $urlDisplayList = Controller::buildLink(
            $_SERVER['PHP_SELF'],
            array(
                'action' => CTMANUFACTURER_ACT_DISPLAY_LIST
            )
        );
        $this->setPageTitle('Edit Manufacturer');
        $this->setTemplateFiles(
            array('ManufacturerEdit' => 'ManufacturerEdit.inc')
        );
        $this->template->set_var(
            array(
                'manufacturerID' => $dsManufacturer->getValue(DBEManufacturer::manufacturerID),
                'name'           => Controller::htmlInputText($dsManufacturer->getValue(DBEManufacturer::name)),
                'nameMessage'    => Controller::htmlDisplayText($dsManufacturer->getMessage(DBEManufacturer::name)),
                'urlUpdate'      => $urlUpdate,
                'urlDelete'      => $urlDelete,
                'txtDelete'      => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'ManufacturerEdit', true);
        $this->parsePage();
    }// end function editManufacturer()

    /**
     * Update call manufacturer details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsManufacturer->populateFromArray($_REQUEST['manufacturer']));
        if ($this->formError) {
            if (!$this->dsManufacturer->getValue(DBEManufacturer::manufacturerID)) {
                $_REQUEST['action'] = 'editManufacturer';
            } else {
                $_REQUEST['action'] = 'createManufacturer';
            }
            $this->edit();
            exit;
        }

        $this->buManufacturer->updateManufacturer($this->dsManufacturer);

        $manufacturerID = $this->dsManufacturer->getValue(DBEManufacturer::manufacturerID);

        if ($_SESSION['manufacturerParentIDField']) {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'           => 'displayPopup',
                    'manufacturerName' => $manufacturerID,
                    'htmlFmt'          => CT_HTML_FMT_POPUP
                )
            );
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTMANUFACTURER_ACT_DISPLAY_LIST
                )
            );

        }

        header('Location: ' . $urlNext);
    }

    /**
     * Delete Manufacturer
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function delete()
    {
        $this->setMethodName('delete');
        if (!$this->buManufacturer->deleteManufacturer($_REQUEST['manufacturerID'])) {
            $this->displayFatalError('Cannot delete this manufacturer');
            exit;
        } else {
            $urlNext = Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTMANUFACTURER_ACT_DISPLAY_LIST
                )
            );
            header('Location: ' . $urlNext);
            exit;
        }
    }
}
