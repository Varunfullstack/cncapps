<?php


namespace CNCLTD\DUOApi\Accounts;


use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AccountsResponseTransformer implements Transformer
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