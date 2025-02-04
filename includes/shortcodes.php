<?php
// Voorkom directe toegang
if (!defined('ABSPATH')) exit;

// Shortcode om het donatieformulier in te voegen
function mollie_donatie_form_shortcode() {
    ob_start(); ?>
    <style>
        .donatie-formulier {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: #38629F;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .donatie-formulier label {
            font-weight: bold;
            display: block;
            margin-top: 10px;
        }
        .donatie-formulier input, .donatie-formulier select, .donatie-formulier button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
            appearance: none;
        }
        .donatie-formulier button {
            background:rgb(14, 170, 0);
            color: white;
            font-size: 16px;
            border: none;
            cursor: pointer;
        }
        .donatie-formulier button:hover {
            background:rgb(14, 170, 0);
        }
    </style>

    <div class="donatie-formulier">
        <h3>Steun ons met een donatie!</h3>
        <form method="post" action="">
            <label for="naam">Naam</label>
            <input type="text" id="naam" name="naam" required placeholder="Je naam">

            <label for="email">E-mail</label>
            <input type="email" id="email" name="email" required placeholder="Je e-mail">

            <label for="amount">Bedrag (€)</label>
            <input type="number" id="amount" name="amount" min="5.80" step="0.01" required placeholder="Bijv. 5.80">

            <label for="recurring">Type Donatie</label>
            <select id="recurring" name="recurring">
                <option value="oneoff">Eenmalig</option>
            </select>

            <button type="submit" name="donate">Doneer Nu</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode('mollie_donation_form', 'mollie_donatie_form_shortcode');


