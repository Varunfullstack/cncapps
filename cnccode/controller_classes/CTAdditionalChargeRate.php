<?php

namespace CNCLTD\Controller;

use CTCNC;
use Exception;

class CTAdditionalChargeRate extends CTCNC
{

    /**
     * CTAdditionalChargeRate constructor.
     */
    public function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = SALES_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(312);
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
//            case CTACTIVITYTYPE_ACT_EDIT:
//            case CTACTIVITYTYPE_ACT_CREATE:
//                $this->edit();
//                break;
//            case CTACTIVITYTYPE_ACT_DELETE:
//                $this->delete();
//                break;
//            case CTACTIVITYTYPE_ACT_UPDATE:
//                $this->update();
//                break;
//            case "getCallActTypes":
//                echo json_encode($this->getCallActTypes());
//                exit;
//            case "getAllDetails":
//                echo json_encode($this->getActTypeList());
//                exit;
//            case CTACTIVITYTYPE_ACT_DISPLAY_LIST:
//            case "updateActivityTypeOrder":
//                $this->updateActivityTypeOrder();
//                echo json_encode(["status" => "ok"]);
//                exit;
            default:
                $this->displayReactApp();
                break;
        }
    }

    private function displayReactApp()
    {
        $this->setMethodName('displayReactApp');
        $this->setPageTitle('Additional Charge Rates');
        $this->setTemplateFiles(
            array('AdditionalChargeRateReact' => 'AdditionalChargeRateReact')
        );
        $this->loadReactScript('AdditionalChargeRateWrapper.js');
        $this->loadReactCSS('AdditionalChargeRateReactWrapper.css');
        $this->template->parse('CONTENTS', 'AdditionalChargeRateReact', true);
        $this->parsePage();
    }

}