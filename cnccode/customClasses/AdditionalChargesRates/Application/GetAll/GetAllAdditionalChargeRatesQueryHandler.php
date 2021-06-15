<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetAll;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateRepository;
use CNCLTD\Shared\Domain\Bus\QueryHandler;

class GetAllAdditionalChargeRatesQueryHandler implements QueryHandler
{
    /**
     * @var AdditionalChargeRateRepository
     */
    private $repository;

    /**
     * GetAllAdditionalChargeRatesQueryHandler constructor.
     */
    public function __construct(AdditionalChargeRateRepository $repository)
    {
        $this->repository = $repository;
    }

    public function __invoke(GetAllAdditionalChargeRatesQuery $query): GetAllAdditionalChargeRatesResponse
    {
        return new GetAllAdditionalChargeRatesResponse($this->repository->searchAll());
    }


}
