-- Create pflegepro database and tables for PostgreSQL

-- First, create the database (run this separately)
-- CREATE DATABASE pflegepro;

-- Connect to pflegepro database, then run the following:

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS Patient CASCADE;
DROP TABLE IF EXISTS Betreuer CASCADE;
DROP TABLE IF EXISTS Benutzer CASCADE;
DROP TABLE IF EXISTS Station CASCADE;

-- Create Station table
CREATE TABLE Station (
    StationID SERIAL PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Adresse VARCHAR(255) DEFAULT ''
);

-- Create Benutzer table
CREATE TABLE Benutzer (
    BenutzerID SERIAL PRIMARY KEY,
    Name VARCHAR(100) NOT NULL,
    Rolle VARCHAR(20) NOT NULL CHECK (Rolle IN ('admin', 'Betreuer', 'Patient')),
    Benutzername VARCHAR(50) UNIQUE NOT NULL,
    Passwort VARCHAR(255) NOT NULL,
    Kontaktdaten VARCHAR(255) DEFAULT ''
);

-- Create Betreuer table
CREATE TABLE Betreuer (
    BetreuerID SERIAL PRIMARY KEY,
    BenutzerID INTEGER NOT NULL REFERENCES Benutzer(BenutzerID) ON DELETE CASCADE,
    StationID INTEGER NOT NULL REFERENCES Station(StationID) ON DELETE CASCADE
);

-- Create Patient table
CREATE TABLE Patient (
    PatientID SERIAL PRIMARY KEY,
    BenutzerID INTEGER NOT NULL REFERENCES Benutzer(BenutzerID) ON DELETE CASCADE,
    Adresse VARCHAR(255) NOT NULL,
    Geburtsdatum DATE NOT NULL,
    Pflegeart VARCHAR(100) DEFAULT '',
    StationID INTEGER REFERENCES Station(StationID) ON DELETE SET NULL,
    BetreuerID INTEGER REFERENCES Betreuer(BetreuerID) ON DELETE SET NULL
);

-- Insert sample data
INSERT INTO Station (Name, Adresse) VALUES 
('Station A', 'Erdgeschoss'),
('Station B', '1. Stock'),
('Station C', '2. Stock');

-- Insert admin user
INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES 
('Administrator', 'admin', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@pflegepro.de');

-- Insert sample Betreuer
INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES 
('Maria Müller', 'Betreuer', 'maria.mueller', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'maria@pflegepro.de'),
('Peter Schmidt', 'Betreuer', 'peter.schmidt', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'peter@pflegepro.de');

INSERT INTO Betreuer (BenutzerID, StationID) VALUES 
(2, 1),
(3, 2);

-- Insert sample Patient
INSERT INTO Benutzer (Name, Rolle, Benutzername, Passwort, Kontaktdaten) VALUES 
('Hans Weber', 'Patient', 'hans.weber', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'hans@email.de / 0123456789');

INSERT INTO Patient (BenutzerID, Adresse, Geburtsdatum, Pflegeart, StationID, BetreuerID) VALUES 
(4, 'Musterstraße 123, 12345 Musterstadt', '1950-05-15', 'Grundpflege', 1, 1);
