<?php

namespace CNCLTD\DUOApi\Users;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;

class RetrieveUsersResponseTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('response', 'response', User::class));
        $classBindings->register(new AliasBinding('nextOffset', 'next_offset', false));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return RetrieveUsersResponse::class;
    }
}