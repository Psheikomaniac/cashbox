# Verbesserungsvorschläge für das Cashbox-Projekt

## 1. DTO-Verbesserungen

### Problem: Uneinheitliche Verwendung von Enums in DTOs

In der `UpdatePaymentDTO`-Klasse werden Strings anstelle von Enums verwendet. Dies ist inkonsistent mit der restlichen Anwendung, die native PHP 8.4 Enums verwendet.

```php
// Aktuell in UpdatePaymentDTO.php
#[Assert\Choice(choices: ['EUR', 'USD', 'GBP'])]
public ?string $currency = null,

#[Assert\Choice(choices: ['cash', 'bank_transfer', 'credit_card', 'mobile_payment'])]
public ?string $type = null,
```

### Lösung

```php
// Verbessert
#[Assert\Type(CurrencyEnum::class)]
public ?CurrencyEnum $currency = null,

#[Assert\Type(PaymentTypeEnum::class)]
public ?PaymentTypeEnum $type = null,
```

## 2. Legacy-Methoden entfernen

### Problem: Veraltete Methoden in User.php

In der `User`-Klasse existieren mehrere als "Legacy" markierte Methoden, die jedoch noch verwendet werden. Dies führt zu einer inkonsistenten API und doppelter Funktionalität.

```php
// Legacy compatibility methods - to be removed in future versions
public function getFirstName(): string
public function setFirstName(string $firstName): self
public function getLastName(): string
public function setLastName(string $lastName): self
public function getFullName(): string
```

### Lösung

1. Identifizieren, wo diese Methoden verwendet werden
2. Umstellung dieser Stellen auf die Value-Object-Methoden
3. Entfernen der Legacy-Methoden

## 3. Repository-Optimierungen

### Problem: Inkonsistente Fehlerbehandlung in PenaltyRepository

Das PenaltyRepository gibt keine konsistenten Rückgabewerte bei Fehlerfällen zurück. Bei einigen Methoden könnten leere Arrays zurückgegeben werden, wenn keine Ergebnisse gefunden werden.

### Lösung

Standardisierte Fehlerbehandlung implementieren, z.B. durch Verwendung von Collections oder spezifischen Null-Objekten.

## 4. HTML-Sicherheit in ExportGeneratorService

### Problem: Unzureichende Escape-Behandlung bei HTML-Generierung

In der Methode `generateHtmlFromData` im `ExportGeneratorService` werden Daten direkt in HTML eingefügt, ohne sie zu escapen:

```php
$html .= '<th>' . $header . '</th>';
// und
$html .= '<td>' . $value . '</td>';
```

### Lösung

Daten mit `htmlspecialchars` escapen, um XSS-Angriffe zu verhindern:

```php
$html .= '<th>' . htmlspecialchars((string)$header, ENT_QUOTES, 'UTF-8') . '</th>';
// und
$html .= '<td>' . htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8') . '</td>';
```

## 5. Enum-Handling in Doctrine-Typen

### Problem: Fehlerbehandlung in PaymentTypeEnumType

In der `convertToPHPValue`-Methode von `PaymentTypeEnumType` fehlt die Fehlerbehandlung für ungültige Enum-Werte:

```php
public function convertToPHPValue($value, AbstractPlatform $platform): ?PaymentTypeEnum
{
    if ($value === null) {
        return null;
    }

    return PaymentTypeEnum::from($value);
}
```

### Lösung

Try-Catch-Block hinzufügen, um ungültige Enum-Werte abzufangen:

```php
public function convertToPHPValue($value, AbstractPlatform $platform): ?PaymentTypeEnum
{
    if ($value === null) {
        return null;
    }

    try {
        return PaymentTypeEnum::from($value);
    } catch (\ValueError $e) {
        throw new \Doctrine\DBAL\Exception\InvalidTypeException(
            sprintf('Invalid PaymentTypeEnum value: %s', $value),
            $e
        );
    }
}
```

## 6. Allgemeine PHP 8.4 Empfehlungen

### Problem: Ungenutzte PHP 8.4 Features

Das Projekt nutzt nicht alle Vorteile von PHP 8.4, wie z.B. readonly Properties und Constructor Property Promotion.

### Lösung

1. Konsistente Verwendung von readonly Properties für alle Value Objects
2. Constructor Property Promotion in allen Klassen
3. Verwendung von Union und Intersection Types für präzisere Typdefinitionen
4. Einsatz von Named Arguments für klarere Methodenaufrufe

## 7. Verbesserung der Docker-Konfiguration

### Problem: Duplizierte Docker-Konfigurationen

Es existieren doppelte Docker-Konfigurationsdateien mit leicht unterschiedlichen Konfigurationen.

### Lösung

1. Konsolidierung in eine einzige `compose.yaml`-Datei
2. Verwendung von Multi-Stage-Builds für effizientere Docker-Images
3. Einführung von Docker-Compose-Profilen für verschiedene Umgebungen
4. Optimierung der Dockerfile mit effizienten Layer-Caching

## 8. Testabdeckung verbessern

### Problem: Mangelnde Tests

Es gibt zu wenige Tests für die Komplexität der Anwendung, was die Wartbarkeit und Zuverlässigkeit beeinträchtigt.

### Lösung

1. Unit-Tests für alle Domain-Klassen und Services erstellen
2. Funktionale Tests für Controller und API-Endpoints implementieren
3. Integration Tests für Datenbank-Interaktionen entwickeln
4. Test-Fixtures und Factories für konsistente Testdaten erstellen

## 9. API-Konsistenz verbessern

### Problem: Inkonsistente API-Definitionen

API-Platform-Definitionen werden nicht einheitlich in allen Entitäten verwendet.

### Lösung

1. Standardisierung der API-Resource-Definitionen
2. Einheitliche Verwendung von Normalisierungs- und Denormalisierungskontexten
3. OpenAPI-Dokumentation für alle Endpoints

## 10. Security-Verbesserungen

### Problem: Potenzielle Sicherheitslücken

Fehlende oder unzureichende Eingabevalidierung in einigen Controller-Methoden.

### Lösung

1. Eingabevalidierung für alle Daten implementieren
2. CSRF-Schutz für alle Formulare sicherstellen
3. Content Security Policy einrichten
4. XSS-Schutz durch korrektes Escapen aller Ausgaben
