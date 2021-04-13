<?php

namespace CNCLTD\Test\SupportedCustomerAssets;

use CNCLTD\SupportedCustomerAssets\UnsupportedCustomerAssetService;
use PHPUnit\Framework\TestCase;

class UnsupportedCustomerAssetServiceTest extends TestCase
{

    public function testGetAllForCustomer()
    {

    }

    public function testUpdate()
    {

    }

    public function testCheckAssetUnsupported()
    {
        $test = new UnsupportedCustomerAssetService();
        $result = $test->checkAssetUnsupported(0, "");
        self::assertFalse($result, "It should not tell the asset is unsupported if there are no unsupported assets");
    }
}
