<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\FieldBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AccessDeviceTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new FieldBinding('location', 'location', AccessDeviceLocation::class));
        $classBindings->register(new AliasBinding('ip', 'ip'));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AccessDevice::class;
    }
}