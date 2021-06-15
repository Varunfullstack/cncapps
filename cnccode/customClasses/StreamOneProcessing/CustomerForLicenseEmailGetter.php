<?php

namespace CNCLTD\StreamOneProcessing;

use DBECustomer;

class CustomerForLicenseEmailGetter
{
    private static $customerCache = [];

    public function __invoke(string $email)
    {
        $that = null;
        if (!array_key_exists($email, self::$customerCache)) {
            $dbeCustomer                = new DBECustomer($that);
            self::$customerCache[$email] = null;
            if ($dbeCustomer->getCustomerByStreamOneEmail($email)) {
                self::$customerCache[$email] = $dbeCustomer;
            }
        }
        return self::$customerCache[$email];
    }
}