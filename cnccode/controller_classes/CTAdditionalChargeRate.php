<?php

namespace CNCLTD\Controller;

use CNCLTD\AdditionalChargesRates\Application\GetAll\GetAllAdditionalChargeRatesQuery;
use CNCLTD\AdditionalChargesRates\Application\GetAll\GetAllAdditionalChargeRatesResponse;
use CNCLTD\Shared\Domain\Bus\QueryBus;
use CTCNC;
use Exception;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTAdditionalChargeRate extends CTCNC
{
    const GET_ADDITIONAL_CHARGE_RATES = 'getAdditionalChargeRates';
    /**
     * @var QueryBus
     */
    private $queryBus;

    /**
     * CTAdditionalChargeRate constructor.
     */
    public function __construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg, QueryBus $queryBus)
    {
        parent::__construct($requestMethod, $postVars, $getVars, $cookieVars, $cfg);
        $roles = SALES_PERMISSION;
        if (!self::hasPermissions($roles)) {
            Header("Location: /NotAllowed.php");
            exit;
        }
        $this->setMenuId(312);
        $this->queryBus = $queryBus;
    }

    /**
     * Route to function based upon action passed
     * @throws Exception
     */
    function defaultAction()
    {
        switch ($this->getAction()) {
            case self::GET_ADDITIONAL_CHARGE_RATES:
            {
                echo json_encode($this->getAdditionalChargeRagesController());
                break;
            }
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
        $this->loadReactScript('AdditionalChargeRateWrapperComponent.js');
        $this->loadReactCSS('AdditionalChargeRateWrapperComponent.css');
        $this->template->parse('CONTENTS', 'AdditionalChargeRateReact', true);
        $this->parsePage();
    }

    private function getAdditionalChargeRagesController()
    {
        /** @var GetAllAdditionalChargeRatesResponse $response */
        $response = $this->queryBus->ask(new GetAllAdditionalChargeRatesQuery());
        return ["status" => "ok", "data" => $response->additionalChargesRates()];
    }

}