<?php
// Voorkom directe toegang
if (!defined('ABSPATH')) exit;

// Functie om bevestigingsmail te sturen
function send_donation_confirmation($email, $naam, $amount, $currency, $method, $iban, $payment_date, $payment_id) {
    error_log("📧 Versturen van donatiebevestiging naar: " . $email);

    $subject = "Bedankt voor je donatie, $naam!";
    $message = "
        <p>Beste $naam,</p>
        <p>Bedankt voor je donatie! Hier zijn de details van je betaling:</p>
        <table style='border-collapse: collapse; width: 100%; max-width: 600px;'>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>🎁 Donatiebedrag:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'><strong>$amount $currency</strong></td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>📅 Datum:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>$payment_date</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>💳 Betaalmethode:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>$method</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>🏦 IBAN:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>$iban</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ddd; padding: 8px;'>🆔 Transactie-ID:</td>
                <td style='border: 1px solid #ddd; padding: 8px;'>$payment_id</td>
            </tr>
        </table>
        <p>Mocht je vragen hebben, neem dan gerust contact met ons op en vermeld je transactie-ID.</p>
        <p>Met vriendelijke groet,<br>Het team</p>
    ";

    $headers = array('Content-Type: text/html; charset=UTF-8');
    $mail_sent = wp_mail($email, $subject, $message, $headers);
    
    if ($mail_sent) {
        error_log("✅ E-mail succesvol verzonden naar " . $email);
    } else {
        error_log("⛔ FOUT: E-mail kon niet worden verzonden naar " . $email);
    }
}

