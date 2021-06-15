<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AccessDeviceLocationTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding("city", "city"));
        $classBindings->register(new AliasBinding("country", "country"));
        $classBindings->register(new AliasBinding("state", "state"));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AccessDeviceLocation::class;
    }
}