<?php

namespace App\Controller\Admin;

use App\DTO\Payment\CreatePaymentDTO;
use App\Entity\Payment;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use App\Form\PaymentType;
use App\Repository\PaymentRepository;
use App\Repository\TeamUserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/payments', name: 'admin_payment_')]
#[IsGranted('ROLE_ADMIN')]
class PaymentAdminController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PaymentRepository $paymentRepository,
        private readonly TeamUserRepository $teamUserRepository,
        private readonly ValidatorInterface $validator
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function index(): Response
    {
        $payments = $this->paymentRepository->findAll();

        return $this->render('admin/payment/index.html.twig', [
            'payments' => $payments,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Create an initial DTO with default values
        $paymentDTO = new CreatePaymentDTO(
            '', // teamUserId
            0,  // amount
            'EUR', // currency
            'cash', // type
            null, // description
            null  // reference
        );

        $form = $this->createForm(PaymentType::class, $paymentDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the submitted data from the form
            $paymentDTO = $form->getData();

            $teamUser = $this->teamUserRepository->find($paymentDTO->teamUserId);

            if (!$teamUser) {
                $this->addFlash('error', 'Team user not found');
                return $this->redirectToRoute('admin_payment_new');
            }

            try {
                $currency = CurrencyEnum::from($paymentDTO->currency);
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Invalid currency');
                return $this->redirectToRoute('admin_payment_new');
            }

            try {
                $type = PaymentTypeEnum::from($paymentDTO->type);
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Invalid payment type');
                return $this->redirectToRoute('admin_payment_new');
            }

            $payment = new Payment();
            $payment->setTeamUser($teamUser);
            $payment->setAmount($paymentDTO->amount);
            $payment->setCurrency($currency);
            $payment->setType($type);
            $payment->setDescription($paymentDTO->description);
            $payment->setReference($paymentDTO->reference);

            if ($payment->requiresReference() && !$payment->getReference()) {
                $this->addFlash('error', 'Reference is required for this payment type');
                return $this->redirectToRoute('admin_payment_new');
            }

            $errors = $this->validator->validate($payment);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('admin_payment_new');
            }

            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            $this->addFlash('success', 'Payment created successfully');
            return $this->redirectToRoute('admin_payment_show', ['id' => $payment->getId()]);
        }

        return $this->render('admin/payment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): Response
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            throw $this->createNotFoundException('Payment not found');
        }

        return $this->render('admin/payment/show.html.twig', [
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(string $id, Request $request): Response
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            throw $this->createNotFoundException('Payment not found');
        }

        // Create a DTO from the entity
        $paymentDTO = new CreatePaymentDTO(
            $payment->getTeamUser()->getId()->toString(),
            $payment->getAmount(),
            $payment->getCurrency()->value,
            $payment->getType()->value,
            $payment->getDescription(),
            $payment->getReference()
        );

        $form = $this->createForm(PaymentType::class, $paymentDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Get the submitted data from the form
            $paymentDTO = $form->getData();

            $teamUser = $this->teamUserRepository->find($paymentDTO->teamUserId);

            if (!$teamUser) {
                $this->addFlash('error', 'Team user not found');
                return $this->redirectToRoute('admin_payment_edit', ['id' => $id]);
            }

            try {
                $currency = CurrencyEnum::from($paymentDTO->currency);
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Invalid currency');
                return $this->redirectToRoute('admin_payment_edit', ['id' => $id]);
            }

            try {
                $type = PaymentTypeEnum::from($paymentDTO->type);
            } catch (\ValueError $e) {
                $this->addFlash('error', 'Invalid payment type');
                return $this->redirectToRoute('admin_payment_edit', ['id' => $id]);
            }

            $payment->setTeamUser($teamUser);
            $payment->setAmount($paymentDTO->amount);
            $payment->setCurrency($currency);
            $payment->setType($type);
            $payment->setDescription($paymentDTO->description);
            $payment->setReference($paymentDTO->reference);

            if ($payment->requiresReference() && !$payment->getReference()) {
                $this->addFlash('error', 'Reference is required for this payment type');
                return $this->redirectToRoute('admin_payment_edit', ['id' => $id]);
            }

            $errors = $this->validator->validate($payment);
            if (count($errors) > 0) {
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
                return $this->redirectToRoute('admin_payment_edit', ['id' => $id]);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Payment updated successfully');
            return $this->redirectToRoute('admin_payment_show', ['id' => $payment->getId()]);
        }

        return $this->render('admin/payment/edit.html.twig', [
            'form' => $form->createView(),
            'payment' => $payment,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(string $id, Request $request): Response
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            throw $this->createNotFoundException('Payment not found');
        }

        if ($this->isCsrfTokenValid('delete'.$payment->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($payment);
            $this->entityManager->flush();
            $this->addFlash('success', 'Payment deleted successfully');
        }

        return $this->redirectToRoute('admin_payment_list');
    }
}
