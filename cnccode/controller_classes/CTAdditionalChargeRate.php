<?php

namespace CNCLTD\Controller;

use CNCLTD\AdditionalChargesRates\Application\Add\AddAdditionalChargeRateRequest;
use CNCLTD\AdditionalChargesRates\Application\Add\AddAdditionalChargeRateUseCase;
use CNCLTD\AdditionalChargesRates\Application\GetAll\GetAllAdditionalChargeRatesQuery;
use CNCLTD\AdditionalChargesRates\Application\GetAll\GetAllAdditionalChargeRatesResponse;
use CNCLTD\AdditionalChargesRates\Application\GetOne\GetOneAdditionalChargeRateResponse;
use CNCLTD\AdditionalChargesRates\Application\GetOne\GetOneAdditionalChargeRatesQuery;
use CNCLTD\AdditionalChargesRates\Application\Update\UpdateAdditionalChargeRateRequest;
use CNCLTD\AdditionalChargesRates\Application\Update\UpdateAdditionalChargeRateUseCase;
use CNCLTD\Exceptions\JsonHttpException;
use CNCLTD\Shared\Domain\Bus\QueryBus;
use CTCNC;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

global $cfg;
require_once($cfg['path_ct'] . '/CTCNC.inc.php');

class CTAdditionalChargeRate extends CTCNC
{
    const GET_ADDITIONAL_CHARGE_RATES = 'getAdditionalChargeRates';
    const GET_BY_ID                   = 'getById';
    const ADD                         = 'add';
    const UPDATE                      = 'update';
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

    function update()
    {
        echo json_encode($this->updateController(), JSON_NUMERIC_CHECK);
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
                echo json_encode($this->getAdditionalChargeRagesController(), JSON_NUMERIC_CHECK);
                break;
            }
            case self::GET_BY_ID:
            {
                echo json_encode($this->getAdditionalChargeRateByIdController(), JSON_NUMERIC_CHECK);
                break;
            }
            case self::ADD:
            {
                echo json_encode($this->addController(), JSON_NUMERIC_CHECK);
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

    private function getAdditionalChargeRateByIdController()
    {
        $id = @$_REQUEST['id'];
        if (!$id) {
            throw new JsonHttpException(400, 'ID is required');
        }
        /** @var GetOneAdditionalChargeRateResponse $response */
        $response = $this->queryBus->ask(new GetOneAdditionalChargeRatesQuery($id));
        return ["status" => "ok", "data" => $response];
    }

    private function addController()
    {
        $jsonData = $this->getBody(true);
        if (!$jsonData) {
            throw new JsonHttpException(400, 'Request is invalid');
        }
        $request              = new AddAdditionalChargeRateRequest($this->getBody(true));
        $validationViolations = $request->validate();
        if ($validationViolations->count()) {
            $this->throwValidationErrors($validationViolations);
        }
        global $additionalChargeRateRepository;
        $usecase = new AddAdditionalChargeRateUseCase($additionalChargeRateRepository);
        $usecase->__invoke($request);
        return ["status" => "ok"];
    }

    protected function throwValidationErrors(ConstraintViolationListInterface $validationViolations): JsonResponse
    {
        $validationErrors = [];
        /** @var ConstraintViolationInterface $validationViolation */
        foreach ($validationViolations as $validationViolation) {
            $validationErrors[] = [
                "field"   => $validationViolation->getPropertyPath(),
                "message" => $validationViolation->getMessage(),
                "code"    => $validationViolation->getCode()
            ];
        }
        throw new JsonHttpException(400, 'Validation Failed', $validationErrors);
    }

    private function updateController()
    {
        $jsonData = $this->getBody(true);
        if (!$jsonData) {
            throw new JsonHttpException(400, 'Request is invalid');
        }
        $request              = new UpdateAdditionalChargeRateRequest($this->getBody(true));
        $validationViolations = $request->validate();
        if ($validationViolations->count()) {
            $this->throwValidationErrors($validationViolations);
        }
        global $additionalChargeRateRepository;
        $usecase = new UpdateAdditionalChargeRateUseCase($additionalChargeRateRepository);
        $usecase->__invoke($request);
        return ["status" => "ok"];
    }

}