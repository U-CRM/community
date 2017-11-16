<?php

// Compatibility: This script is compatible with UCRM 2.8.0 and newer.
// Purpose:       Import new payments from Fio CZ bank account to UCRM.

// Copy these config files from config.dist to config directory and change to constants to your needs.
require __DIR__ . '/../../config/ucrm_api.php';
require __DIR__ . '/../../config/fio_cz.php';



require __DIR__ . '/../../sdk.php';

define('FIO_CZ_SAVE_FILE', TEMP_DIR . '/fio_cz_last_payment.txt');

function downloadTransactionsFromFio(\DateTimeImmutable $since, \DateTimeImmutable $until)
{
    $url = sprintf(
        'https://www.fio.cz/ib_api/rest/periods/%s/%s/%s/transactions.json',
        FIO_CZ_API_TOKEN,
        $since->format('Y-m-d'),
        $until->format('Y-m-d')
    );

    return curlQuery(
        $url,
        [
            'Content-Type: application/json',
        ]
    );
}

function transformTransactionsData(array $data)
{
    $transactions = $data['accountStatement']['transactionList']['transaction'];

    return array_map(
        function ($transaction) {
            $data = [];

            foreach ($transaction as $column) {
                if (! $column) {
                    continue;
                }

                $data[$column['name']] = $column['value'];
            }

            return [
                'amount' => $transaction['column1']['value'],
                'currency' => $transaction['column14']['value'],
                'date' => $transaction['column0']['value'],
                'reference' => $transaction['column5']['value'],
                'id' => $transaction['column22']['value'],
                'data' => $data,
            ];
        },
        $transactions
    );
}

function removeIncomingTransactions(array $transactions)
{
    return array_filter(
        $transactions,
        function ($transaction) {
            return $transaction['amount'] > 0;
        }
    );
}

function matchClientFromUcrm(array $transaction, $matchBy)
{
    $url = sprintf('%s/api/v%s/clients', UCRM_API_URL, UCRM_API_VERSION);

    if ($matchBy === 'invoiceNumber') {
        $url = sprintf('%s/api/v%s/invoices', UCRM_API_URL, UCRM_API_VERSION);
        $parameters = [
            'number' => $transaction['reference'],
        ];
    } elseif ($matchBy === 'clientId') {
        $parameters = [
            'id' => $transaction['reference'],
        ];
    } elseif ($matchBy === 'clientUserIdent') {
        $parameters = [
            'userIdent' => $transaction['reference'],
        ];
    } else {
        $parameters = [
            'customAttributeKey' => $matchBy,
            'customAttributeValue' => $transaction['reference'],
        ];
    }

    $results = curlQuery(
        $url,
        [
            'Content-Type: application/json',
            'X-Auth-App-Key: ' . UCRM_API_KEY,
        ],
        $parameters
    );

    switch (count($results)) {
        case 0:
            printf('No result found for transaction %s.' . PHP_EOL, $transaction['id']);
            return null;
        case 1:
            if ($matchBy === 'invoiceNumber') {
                return [$results[0]['clientId'], $results[0]['id']];
            } else {
                return [$results[0]['id'], null];
            }
        default:
            printf('Multiple matching results found for transaction %s.' . PHP_EOL, $transaction['id']);
            return null;
    }
}

function transformTransactionToUcrmPayment(array $transaction, $clientId, $invoiceId)
{
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', substr($transaction['date'], 0, 10));

    $note = '';
    foreach ($transaction['data'] as $key => $value) {
        $note .= $key . ': ' . $value . PHP_EOL;
    }

    return [
        'clientId' => $clientId,
        'method' => 3, // bank transfer
        'amount' => $transaction['amount'],
        'currencyCode' => $transaction['currency'],
        'note' => $note,
        'invoiceIds' => $invoiceId ? [$invoiceId] : [],
        'providerName' => 'Fio CZ',
        'providerPaymentId' => (string) $transaction['id'],
        'providerPaymentTime' => $date->format('Y-m-d\TH:i:sO'),
        'applyToInvoicesAutomatically' => ! $invoiceId,
    ];
}

function sendPaymentToUcrm(array $payment)
{
    $url = sprintf('%s/api/v%s/payments', UCRM_API_URL, UCRM_API_VERSION);

    curlCommand(
        $url,
        'POST',
        [
            'Content-Type: application/json',
            'X-Auth-App-Key: ' . UCRM_API_KEY,
        ],
        json_encode((object) $payment)
    );
}

function saveLastProcessedTransaction($transaction)
{
    file_put_contents(FIO_CZ_SAVE_FILE, substr($transaction['date'], 0, 10) . PHP_EOL . $transaction['id']);
}

function saveLastProcessedDate(\DateTimeImmutable $date)
{
    file_put_contents(FIO_CZ_SAVE_FILE, $date->format('Y-m-d'));
}

function determineStartDate()
{
    $configStartDate = DateTimeImmutable::createFromFormat('!Y-m-d', FIO_CZ_START_DATE);

    if (! file_exists(FIO_CZ_SAVE_FILE)) {
        return [$configStartDate, null];
    }

    $contents = file_get_contents(FIO_CZ_SAVE_FILE);
    $rows = explode(PHP_EOL, $contents);
    $rows = array_map('trim', $rows);

    $startDate = DateTimeImmutable::createFromFormat('!Y-m-d', $rows[0]);
    $lastProcessedPayment = isset($rows[1]) ? $rows[1] : null;

    if (! $lastProcessedPayment) {
        // The last day was processed entirely, go to the next day.
        $startDate = $startDate->modify('+1 day');
    }

    if ($startDate < $configStartDate) {
        return [$configStartDate, null];
    }

    return [$startDate, $lastProcessedPayment];
}

function removePreviouslyProcessedTransactions(array $transactions, $lastProcessedPayment)
{
    while ($transactions && (string) $transactions[0]['id'] !== $lastProcessedPayment) {
        array_shift($transactions);
    }

    if (! $transactions) {
        throw new \Exception(sprintf('Could not find previously processed transaction %s.', $lastProcessedPayment));
    }

    array_shift($transactions);

    return $transactions;
}

[$startDate, $lastProcessedPayment] = determineStartDate();
$endDate = new DateTimeImmutable('yesterday midnight');

$transactions = downloadTransactionsFromFio($startDate, $endDate);
$transactions = transformTransactionsData($transactions);
$transactions = removeIncomingTransactions($transactions);
if ($lastProcessedPayment) {
    $transactions = removePreviouslyProcessedTransactions($transactions, $lastProcessedPayment);
}

foreach ($transactions as $transaction) {
    printf('Processing transaction %s.', $transaction['id']);
    [$clientId, $invoiceId] = matchClientFromUcrm($transaction, PAYMENT_MATCH_ATTRIBUTE);
    $payment = transformTransactionToUcrmPayment($transaction, $clientId, $invoiceId);
    sendPaymentToUcrm($payment);
    saveLastProcessedTransaction($transaction);
}

saveLastProcessedDate($endDate);
