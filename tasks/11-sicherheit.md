# Implementierungsplan: Sicherheit

## 1. Secrets Management

### Problem
Passwörter und sensible Daten sind im Klartext in den Konfigurationsdateien gespeichert.

### Lösung

1. **Implementierung von Symfony Secrets für sensible Daten**

```bash
# Secrets-Schlüssel generieren
php bin/console secrets:generate-keys

# Secrets für verschiedene Umgebungen hinzufügen
php bin/console secrets:set POSTGRES_PASSWORD --env=dev
php bin/console secrets:set POSTGRES_PASSWORD --env=prod
php bin/console secrets:set JWT_PASSPHRASE --env=dev
php bin/console secrets:set JWT_PASSPHRASE --env=prod
php bin/console secrets:set API_KEY --env=dev
php bin/console secrets:set API_KEY --env=prod
```

2. **Aktualisierung der `.env`-Datei ohne sensible Daten**

```env
# .env - Keine sensiblen Daten mehr enthalten

###> symfony/framework-bundle ###
APP_ENV=dev
APP_SECRET=%env(APP_SECRET)%
###< symfony/framework-bundle ###

###> doctrine/doctrine-bundle ###
DATABASE_URL="postgresql://postgres:%env(POSTGRES_PASSWORD)%@database:5432/cashbox_db?serverVersion=16&charset=utf8"
###< doctrine/doctrine-bundle ###

###> nelmio/cors-bundle ###
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'
###< nelmio/cors-bundle ###

###> lexik/jwt-authentication-bundle ###
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=%env(JWT_PASSPHRASE)%
###< lexik/jwt-authentication-bundle ###
```

3. **Docker-Secrets für Docker-Compose**

```yaml
# compose.yaml
services:
  php:
    # Bisherige Konfiguration
    environment:
      - APP_ENV=${APP_ENV:-dev}
      - DATABASE_URL=postgresql://postgres:${POSTGRES_PASSWORD}@database:5432/cashbox_db?serverVersion=16&charset=utf8
    secrets:
      - postgres_password
      - jwt_passphrase

  database:
    # Bisherige Konfiguration
    environment:
      POSTGRES_DB: cashbox_db
      POSTGRES_USER: postgres
    secrets:
      - postgres_password
    environment:
      - POSTGRES_PASSWORD_FILE=/run/secrets/postgres_password

secrets:
  postgres_password:
    file: ${POSTGRES_PASSWORD_FILE:-./secrets/postgres_password.txt}
  jwt_passphrase:
    file: ${JWT_PASSPHRASE_FILE:-./secrets/jwt_passphrase.txt}
```

## 2. JWT-Authentifizierung

### Problem
Fehlende oder unvollständige JWT-Implementierung für sichere API-Authentifizierung.

### Lösung

1. **Konfiguration des LexikJWTAuthenticationBundle**

```yaml
# config/packages/lexik_jwt_authentication.yaml
lexik_jwt_authentication:
    secret_key: '%env(resolve:JWT_SECRET_KEY)%'
    public_key: '%env(resolve:JWT_PUBLIC_KEY)%'
    pass_phrase: '%env(JWT_PASSPHRASE)%'
    token_ttl: 3600 # Token-Gültigkeit: 1 Stunde
    user_identity_field: email # Feld zur Benutzeridentifikation
    clock_skew: 0
    token_extractors:
        authorization_header:      # Token aus Authorization-Header extrahieren
            enabled: true
            prefix: Bearer
            name: Authorization
        cookie:                    # Token aus Cookie extrahieren (optional)
            enabled: true
            name: BEARER
```

2. **Konfiguration der Sicherheit in Symfony**

```yaml
# config/packages/security.yaml
security:
    # Passwort-Encoder einrichten
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    # Anbieter einrichten
    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: emailValue

    # Firewalls einrichten
    firewalls:
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        login:
            pattern: ^/api/login
            stateless: true
            json_login:
                check_path: /api/login_check
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        api:
            pattern: ^/api
            stateless: true
            jwt: ~

    # Zugriffsregeln
    access_control:
        - { path: ^/api/login, roles: PUBLIC_ACCESS }
        - { path: ^/api/docs, roles: PUBLIC_ACCESS }
        - { path: ^/api, roles: IS_AUTHENTICATED_FULLY }
```

3. **Implementierung des User-Entity für Security**

