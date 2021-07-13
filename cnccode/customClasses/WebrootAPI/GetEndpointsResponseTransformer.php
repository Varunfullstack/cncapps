<?php


namespace CNCLTD\WebrootAPI;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class GetEndpointsResponseTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding('totalAvailable', "TotalAvailable"));
        $classBindings->register(new AliasBinding('pageNumber', "pageNr"));
        $classBindings->register(new ArrayBinding('endpoints', "Endpoints", Endpoint::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return GetEndpointsResponse::class;
    }
}