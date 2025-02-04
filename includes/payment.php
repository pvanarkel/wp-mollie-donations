<?php
// Voorkom directe toegang
if (!defined('ABSPATH')) exit;

// Functie om een betaling te verwerken
function process_donation() {
    if (isset($_POST['donate'])) {
        $naam = sanitize_text_field($_POST['naam']);
        $email = sanitize_email($_POST['email']);
        $amount = number_format((float)$_POST['amount'], 2, '.', '');
        $recurring = $_POST['recurring'] == "monthly" ? "recurring" : "oneoff";

        $mollie_mode = get_option('mollie_mode', 'test');
        $mollie_api_key = ($mollie_mode === 'live') ? get_option('mollie_live_api_key') : get_option('mollie_test_api_key');

        error_log("🔍 Mollie modus: $mollie_mode, API Key: " . substr($mollie_api_key, 0, 6) . "****");

        // ✅ Aparte klant-ID opslag per modus
        $customer_meta_key = ($mollie_mode === 'live') ? 'mollie_live_customer_id' : 'mollie_test_customer_id';
        $customer_id = get_user_meta(get_current_user_id(), $customer_meta_key, true);

        if (!$customer_id) {
            $customer_data = array("name" => $naam, "email" => $email);
            error_log("📢 Mollie klant aanmaken met: " . print_r($customer_data, true));

            $customer_response = wp_remote_post("https://api.mollie.com/v2/customers", array(
                'headers' => array('Authorization' => "Bearer $mollie_api_key", 'Content-Type' => 'application/json'),
                'body' => json_encode($customer_data)
            ));
            $customer = json_decode(wp_remote_retrieve_body($customer_response), true);

            error_log("📢 Mollie klant respons: " . print_r($customer, true));

            if (isset($customer['id'])) {
                $customer_id = $customer['id'];
                update_user_meta(get_current_user_id(), $customer_meta_key, $customer_id);
            } else {
                error_log("⛔ FOUT: Geen klant-ID ontvangen van Mollie.");
                echo "Er is iets misgegaan bij het aanmaken van je klantprofiel. Probeer opnieuw.";
                return;
            }
        } else {
            error_log("🔄 Bestaande Mollie klant-ID gevonden ($mollie_mode modus): " . $customer_id);
        }

        // Stap 2: Mollie betaling starten
        $payment_data = array(
            "amount" => ["currency" => "EUR", "value" => $amount],
            "description" => ($recurring == "recurring") ? "Eerste betaling voor abonnement" : "Eenmalige donatie",
            "redirectUrl" => home_url('/donatie-bedankt/'),
            "webhookUrl" => home_url('/wp-json/mollie/v1/webhook/'),
            "customerId" => $customer_id,
            "metadata" => array(
                "email" => $email,
                "name" => $naam
            )
        );

        if ($recurring == "recurring") {
            $payment_data["sequenceType"] = "first";
        }

        error_log("📢 Mollie betaling metadata: " . print_r($payment_data['metadata'], true));

        $payment_response = wp_remote_post("https://api.mollie.com/v2/payments", array(
            'headers' => array('Authorization' => "Bearer $mollie_api_key", 'Content-Type' => 'application/json'),
            'body' => json_encode($payment_data)
        ));
        $payment = json_decode(wp_remote_retrieve_body($payment_response), true);

        error_log("📢 Mollie betaling respons: " . print_r($payment, true));

        if (isset($payment['_links']['checkout']['href'])) {
            wp_redirect($payment['_links']['checkout']['href']);
            exit;
        } else {
            error_log("⛔ FOUT: Geen betaal-URL ontvangen van Mollie.");
            echo "Er is iets misgegaan bij het verwerken van je donatie. Probeer het later opnieuw.";
        }
    }
}
add_action('init', 'process_donation');

// Webhook-handler: Alleen e-mail verzenden als de betaling succesvol is
function mollie_webhook_handler() {
    error_log("✅ Webhook AANGEROEPEN!");

    $payment_id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : null;

    if (!$payment_id) {
        $json = file_get_contents("php://input");
        $data = json_decode($json, true);
        error_log("📥 Webhook ontvangen als JSON: " . print_r($data, true));

        if (isset($data['id'])) {
            $payment_id = sanitize_text_field($data['id']);
        }
    }

    if (!$payment_id) {
        error_log("⛔ FOUT: Geen betaling ID ontvangen in Webhook.");
        wp_die("Geen betaling ID ontvangen", 400);
    }

    $mollie_mode = get_option('mollie_mode', 'test');
    $mollie_api_key = ($mollie_mode === 'live') ? get_option('mollie_live_api_key') : get_option('mollie_test_api_key');

    error_log("🔍 Webhook gebruikt Mollie modus: $mollie_mode");
    error_log("🔍 Webhook gebruikt Mollie API Key: " . substr($mollie_api_key, 0, 6) . "****");

    $response = wp_remote_get("https://api.mollie.com/v2/payments/$payment_id", array(
        'headers' => array('Authorization' => "Bearer $mollie_api_key", 'Content-Type' => 'application/json'),
    ));

    $body = wp_remote_retrieve_body($response);
    $payment = json_decode($body, true);
    error_log("🔍 Mollie API Response: " . $body);

    if (!isset($payment['status'])) {
        error_log("⛔ FOUT: Ongeldige Mollie API-respons ontvangen.");
        wp_die("Ongeldige API-respons", 500);
    }

    error_log("🔍 Huidige betalingsstatus: " . $payment['status']);

    if ($payment['status'] === 'paid') {
        $email = isset($payment['metadata']['email']) ? $payment['metadata']['email'] : '';
        $naam = isset($payment['metadata']['name']) ? $payment['metadata']['name'] : 'Onbekend';
        $amount = isset($payment['amount']['value']) ? $payment['amount']['value'] : '0.00';
        $currency = isset($payment['amount']['currency']) ? $payment['amount']['currency'] : 'EUR';
        $method = isset($payment['method']) ? $payment['method'] : 'Onbekend';
        $iban = isset($payment['details']['consumerAccount']) ? $payment['details']['consumerAccount'] : 'Niet beschikbaar';
        $payment_date = isset($payment['paidAt']) ? date("d-m-Y H:i", strtotime($payment['paidAt'])) : 'Onbekend';

        send_donation_confirmation($email, $naam, $amount, $currency, $method, $iban, $payment_date, $payment_id);
        error_log("✅ E-mail verzonden voor betaling $payment_id");
    } else {
        error_log("⛔ Betaling is nog niet afgerond. Webhook stopt hier.");
    }

    http_response_code(200);
    error_log("✅ Webhook correct afgesloten.");
    wp_die("OK", 200);
}

// Webhook registreren in WordPress
add_action('rest_api_init', function () {
    register_rest_route('mollie/v1', '/webhook/', array(
        'methods' => 'POST',
        'callback' => 'mollie_webhook_handler',
        'permission_callback' => '__return_true'
    ));
});

