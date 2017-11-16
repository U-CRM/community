<?php

// Compatibility: This script is compatible with UCRM 2.8.0 and newer.
// Purpose:       Export billings from UCRM to file formats used in QuickBooks.
require_once __DIR__ . '/../../sdk.php';


function getPayments(DateTimeInterface $createdDateFrom = null, DateTimeInterface $createdDateTo = null)
{
    $parameters = [];
    if ($createdDateFrom) {
        $parameters['createdDateFrom'] = $createdDateFrom->format('Y-m-d');
    }

    if ($createdDateTo) {
        $parameters['createdDateTo'] = $createdDateTo->format('Y-m-d');
    }

    return ucrmApiQuery('payments', $parameters);
}

function createCsvFile(DateTimeInterface $createdDateFrom = null, DateTimeInterface $createdDateTo = null)
{
    $payments = getPayments($createdDateFrom, $createdDateTo);
    $handler = fopen(TEMP_DIR . '/quickbooks.csv', 'wb');

    fputcsv($handler, ['Date', 'Description', 'Amount']);

    foreach ($payments as $payment) {
        $description = 'UCRM Payment, ';

        if ($payment['clientId']) {
            $description .= 'Client ID: ' . $payment['clientId'];
        }

        fputcsv(
            $handler,
            [
                (new DateTimeImmutable($payment['createdDate']))->format('n/d/Y'),
                $description,
                $payment['amount']
            ]
        );
    }

    fclose($handler);
}

createCsvFile();
