<?php

namespace CNCLTD\AdditionalChargesRates\Infra\Persistence;

use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRate;
use CNCLTD\AdditionalChargesRates\Domain\AdditionalChargeRateId;
use CNCLTD\AdditionalChargesRates\Domain\CustomerSpecificPriceAllowed;
use CNCLTD\AdditionalChargesRates\Domain\CustomerId;
use CNCLTD\AdditionalChargesRates\Domain\Description;
use CNCLTD\AdditionalChargesRates\Domain\InvalidAdditionalChargeRageIdValue;
use CNCLTD\AdditionalChargesRates\Domain\Notes;
use CNCLTD\AdditionalChargesRates\Domain\SalePrice;
use CNCLTD\AdditionalChargesRates\Domain\SpecificCustomerPrice;
use CNCLTD\Exceptions\EmptyStringException;

class AdditionalChargeRatePDO extends AdditionalChargeRate
{
    /**
     * @throws InvalidAdditionalChargeRageIdValue
     * @throws EmptyStringException
     */
    public static function fromPersistence($data): AdditionalChargeRatePDO
    {
        $instance = new self(
            AdditionalChargeRateId::fromNative($data['id']),
            new Description($data['description']),
            new Notes($data['notes']),
            new SalePrice($data['salePrice']),
            new CustomerSpecificPriceAllowed($data['customerSpecificPriceAllowed'])
        );
        if ($instance->isCustomerSpecificPriceAllowed()) {
            foreach ($data['specificCustomerPrices'] as $specificPrice) {
                $instance->specificCustomerPrices[] = new SpecificCustomerPrice(
                    new CustomerId($specificPrice['customerId']), new SalePrice($specificPrice['salePrice'])
                );
            }
        }
        return $instance;
    }


}