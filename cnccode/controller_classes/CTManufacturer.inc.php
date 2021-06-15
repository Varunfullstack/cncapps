<?php
/**
 * Manufacturer controller class
 * CNC Ltd
 *
 * @access public
 * @authors Karim Ahmed - Sweet Code Limited
 */

use CNCLTD\Exceptions\APIException;

global $cfg;
require_once($cfg['path_bu'] . '/BUManufacturer.inc.php');
require_once($cfg['path_ct'] . '/CTCNC.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php');
require_once($cfg['path_func'] . '/Common.inc.php');
// Messages
define('CTMANUFACTURER_MSG_NONE_FND', 'No manufacturers found');
define('CTMANUFACTURER_MSG_MANUFACTURER_NOT_FND', 'Manufacturer not found');
define('CTMANUFACTURER_MSG_MANUFACTURERID_NOT_PASSED', 'ManufacturerID not passed');
define('CTMANUFACTURER_MSG_MANUFACTURER_ARRAY_NOT_PASSED', 'Manufacturer array not passed');
 


class CTManufacturer extends CTCNC
{
    const SEARCH_MANUFACTURER_BY_NAME = "SEARCH_MANUFACTURER_BY_NAME";
    const CONST_ITEMS='items';
    const CONST_MANUFACTURER_LIST='manufacturerList';
    /** @var DSForm */
    public $dsManufacturer;
    /**
     * @var BUManufacturer
     */
    public $buManufacturer;

    function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = MAINTENANCE_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(803);
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
        switch ($this->getAction()) {
            case self::CONST_MANUFACTURER_LIST:
                echo  json_encode($this->getManufacturerList(),JSON_NUMERIC_CHECK);
                break;
            case self::CONST_ITEMS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getItems(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addItem(),JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateItem(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteItem(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;                 
           
            case 'displayPopup':
                $this->displayManufacturerSelectPopup();
                break;
            case self::SEARCH_MANUFACTURER_BY_NAME:
                $data = $this->getJSONData();
                $name = "";
                if (!empty($data['name'])) {
                    $name = $data['name'];
                }
                $manufacturers = new DBEManufacturer($this);
                $manufacturers->getRowsByNameMatch($name);
                $toReturnData = [];
                while ($manufacturers->fetchNext()) {
                    $toReturnData[] = $manufacturers->getRowAsAssocArray();
                }
                echo json_encode(["status" => "ok", "data" => $toReturnData]);
                break;
            case CTMANUFACTURER_ACT_DISPLAY_LIST:
            default:
                $this->setTemplate();
                break;
        }
    }
    function setTemplate()
    {
        $this->setMethodName('setTemplate');
        $this->setPageTitle('Manufacturers');
        $this->setTemplateFiles(
            array('ManufacturerList' => 'ManufacturerList.inc')
        );
        $this->loadReactScript('ManufacturerComponent.js');
        $this->loadReactCSS('ManufacturerComponent.css');      
        $this->template->parse('CONTENTS', 'ManufacturerList', true);
        $this->parsePage();
    }
    /**
     * see if parent form fields need to be populated
     * @access private
     */
    function setParentFormFields()
    {
        if ($this->getParam('parentIDField')) {
            $this->setSessionParam('manufacturerParentIDField', $this->getParam('parentIDField'));
        }
        if ($this->getParam('parentDescField')) {
            $this->setSessionParam('manufacturerParentDescField', $this->getParam('parentDescField'));
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
        if ($this->getParam('manufacturerName'){0} == '/') {
            header('Location: ' . $urlCreate);
            exit;
        }
        $dsManufacturer = new DataSet($this);
        $this->buManufacturer->getManufacturersByNameMatch($this->getParam('manufacturerName'), $dsManufacturer);
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
                        'manufacturerName' => $this->getParam('manufacturerName'),
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

    
    function getItems(){
        $data=[];       
        $dsManufacturer = new DataSet($this);
        $this->buManufacturer->getAll($dsManufacturer);        
        if ($dsManufacturer->rowCount() > 0) {
            
            while ($dsManufacturer->fetchNext()) {
                

                $manufacturerID = $dsManufacturer->getValue(DBEManufacturer::manufacturerID);
                $canDelete=false;
                if ($this->buManufacturer->canDeleteManufacturer($manufacturerID)) {
                    $canDelete=true;
                }
                $data []=
                    array(
                        'manufacturerID' => $manufacturerID,
                        'name'           => Controller::htmlDisplayText(
                            $dsManufacturer->getValue(DBEManufacturer::name)
                        ),           
                        'canDelete'            =>$canDelete
                    );    
            }
        }
        return $this->success( $data);
    }
    function addItem(){
        $body=$this->getBody();
        if(!isset($body->name))
            return $this->fail(APIException::badRequest,"Missing name");
        $dbeManufacturer = new DBEManufacturer($this);
        $dbeManufacturer->setValue(DBEManufacturer::name,$body->name);
        // check dublicated
        $hasName=$dbeManufacturer->hasName(DBEManufacturer::name );
        
        if($hasName)
            return $this->fail(APIException::conflict,"Confilicted Name");
        
        $dbeManufacturer->insertRow();
        return $this->success();
    }
    function updateItem(){
        $body=$this->getBody();
        if(!isset($body->name)||!isset($body->manufacturerID))
            return $this->fail(APIException::badRequest,"Missing name");
        $dbeManufacturer = new DBEManufacturer($this);
        $hasName=$dbeManufacturer->hasName(DBEManufacturer::name,$body->manufacturerID );
        if($hasName)
            return $this->fail(APIException::conflict,"Confilicted Name");

        $dbeManufacturer->getRow($body->manufacturerID);
        $dbeManufacturer->setValue(DBEManufacturer::name,$body->name);        
        $dbeManufacturer->updateRow();

        return $this->success();
    }
    function deleteItem(){
        $id=@$_REQUEST["id"];
        if(!isset($id))
            return $this->fail(APIException::badRequest,"Missing name");
        $dbeManufacturer = new DBEManufacturer($this);
        $dbeManufacturer->getRow($id);        
        $dbeManufacturer->deleteRow();
        return $this->success();
    }
    function getManufacturerList(){
        $data=[];       
        $dsManufacturer = new DataSet($this);
        $this->buManufacturer->getAll($dsManufacturer);        
        if ($dsManufacturer->rowCount() > 0) {            
            while ($dsManufacturer->fetchNext()) {
                $manufacturerID = $dsManufacturer->getValue(DBEManufacturer::manufacturerID);                
                $data []=
                    array(
                        'id' => $manufacturerID,
                        'name'           => Controller::htmlDisplayText(
                            $dsManufacturer->getValue(DBEManufacturer::name)
                        ) 
                    );    
            }
        }
        return $this->success( $data);
    }
}
