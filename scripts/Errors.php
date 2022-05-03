<?php
/********************************* Error messages *********************************/
/* General */
$ERROR_EXECUTION = "Ein Fehler ist aufgetreten.";

/* Database */
$ERROR_DATABASE_CONNECTION = "Konnte keine Verbindung zur Datenbank herstellen. Läuft diese überhaupt?";
$ERROR_DATABASE_QUERY = "Fehler bei der Vorbereitung.";

/* Table */
$ERROR_TABLE_CREATION = "Eine Tabelle konnte nicht erstellt werden.";
$INFO_TABLE_CREATED = "Eine Tabelle wurde erfolgreich erstellt.";

/* Form reservation */
$ERROR_RESERVATION_TIMESLOT_NOT_SELECTED = "Bitte wähle mindestens einen Zeitslot aus.";
$ERROR_RESERVATION_KAJAK_TYPE_NOT_FOUND = "Der Kajak-Typ wurde nicht gefunden. Haben wir es mit einem Hackermann zu tun?";
$ERROR_RESERVATION_KAJAK_NOT_AVAILABLE = "Mindestens ein angefragter Kajak-Typ ist nicht verfügbar.";
$ERROR_RESERVATION_KAJAK_NOT_SELECTED = "Bitte wähle mindestens ein Kajak aus.";
$ERROR_RESERVATION = $ERROR_EXECUTION;
$ERROR_CHECK_FORM = "Deine Angaben sind fehlerhaft. Bitte überprüfe sie.";
$ERROR_TIMESLOT_GAP = "Bitte wähle die Zeitslots, sodass es keine Lücken gibt.";
$INFO_RESERVATION_SUCCESS = "Reservierung erfolgreich!";

/* E-Mail */
$ERROR_MAIL_NOT_SENT = "E-Mail konnte nicht versendet werden.";
$ERROR_SUCCESS_BUT_MAIL_NOT_SENT = "Reservierung erfolgreich, aber " . $ERROR_MAIL_NOT_SENT;

/* Cancellation */
$ERROR_CANCELLATION = "Reservierung konnte nicht storniert werden.";
$ERROR_CANCELLATION_NOT_FOUND = "Reservierung konnte nicht gefunden werden.";
$INFO_CANCELLATION_CANCELED = "Reservierung wurde erfolgreich storniert.";

/* Kajak */
$ERROR_TYPE_NOT_IN_CONFIG = "Kajak-Typ nicht in Konfiguration gefunden.";
$ERROR_TOO_MANY_SEATS = "Kajak hat zu viele Sitze.";
$ERROR_KAJAK_NOT_CREATED = "Kajak konnte nicht erstellt werden.";

/* Login */
$ERROR_LOGIN = "YOU SHALL NOT PASS!";