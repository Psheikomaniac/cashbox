# Sicherheitsverbesserungen im Cashbox-Projekt

## Aktuelle Probleme

### 1. Authentifizierung ist fehlerhaft
- JWT ist konfiguriert, aber alle API-Routen erlauben PUBLIC_ACCESS
- In der security.yaml-Datei sind alle API-Routen auf PUBLIC_ACCESS gesetzt, was bedeutet, dass keine Authentifizierung erforderlich ist

### 2. Kein echtes Benutzerauthentifizierungssystem
- Verwendung eines In-Memory-Providers ohne echtes Benutzersystem
- Die security.yaml-Datei zeigt, dass die Anwendung einen In-Memory-Benutzeranbieter verwendet

### 3. Fehlendes Passwortfeld in der User-Entität
- Die User-Entität hat kein Passwortfeld
- Die User-Entität implementiert weder UserInterface noch PasswordAuthenticatedUserInterface
- Ohne diese ist eine ordnungsgemäße Authentifizierung nicht möglich

### 4. Keine Ratenbegrenzung oder CORS-Konfiguration
- Der Anwendung fehlt eine Ratenbegrenzung zur Verhinderung von Brute-Force-Angriffen
- Keine CORS-Konfiguration zur Steuerung, welche Domains auf die API zugreifen können

## Empfohlene Maßnahmen

### 1. Aktualisierung der User-Entität
- Hinzufügen eines Passwortfelds zur User-Entität
- Implementierung von UserInterface und PasswordAuthenticatedUserInterface
- Hinzufügen eines Rollenfelds zur Unterstützung der rollenbasierten Zugriffssteuerung

### 2. Konfiguration einer ordnungsgemäßen Authentifizierung
- Ersetzen des In-Memory-Providers durch einen Datenbank-Benutzeranbieter
- Aktualisierung von security.yaml, um Authentifizierung für API-Routen zu verlangen
- Nur notwendige Routen als PUBLIC_ACCESS belassen (Login, Registrierung)

### 3. Implementierung von Ratenbegrenzungen
- Hinzufügen von Ratenbegrenzungen für Anmeldeversuche zur Verhinderung von Brute-Force-Angriffen
- Konfiguration von Ratenbegrenzungen für API-Endpunkte zur Verhinderung von Missbrauch

### 4. Konfiguration von CORS
- Einrichtung einer ordnungsgemäßen CORS-Konfiguration zur Steuerung, welche Domains auf die API zugreifen können
- Einschränkung erlaubter Ursprünge, Methoden und Header

## Implementierungspriorität

Diese Aufgabe sollte mit höchster Priorität behandelt werden, da sie eine kritische Sicherheitslücke in der Anwendung darstellt. Ohne ordnungsgemäße Authentifizierung sind die Daten der Anwendung für unbefugten Zugriff exponiert.
