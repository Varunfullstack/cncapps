<?php
declare(strict_types=1);

namespace CNCLTD\Shared\Infrastructure\Bus\Query;

use CNCLTD\Shared\Domain\Bus\QueryBus;
use CNCLTD\Shared\Infrastructure\Bus\CallableFirstParameterExtractor;
use CNCLTD\Shared\Domain\Bus\Query;
use CNCLTD\Shared\Domain\Bus\Response;
use Symfony\Component\Messenger\Exception\NoHandlerForMessageException;
use Symfony\Component\Messenger\Handler\HandlersLocator;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class InMemorySymfonyQueryBus implements QueryBus
{
    /**
     * @var MessageBus
     */
    private $bus;


    /**
     * InMemorySymfonyQueryBus constructor.
     */
    public function __construct(iterable $queryHandlers)
    {
        $this->bus = new MessageBus(
            [
                new HandleMessageMiddleware(
                    new HandlersLocator(
                        CallableFirstParameterExtractor::forCallables($queryHandlers)
                    )
                )
            ]
        );
    }

    public function ask(Query $query): ?Response
    {
        try {
            /** @var HandledStamp $stamp */
            $stamp = $this->bus->dispatch($query)->last(HandledStamp::class);
            return $stamp->getResult();
        } catch (NoHandlerForMessageException $ex) {
            throw new QueryNotRegisteredError($query);
        }
    }
}