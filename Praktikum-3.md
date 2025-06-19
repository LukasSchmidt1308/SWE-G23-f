# PflegePro – Praktikum 3 (Software Engineering) – Projektübersicht

## Projektziel

Entwicklung eines webbasierten Softwaresystems zur Unterstützung der Pflege und Verwaltung von Patienten in Pflegeheimen und häuslicher Betreuung. Das System soll die Überwachung von Gesundheitsparametern, die effiziente Zuweisung von Pflegekräften und die Verwaltung der Patientendaten ermöglichen. Kritische Gesundheitswerte werden automatisch erkannt und als Warnhinweis (JavaScript-Prompt) angezeigt[1].

---

## Anforderungen

### Admin
- Hat vordefinierten Zugang (Login: admin/admin)
- Kann Betreuer und Patienten anlegen, bearbeiten und löschen, inklusive persönlicher Daten (Name, Adresse, Geburtsdatum, Kontaktdaten) sowie Pflegetyp (Pflegestation oder häusliche Pflege)
- Legt bei der Patientenerstellung fest, ob der Patient in einer Pflegestation oder zu Hause betreut wird (Drop-Down-Auswahl mit vorhandenen Stationen sowie „häusliche Pflege“ als Option)
- Weist Patienten automatisch einem Betreuer der gewählten Station zu; bei häuslicher Pflege kann ein beliebiger Betreuer zugeordnet werden
- Kann Betreuer verwalten und ihnen Pflegestationen zuweisen
- Hat Übersicht über alle Betreuer und Patienten im System

### Betreuer (Pflegekräfte)
- Login mit individuellem Benutzernamen und Passwort
- Sind jeweils nur einer Pflegestation zugeordnet
- Können maximal 24 Patienten betreuen
- Sehen ausschließlich ihre zugewiesenen Patienten
- Erfassen und bearbeiten die Gesundheitsparameter ihrer Patienten (Blutdruck, Temperatur, Blutzucker)
- Erhalten Warnhinweise bei Grenzwertüberschreitungen (Temperatur > 40°C, Blutdruck > 180 oder < 90, Blutzucker > 180 oder < 70)
- Alle wichtigen Informationen zu ihren Patienten – einschließlich Gesundheitsparametern, persönlichen Daten (Name, Adresse, Kontakt) und Warnhinweisen – werden auf einer persönlichen Übersichtsseite angezeigt

### Patient
- Login mit individuellen Zugangsdaten
- Können ausschließlich ihre eigenen Gesundheitsdaten (Blutdruck, Temperatur, Blutzucker) auf einer persönlichen Übersichtsseite einsehen
- Können keine eigenen Daten bearbeiten


---

## Datenmodell

- **Benutzer**
  - BenutzerID (int)
  - Name (varchar)
  - Rolle (varchar)
  - Benutzername (varchar)
  - Passwort (varchar)
  - Kontaktdaten (varchar)

- **Patient**
  - PatientID (int)
  - BenutzerID (int)
  - Adresse (varchar)
  - Geburtsdatum (date)
  - Pflegeart (varchar)
  - StationID (int)
  - BetreuerID (int)

- **Betreuer**
  - BetreuerID (int)
  - BenutzerID (int)
  - StationID (int)
  - MaxPatienten (int)

- **Station**
  - StationID (int)
  - Name (varchar)
  - Adresse (varchar)

- **Gesundheitsparameter**
  - ParameterID (int)
  - PatientID (int)
  - Datum (datetime)
  - Blutdruck (varchar)
  - Temperatur (float)
  - Blutzucker (float)

- **Warnhinweis**
  - WarnID (int)
  - PatientID (int)
  - Parameter (varchar)
  - Wert (varchar)
  - Zeitstempel (datetime)

Beziehungen:
- Ein Benutzer kann Patient, Betreuer oder Admin sein.
- Ein Patient ist genau einem Betreuer und einer Station (oder häuslicher Pflege) zugeordnet.
- Ein Betreuer kann mehrere Patienten betreuen (maximal 24).
- Eine Station kann mehrere Betreuer und Patienten haben.
- Gesundheitsparameter und Warnhinweise sind jeweils einem Patienten zugeordnet.

---

## Architektur

- Frontend: HTML & CSS für die Benutzeroberfläche, JavaScript für Interaktivität und Warn-Prompts

- Backend: PHP für die serverseitige Logik, Rollenverwaltung, Datenverarbeitung und Kommunikation mit der Datenbank

- Datenbank: Relationale SQL-Datenbank (z. B. MySQL) für die Speicherung aller Entitäten und Beziehungen

- Kommunikation: HTTP, Formulare und AJAX für den Datenaustausch zwischen Frontend und Backend

- Sicherheit: Anmeldung mit individuellen Zugangsdaten, rollenbasierte Rechtevergabe, Datenschutzkonformität (z. B. DSGVO)

---

## To-Do

- Datenmodell in SQL anlegen
- Frontend- und Backend-Logik für alle Rollen umsetzen
- Warnmechanismus als JavaScript-Prompt implementieren und testen
- Testfälle erstellen: Grenzwertüberschreitungen simulieren und Warnungen prüfen
- Benutzeroberflächen (Mockups/Wireframes) für Admin, Betreuer und Patient entwickeln
- Dokumentation aktuell halten und im Git-Projekt pflegen
