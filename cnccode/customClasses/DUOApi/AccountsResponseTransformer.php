<?php


namespace CNCLTD\DUOApi;


use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;

class AccountsResponseTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('response', 'response', Account::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AccountsResponse::class;
    }
}