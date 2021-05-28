<?php
declare(strict_types=1);

namespace CNCLTD\Shared\Domain\Bus;


interface QueryBus
{
    public function ask(Query $query): ?Response;
}