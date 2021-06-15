<?php

namespace CNCLTD\DUOApi\AccountInfo;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AccountInfoTransformer implements Transformer
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