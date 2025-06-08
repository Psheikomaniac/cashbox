# Implementierungsplan: Symfony 7.2 Best Practices

## 1. Controller mit Attributen statt Annotationen

### Problem
Einige Controller verwenden veraltete Annotations oder haben unvollständige Attributdefinitionen.

### Lösung

- Umstellung aller Controller auf moderne Attribute
- Konsistente Nutzung von Route- und Security-Attributen
- Verbesserte Lesbarkeit und Wartbarkeit der Controller

## 2. API Platform statt manueller Controller

### Problem
Viel API-Logik wird manuell in Controllern implementiert, anstatt API Platform zu nutzen.

### Lösung

- Umstellung auf API Platform für standardisierte REST-Schnittstellen
- Nutzung von benutzerdefinierten Providern für spezielle Datenabruflogik
- Standardisierung der API-Schnittstellen nach REST-Prinzipien

## 3. Symfony Autowiring und Dependency Injection

### Problem
Servicekonfigurationen in YAML-Dateien oder manuelles Einrichten von Services.

### Lösung

- Nutzung von Autowiring und Typdeklarationen für Dependency Injection
- Verwendung von Attributen zur Konfiguration von Services
- Reduzierung der manuellen Konfiguration in YAML-Dateien

## 4. Symfony Messenger für asynchrone Verarbeitung

### Problem
Domänen-Events werden nicht effizient oder überhaupt nicht verarbeitet.

### Lösung

- Einrichtung des Symfony Messenger-Systems
- Implementierung von Event-Message-Transformern
- Erstellung von Message-Handlern für asynchrone Verarbeitung
- Nutzung von Queues für optimierte Leistung

## 5. Symfony Form Handling für Admin-Interfaces

### Problem
Formulardaten werden manuell validiert und verarbeitet.

### Lösung

- Erstellung von Formulartypen für alle Eingabeformulare
- Standardisierte Verarbeitung in Controllern
- Verbesserte Validierung und Fehlermeldungen

## 6. Symfony Serializer und Normalizer

### Problem
Inkonsistente oder manuelle Serialisierung von Objekten zu JSON.

### Lösung

- Implementierung benutzerdefinierter Normalizer für komplexe Objekte
- Standardisierung der Serialisierung über die gesamte Anwendung
- Optimierte Behandlung von relationalen Daten

## Zeitplan und Priorisierung

### Sofort (Woche 1)
- Aktualisierung aller Controller auf Attribute statt Annotations
- Einführung von Autowiring für alle Services

### Kurzfristig (Woche 2-3)
- Implementierung des Messenger-Systems für asynchrone Verarbeitung
- Umstellung einfacher API-Endpunkte auf API Platform

### Mittelfristig (Woche 4-6)
- Erstellung von benutzerdefinierten Normalizern für komplexe Objekte
- Umstellung auf Symfony Forms für Admin-Interfaces

### Langfristig (kontinuierlich)
- Migration aller manuellen Controller zu API Platform
- Optimierung der Messenger-Konfiguration und -Handler
