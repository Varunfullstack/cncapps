<?php


namespace CNCLTD\DUOApi\AccountInfo;


use Karriere\JsonDecoder\Bindings\FieldBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AccountInfoResponseTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new FieldBinding('response', 'response', AccountInfo::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AccountInfoResponse::class;
    }
}