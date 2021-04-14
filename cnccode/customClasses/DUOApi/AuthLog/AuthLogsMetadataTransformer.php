<?php

namespace CNCLTD\DUOApi\AuthLog;

use Karriere\JsonDecoder\Bindings\CallbackBinding;
use Karriere\JsonDecoder\ClassBindings;

class AuthLogsMetadataTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(
            new CallbackBinding(
                'response', function ($jsonData, $jsonDecoder) {

            }
            )
        );
        $classBindings->register();
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return AuthLogsMetadata::class;
    }
}