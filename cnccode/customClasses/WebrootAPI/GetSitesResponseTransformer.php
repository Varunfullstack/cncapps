<?php


namespace CNCLTD\WebrootAPI;


use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\Bindings\ArrayBinding;
use Karriere\JsonDecoder\ClassBindings;
use Karriere\JsonDecoder\Transformer;

class GetSitesResponseTransformer implements Transformer
{

    /**
     * @inheritDoc
     */
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding('totalSeatsAllowed', "TotalSeatsAllowed"));
        $classBindings->register(new AliasBinding('parentDescription', "ParentDescription"));
        $classBindings->register(new AliasBinding('sumTotalDevices', "SumTotalDevices"));
        $classBindings->register(new AliasBinding('sumTotalDevicesAllowed', "SumTotalDevicesAllowed"));
        $classBindings->register(new AliasBinding('sumTotalDevicesNotTrial', "SumTotalDevicesNotTrial"));
        $classBindings->register(new AliasBinding('sumTotalMobileDevicesAllowed', "SumTotalMobileDevicesAllowed"));
        $classBindings->register(new AliasBinding('totalCount', "TotalCount"));
        $classBindings->register(new ArrayBinding('sites', "Sites", Site::class));
    }

    /**
     * @inheritDoc
     */
    public function transforms()
    {
        return GetSitesResponse::class;
    }
}