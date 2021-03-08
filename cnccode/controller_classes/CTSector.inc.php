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
require_once($cfg['path_bu'] . '/BUSector.inc.php');
require_once($cfg['path_dbe'] . '/DSForm.inc.php'); 

class CTSector extends CTCNC
{
    const getCustomerWithoutSector = "getCustomerWithoutSector";
    const CONST_SECTORS='sectors';

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
            case self::CONST_SECTORS:
                switch ($this->requestMethod) {
                    case 'GET':
                        echo  json_encode($this->getSectors(),JSON_NUMERIC_CHECK);
                        break;
                    case 'POST':
                        echo  json_encode($this->addSector(),JSON_NUMERIC_CHECK);
                        break;
                    case 'PUT':
                        echo  json_encode($this->updateSector(),JSON_NUMERIC_CHECK);
                        break;
                    case 'DELETE':
                        echo  json_encode($this->deleteSector(),JSON_NUMERIC_CHECK);
                        break;
                    default:
                        # code...
                        break;
                }
                exit;
                break;
            
            case self::getCustomerWithoutSector:
                $this->checkPermissions(SALES_PERMISSION);
                $this->getCustomerWithoutSector();
                break;
          
            default:
                $this->checkPermissions(MAINTENANCE_PERMISSION);
                $this->displayList();
                break;
        }
    }


    /**
     * Get customer without sector
     *
     * @access private
     * @authors Karim Ahmed - Sweet Code Limited
     * @throws Exception
     */
    function getCustomerWithoutSector()
    {

        $this->setMethodName('getCustomerWithoutSector');

        if ($customerID = $this->buSector->getCustomerWithoutSector()) {
            $urlNext =
                Controller::buildLink(
                    'Customer.php',
                    array(
                        'action'     => 'dispEdit',
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
        $this->template->parse(
            'CONTENTS',
            'SectorList',             
        );
        
        $this->loadReactScript('SectorComponent.js');
        $this->loadReactCSS('SectorComponent.css'); 
        $this->parsePage();        
    }

    //-----------------------new 
    function getSectors()
    {
        $dsSector = new DataSet($this);
        $this->buSector->getAll($dsSector);
        $data=[];
        if ($dsSector->rowCount() > 0) {
            while ($dsSector->fetchNext()) {

                $sectorID = $dsSector->getValue(DBESector::sectorID);
                $canDelete = false;                
                if ($this->buSector->canDelete($sectorID)) {
                    $canDelete=true;
                }
                $data []=array(
                        'id'    => $sectorID,
                        'description' => Controller::htmlDisplayText($dsSector->getValue(DBESector::description)),
                        'canDelete'     => $canDelete,                        
                    );
            }//while $dsSector->fetchNext()
        }
        return $this->success($data);
    }

    function addSector(){
        $body=$this->getBody();
        $description=$body->description;
        if(!isset($description))
            return $this->fail(APIException::badRequest,"Missing Data");
        $dbeSector=new DBESector($this);        
        $dbeSector->setValue(DBESector::description, $description);
        $dbeSector->insertRow();
        return $this->success();
    }
    function updateSector()
    {
        $body=$this->getBody();
        if(!isset($body->id))
        return $this->fail(APIException::badRequest,"Missing Data");
        $dbeSector=new DBESector($this);        
        $dbeSector->getRow($body->id);
        if(!$dbeSector->rowCount())
            return $this->fail(APIException::notFound,"Not Found" );
        $dbeSector->setValue(DBESector::description, $body->description);
        $dbeSector->updateRow();
        return $this->success();
    }
    function deleteSector()
    {
        $id=@$_REQUEST['id'];
        if(!isset($id))
            return $this->fail(APIException::badRequest,"Missing Data");

        $dbeSector=new DBESector($this);        
        $dbeSector->getRow($id);
        if(!$dbeSector->rowCount())
            return $this->fail(APIException::notFound,"Not Found" );
        $dbeSector->deleteRow();
        return $this->success();
    }
}
