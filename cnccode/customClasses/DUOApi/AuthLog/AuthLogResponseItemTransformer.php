<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\Bindings\FieldBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AuthLogResponseItemTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new ArrayBinding('authLogs', 'authlogs', AuthLog::class));
        $classBindings->register(new FieldBinding('metadata', 'metadata', AuthLogsMetadata::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AuthLogResponseItem::class;
    }
}