<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

function send_reservation_email(string $name, string $email_address_to, array $kajaks, array $timeslot, string $date): bool
{
    $formatted_date = date('d.m.Y', strtotime($date));
    $formatted_timeslot_from = date('H:i', strtotime($timeslot[0]));
    $formatted_timeslot_to = date('H:i', strtotime($timeslot[1]));

    return send_mail($email_address_to, "Reservierungsbestätigung Kajak am $formatted_date", "
        <b>Hallo $name,</b>
        <p>
            Du hast am $formatted_date von $formatted_timeslot_from bis $formatted_timeslot_to Uhr eine Reservierung für folgende Kajaks:<br/>
            <ul>
                <li>Einzelkajak: $kajaks[0] Stück</li>
                <li>Doppelkajak: $kajaks[1] Stück</li>
            </ul>
        </p>
        <b>
            <u>Bitte antworte auf diese E-Mail</u>, falls du die Reservierung stornieren möchtest.
        </b>
    ");
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

        $mail->setFrom($email_address, 'Kajak Reservation');
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