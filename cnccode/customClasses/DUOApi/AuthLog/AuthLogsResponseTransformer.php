<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AuthLogsResponseTransformer implements Transformer
{
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('response', 'response', AuthLogResponseItem::class));
        $classBindings->register();
    }

    public function transforms()
    {
        return AuthLogsResponse::class;
    }

}