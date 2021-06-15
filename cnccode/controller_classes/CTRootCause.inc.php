<?php
/**
 * Further Action controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_bu'] . '/BURootCause.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
// Actions
define('CTROOTCAUSE_ACT_DISPLAY_LIST', 'rootCauseList');
define('CTROOTCAUSE_ACT_CREATE', 'createRootCause');
define('CTROOTCAUSE_ACT_EDIT', 'editRootCause');
define('CTROOTCAUSE_ACT_DELETE', 'deleteRootCause');
define('CTROOTCAUSE_ACT_UPDATE', 'updateRootCause');

class CTRootCause extends CTCNC
{
    /** @var DSForm */
    public $dsRootCause;
    /** @var BURootCause */
    public $buRootCause;
    const CONST_ROOT_CAUSE='rootCause';
    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = MAINTENANCE_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(802);
        $this->buRootCause = new BURootCause($this);
        $this->dsRootCause = new DSForm($this);
        $this->dsRootCause->copyColumnsFrom($this->buRootCause->dbeRootCause);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        $this->checkPermissions(MAINTENANCE_PERMISSION);
        switch ($this->getAction()) {
            case self::CONST_ROOT_CAUSE:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getItemTypes(),JSON_NUMERIC_CHECK);
                        break;
                     case 'POST':
                         echo  json_encode($this->updateItem(),JSON_NUMERIC_CHECK);
                         break;
                    case 'PUT':
                        echo  json_encode($this->updateItem(),JSON_NUMERIC_CHECK);
                        break;
                     case 'DELETE':
                         echo  json_encode($this->deleteItemType(),JSON_NUMERIC_CHECK);
                         break;                    
                }
                exit;  
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
        //--------new 
        $this->setPageTitle('Root Causes');
        $this->setTemplateFiles(
            array('RootCauseList' => 'RootCauseList.inc')
        );
        $this->loadReactScript('RootCauseComponent.js');
        $this->loadReactCSS('RootCauseComponent.css');
        $this->template->parse(
            'CONTENTS',
            'RootCauseList',
            true
        );
        $this->parsePage();      
    }
    //------------new
    function getItemTypes()
    {
        $items=[];
        $dsRootCause = new DataSet($this);
        $this->buRootCause->getAll($dsRootCause);
        if ($dsRootCause->rowCount() > 0) {         
            while ($dsRootCause->fetchNext()) {

                $rootCauseID = $dsRootCause->getValue(DBERootCause::rootCauseID);                
                $canDelete=$this->buRootCause->canDelete($rootCauseID);                  
                $items []=
                    array(
                        'rootCauseID' => $rootCauseID,
                        'description' => Controller::htmlDisplayText($dsRootCause->getValue(DBERootCause::description)),
                        'canDelete'   => $canDelete,               
                        'longDescription'        => Controller::htmlInputText(
                            $dsRootCause->getValue(DBERootCause::longDescription)
                        ),                        
                        'fixedExplanation'       =>  
                            $dsRootCause->getValue(DBERootCause::fixedExplanation)
                         ,         
                    );
            }//while $dsRootCause->fetchNext()
        }
        return $this->success($items);
    }
     /**
     * Update call Further Action details
     * @access private
     * @throws Exception
     */
    function updateItem()
    {
        $body=$this->getBody(true);      
        //$this->dsRootCause->debug=true;  
        $this->formError = (!$this->dsRootCause->populateFromArray(["0"=> $body]));
        if ($this->formError) {
           return $this->fail(APIException::badRequest,"Error in save data");
        }
        $this->buRootCause->updateRootCause($this->dsRootCause);
        return $this->success();
    }
         /**
     * Delete Further Action
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function deleteItemType()
    {
         if (!$this->buRootCause->deleteRootCause($this->getParam('rootCauseID'))) {
             return $this->fail(APIException::badRequest,'Cannot delete this row');
          
        } 
        return $this->success();
    }// end function editFurther Action()

}
