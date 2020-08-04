<?php


namespace CNCLTD\DUOApi;


use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;

class DuoAccountsResponseTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('response', 'response', DuoAccount::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return DuoAccountsResponse::class;
    }
}