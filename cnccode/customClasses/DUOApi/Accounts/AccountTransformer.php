<?php


namespace CNCLTD\DUOApi\Accounts;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AccountTransformer implements Transformer
{

    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding("accountId", "account_id"));
        $classBindings->register(new AliasBinding("apiHostname", "api_hostname"));
        $classBindings->register(new AliasBinding("name", "name"));
    }

    public function transforms()
    {
        return Account::class;
    }
}