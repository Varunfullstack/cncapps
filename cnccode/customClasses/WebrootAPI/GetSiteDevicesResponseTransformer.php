<?php


namespace CNCLTD\WebrootAPI;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;

class GetSiteDevicesResponseTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding('continuationToken', "ContinuationToken"));
        $classBindings->register(new AliasBinding('continuationURI', "ContinuationURI"));
        $classBindings->register(new AliasBinding('count', "Count"));
        $classBindings->register(new ArrayBinding('devices', "QueryResults", SiteDevice::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return GetSiteDevicesResponse::class;
    }
}