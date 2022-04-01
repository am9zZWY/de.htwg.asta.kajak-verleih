<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Create email signature.
 * @return string
 */
function email_signature(): string
{
    return "
<br/><br/>
Beste Grüße<br/>
Dein Kajak-Team<br/><br/>
------------------------------------------<br/>
<strong>Allgemeiner Studierendenausschuss (AStA)</strong>
<br/>
HTWG<br/>
Hochschule Konstanz<br/>
Technik, Wirtschaft und Gestaltung<br/>
Büro Gebäude D<br/>
Alfred-Wachtel-Straße 8<br/>
D-78462 Konstanz<br/>
<br/>
Fon: 07531 / 206 – 431<br/>
    ";
}

function send_reservation_email(string $reservation_id, string $name, string $email_address_to, array $kajaks, array $timeslot, string $date): bool
{
    $formatted_date = date('d.m.Y', strtotime($date));
    $formatted_timeslot_from = date('H:i', strtotime($timeslot[0]));
    $formatted_timeslot_to = date('H:i', strtotime($timeslot[1]));

    return send_mail($email_address_to, "Reservierungsbestätigung Kajak am $formatted_date", "
        Hallo $name,
        <p>
            Du hast am $formatted_date von $formatted_timeslot_from bis $formatted_timeslot_to Uhr eine Reservierung mit der <strong>ID $reservation_id</strong> für folgende Kajaks:<br/>
            <ul>
                <li>Einzelkajak: $kajaks[0] Stück</li>
                <li>Doppelkajak: $kajaks[1] Stück</li>
            </ul>
        </p>
        <p>
            Um die Reservierung zu stornieren, klicke bitte auf den folgenden Link:<br/>
            <a href='http://{$_SERVER['HTTP_HOST']}/cancel?id={$reservation_id}'>Stornieren</a>
        </p>
    " . email_signature());
}

function send_cancellation_email(string $reservation_id, string $email_address_to): bool
{
    return send_mail($email_address_to, "Stornierungsbestätigung Kajak", "
        Hallo,
        <p>
            Du hast deine Reservierung mit der <strong>ID $reservation_id</strong> storniert.
        </p>
        <strong>
            Falls dies ein Fehler war, kontaktiere uns bitte. Falls in der Zwischenzeit am selben Tag bereits jemand anderes reserviert hat, ist eine Rücknahme der Stornierung nicht möglich.
        </strong>
    " . email_signature());
}

function send_mail(string $email_address_to, string $subject, string $body): bool
{
    $email_address = get_env('MAIL_ADDRESS');

    $mail = new PHPMailer(true);
    try {
        /* $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Enable verbose debug output. */
        $mail->isSMTP();

        /* https://stackoverflow.com/questions/2491475/phpmailer-character-encoding-issues */
        $mail->Encoding = 'base64';
        $mail->CharSet = 'UTF-8';

        $mail->SMTPAuth = true;
        $mail->SMTPKeepAlive = true;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );
        $mail->Host = get_env('MAIL_HOST');
        $mail->Port = get_env('MAIL_PORT');
        $mail->Username = get_env('MAIL_USERNAME');
        $mail->Password = get_env('MAIL_PASSWORD');

        $mail->setFrom($email_address, 'AStA Kajak-Reservierungsservice');
        $mail->addAddress($email_address_to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}