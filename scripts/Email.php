<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Create email signature.
 *
 * @return string
 */
function get_email_signature(): string
{
    return '
<br><br>
Beste Grüße<br><br>
Dein Kajak-Team<br>
------------------------------------------<br>
<strong>Allgemeiner Studierendenausschuss (AStA)</strong>
<br>
HTWG<br>
Hochschule Konstanz<br>
Technik, Wirtschaft und Gestaltung<br>
Büro Gebäude D<br>
Alfred-Wachtel-Straße 8<br>
D-78462 Konstanz<br>
<br>
Fon: 07531 / 206 – 431<br>
    ';
}

/**
 * Send email for kajak reservation.
 *
 * @param string        $reservation_id
 * @param string        $name
 * @param string        $email_address_to
 * @param array         $kajaks
 * @param array<string> $timeslot
 * @param string        $date
 * @param int           $price
 *
 * @return bool
 */
function send_reservation_email(string $reservation_id, string $name, string $email_address_to, array $kajaks, array $timeslot, string $date, int $price): bool
{
    $formatted_date = date('d.m.Y', strtotime($date));
    $formatted_timeslot_from = date('H:i', strtotime($timeslot[0]));
    $formatted_timeslot_to = date('H:i', strtotime($timeslot[1]));

    $format_kajaks = '<ul>' . implode(array_map(static function ($kajak) {
            return '<li>' . $kajak['kajak_name'] . ' mit ' . $kajak['seats'] . ' Sitzen</li>';
        }, $kajaks)) . '</ul>';

    return send_mail($email_address_to, "Reservierungsbestätigung Kajak am $formatted_date", "
        Hallo $name,
        <p>
            Du hast am $formatted_date von $formatted_timeslot_from bis $formatted_timeslot_to Uhr eine Reservierung mit der <strong>ID $reservation_id</strong> für folgende Kajaks:<br>
            $format_kajaks
        </p>
        <p>
            Bitte bringe <strong>$price Euro</strong> in Bar und deinen <strong>Studierendenausweis</strong> mit.
        </p>
        <p>
            Um die Reservierung zu stornieren, klicke bitte auf den folgenden Link:<br>
            <a href='http://{$_SERVER['HTTP_HOST']}/cancel?id=$reservation_id'>Stornieren</a>
        </p>
    " . get_email_signature());
}

/**
 * Send email for kajak cancellation.
 *
 * @param string $reservation_id
 * @param string $email_address_to
 *
 * @return bool
 */
function send_cancellation_email(string $reservation_id, string $email_address_to): bool
{
    return send_mail($email_address_to, 'Stornierungsbestätigung Kajak', "
        Hallo,
        <p>
            Du hast deine Reservierung mit der <strong>ID $reservation_id</strong> storniert.
        </p>
        <strong>
            Falls dies ein Fehler war, kontaktiere uns bitte. Falls in der Zwischenzeit am selben Tag bereits jemand anderes reserviert hat, ist eine Rücknahme der Stornierung nicht möglich.
        </strong>
    " . get_email_signature());
}

/**
 * Sends an email to a given address.
 *
 * @param string $email_address_to
 * @param string $subject
 * @param string $body
 *
 * @return bool
 */
function send_mail(string $email_address_to, string $subject, string $body): bool
{
    $email_address = get_env('MAIL_ADDRESS');

    $mail = new PHPMailer(TRUE);
    try {
        /* $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output. */
        $mail->isSMTP();

        /* https://stackoverflow.com/questions/2491475/phpmailer-character-encoding-issues */
        $mail->Encoding = 'base64';
        $mail->CharSet = 'UTF-8';

        $mail->SMTPAuth = TRUE;
        $mail->SMTPKeepAlive = TRUE;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer' => FALSE,
                'verify_peer_name' => FALSE,
                'allow_self_signed' => TRUE
            ]
        ];
        $mail->Host = get_env('MAIL_HOST');
        $mail->Port = get_env('MAIL_PORT');
        $mail->Username = get_env('MAIL_USERNAME');
        $mail->Password = get_env('MAIL_PASSWORD');

        $mail->setFrom($email_address, 'AStA Kajak-Reservierungsservice');
        $mail->addAddress($email_address_to === '' ? $email_address : $email_address_to);

        $mail->isHTML(TRUE);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return TRUE;
    } catch (Exception $exception) {
        error('send_mail', $exception);
        return FALSE;
    }
}