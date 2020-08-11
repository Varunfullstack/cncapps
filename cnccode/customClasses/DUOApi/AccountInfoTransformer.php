<?php


namespace CNCLTD\DUOApi;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;

class AccountInfoTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding("adminCount", "admin_count"));
        $classBindings->register(new AliasBinding("integrationCount", "integration_count"));
        $classBindings->register(new AliasBinding("telephonyCreditsRemaining", "telephony_credits_remaining"));
        $classBindings->register(new AliasBinding("userCount", "user_count"));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AccountInfo::class;
    }
}