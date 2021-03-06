<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class UserTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding('name', 'name'));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return User::class;
    }
}