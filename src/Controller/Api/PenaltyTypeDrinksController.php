<?php

namespace App\Controller\Api;

use App\Entity\PenaltyType;
use App\Repository\PenaltyTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class PenaltyTypeDrinksController extends AbstractController
{
    public function __construct(
        private readonly PenaltyTypeRepository $penaltyTypeRepository
    ) {
    }

    public function __invoke(): iterable
    {
        return $this->penaltyTypeRepository->findDrinks();
    }
}
