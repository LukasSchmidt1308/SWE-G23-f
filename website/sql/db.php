<?php
require_once 'db.php'; // stellt $mysqli bereit
$sql = <<<SQL



-- Tabellen erstellen
CREATE DATABASE IF NOT EXISTS pflegepro;
USE pflegepro;

CREATE TABLE IF NOT EXISTS Benutzer(
    BenutzerID INTEGER,
    Name VARCHAR(20) NOT NULL,
    Rolle VARCHAR(20) 
        CHECK (Rolle IN ('Patient','Betreuer','Admin')),
    Benutzername VARCHAR(20) NOT NULL UNIQUE,
    Passwort VARCHAR(20) NOT NULL,
    Kontaktdaten VARCHAR(20) NOT NULL,
    PRIMARY KEY(BenutzerID)
)

CREATE TABLE IF NOT EXISTS Station(
    StationID INTEGER,
    Name VARCHAR(20) NOT NULL,
    Adresse VARCHAR(50) NOT NULL,
    PRIMARY KEY(StationID)  
)

CREATE TABLE IF NOT EXISTS Betreuer(
    BetreuerID INTEGER,
    BenutzerID INTEGER,
    StationID INTEGER,
    MaxPatienten INTEGER DEFAULT 24,
    PRIMARY KEY(BetreuerID),
    FOREIGN KEY (BenutzerID) REFERENCES Benutzer(BenutzerID)
        ON DELETE CASCADE ON UPDATE CASCADE,,
    FOREIGN KEY (StationID) REFERENCES Station(StationID)
        ON DELETE RESTRICT ON UPDATE CASCADE
)



CREATE TABLE IF NOT EXISTS Patient(
    PatientID INTEGER,
    BenutzerID INTEGER,
    Adresse VARCHAR(50) NOT NULL,
    Geburtsdatum DATE NOT NULL,
    Pflegeart VARCHAR(20) NOT NULL,
    StationID INTEGER NULL,
    BetreuerID INTEGER,
    PRIMARY KEY(PatientID),
    FOREIGN KEY (BenutzerID) REFERENCES Benutzer(BenutzerID)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (StationID) REFERENCES Station(StationID)
        ON DELETE SET NULL  ON UPDATE CASCADE,
    FOREIGN KEY (BetreuerID) REFERENCES Betreuer(BetreuerID)
        ON DELETE RESTRICT ON UPDATE CASCADE
)

CREATE TABLE IF NOT EXISTS Gesundheitsparameter(
    ParameterID INTEGER,
    PatientID INTEGER,
    Datum DATETIME NOT NULL,
    Blutdruck VARCHAR(20),
    Temperatur FLOAT,
    Blutzucker FLOAT,
    PRIMARY KEY(ParameterID),
    FOREIGN KEY (PatientID) REFERENCES Patient(PatientID)
        ON DELETE CASCADE ON UPDATE CASCADE
)

CREATE TABLE IF NOT EXISTS Warnhinweis(
    WarnID INTEGER,
    PatientID INTEGER,
    Parameter VARCHAR(20),
    Wert VARCHAR(20),
    Zeitstempel DATETIME,
    PRIMARY KEY(WarnID),
    FOREIGN KEY (PatientID) REFERENCES Patient(PatientID)
        ON DELETE CASCADE ON UPDATE CASCADE
)


CREATE OR REPLACE FUNCTION ch_max_pat()
RETURNS TRIGGER AS $$

BEGIN
  DECLARE cnt INT;
  SELECT COUNT(*) INTO cnt
    FROM Patient
   WHERE BetreuerID = NEW.BetreuerID;

  IF cnt >= (SELECT MaxPatienten FROM Betreuer WHERE BetreuerID = NEW.BetreuerID) THEN
   RAISE EXCEPTION 'Maximale Patientenzahl für diesen Betreuer erreicht.';
   END IF;
END;

$$ LANGUAGE plpgsql;



CREATE TRIGGER trg_max_pat
BEFORE INSERT ON Patient
FOR EACH ROW
EXECUTE FUNCTION ch_max_pat();

SQL;

// Multi-Query ausführen
if ($mysqli->multi_query($sql)) {
    do {
        /* leere Ausgabe-Buffer */
    } while ($mysqli->more_results() && $mysqli->next_result());
    echo "Datenbank und Tabellen erfolgreich erstellt!";
} else {
    echo "Fehler beim Erstellen der Tabellen: (" . $mysqli->errno . ") " . $mysqli->error;
}

$mysqli->close();
?>
