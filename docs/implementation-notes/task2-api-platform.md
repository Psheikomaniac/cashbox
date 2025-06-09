# Task 2: API Platform statt manueller Controller

## Status: Completed

In diesem Task wurde die Umstellung von manuellen Controllern auf API Platform umgesetzt. Als Beispiel wurde der `ContributionController` ausgewählt und vollständig auf API Platform migriert.

## Implementierte Änderungen

### 1. DTOs für Contribution API

Es wurden drei DTOs erstellt, um die Ein- und Ausgabe der API zu standardisieren:

- `CreateContributionDTO`: Für die Erstellung neuer Beiträge mit Validierungsregeln
- `UpdateContributionDTO`: Für die Aktualisierung bestehender Beiträge mit Validierungsregeln
- `ContributionResponseDTO`: Für die einheitliche Formatierung der API-Antworten

### 2. Custom Providers

Es wurden zwei Provider implementiert, um die Datenabfrage zu steuern:

- `ContributionCollectionProvider`: Für Sammlungen von Beiträgen, mit Unterstützung für verschiedene Filter (unbezahlt, bevorstehend, Team, Benutzer)
- `ContributionItemProvider`: Für einzelne Beiträge

### 3. Custom Processor

Es wurde ein `ContributionProcessor` implementiert, der die Erstellung, Aktualisierung, Bezahlung und Deaktivierung von Beiträgen verarbeitet. Der Processor respektiert das Domain-Modell und verwendet die Business-Methoden der Entity:

- `pay()`: Markiert einen Beitrag als bezahlt
- `activate()` / `deactivate()`: Aktiviert oder deaktiviert einen Beitrag
- `updateDueDate()`: Aktualisiert das Fälligkeitsdatum

### 4. API Resource Konfiguration

Die `Contribution` Entity wurde mit dem `#[ApiResource]` Attribut konfiguriert, um alle API-Endpunkte zu definieren:

- `GET /contributions`: Alle Beiträge abrufen
- `GET /contributions/unpaid`: Unbezahlte Beiträge abrufen
- `GET /contributions/upcoming`: Bevorstehende Beiträge abrufen
- `GET /contributions/team/{teamId}`: Beiträge nach Team filtern
- `GET /contributions/user/{userId}`: Beiträge nach Benutzer filtern
- `GET /contributions/{id}`: Einen einzelnen Beitrag abrufen
- `POST /contributions`: Einen neuen Beitrag erstellen
- `PUT /contributions/{id}`: Einen Beitrag aktualisieren
- `POST /contributions/{id}/pay`: Einen Beitrag als bezahlt markieren
- `DELETE /contributions/{id}`: Einen Beitrag deaktivieren (Soft Delete)

## Vorteile der Umstellung

1. **Standardisierung**: Einheitliche API-Struktur und -Verhalten
2. **Trennung der Zuständigkeiten**: DTOs für Ein-/Ausgabe, Provider für Datenabfrage, Processor für Geschäftslogik
3. **Dokumentation**: Automatische OpenAPI/Swagger-Dokumentation
4. **Erweiterbarkeit**: Einfaches Hinzufügen neuer Operationen oder Filter
5. **Wartbarkeit**: Klare Struktur und weniger duplizierter Code

## Nächste Schritte

- Weitere Controller auf API Platform umstellen
- Sicherheitsregeln verfeinern
- Caching-Strategien implementieren
- Pagination und Sortierung optimieren
