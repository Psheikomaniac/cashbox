<?php

namespace App\Controller\Admin;

use App\DTO\PenaltyInputDTO;
use App\DTO\PenaltyOutputDTO;
use App\Entity\Penalty;
use App\Enum\CurrencyEnum;
use App\Event\PenaltyCreatedEvent;
use App\Event\PenaltyPaidEvent;
use App\Form\PenaltyType;
use App\Repository\PenaltyRepository;
use App\Repository\PenaltyTypeRepository;
use App\Repository\TeamRepository;
use App\Repository\TeamUserRepository;
use App\Repository\UserRepository;
use App\ValueObject\Money;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/penalties', name: 'admin_penalty_')]
#[IsGranted('ROLE_ADMIN')]
class PenaltyAdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PenaltyRepository $penaltyRepository,
        private readonly PenaltyTypeRepository $penaltyTypeRepository,
        private readonly TeamRepository $teamRepository,
        private readonly UserRepository $userRepository,
        private readonly TeamUserRepository $teamUserRepository,
        private readonly ValidatorInterface $validator,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        $penalties = $this->penaltyRepository->findAll();

        return $this->render('admin/penalty/index.html.twig', [
            'penalties' => $penalties,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $penaltyInputDTO = new PenaltyInputDTO();
        $penaltyInputDTO->currency = 'EUR';
        $penaltyInputDTO->archived = false;

        $form = $this->createForm(PenaltyType::class, $penaltyInputDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teamUser = $this->findTeamUser($penaltyInputDTO->teamId, $penaltyInputDTO->userId);
            if (!$teamUser) {
                $this->addFlash('error', 'Team user not found');
                return $this->redirectToRoute('admin_penalty_new');
            }

            $penaltyType = $this->penaltyTypeRepository->find($penaltyInputDTO->typeId);
            if (!$penaltyType) {
                $this->addFlash('error', 'Penalty type not found');
                return $this->redirectToRoute('admin_penalty_new');
            }

            try {
                $currency = CurrencyEnum::from($penaltyInputDTO->currency);
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Invalid currency');
                return $this->redirectToRoute('admin_penalty_new');
            }

            $penalty = new Penalty();
            $penalty->setTeamUser($teamUser);
            $penalty->setType($penaltyType);
            $penalty->setReason($penaltyInputDTO->reason);
            $penalty->setAmount($penaltyInputDTO->amount);
            $penalty->setCurrency($currency);
            $penalty->setArchived($penaltyInputDTO->archived);

            if ($penaltyInputDTO->paidAt) {
                try {
                    $paidAt = new \DateTimeImmutable($penaltyInputDTO->paidAt);
                    $penalty->setPaidAt($paidAt);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Invalid paid at date');
                    return $this->redirectToRoute('admin_penalty_new');
                }
            }

            $errors = $this->validator->validate($penalty);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('admin_penalty_new');
            }

            $this->entityManager->persist($penalty);
            $this->entityManager->flush();

            // Dispatch PenaltyCreatedEvent
            $this->eventDispatcher->dispatch(new PenaltyCreatedEvent(
                $penalty->getId(),
                $penalty->getTeamUser()->getUser()->getId(),
                $penalty->getTeamUser()->getTeam()->getId(),
                $penalty->getReason(),
                new Money($penalty->getAmount(), $penalty->getCurrency())
            ));

            $this->addFlash('success', 'Penalty created successfully');
            return $this->redirectToRoute('admin_penalty_show', ['id' => $penalty->getId()]);
        }

        return $this->render('admin/penalty/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            throw $this->createNotFoundException('Penalty not found');
        }

        return $this->render('admin/penalty/show.html.twig', [
            'penalty' => $penalty,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(string $id, Request $request): Response
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            throw $this->createNotFoundException('Penalty not found');
        }

        // Create a DTO from the entity
        $penaltyInputDTO = new PenaltyInputDTO();
        $penaltyInputDTO->teamId = $penalty->getTeamUser()->getTeam()->getId()->toString();
        $penaltyInputDTO->userId = $penalty->getTeamUser()->getUser()->getId()->toString();
        $penaltyInputDTO->typeId = $penalty->getType()->getId()->toString();
        $penaltyInputDTO->reason = $penalty->getReason();
        $penaltyInputDTO->amount = $penalty->getAmount();
        $penaltyInputDTO->currency = $penalty->getCurrency()->value;
        $penaltyInputDTO->archived = $penalty->isArchived();
        $penaltyInputDTO->paidAt = $penalty->getPaidAt() ? $penalty->getPaidAt()->format('Y-m-d\TH:i:s') : null;

        $form = $this->createForm(PenaltyType::class, $penaltyInputDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $teamUser = $this->findTeamUser($penaltyInputDTO->teamId, $penaltyInputDTO->userId);
            if (!$teamUser) {
                $this->addFlash('error', 'Team user not found');
                return $this->redirectToRoute('admin_penalty_edit', ['id' => $id]);
            }

            $penaltyType = $this->penaltyTypeRepository->find($penaltyInputDTO->typeId);
            if (!$penaltyType) {
                $this->addFlash('error', 'Penalty type not found');
                return $this->redirectToRoute('admin_penalty_edit', ['id' => $id]);
            }

            try {
                $currency = CurrencyEnum::from($penaltyInputDTO->currency);
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Invalid currency');
                return $this->redirectToRoute('admin_penalty_edit', ['id' => $id]);
            }

            $penalty->setTeamUser($teamUser);
            $penalty->setType($penaltyType);
            $penalty->setReason($penaltyInputDTO->reason);
            $penalty->setAmount($penaltyInputDTO->amount);
            $penalty->setCurrency($currency);
            $penalty->setArchived($penaltyInputDTO->archived);

            if ($penaltyInputDTO->paidAt) {
                try {
                    $paidAt = new \DateTimeImmutable($penaltyInputDTO->paidAt);
                    $penalty->setPaidAt($paidAt);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Invalid paid at date');
                    return $this->redirectToRoute('admin_penalty_edit', ['id' => $id]);
                }
            } else {
                $penalty->setPaidAt(null);
            }

            $errors = $this->validator->validate($penalty);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('admin_penalty_edit', ['id' => $id]);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Penalty updated successfully');
            return $this->redirectToRoute('admin_penalty_show', ['id' => $penalty->getId()]);
        }

        return $this->render('admin/penalty/edit.html.twig', [
            'form' => $form->createView(),
            'penalty' => $penalty,
        ]);
    }

    #[Route('/{id}/pay', name: 'pay', methods: ['POST'])]
    public function pay(string $id, Request $request): Response
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            throw $this->createNotFoundException('Penalty not found');
        }

        if ($this->isCsrfTokenValid('pay'.$penalty->getId(), $request->request->get('_token'))) {
            $paidAt = new \DateTimeImmutable();
            $penalty->setPaidAt($paidAt);

            $this->entityManager->flush();

            // Dispatch PenaltyPaidEvent
            $this->eventDispatcher->dispatch(new PenaltyPaidEvent(
                $penalty->getId(),
                $paidAt
            ));

            $this->addFlash('success', 'Penalty marked as paid successfully');
        }

        return $this->redirectToRoute('admin_penalty_show', ['id' => $penalty->getId()]);
    }

    #[Route('/{id}/archive', name: 'archive', methods: ['POST'])]
    public function archive(string $id, Request $request): Response
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            throw $this->createNotFoundException('Penalty not found');
        }

        if ($this->isCsrfTokenValid('archive'.$penalty->getId(), $request->request->get('_token'))) {
            $penalty->setArchived(true);
            $this->entityManager->flush();
            $this->addFlash('success', 'Penalty archived successfully');
        }

        return $this->redirectToRoute('admin_penalty_show', ['id' => $penalty->getId()]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        $penalty = $this->penaltyRepository->find($id);

        if (!$penalty) {
            throw $this->createNotFoundException('Penalty not found');
        }

        if ($this->isCsrfTokenValid('delete'.$penalty->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($penalty);
            $this->entityManager->flush();
            $this->addFlash('success', 'Penalty deleted successfully');
        }

        return $this->redirectToRoute('admin_penalty_list');
    }

    private function findTeamUser(string $teamId, string $userId)
    {
        $team = $this->teamRepository->find($teamId);
        $user = $this->userRepository->find($userId);

        if (!$team || !$user) {
            return null;
        }

        return $this->teamUserRepository->findOneByTeamAndUser($team, $user);
    }
}
