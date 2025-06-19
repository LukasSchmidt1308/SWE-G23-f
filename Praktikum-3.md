# PflegePro – Praktikum 3 (Software Engineering) – Projektübersicht

## Projektziel

Entwicklung eines webbasierten Softwaresystems zur Unterstützung der Pflege und Verwaltung von Patienten in Pflegeheimen und häuslicher Betreuung. Das System soll die Überwachung von Gesundheitsparametern, die effiziente Zuweisung von Pflegekräften und die Verwaltung der Patientendaten ermöglichen. Kritische Gesundheitswerte werden automatisch erkannt und als Warnhinweis (JavaScript-Prompt) angezeigt[1].

---

## Aufgaben und Ziele

- Rollenbasierte Zugriffssteuerung: Admin, Betreuer (Pflegekraft), Patient mit jeweils eigenen Rechten und Ansichten
- Admin-Funktionen:
  - Betreuer und Patienten anlegen, bearbeiten, löschen (inkl. persönlicher Daten, Pflegeart, Pflegestation)
  - Patienten automatisch einem Betreuer der gewählten Station zuweisen (bei häuslicher Pflege Auswahl eines beliebigen Betreuers)
  - Betreuer verwalten und ihnen Pflegestationen zuweisen
  - Übersicht über alle Betreuer und Patienten
- Betreuer-Funktionen:
  - Login mit individuellem Benutzernamen und Passwort
  - Jeder Betreuer ist genau einer Pflegestation zugeordnet und kann maximal 24 Patienten betreuen
  - Betreuer sieht ausschließlich die eigenen Patienten
  - Erfassung und Bearbeitung der Gesundheitsparameter (Blutdruck, Temperatur, Blutzucker) für die eigenen Patienten
  - Warnhinweise bei Grenzwertüberschreitungen (Temperatur > 40°C, Blutdruck > 180 oder < 90, Blutzucker > 180 oder < 70) werden als JavaScript-Prompt angezeigt
  - Alle relevanten Patientendaten und Warnhinweise werden auf einer Übersichtsseite angezeigt
- Patienten-Funktionen:
  - Login mit individuellen Zugangsdaten
  - Patient kann ausschließlich die eigenen Gesundheitsdaten (Blutdruck, Temperatur, Blutzucker) auf einer persönlichen Übersichtsseite einsehen
  - Patient kann keine eigenen Daten bearbeiten
- Warnmechanismus:
  - Bei Überschreitung der definierten Grenzwerte wird ein Warnhinweis als Prompt angezeigt
- Testkonzept:
  - Im Uni-Testsystem werden gezielt kritische Gesundheitsparameter gesetzt, um die Funktion des Warnmechanismus zu überprüfen

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
