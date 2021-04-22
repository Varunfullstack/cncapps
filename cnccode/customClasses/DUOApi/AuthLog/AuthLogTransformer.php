<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\FieldBinding;
use Karriere\JsonDecoder\ClassBindings;

class AuthLogTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new FieldBinding('accessDevice', 'access_device', AccessDevice::class));
        $classBindings->register(new FieldBinding('application', 'application', Application::class));
        $classBindings->register(new FieldBinding('user', 'user', User::class));
        $classBindings->register(new AliasBinding('timestamp', 'timestamp'));
        $classBindings->register(new AliasBinding('email', 'email'));
        $classBindings->register(new AliasBinding('result', 'result'));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AuthLog::class;
    }
}