# Task 3: Symfony Autowiring and Dependency Injection

## Status: Completed

In diesem Task wurde die Umstellung auf Symfony Autowiring und Dependency Injection mit Attributen umgesetzt. Dadurch wird die manuelle Konfiguration in YAML-Dateien reduziert und die Wartbarkeit verbessert.

## Implementierte Änderungen

### 1. Repository-Klassen

Alle Repository-Klassen wurden mit Attributen für Service-Konfiguration versehen:

- `#[AsAlias(RepositoryInterface::class)]` für die Bindung der Interface an die Implementierung
- `#[AutoconfigureTag('app.repository')]` für das Tagging des Services

Beispiel:

```php
#[AsAlias(PenaltyRepositoryInterface::class)]
#[AutoconfigureTag('app.repository')]
class DoctrinePenaltyRepository implements PenaltyRepositoryInterface
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    // ...
}
```

Die manuelle Konfiguration in `repositories.yaml` wurde entfernt und durch einen Kommentar ersetzt.

### 2. CQRS-Services

#### Command und Query Bus

Die Command- und Query-Bus-Klassen wurden mit Attributen für Service-Konfiguration versehen:

```php
class CommandBus
{
    public function __construct(
        #[Autowire(service: 'command.bus')]
        private readonly MessageBusInterface $commandBus
    ) {}

    // ...
}
```

```php
class QueryBus
{
    public function __construct(
        #[Autowire(service: 'query.bus')]
        private readonly MessageBusInterface $queryBus
    ) {}

    // ...
}
```

#### Middleware-Klassen

Es wurden neue Middleware-Klassen erstellt, die Symfony's eingebaute Middleware-Klassen erweitern und mit Attributen für Service-Konfiguration versehen sind:

- `CommandBusHandlerMiddleware` mit `#[AutoconfigureTag('command.bus.middleware', ['priority' => 0])]`
- `CommandBusValidationMiddleware` mit `#[AutoconfigureTag('command.bus.middleware', ['priority' => 100])]`
- `QueryBusHandlerMiddleware` mit `#[AutoconfigureTag('query.bus.middleware', ['priority' => 0])]`
- `QueryBusValidationMiddleware` mit `#[AutoconfigureTag('query.bus.middleware', ['priority' => 100])]`

Die manuelle Konfiguration in `cqrs.yaml` wurde reduziert und durch Kommentare ersetzt.

### 3. RateLimiter-Services

Der `RateLimiterListener` wurde mit Attributen für Service-Konfiguration versehen:

```php
class RateLimiterListener
{
    public function __construct(
        #[Autowire(service: 'limiter.login')]
        private RateLimiterFactory $loginLimiter,
        #[Autowire(service: 'limiter.api')]
        private RateLimiterFactory $apiLimiter,
        #[Autowire(service: 'limiter.sensitive_operations')]
        private RateLimiterFactory $sensitiveOperationsLimiter,
        private RequestStack $requestStack,
    ) {}

    // ...
}
```

Die manuelle Konfiguration in `services.yaml` wurde reduziert und durch Kommentare ersetzt.

## Vorteile der Umstellung

1. **Verbesserte Lesbarkeit**: Die Abhängigkeiten sind direkt in den Klassen sichtbar
2. **Reduzierte Konfiguration**: Weniger manuelle Konfiguration in YAML-Dateien
3. **Typsicherheit**: Durch Typdeklarationen wird die Typsicherheit verbessert
4. **Wartbarkeit**: Änderungen an Abhängigkeiten können direkt in den Klassen vorgenommen werden
5. **IDE-Unterstützung**: Bessere Unterstützung durch IDEs für Refactoring und Navigation

## Nächste Schritte

- Weitere Services auf Autowiring und Attribute umstellen
- Verbleibende manuelle Konfiguration in YAML-Dateien reduzieren
- Dokumentation für Entwickler aktualisieren
