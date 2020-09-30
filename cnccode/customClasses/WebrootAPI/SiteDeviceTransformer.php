<?php


namespace CNCLTD\WebrootAPI;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;

class SiteDeviceTransformer implements \Karriere\JsonDecoder\Transformer
{
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding("hostName", "HostName", false));
        $classBindings->register(new AliasBinding("lastSeen", "LastSeen", false));
    }

    public function transforms()
    {
        return SiteDevice::class;
    }
}