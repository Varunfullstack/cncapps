<?php

namespace CNCLTD\AdditionalChargesRates\Application\GetOne;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateRepository;
use CNCLTD\Shared\Domain\Bus\QueryHandler;

class GetOneAdditionalChargeRatesQueryHandler implements QueryHandler
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

    public function __invoke(GetOneAdditionalChargeRatesQuery $query): GetOneAdditionalChargeRateResponse
    {
        return GetOneAdditionalChargeRateResponse::fromDomain(
            $this->repository->ofId(new AdditionalChargeRateId($query->id()))
        );
    }


}
