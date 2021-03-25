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
define(
    'CTSECTOR_ACT_DISPLAY_LIST',
    'sectorList'
);
define(
    'CTSECTOR_ACT_CREATE',
    'createSector'
);
define(
    'CTSECTOR_ACT_EDIT',
    'editSector'
);
define(
    'CTSECTOR_ACT_DELETE',
    'deleteSector'
);
define(
    'CTSECTOR_ACT_UPDATE',
    'updateSector'
);

class CTSector extends CTCNC
{
    public $dsSector;
    public $buSector;

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
        $this->setMenuId(808);
        $this->buSector = new BUSector($this);
        $this->dsSector = new DSForm($this);
        $this->dsSector->copyColumnsFrom($this->buSector->dbeSector);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case CTSECTOR_ACT_EDIT:
            case CTSECTOR_ACT_CREATE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->edit();
                break;
            case CTSECTOR_ACT_DELETE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->delete();
                break;
            case CTSECTOR_ACT_UPDATE:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->update();
                break;
            case CTSECTOR_ACT_DISPLAY_LIST:
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->displayList();
                break;
        }
    }

/**
     * Edit/Add Further Action
     * @access private
     * @throws Exception
     */
    function edit()
    {
        $this->setMethodName('edit');
        $dsSector = &$this->dsSector; // ref to class var

        if (!$this->getFormError()) {
            if ($this->getAction() == CTSECTOR_ACT_EDIT) {
                $this->buSector->getSectorByID(
                    $this->getParam('sectorID'),
                    $dsSector
                );
                $sectorID = $this->getParam('sectorID');
            } else {                                                                    // creating new
                $dsSector->initialise();
                $dsSector->setValue(
                    DBESector::sectorID,
                    null
                );
                $sectorID = null;
            }
        } else {                                                                        // form validation error
            $dsSector->initialise();
            $dsSector->fetchNext();
            $sectorID = $dsSector->getValue(DBESector::sectorID);
        }
        $urlDelete = null;
        $txtDelete = null;
        if ($this->getAction() == CTSECTOR_ACT_EDIT && $this->buSector->canDelete($this->getParam('sectorID'))) {
            $urlDelete =
                Controller::buildLink(
                    $_SERVER['PHP_SELF'],
                    array(
                        'action'   => CTSECTOR_ACT_DELETE,
                        'sectorID' => $sectorID
                    )
                );
            $txtDelete = 'Delete';
        }
        $urlUpdate =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'action'   => CTSECTOR_ACT_UPDATE,
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
                'sectorID'           => $sectorID,
                'description'        => Controller::htmlInputText($dsSector->getValue(DBESector::description)),
                'descriptionMessage' => Controller::htmlDisplayText($dsSector->getMessage(DBESector::description)),
                'urlUpdate'          => $urlUpdate,
                'urlDelete'          => $urlDelete,
                'txtDelete'          => $txtDelete,
                'urlDisplayList'     => $urlDisplayList
            )
        );
        $this->template->parse(
            'CONTENTS',
            'SectorEdit',
            true
        );
        $this->parsePage();
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
        if (!$this->buSector->deleteSector($this->getParam('sectorID'))) {
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
    }// end function editFurther Action()

    /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function update()
    {
        $this->setMethodName('update');
        $this->formError = (!$this->dsSector->populateFromArray($this->getParam('sector')));
        if ($this->formError) {
            if (!$this->dsSector->getValue(DBESector::sectorID)) {
                $this->setAction(CTSECTOR_ACT_EDIT);
            } else {
                $this->setAction(CTSECTOR_ACT_CREATE);
            }
            $this->edit();
            exit;
        }

        $this->buSector->updateSector($this->dsSector);

        $urlNext =
            Controller::buildLink(
                $_SERVER['PHP_SELF'],
                array(
                    'sectorID' => $this->dsSector->getValue(DBESector::sectorID),
                    'action'   => CTCNC_ACT_VIEW
                )
            );
        header('Location: ' . $urlNext);
    }


    /**
     * Display list of types
     * @access private
     * @throws Exception
     */
    function displayList()
    {
        $this->setMethodName('displayList');
        $this->setPageTitle('Business Sectors');
        $this->setTemplateFiles(
            array('SectorList' => 'SectorList.inc')
        );
        $dsSector = new DataSet($this);

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

                $sectorID = $dsSector->getValue(DBESector::sectorID);

                $urlEdit =
                    Controller::buildLink(
                        $_SERVER['PHP_SELF'],
                        array(
                            'action'   => CTSECTOR_ACT_EDIT,
                            'sectorID' => $sectorID
                        )
                    );
                $txtEdit = '[edit]';

                $urlDelete = null;
                $txtDelete = null;
                if ($this->buSector->canDelete($sectorID)) {
                    $urlDelete =
                        Controller::buildLink(
                            $_SERVER['PHP_SELF'],
                            array(
                                'action'   => CTSECTOR_ACT_DELETE,
                                'sectorID' => $sectorID
                            )
                        );
                    $txtDelete = '[delete]';
                }

                $this->template->set_var(
                    array(
                        'sectorID'    => $sectorID,
                        'description' => Controller::htmlDisplayText($dsSector->getValue(DBESector::description)),
                        'urlEdit'     => $urlEdit,
                        'urlDelete'   => $urlDelete,
                        'txtEdit'     => $txtEdit,
                        'txtDelete'   => $txtDelete
                    )
                );

                $this->template->parse(
                    'sectors',
                    'SectorBlock',
                    true
                );

            }//while $dsSector->fetchNext()
        }
        $this->template->parse(
            'CONTENTS',
            'SectorList',
            true
        );
        $this->parsePage();
    }
}
