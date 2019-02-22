<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTSECTOR_ACT_DISPLAY_LIST', 'sectorList');
define('CTSECTOR_ACT_CREATE', 'createSector');
define('CTSECTOR_ACT_EDIT', 'editSector');
define('CTSECTOR_ACT_DELETE', 'deleteSector');
define('CTSECTOR_ACT_UPDATE', 'updateSector');

class CTSECTOR extends CTCNC
{
    public $dsSector;
    public $buSector;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = [
            "sales",
            "maintenance"
        ];
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->buSector = new BUSector($this);
        $this->dsSector = new DSForm($this);
        $this->dsSector->copyColumnsFrom($this->buSector->dbeSector);
    }

    /**
     * Route to function based upon action passed
     */
    function defaultAction()
    {
        switch ($_REQUEST['action']) {
            case CTSECTOR_ACT_EDIT:
            case CTSECTOR_ACT_CREATE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->edit();
                break;
            case CTSECTOR_ACT_DELETE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->delete();
                break;
            case CTSECTOR_ACT_UPDATE:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
                $this->update();
                break;
            case getCustomerWithoutSector:
                $this->checkPermissions(PHPLIB_PERM_SALES);
                $this->getCustomerWithoutSector();
                break;
            case CTSECTOR_ACT_DISPLAY_LIST:
            default:
                $this->checkPermissions(PHPLIB_PERM_MAINTENANCE);
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
        $this->setPageTitle('Business Sectors');
        $this->setTemplateFiles(
            array('SectorList' => 'SectorList.inc')
        );

        $this->buSector->getAll($dsSector);

        $urlCreate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSECTOR_ACT_CREATE
                )
            );

        $this->template->set_var(
            array('urlCreate' => $urlCreate)
        );

        if ($dsSector->rowCount() > 0) {

            $this->template->set_block(
                'SectorList',
                'SectorBlock',
                'sectors'
            );

            while ($dsSector->fetchNext()) {

                $sectorID = $dsSector->getValue('sectorID');

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action' => CTSECTOR_ACT_EDIT,
                            'sectorID' => $sectorID
                        )
                    );
                $txtEdit = '[edit]';

                if ($this->buSector->canDelete($sectorID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action' => CTSECTOR_ACT_DELETE,
                                'sectorID' => $sectorID
                            )
                        );
                    $txtDelete = '[delete]';
                } else {
                    $urlDelete = '';
                    $txtDelete = '';
                }

                $this->template->set_var(
                    array(
                        'sectorID' => $sectorID,
                        'description' => Controller::htmlDisplayText($dsSector->getValue('description')),
                        'urlEdit' => $urlEdit,
                        'urlDelete' => $urlDelete,
                        'txtEdit' => $txtEdit,
                        'txtDelete' => $txtDelete
                    )
                );

                $this->template->parse('sectors', 'SectorBlock', true);

            }//while $dsSector->fetchNext()
        }
        $this->template->parse('CONTENTS', 'SectorList', true);
        $this->parsePage();
    }

    /**
     * Edit/Add Further Action
     * @access private
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsSector = &$this->dsSector; // ref to class var

        if (!$this->getFormError()) {
            if ($_REQUEST['action'] == CTSECTOR_ACT_EDIT) {
                $this->buSector->getSectorByID($_REQUEST['sectorID'], $dsSector);
                $sectorID = $_REQUEST['sectorID'];
            } else {                                                                    // creating new
                $dsSector->initialise();
                $dsSector->setValue('sectorID', '0');
                $sectorID = '0';
            }
        } else {                                                                        // form validation error
            $dsSector->initialise();
            $dsSector->fetchNext();
            $sectorID = $dsSector->getValue('sectorID');
        }
        if ($_REQUEST['action'] == CTSECTOR_ACT_EDIT && $this->buSector->canDelete($_REQUEST['sectorID'])) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSECTOR_ACT_DELETE,
                        'sectorID' => $sectorID
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
                    'action' => CTSECTOR_ACT_UPDATE,
                    'sectorID' => $sectorID
                )
            );
        $urlDisplayList =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action' => CTSECTOR_ACT_DISPLAY_LIST
                )
            );
        $this->setPageTitle('Edit Business Sector');
        $this->setTemplateFiles(
            array('SectorEdit' => 'SectorEdit.inc')
        );
        $this->template->set_var(
            array(
                'sectorID' => $sectorID,
                'description' => Controller::htmlInputText($dsSector->getValue('description')),
                'descriptionMessage' => Controller::htmlDisplayText($dsSector->getMessage('description')),
                'urlUpdate' => $urlUpdate,
                'urlDelete' => $urlDelete,
                'txtDelete' => $txtDelete,
                'urlDisplayList' => $urlDisplayList
            )
        );
        $this->template->parse('CONTENTS', 'SectorEdit', true);
        $this->parsePage();
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     */
    function update()
    {
        $this->setMethodName('update');
        $dsSector = &$this->dsSector;
        $this->formError = (!$this->dsSector->populateFromArray($_REQUEST['sector']));
        if ($this->formError) {
            if ($this->dsSector->getValue('sectorID') == '') {                    // attempt to insert
                $_REQUEST['action'] = CTSECTOR_ACT_EDIT;
            } else {
                $_REQUEST['action'] = CTSECTOR_ACT_CREATE;
            }
            $this->edit();
            exit;
        }

        $this->buSector->updateSector($this->dsSector);

        $urlNext =
            Controller::buildLink($_SERVER['PHP_SELF'],
                             array(
                                 'sectorID' => $this->dsSector->getValue('sectorID'),
                                 'action' => CTCNC_ACT_VIEW
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
        if (!$this->buSector->deleteSector($_REQUEST['sectorID'])) {
            $this->displayFatalError('Cannot delete this Further Action');
            exit;
        } else {
            $urlNext =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action' => CTSECTOR_ACT_DISPLAY_LIST
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        }
    }

    /**
     * Get customer without sector
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     */
    function getCustomerWithoutSector()
    {

        $this->setMethodName('getCustomerWithoutSector');

        if ($customerID = $this->buSector->getCustomerWithoutSector()) {
            $urlNext =
                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action' => 'dispEdit',
                        'customerID' => $customerID
                    )
                );
            header('Location: ' . $urlNext);
            exit;
        } else {
            $this->setPageTitle('There are no customers without a Sector');
            $this->parsePage();
        }
    }
}// end of class
?>