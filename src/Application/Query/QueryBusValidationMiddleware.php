<?php

namespace App\Application\Query;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Messenger\Middleware\ValidationMiddleware;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AutoconfigureTag('query.bus.middleware', ['priority' => 100])]
class QueryBusValidationMiddleware extends ValidationMiddleware
{
    public function __construct(
        #[Autowire(service: 'validator')]
        ValidatorInterface $validator
    ) {
        parent::__construct($validator);
    }
}
