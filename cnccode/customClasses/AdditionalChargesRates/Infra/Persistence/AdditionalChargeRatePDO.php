<?php

namespace CNCLTD\AdditionalChargesRates\Infra\Persistence;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\AdditionalChargesRates\Domain\Description;
use CNCLTD\AdditionalChargesRates\Domain\InvalidAdditionalChargeRageIdValue;
use CNCLTD\AdditionalChargesRates\Domain\Notes;
use CNCLTD\AdditionalChargesRates\Domain\SalePrice;
use CNCLTD\AdditionalChargesRates\Domain\SpecificCustomerPrice;
use CNCLTD\AdditionalChargesRates\Domain\TimeBudgetMinutes;
use CNCLTD\AdditionalChargesRates\Domain\TimeBudgetMinutesCannotBeNegativeException;
use CNCLTD\Exceptions\EmptyStringException;
use CNCLTD\Exceptions\StringTooLongException;

class AdditionalChargeRatePDO extends AdditionalChargeRate
{
    /**
     * @param $data
     * @return AdditionalChargeRatePDO
     * @throws EmptyStringException
     * @throws InvalidAdditionalChargeRageIdValue
     * @throws TimeBudgetMinutesCannotBeNegativeException
     * @throws StringTooLongException
     */
    public static function fromPersistence($data): AdditionalChargeRatePDO
    {
        $instance = new self(
            AdditionalChargeRateId::fromNative($data['id']),
            new Description($data['description']),
            new Notes($data['notes']),
            new SalePrice($data['salePrice']),
            new TimeBudgetMinutes($data['timeBudgetMinutes'])
        );
        foreach ($data['specificCustomerPrices'] as $specificPrice) {
            $instance->specificCustomerPrices[] = new SpecificCustomerPrice(
                new CustomerId($specificPrice['customerId']),
                new SalePrice($specificPrice['salePrice']),
                new TimeBudgetMinutes($specificPrice['timeBudgetMinutes']),
            );
        }
        return $instance;
    }


}