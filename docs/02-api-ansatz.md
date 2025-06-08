# Wahl eines einheitlichen API-Ansatzes

## Aktuelle Probleme

### 1. Doppelte API-Implementierung
- Das Projekt verwendet derzeit sowohl API Platform als auch manuelle Controller für dieselben Ressourcen
- Beispielsweise wird die User-Entität über beide Wege exponiert:
  - API Platform-Annotationen in der Entitätsklasse
  - Manuelle Controller mit doppelten Endpunkten

### 2. Verwirrung und Wartungsaufwand
- Zwei verschiedene Wege zur Exponierung derselben Ressourcen schaffen Verwirrung
- Änderungen müssen an mehreren Stellen vorgenommen werden
- Inkonsistentes Verhalten zwischen den beiden Ansätzen
- Schwierig zu warten und zu erweitern

## Empfohlene Maßnahmen

### 1. Wahl eines Ansatzes

#### Option 1: Vollständige Nutzung von API Platform
- Entfernung manueller Controller für Ressourcen, die bereits über API Platform exponiert werden
- Nutzung der API Platform-Funktionen zur Anpassung (benutzerdefinierte Operationen, Datenanbieter, Datenpersister)
- Nutzung der eingebauten Funktionen von API Platform (Paginierung, Filterung, Validierung)

#### Option 2: Entfernung von API Platform
- Entfernung der API Platform-Annotationen aus Entitäten
- Standardisierung manueller Controller
- Implementierung konsistenter Muster für CRUD-Operationen

### 2. Standardisierung des gewählten Ansatzes
- Erstellung eines konsistenten Musters für alle Ressourcen
- Dokumentation des Ansatzes für zukünftige Entwicklung
- Sicherstellung, dass alle Ressourcen dem gleichen Muster folgen

### 3. Aktualisierung der Dokumentation
- Aktualisierung der API-Dokumentation zur Widerspiegelung des gewählten Ansatzes
- Bereitstellung von Beispielen zur Nutzung der API

## Implementierungspriorität

Diese Aufgabe sollte mit hoher Priorität nach der Behebung der Sicherheitsprobleme angegangen werden. Ein konsistenter API-Ansatz wird den Codebase wartbarer machen und die Verwirrung für Entwickler, die am Projekt arbeiten, reduzieren.
