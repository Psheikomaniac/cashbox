# Implementierungsplan: Symfony 7.2 Best Practices

## 1. Controller mit Attributen statt Annotationen

### Problem
Einige Controller verwenden veraltete Annotations oder haben unvollständige Attributdefinitionen.

### Lösung

1. **Vorher: Annotations oder unvollständige Attribute**

```php
/**
 * @Route("/api/penalties")
 */
class PenaltyController extends AbstractController
{
    /**
     * @Route("", methods={"GET"})
     */
    public function getAll(): JsonResponse
    {
        // Implementation
    }
}
```

2. **Nachher: Moderne Attribute**

```php
#[Route('/api/penalties')]
class PenaltyController extends AbstractController
{
    #[Route('', name: 'penalty_list', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getAll(): JsonResponse
    {
        // Implementation
    }

    #[Route('/{id}', name: 'penalty_show', methods: ['GET'], requirements: ['id' => Uuid::PATTERN])]
    #[IsGranted('PENALTY_VIEW', 'penalty')]
    public function getOne(Penalty $penalty): JsonResponse
    {
        // Implementation mit Parameter-Konvertierung
        return $this->json(PenaltyOutputDTO::createFromEntity($penalty));
    }
}
```

## 2. API Platform statt manueller Controller

### Problem
Viel API-Logik wird manuell in Controllern implementiert, anstatt API Platform zu nutzen.

### Lösung

1. **Vorher: Manueller Controller**

```php
#[Route('/api/payments')]
class PaymentController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function getAll(): JsonResponse
    {
        $payments = $this->paymentRepository->findAll();
        $paymentDTOs = array_map(fn (Payment $payment) => PaymentOutputDTO::createFromEntity($payment), $payments);

        return $this->json($paymentDTOs);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getOne(string $id): JsonResponse
    {
        $payment = $this->paymentRepository->find($id);

        if (!$payment) {
            return $this->json(['message' => 'Payment not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json(PaymentOutputDTO::createFromEntity($payment));
    }
}
```

2. **Nachher: API Platform Resource**

```php
#[ApiResource(
    shortName: 'Payment',
    operations: [
        new GetCollection(
            uriTemplate: '/payments',
            security: "is_granted('ROLE_USER')",
            name: 'get_payments'
        ),
        new Get(
            uriTemplate: '/payments/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('PAYMENT_VIEW', object)"
        ),
        new Post(
            uriTemplate: '/payments',
            security: "is_granted('ROLE_USER')",
            input: CreatePaymentDTO::class,
            output: PaymentResponseDTO::class,
            provider: PaymentCollectionProvider::class
        ),
        new Put(
            uriTemplate: '/payments/{id}',
            requirements: ['id' => '[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}'],
            security: "is_granted('PAYMENT_EDIT', object)",
            input: UpdatePaymentDTO::class
        )
    ],
    formats: ['jsonld', 'json', 'csv'],
    normalizationContext: ['groups' => ['payment:read']],
    denormalizationContext: ['groups' => ['payment:write']],
    paginationItemsPerPage: 20
)]
class Payment
{
    // Entity-Definition bleibt unverändert
}
```

3. **Provider für benutzerdefinierte Datenabruflogik**

```php
<?php

namespace App\ApiPlatform\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\DTO\Payment\PaymentResponseDTO;
use App\Repository\PaymentRepository;

class PaymentCollectionProvider implements ProviderInterface
{
    public function __construct(private readonly PaymentRepository $paymentRepository)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $payments = $this->paymentRepository->findAll();

        return array_map(
            fn ($payment) => PaymentResponseDTO::fromEntity($payment),
            $payments
        );
    }
}
```

## 3. Symfony Autowiring und Dependency Injection

### Problem
Servicekonfigurationen in YAML-Dateien oder manuelles Einrichten von Services.

### Lösung

1. **Vorher: Manuelle Service-Konfiguration**

```yaml
# config/services.yaml
services:
    app.service.export:
        class: App\Service\ExportGeneratorService
        arguments:
            - '@App\Repository\ReportRepository'
            - '@App\Service\ReportGeneratorService'
            - '@logger'
            - '%kernel.project_dir%'
```

2. **Nachher: Autowiring und Typdeklarationen**

```php
// Klasse konfiguriert ihre eigenen Abhängigkeiten
namespace App\Service;

use App\Repository\ReportRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ExportGeneratorService
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly ReportGeneratorService $reportGeneratorService,
        private readonly LoggerInterface $logger,
        #[Autowire('%kernel.project_dir%')] private readonly string $projectDir
    ) {
        $this->exportDir = $projectDir . '/var/exports';
        // Rest des Konstruktors
    }
}
```

## 4. Symfony Messenger für asynchrone Verarbeitung

### Problem
Domänen-Events werden nicht effizient oder überhaupt nicht verarbeitet.

### Lösung

1. **Konfiguration des Messenger-Systems**

```yaml
# config/packages/messenger.yaml
framework:
    messenger:
        failure_transport: failed

        transports:
            async: '%env(MESSENGER_TRANSPORT_DSN)%'
            failed: 'doctrine://default?queue_name=failed'

        routing:
            'App\Message\PenaltyCreatedMessage': async
            'App\Message\PenaltyPaidMessage': async
            'App\Message\ReportGenerationMessage': async
```

2. **Event-Message-Transformer**

```php
<?php

namespace App\Message;

final readonly class PenaltyCreatedMessage
{
    public function __construct(
        public string $penaltyId,
        public string $userId,
        public string $teamId,
        public string $reason,
        public int $amount,
        public string $currency
    ) {}
}
```

3. **Event-Listener zum Versenden von Messages**

