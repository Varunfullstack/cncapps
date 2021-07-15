<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class AuthLogsMetadataTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding('nextOffset', "next_offset"));
        $classBindings->register(new AliasBinding('totalObjects', 'total_objects'));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AuthLogsMetadata::class;
    }
}