<?php

namespace App\Application\Query;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

#[AutoconfigureTag('query.bus.middleware', ['priority' => 0])]
class QueryBusHandlerMiddleware extends HandleMessageMiddleware
{
    public function __construct(
        HandlersLocatorInterface $handlersLocator
    ) {
        parent::__construct($handlersLocator);
    }
}
