<?php

namespace App\Application\Command;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Symfony\Component\Messenger\Middleware\HandleMessageMiddleware;

#[AutoconfigureTag('command.bus.middleware', ['priority' => 0])]
class CommandBusHandlerMiddleware extends HandleMessageMiddleware
{
    public function __construct(
        HandlersLocatorInterface $handlersLocator
    ) {
        parent::__construct($handlersLocator);
    }
}