```php
<?php

namespace App\EventSubscriber;

use App\Event\PenaltyCreatedEvent;
use App\Message\PenaltyCreatedMessage;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class PenaltyEventSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            PenaltyCreatedEvent::class => 'onPenaltyCreated',
        ];
    }

    public function onPenaltyCreated(PenaltyCreatedEvent $event): void
    {
        $this->messageBus->dispatch(new PenaltyCreatedMessage(
            $event->getPenaltyId()->toString(),
            $event->getUserId()->toString(),
            $event->getTeamId()->toString(),
            $event->getReason(),
            $event->getMoney()->getAmount(),
            $event->getMoney()->getCurrency()->value
        ));
    }
}
```

4. **Message-Handler**

```php
<?php

namespace App\MessageHandler;

use App\Message\PenaltyCreatedMessage;
use App\Service\NotificationService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PenaltyCreatedHandler
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly LoggerInterface $logger
    ) {}

    public function __invoke(PenaltyCreatedMessage $message): void
    {
        $this->logger->info('Verarbeite PenaltyCreatedMessage', [
            'penaltyId' => $message->penaltyId,
            'userId' => $message->userId
        ]);

        // Benachrichtigung senden
        $this->notificationService->sendPenaltyNotification(
            $message->userId,
            $message->penaltyId,
            $message->reason,
            $message->amount,
            $message->currency
        );
    }
}
```

## 5. Symfony Form Handling für Admin-Interfaces

### Problem
Formulardaten werden manuell validiert und verarbeitet.

### Lösung

1. **Formulartyp erstellen**

```php
<?php

namespace App\Form;

use App\DTO\Payment\CreatePaymentDTO;
use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('amount', MoneyType::class, [
                'label' => 'Betrag',
                'currency' => false,
                'divisor' => 100,
                'html5' => true,
            ])
            ->add('currency', ChoiceType::class, [
                'label' => 'Währung',
                'choices' => array_combine(
                    array_map(fn(CurrencyEnum $currency) => $currency->value, CurrencyEnum::cases()),
                    array_map(fn(CurrencyEnum $currency) => $currency->value, CurrencyEnum::cases())
                ),
                'placeholder' => 'Währung wählen',
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Zahlungsart',
                'choices' => array_combine(
                    array_map(fn(PaymentTypeEnum $type) => $type->value, PaymentTypeEnum::cases()),
                    array_map(fn(PaymentTypeEnum $type) => $type->value, PaymentTypeEnum::cases())
                ),
                'placeholder' => 'Zahlungsart wählen',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Beschreibung',
                'required' => false,
            ])
            ->add('reference', TextType::class, [
                'label' => 'Referenz',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreatePaymentDTO::class,
            'csrf_protection' => true,
        ]);
    }
}
```

2. **Controller mit Formularverarbeitung**

```php
<?php

namespace App\Controller\Admin;

use App\DTO\Payment\CreatePaymentDTO;
use App\Form\PaymentType;
use App\Service\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    #[Route('/admin/payments/new', name: 'admin_payment_new')]
    public function new(Request $request, PaymentService $paymentService): Response
    {
        $paymentDTO = new CreatePaymentDTO();
        $form = $this->createForm(PaymentType::class, $paymentDTO);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $payment = $paymentService->createPayment($paymentDTO);

            $this->addFlash('success', 'Zahlung erfolgreich erstellt!');
            return $this->redirectToRoute('admin_payment_show', [
                'id' => $payment->getId(),
            ]);
        }

        return $this->render('admin/payment/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
```

## 6. Symfony Serializer und Normalizer

### Problem
Inkonsistente oder manuelle Serialisierung von Objekten zu JSON.

### Lösung

1. **Benutzerdefinierte Normalizer für komplexe Objekte**

```php
<?php

namespace App\Serializer\Normalizer;

use App\Entity\Payment;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PaymentNormalizer implements NormalizerInterface
{
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        if (!$object instanceof Payment) {
            throw new \InvalidArgumentException('The object must be an instance of ' . Payment::class);
        }

        return [
            'id' => $object->getId()->toString(),
            'amount' => $object->getFormattedAmount(),
            'rawAmount' => $object->getAmount(),
            'currency' => $object->getCurrency()->value,
            'type' => $object->getType()->value,
            'description' => $object->getDescription(),
            'reference' => $object->getReference(),
            'teamUser' => [
                'id' => $object->getTeamUser()->getId()->toString(),
                'user' => [
                    'id' => $object->getTeamUser()->getUser()->getId()->toString(),
                    'name' => $object->getTeamUser()->getUser()->getName()->getFullName()
                ],
                'team' => [
                    'id' => $object->getTeamUser()->getTeam()->getId()->toString(),
                    'name' => $object->getTeamUser()->getTeam()->getName()
                ]
            ],
            'createdAt' => $object->getCreatedAt()->format('c'),
            'updatedAt' => $object->getUpdatedAt()->format('c')
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null, array $context = []): bool
    {
        return $data instanceof Payment;
    }
}
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Aktualisierung aller Controller auf Attribute statt Annotations
   - Einführung von Autowiring für alle Services

2. **Kurzfristig (Woche 2-3):**
   - Implementierung des Messenger-Systems für asynchrone Verarbeitung
   - Umstellung einfacher API-Endpunkte auf API Platform

3. **Mittelfristig (Woche 4-6):**
   - Erstellung von benutzerdefinierten Normalizern für komplexe Objekte
   - Umstellung auf Symfony Forms für Admin-Interfaces

4. **Langfristig (kontinuierlich):**
   - Migration aller manuellen Controller zu API Platform
   - Optimierung der Messenger-Konfiguration und -Handler
