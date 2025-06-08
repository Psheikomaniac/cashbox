# Implementierungsplan: Docker-Setup

## 1. Konsolidierung der Docker-Konfiguration

### Problem
Duplizierte Docker-Konfigurationsdateien (`docker-compose.yaml` und `compose.yaml`) mit Inkonsistenzen.

### Lösung

- Entfernen der veralteten Konfigurationsdatei
- Erstellen einer optimierten `compose.yaml`
- Erstellung eines `docker-compose.override.yml` für die Entwicklungsumgebung

## 2. Implementierung eines Multi-Stage Builds

### Problem
Uneffiziente Docker-Image-Erstellung durch wiederholte Installationen in jedem Container.

### Lösung

- Erstellen eines optimierten Dockerfiles mit Build-, Produktions- und Entwicklungs-Stages
- Erstellen des Docker-Entrypoints für flexible Konfiguration
- Implementierung eines Gesundheitscheck-Skripts

## 3. Implementierung separater Docker-Compose-Profile

### Problem
Nicht alle Services (wie GitLab) werden in jeder Umgebung benötigt, verbrauchen aber Ressourcen.

### Lösung

- Dokumentation der Docker-Compose-Profile
- Aktualisierung der `compose.yaml` für verschiedene Profile
- Optimierung der Ressourcennutzung durch bedarfsgerechte Service-Aktivierung

## 4. Optimierung der Datenbankinitialisierung

### Problem
Unkonsistente Datenbankinitialisierung bei verschiedenen Umgebungen.

### Lösung

- Erstellung von Datenbankinitialisierungsskripten
- Automatische Generierung von Testdaten
- Implementierung eines Symfony-Befehls zur Testdaten-Generierung

## 5. Implementierung einer optimierten CI/CD-Pipeline

### Problem
Fehlen einer konsistenten CI/CD-Konfiguration für die Docker-Umgebung.

### Lösung

- Erstellung einer `.gitlab-ci.yml`-Datei
- Konfiguration von Build-, Test- und Deployment-Jobs
- Erstellung eines Deployment-Skripts

## Zeitplan und Priorisierung

### Sofort (Woche 1)
- Konsolidierung der Docker-Konfigurationsdateien
- Implementierung des Multi-Stage-Builds

### Kurzfristig (Woche 2-3)
- Erstellung der Docker-Compose-Profile
- Optimierung der Datenbankinitialisierung

### Mittelfristig (Woche 4-6)
- Implementierung des CI/CD-Pipelines
- Erstellung der Testdaten-Generator-Befehle

### Langfristig (kontinuierlich)
- Überwachung und Optimierung der Docker-Container
- Erweiterung der CI/CD-Pipeline mit zusätzlichen Tests und Qualitätssicherung
