<?php


namespace CNCLTD\Data;


use DBESite;

class SiteMapper
{
    static function fromDTOToDB($dto, DBESite $DBESite)
    {
        $identityFunction = function ($value) { return $value; };
        $toFlagFunction = function ($value) { return $value ? 'Y' : 'N'; };

        $mappings = [
            "customerID"     => ["field" => DBESite::customerID, "transformFunc" => $identityFunction],
            "siteNo"         => ["field" => DBESite::siteNo, "transformFunc" => $identityFunction],
            "address1"       => ["field" => DBESite::add1, "transformFunc" => $identityFunction],
            "address2"       => ["field" => DBESite::add2, "transformFunc" => $identityFunction],
            "address3"       => ["field" => DBESite::add3, "transformFunc" => $identityFunction],
            "town"           => ["field" => DBESite::town, "transformFunc" => $identityFunction],
            "county"         => ["field" => DBESite::county, "transformFunc" => $identityFunction],
            "postcode"       => ["field" => DBESite::postcode, "transformFunc" => $identityFunction],
            "invoiceContact" => ["field" => DBESite::invoiceContactID, "transformFunc" => $identityFunction],
            "deliverContact" => ["field" => DBESite::deliverContactID, "transformFunc" => $identityFunction],
            "debtorCode"     => ["field" => DBESite::debtorCode, "transformFunc" => $identityFunction],
            "sageRef"        => ["field" => DBESite::sageRef, "transformFunc" => $identityFunction],
            "phone"          => ["field" => DBESite::phone, "transformFunc" => $identityFunction],
            "maxTravelHours" => ["field" => DBESite::maxTravelHours, "transformFunc" => $identityFunction],
            "active"         => ["field" => DBESite::activeFlag, "transformFunc" => $toFlagFunction],
            "nonUKFlag"      => ["field" => DBESite::nonUKFlag, "transformFunc" => $toFlagFunction],
            "what3Words"     => ["field" => DBESite::what3Words, "transformFunc" => $identityFunction],
        ];

        foreach ($dto as $key => $value) {
            if (key_exists($key, $mappings)) {
                $DBESite->setValue($mappings[$key]['field'], $mappings[$key]['transformFunc']($value));
            }
        }
        return $DBESite;
    }
}