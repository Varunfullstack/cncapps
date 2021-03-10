<?php


namespace CNCLTD\WebrootAPI;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;

class GetEndpointsResponseTransformer implements \Karriere\JsonDecoder\Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding('totalPages', "TotalAvailable"));
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