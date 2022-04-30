<?php
/********************************* Error messages *********************************/

/* Database */
$ERROR_DATABASE_CONNECTION = "Konnte keine Verbindung zur Datenbank herstellen. Läuft diese überhaupt?";
$ERROR_DATABASE_QUERY = "Fehler bei der Vorbereitung.";

/* Table */
$INFO_TABLE_CREATED = "Eine Tabelle wurde erfolgreich erstellt.";
$ERROR_TABLE_CREATION = "Eine Tabelle konnte nicht erstellt werden.";

/* Form reservation */
$ERROR_RESERVATION_TIMESLOT_NOT_SELECTED = "Bitte wähle eine Zeit aus.";
$ERROR_RESERVATION_KAJAK_TYPE_NOT_FOUND = "Kajak-Typ nicht gefunden.";
$ERROR_RESERVATION_KAJAK_NOT_AVAILABLE = "Kajaks nicht verfügbar.";
$ERROR_RESERVATION_KAJAK_NOT_SELECTED = "Bitte wähle einen Kajak aus.";
$ERROR_RESERVATION = "Ein Fehler ist aufgetreten.";
$INFO_RESERVATION_SUCCESS = "Reservierung erfolgreich!";

/* E-Mail */
$ERROR_MAIL_NOT_SENT = "E-Mail konnte nicht versendet werden.";
$ERROR_SUCCESS_BUT_MAIL_NOT_SENT = "Reservierung erfolgreich, aber " . $ERROR_MAIL_NOT_SENT;

/* Cancellation */
$INFO_CANCELLATION_CANCELED = "Reservierung wurde storniert.";
$ERROR_CANCELLATION = "Reservierung konnte nicht storniert werden.";
$ERROR_CANCELLATION_NOT_FOUND = "Reservierung konnte gefunden nicht gefunden.";

/* Kajak */
$ERROR_TYPE_NOT_IN_CONFIG = "Kajak-Typ nicht in Konfiguration gefunden.";
$ERROR_KAJAK_NOT_CREATED = "Kajak konnte nicht erstellt werden.";

/* Login */
$ERROR_LOGIN = "Einloggen fehlgeschlagen!";