```php
<?php

namespace App\Entity;

use App\ValueObject\Email;
use App\ValueObject\PersonName;
use App\ValueObject\PhoneNumber;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Bestehende Eigenschaften

    #[ORM\Column(length: 180, unique: true)]
    private ?string $emailValue = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column(nullable: true)]
    private ?string $password = null;

    // Implementierung der UserInterface-Methoden
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Jeder Benutzer hat mindestens ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
        // Falls temporäre, sensible Daten gespeichert wurden
    }

    public function getUserIdentifier(): string
    {
        return $this->emailValue ?? '';
    }
}
```

4. **Implementierung des AuthController**

```php
<?php

namespace App\Controller;

use App\DTO\Auth\LoginRequestDTO;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api')]
class AuthController extends AbstractController
{
    #[Route('/login_check', name: 'api_login_check', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Diese Methode wird nie aufgerufen, da der JWT-Authentifizierungs-Handler
        // die Anfrage abfängt und den Token generiert
        throw new \RuntimeException('Diese Methode sollte nicht aufgerufen werden');
    }

    #[Route('/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        // Validierung
        $loginRequest = $serializer->deserialize($request->getContent(), LoginRequestDTO::class, 'json');
        $errors = $validator->validate($loginRequest);

        if (count($errors) > 0) {
            return $this->json(['errors' => (string) $errors], Response::HTTP_BAD_REQUEST);
        }

        // Neue Benutzerentität erstellen
        $user = new User(
            new PersonName($data['firstName'] ?? '', $data['lastName'] ?? ''),
            new Email($loginRequest->email)
        );

        // Passwort hashen und setzen
        $hashedPassword = $passwordHasher->hashPassword($user, $loginRequest->password);
        $user->setPassword($hashedPassword);

        // Benutzer speichern
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'Benutzer erfolgreich registriert'], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'api_user_info', methods: ['GET'])]
    public function me(#[CurrentUser] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Nicht authentifiziert'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'id' => $user->getId()->toString(),
            'email' => $user->getEmail()?->getValue(),
            'name' => $user->getName()->getFullName(),
            'roles' => $user->getRoles()
        ]);
    }
}
```

## 3. Input-Validierung

### Problem
Unvollständige Validierung von Benutzereingaben in vielen Controllern.

### Lösung

1. **Implementierung von DTOs mit Validierungsconstraints**

```php
<?php

namespace App\DTO\Payment;

use App\Enum\CurrencyEnum;
use App\Enum\PaymentTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePaymentDTO
{
    public function __construct(
        #[Assert\NotBlank(message: 'Team-Benutzer-ID darf nicht leer sein')]
        #[Assert\Uuid(message: 'Ungültige Team-Benutzer-ID')]
        public string $teamUserId,

        #[Assert\NotBlank(message: 'Betrag darf nicht leer sein')]
        #[Assert\PositiveOrZero(message: 'Betrag muss positiv oder Null sein')]
        public int $amount,

        #[Assert\NotBlank(message: 'Währung darf nicht leer sein')]
        #[Assert\Choice(
            choices: [CurrencyEnum::EUR->value, CurrencyEnum::USD->value, CurrencyEnum::GBP->value],
            message: 'Ungültige Währung'
        )]
        public string $currency = CurrencyEnum::EUR->value,

        #[Assert\NotBlank(message: 'Zahlungstyp darf nicht leer sein')]
        #[Assert\Choice(
            choices: [
                PaymentTypeEnum::CASH->value,
                PaymentTypeEnum::BANK_TRANSFER->value,
                PaymentTypeEnum::CREDIT_CARD->value,
                PaymentTypeEnum::MOBILE_PAYMENT->value
            ],
            message: 'Ungültiger Zahlungstyp'
        )]
        public string $type = PaymentTypeEnum::CASH->value,

        #[Assert\Length(
            max: 255,
            maxMessage: 'Beschreibung darf maximal {{ limit }} Zeichen haben'
        )]
        public ?string $description = null,

        #[Assert\Length(
            max: 255,
            maxMessage: 'Referenz darf maximal {{ limit }} Zeichen haben'
        )]
        public ?string $reference = null
    ) {}
}
```

2. **Implementierung eines zentralen Request-Validators**

```php
<?php

namespace App\Service;

use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestValidator
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator
    ) {}

    /**
     * Deserialisiert und validiert eine Request in ein DTO
     *
     * @template T
     * @param Request $request Die zu validierende Anfrage
     * @param class-string<T> $dtoClass Die DTO-Klasse
     * @return T Das validierte DTO-Objekt
     * @throws ValidationException Wenn die Validierung fehlschlägt
     */
    public function validateRequest(Request $request, string $dtoClass): object
    {
        $dto = $this->serializer->deserialize($request->getContent(), $dtoClass, 'json');

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            throw new ValidationException('Validierungsfehler', $errorMessages);
        }

        return $dto;
    }
}
```

