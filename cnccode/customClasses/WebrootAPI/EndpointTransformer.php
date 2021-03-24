<?php

namespace CNCLTD\WebrootAPI;

use Karriere\JsonDecoder\Bindings\AliasBinding;
use Karriere\JsonDecoder\ClassBindings;

class EndpointTransformer implements \Karriere\JsonDecoder\Transformer
{
    public function register(ClassBindings $classBindings)
    {
        $classBindings->register(new AliasBinding("hostName", "HostName", false));
        $classBindings->register(new AliasBinding("lastSeen", "LastSeen", false));
        $classBindings->register(new AliasBinding("deactivated", "Deactivated", false));
        $classBindings->register(new AliasBinding("machineId", "MachineId", false));
        $classBindings->register(new AliasBinding('endpointId', 'EndpointId', false));
    }

    public function transforms()
    {
        return Endpoint::class;
    }
}