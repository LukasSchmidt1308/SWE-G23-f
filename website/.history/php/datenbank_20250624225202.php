<?php
require_once 'db.php';

$sql = [
    // Drop existing tables first (in correct order due to foreign keys)
    "DROP TABLE IF EXISTS Warnhinweis CASCADE",
    "DROP TABLE IF EXISTS Gesundheitsparameter CASCADE", 
    "DROP TABLE IF EXISTS Patient CASCADE",
    "DROP TABLE IF EXISTS Betreuer CASCADE",
    "DROP TABLE IF EXISTS Benutzer CASCADE",
    "DROP TABLE IF EXISTS Station CASCADE",

    // Create Station table
    "CREATE TABLE Station(
        StationID SERIAL PRIMARY KEY,
        Name VARCHAR(50) NOT NULL,
        Adresse VARCHAR(100) NOT NULL
    )",

    // Create Benutzer table  
    "CREATE TABLE Benutzer(
        BenutzerID SERIAL PRIMARY KEY,
        Name VARCHAR(100) NOT NULL,
        Rolle VARCHAR(20) NOT NULL 
            CHECK (Rolle IN ('Patient','Betreuer','admin')),
        Benutzername VARCHAR(50) NOT NULL UNIQUE,
        Passwort VARCHAR(255) NOT NULL,
        Kontaktdaten VARCHAR(255) NOT NULL
    )",

    // Create Betreuer table
    "CREATE TABLE Betreuer(
        BetreuerID SERIAL PRIMARY KEY,
        BenutzerID INTEGER NOT NULL,
        StationID INTEGER NOT NULL,
        MaxPatienten INTEGER DEFAULT 24,
        FOREIGN KEY (BenutzerID) REFERENCES Benutzer(BenutzerID)
            ON DELETE CASCADE,
        FOREIGN KEY (StationID) REFERENCES Station(StationID)
            ON DELETE RESTRICT
    )",

    // Create Patient table
    "CREATE TABLE Patient(
        PatientID SERIAL PRIMARY KEY,
        BenutzerID INTEGER NOT NULL,
        Adresse VARCHAR(255) NOT NULL,
        Geburtsdatum DATE NOT NULL,
        Pflegeart VARCHAR(100) NOT NULL,
        StationID INTEGER,
        BetreuerID INTEGER,
        FOREIGN KEY (BenutzerID) REFERENCES Benutzer(BenutzerID)
            ON DELETE CASCADE,
        FOREIGN KEY (StationID) REFERENCES Station(StationID)
            ON DELETE SET NULL,
        FOREIGN KEY (BetreuerID) REFERENCES Betreuer(BetreuerID)
            ON DELETE RESTRICT
    )",

    // Create Gesundheitsparameter table
    "CREATE TABLE Gesundheitsparameter(
        ParameterID SERIAL PRIMARY KEY,
        PatientID INTEGER NOT NULL,
        Datum TIMESTAMP NOT NULL,
        Blutdruck VARCHAR(20),
        Temperatur FLOAT,
        Blutzucker FLOAT,
        FOREIGN KEY (PatientID) REFERENCES Patient(PatientID)
            ON DELETE CASCADE
    )",

    // Create Warnhinweis table
    "CREATE TABLE Warnhinweis(
        WarnID SERIAL PRIMARY KEY,
        PatientID INTEGER NOT NULL,
        Parameter VARCHAR(50),
        Wert VARCHAR(50),
        Zeitstempel TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (PatientID) REFERENCES Patient(PatientID)
            ON DELETE CASCADE
    )",

    // Create trigger function for patient limit validation
    "CREATE OR REPLACE FUNCTION check_max_patients() 
    RETURNS TRIGGER AS $$
    DECLARE
        cnt INT;
        max_patients INT;
    BEGIN
        -- Get current patient count for this Betreuer
        SELECT COUNT(*) INTO cnt
        FROM Patient
        WHERE BetreuerID = NEW.BetreuerID;
        
        -- Get max patients allowed for this Betreuer
        SELECT MaxPatienten INTO max_patients
        FROM Betreuer 
        WHERE BetreuerID = NEW.BetreuerID;
        
        -- Check if limit exceeded
        IF cnt >= max_patients THEN
            RAISE EXCEPTION 'Maximale Patientenzahl für diesen Betreuer erreicht. Limit: %', max_patients;
        END IF;
        
        RETURN NEW;
    END;
    $$ LANGUAGE plpgsql",

    // Create trigger
    "DROP TRIGGER IF EXISTS trg_max_pat ON Patient",
    
    "CREATE TRIGGER trg_max_pat
        BEFORE INSERT ON Patient
        FOR EACH ROW
        EXECUTE FUNCTION check_max_patients()"
];

try {
    // Execute each SQL statement
    foreach ($sql as $statement) {
        $mysqli->exec($statement);
    }
    
    echo "Datenbank und Tabellen erfolgreich erstellt!<br>";
    echo "Tabellen: Station, Benutzer, Betreuer, Patient, Gesundheitsparameter, Warnhinweis<br>";
    
} catch (PDOException $e) {
    echo "Fehler beim Erstellen der Tabellen: " . $e->getMessage();
}
?>