3. **Verwendung des Validators in Controllern**

```php
<?php

namespace App\Controller;

use App\DTO\Payment\CreatePaymentDTO;
use App\Service\PaymentService;
use App\Service\RequestValidator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/payments')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly RequestValidator $requestValidator
    ) {}

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        try {
            // Request validieren und in DTO konvertieren
            $createPaymentDTO = $this->requestValidator->validateRequest($request, CreatePaymentDTO::class);

            // Payment erstellen
            $payment = $this->paymentService->createPayment($createPaymentDTO);

            // Response zurückgeben
            return $this->json(['id' => $payment->getId()->toString()], Response::HTTP_CREATED);
        } catch (ValidationException $e) {
            return $this->json(['errors' => $e->getErrors()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
```

## 4. CORS-Konfiguration

### Problem
Unsichere oder zu permissive CORS-Konfiguration.

### Lösung

1. **Aktualisierung der CORS-Konfiguration**

```yaml
# config/packages/nelmio_cors.yaml
nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
        allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
        allow_headers: ['Content-Type', 'Authorization']
        expose_headers: ['Link']
        max_age: 3600
    paths:
        '^/api/':
            allow_origin: ['%env(CORS_ALLOW_ORIGIN)%']
            allow_headers: ['Content-Type', 'Authorization']
            allow_methods: ['GET', 'OPTIONS', 'POST', 'PUT', 'PATCH', 'DELETE']
            max_age: 3600
```

2. **Umgebungsspezifische CORS-Einstellungen**

```env
# .env.dev
CORS_ALLOW_ORIGIN='^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$'

# .env.prod
CORS_ALLOW_ORIGIN='^https://cashbox\.example\.com$'
```

## 5. Sichere Content Security Policy

### Problem
Fehlende oder unzureichende Content Security Policy (CSP).

### Lösung

1. **Implementierung einer CSP mit Symfony**

```yaml
# config/packages/security_headers.yaml
framework:
    assets:
        json_manifest_path: '%kernel.project_dir%/public/build/manifest.json'
        packages:
            app:
                json_manifest_path: '%kernel.project_dir%/public/build/manifest.json'

    # Content Security Policy
    web_link:
        enabled: true

# config/services.yaml (hinzufügen)
services:
    # Content Security Policy
    App\EventSubscriber\ContentSecurityPolicySubscriber:
        tags: ['kernel.event_subscriber']
```

2. **Implementierung eines CSP-EventSubscribers**

```php
<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ContentSecurityPolicySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', -10], // Niedrige Priorität, um nach anderen Modifikationen auszuführen
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();
        $request = $event->getRequest();

        // CSP nur für HTML-Antworten oder wenn explizit angefordert
        $contentType = $response->headers->get('Content-Type');
        if (!$contentType || !str_contains($contentType, 'text/html') && !str_contains($contentType, 'application/json')) {
            return;
        }

        // CSP-Header setzen
        $response->headers->set('Content-Security-Policy', $this->getCSPDirectives());

        // Weitere Sicherheitsheader setzen
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    private function getCSPDirectives(): string
    {
        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Für Entwicklung; in Produktion einschränken
            "style-src 'self' 'unsafe-inline'", // Für Entwicklung; in Produktion einschränken
            "img-src 'self' data: blob:",
            "font-src 'self'",
            "connect-src 'self'",
            "media-src 'self'",
            "object-src 'none'",
            "frame-src 'none'",
            "base-uri 'self'",
            "form-action 'self'"
        ]);
    }
}
```

## Zeitplan und Priorisierung

1. **Sofort (Woche 1):**
   - Implementierung von Secrets Management für sensible Daten
   - Aktualisierung der CORS-Konfiguration

2. **Kurzfristig (Woche 2-3):**
   - Korrekte Implementierung der JWT-Authentifizierung
   - Hinzufügen von Input-Validierung für alle API-Endpunkte

3. **Mittelfristig (Woche 4-6):**
   - Implementierung einer Content Security Policy
   - Sicherheitsaudit und Penetrationstests

4. **Langfristig (kontinuierlich):**
   - Regelmäßige Sicherheitsüberprüfungen und Updates
   - Implementierung von Rate-Limiting und Brute-Force-Schutz
