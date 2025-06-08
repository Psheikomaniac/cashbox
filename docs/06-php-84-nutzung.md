# Implementierungsplan: PHP 8.4 Nutzung

## 1. Constructor Property Promotion

### Problem
Viele Klassen verwenden immer noch den traditionellen Stil mit separater Eigenschaftsdeklaration und -zuweisung im Konstruktor.

### Lösung

- Umstellung auf Constructor Property Promotion
- Reduzierung von Code-Duplizierung
- Verbesserte Lesbarkeit und Wartbarkeit

## 2. Union und Intersection Types

### Problem
Viele Methoden verwenden PHPDoc-Kommentare für Typendeklarationen oder haben gar keine Typen.

### Lösung

- Nutzung nativer Union Types anstelle von PHPDoc
- Implementierung von Intersection Types für komplexe Typenbeziehungen
- Verbesserte Typensicherheit in der gesamten Anwendung

## 3. Readonly Classes und Properties

### Problem
DTOs und Value Objects sind nicht durchgängig als unveränderlich (readonly) markiert.

### Lösung

- Umstellung auf readonly-Klassen für alle DTOs
- Nutzung von readonly-Properties für unveränderliche Werte
- Kombination mit Constructor Property Promotion für kompakteren Code

## 4. Enums für statische Typen

### Problem
String-basierte Enumerationen oder Konstantenklassen werden anstelle von nativen Enums verwendet.

### Lösung

- Ersetzung von Konstantenklassen durch native Enums
- Hinzufügung von Methoden zu Enums für verbesserte Funktionalität
- Implementierung von Hilfsmethoden in Enums zur vereinfachten Nutzung

## 5. Named Arguments

### Problem
Komplexe Methodenaufrufe mit vielen Parametern sind schwer lesbar.

### Lösung

- Umstellung auf Named Arguments für komplexe Methodenaufrufe
- Verbesserte Lesbarkeit bei Methoden mit vielen Parametern
- Klarere Intention durch benannte Parameter

## 6. First-Class Callable Syntax

### Problem
Callback-Funktionen erfordern umständliche Syntax in Array-Funktionen.

### Lösung

- Nutzung der First-Class Callable Syntax für Callbacks
- Vereinfachung von Array-Funktionen mit Callbacks
- Kompakterer und lesbarerer Code

## 7. Type Declarations überall

### Problem
Unvollständige oder fehlende Typ-Deklarationen machen den Code weniger sicher.

### Lösung

- Hinzufügung von Typdeklarationen zu allen Methoden und Parametern
- Nutzung von Union Types für flexible Parameter
- Verbesserung der Codesicherheit und Dokumentation

## 8. PHP 8.4-spezifische Features

### Problem
Neue PHP 8.4 Features werden nicht genutzt.

### Lösung

- Nutzung des neuen `class_alias` für Enumerationen
- Implementierung von Attributen für Validierungslogik
- Anpassung an neue PHP 8.4-Sprachfeatures

## Zeitplan und Priorisierung

### Sofort (Woche 1)
- Aktualisierung der Enums zur Verwendung nativer Enums
- Einführung von Constructor Property Promotion für neue Klassen

### Kurzfristig (Woche 2-3)
- Umstellung der DTOs auf readonly Klassen
- Hinzufügen von Union Types zu kritischen Methoden

### Mittelfristig (Woche 4-6)
- Vollständige Type Declarations für alle Methoden
- Refactoring komplexer Methodenaufrufe zu Named Arguments

### Langfristig (kontinuierlich)
- First-Class Callable Syntax für Array-Funktionen einführen
- Überwachung und Anpassung an zukünftige PHP 8.4 Features
