<?php

namespace CNCLTD\DUOApi\Users;

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
        $classBindings->register(new AliasBinding("email", "email"));
        $classBindings->register(new AliasBinding("firstName", "firstname"));
        $classBindings->register(new AliasBinding("lastLogin", "last_login"));
        $classBindings->register(new AliasBinding("lastName", "lastname"));
        $classBindings->register(new AliasBinding("status", "status"));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return User::class;
    }
}