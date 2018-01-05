<?php

// Compatibility: This script is compatible with UCRM 2.9.0 and newer.
// Purpose:       Import IPPay subscriptions from CSV to UCRM.
// Notes:         The script works with a CSV file with 5 columns:
//                client ID, amount (USD), period (months), next payment date (ISO 8601), IPPay token
//                Example: "16","12.41","12","2017-11-23T00:00:00+0000","123456789"

// Copy these config files from config.dist to config directory and change to constants to your needs.
require __DIR__ . '/../../config/ucrm_api.php';



require __DIR__ . '/../../sdk.php';

function sendPaymentPlanToUcrm(array $paymentPlan)
{
    ucrmApiCommand(
        'payment-plans',
        'POST',
        $paymentPlan
    );
}

function getPaymentPlans($csvFile)
{
    $handle = fopen($csvFile, 'r');

    if ($handle === false) {
        throw new \Exception('Can\'t open CSV file.');
    }

    while (($data = fgetcsv($handle, 0, ",")) !== false) {
        yield [
            'provider' => 'ippay',
            'providerSubscriptionId' => $data[4],
            'clientId' => (int) $data[0],
            'currencyId' => 33, // USD
            'amount' => (float) $data[1],
            'period' => (int) $data[2],
            'startDate' => $data[3],
        ];
    }

    fclose($handle);
}

$csvFile = $argv[1];

foreach (getPaymentPlans($csvFile) as $paymentPlan) {
    sendPaymentPlanToUcrm($paymentPlan);
}
