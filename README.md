# Recommended VS Code Extensions

For this project, the following Visual Studio Code extensions are recommended to support PHP development, database management, and frontend work:

## 1. PHP Server
- **Extension:** `brapifra.phpserver`
- **Purpose:** Easily run and preview your PHP backend locally.
- **How to install:**  
  Open VS Code, go to the Extensions view (`Ctrl+Shift+X` or `Cmd+Shift+X`), search for **PHP Server**, and click **Install**.

## 2. PHP Intelephense
- **Extension:** `bmewburn.vscode-intelephense-client`
- **Purpose:** Provides advanced PHP language support, including autocompletion, linting, and code navigation.
- **How to install:**  
  Search for **Intelephense** in the Extensions view and click **Install**.

## 3. SQLTools
- **Extension:** `mtxr.sqltools`
- **Purpose:** Manage and query your SQL database directly from VS Code.
- **How to install:**  
  Search for **SQLTools** and install it from the Extensions marketplace.

## 4. Five Server (optional)
- **Extension:** `yandeu.five-server`
- **Purpose:** Live reload for HTML, CSS, and JavaScript during frontend development.
- **How to install:**  
  Search for **Five Server** and install it.

## 5. Prettier (optional)
- **Extension:** `esbenp.prettier-vscode`
- **Purpose:** Code formatter for consistent code style across your project.
- **How to install:**  
  Search for **Prettier - Code formatter** and install it.

---

> **Important:** These extensions enhance your coding environment but **do not include the PHP runtime itself**. To run PHP code and use the PHP Server extension properly, you must install the PHP runtime manually on your system.  
> 
> - For **Windows**, download PHP from [windows.php.net](https://windows.php.net/download/) or install XAMPP.  
> - For **macOS**, install PHP via Homebrew with `brew install php`.  
> - For **Linux**, install PHP using your package manager, e.g., `sudo apt install php` on Ubuntu.  
> 
> After installing, make sure to add PHP to your system PATH so VS Code can find the PHP executable.  
> 
> Without this setup, PHP commands and local servers will not work inside VS Code.

---

## Projektstruktur

```
website/
│
├── index.html               # Login-Seite für alle Rollen
├── style.css                # Zentrales CSS für das gesamte Frontend
├── script.js                # Platz für JavaScript (z.B. Warn-Prompts)
├── img/                     # Bilder (z.B. Startbild)
│
└── php/
    ├── login.php            # Dummy-Login-Logik (ersetzt durch DB)
    ├── logout.php           # Logout und Session-Ende
    ├── admin.php            # Admin-Dashboard (Benutzer/Stationen verwalten)
    ├── betreuer.php         # Betreuer-Dashboard (Patienten & Warnparameter)
    └── patient.php          # Patienten-Übersicht (eigene Daten)
```

---

## Rollen & Funktionen

### Admin
- Legt Betreuer, Patienten und Stationen an, bearbeitet und löscht sie.
- Weist Patienten Betreuern und Stationen zu.
- Sieht Übersichten aller Betreuer, Patienten und Stationen.
- Kann Passwörter für neue Benutzer setzen.

### Betreuer
- Sieht nur eigene Patienten.
- Kann Gesundheitsparameter für Patienten erfassen und bearbeiten.
- Sieht Warnhinweise bei Grenzwertüberschreitungen (JavaScript-Prompt).
- Übersicht aller wichtigen Patientendaten.

### Patient
- Sieht ausschließlich eigene Gesundheitsdaten (read-only).
- Keine Bearbeitungsrechte.

---

## Aktueller Stand

- **Frontend**: Alle Seiten und Navigationsstrukturen sind vorhanden.
- **Dummy-Daten**: Alle Daten werden aktuell als Platzhalter im PHP-Code gehalten.
- **Login/Logout**: Funktioniert mit Dummy-Logik (`admin/admin`, `betreuer/betreuer`, `patient/patient`).
- **Admin-Dashboard**: Übersicht und Formulare für Betreuer, Patienten und Stationen (Bearbeiten/Löschen sind Platzhalter).
- **Betreuer-Dashboard**: Übersicht und Warnparameter-Formular, Patientenauswahl per Dropdown.
- **Patienten-Dashboard**: Übersicht der eigenen Daten.
- **Warn-Prompts**: Platz für JavaScript-Logik ist vorbereitet.

---

## Was ist noch zu tun? (To-Do für das Team)

### 1. **Datenbankanbindung**
- SQL-Datenmodell gemäß [`Praktikum-3.md`](Praktikum-3.md) in `/sql/` anlegen.
- In `php/db.php` eine zentrale Datenbankverbindung einrichten.
- Dummy-Daten in allen PHP-Dateien durch echte Datenbankabfragen ersetzen.
- Alle Formulare (Anlegen, Bearbeiten, Löschen) mit der Datenbank verbinden.

### 2. **Login/Session**
- Login-Logik in `login.php` auf echte Benutzerdaten aus der Datenbank umstellen.
- Passwort-Hashing und sichere Authentifizierung implementieren.
- Session-Handling prüfen und absichern.

### 3. **Bearbeiten/Löschen**
- Die "Bearbeiten"- und "Löschen"-Buttons in allen Admin-Tabellen mit echter Backend-Logik hinterlegen (z.B. per Modal oder separater Seite).
- Validierung und Fehlerbehandlung ergänzen.

### 4. **Warnhinweise (JavaScript)**
- In `script.js` die Logik für Warn-Prompts bei Grenzwertüberschreitungen implementieren (siehe Anforderungen).
- Testfälle für verschiedene Grenzwerte anlegen.

### 5. **Dokumentation**
- Diese README aktuell halten.
- SQL-Skripte und ggf. Migrationsanleitungen dokumentieren.
- Kurze Hinweise im Code, wo Teammitglieder weiterarbeiten sollen (`// TODO`-Kommentare).

---

## Hinweise für die Teamarbeit

- **Jede Rolle** sollte ihre eigenen Aufgaben und Funktionen testen.
- **Kommunikation**: Nutzt Pull Requests und Code-Reviews, um Änderungen abzustimmen.
- **Platzhalter**: Überall, wo aktuell Dummy-Daten stehen, sind Kommentare gesetzt – hier bitte später die Datenbanklogik ergänzen.
- **Frontend/Backend-Trennung**: Das Frontend ist so gestaltet, dass die Backend-Logik leicht ergänzt werden kann.

---

## Quickstart für Entwickler

1. Projekt clonen:  
   `git clone ...`
2. Lokalen PHP-Server starten (z.B. mit VS Code Extension oder `php -S localhost:8000 -t website`)
3. Im Browser öffnen:  
   `http://localhost:8000`
4. Mit Dummy-Logins testen:  
   - Admin: `admin/admin`
   - Betreuer: `betreuer/betreuer`
   - Patient: `patient/patient`
5. Datenbankanbindung und echte Logik nach und nach ergänzen.

---

