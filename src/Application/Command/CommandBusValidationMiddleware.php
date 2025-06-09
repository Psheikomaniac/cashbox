<?php

namespace App\Application\Command;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Middleware\ValidationMiddleware;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AutoconfigureTag('command.bus.middleware', ['priority' => 100])]
class CommandBusValidationMiddleware extends ValidationMiddleware
{
    public function __construct(
        #[Autowire(service: 'validator')]
        ValidatorInterface $validator
    ) {
        parent::__construct($validator);
    }
}
