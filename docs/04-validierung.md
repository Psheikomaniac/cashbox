# Hinzufügen von Validierung und Constraints

## Aktuelle Probleme

### 1. Fehlende Validierungsconstraints bei Entitäten
- Entitäten fehlen ordnungsgemäße Validierungsannotationen
- Keine Validierung für erforderliche Felder, Zeichenkettenlängen, numerische Bereiche usw.
- Beispiel: User-Entität hat keine Validierung für E-Mail-Format oder erforderliche Felder

### 2. Keine Unique-Constraints
- Fehlende Unique-Constraints für Felder, die eindeutig sein sollten
- Beispiel: Die E-Mail des Benutzers sollte eindeutig sein, hat aber keinen Constraint

### 3. Inkonsistente Validierung
- Einige Controller führen Validierung durch, andere nicht
- Kein standardisierter Ansatz zur Validierung

### 4. Keine Value Objects
- Verwendung primitiver Typen für komplexe Werte
- Keine Kapselung der Validierungslogik in Value Objects
- Beispiel: Geldwerte werden als einfache Floats ohne Validierung dargestellt

## Empfohlene Maßnahmen

### 1. Hinzufügen von Symfony-Validatoren zu Entitäten
- Verwendung von Symfonys Validierungsannotationen/Attributen auf allen Entitäten

### 2. Hinzufügen von Datenbank-Constraints
- Implementierung von Unique-Constraints auf Datenbankebene

### 3. Erstellung von Value Objects
- Entwicklung von Value Objects für komplexe Werte wie Money, Email, PhoneNumber
- Kapselung der Validierungslogik innerhalb dieser Objekte

### 4. Standardisierung des Validierungsansatzes
- Erstellung eines zentralisierten Validierungsdienstes
- Verwendung von Validierungsgruppen zur Steuerung der Validierung in verschiedenen Kontexten
- Implementierung einer konsistenten Behandlung von Validierungsfehlern

### 5. Hinzufügen von Eingabevalidierung für API-Anfragen
- Validierung von Anfragedaten vor der Verarbeitung
- Verwendung von DTO-Klassen mit Validierungsconstraints
- Rückgabe standardisierter Validierungsfehlerantworten

## Implementierungspriorität

Diese Aufgabe sollte mit mittlerer Priorität nach der Behebung der Sicherheitsprobleme, der Standardisierung des API-Ansatzes und der Implementierung der Fehlerbehandlung angegangen werden. Eine ordnungsgemäße Validierung ist wesentlich für die Datenintegrität und verhindert, dass ungültige Daten in das System gelangen.
