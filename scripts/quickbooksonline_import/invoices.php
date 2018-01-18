<?php

require_once 'vendor/autoload.php';

require_once '../../config/quickbooks.php';

require_once '../../config/ucrm_api.php';

require_once '../../sdk.php';

use \QuickBooksOnline\API\DataService\DataService;
use \QuickBooksOnline\API\Facades\Invoice;
use \QuickBooksOnline\API\Facades\Item;


$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => QUICKBOOKS_OAUTH_CLIENTID,
    'ClientSecret' => QUICKBOOKS_OAUTH_CLIENTSECRET,
    'accessTokenKey' => QUICKBOOKS_OAUTH_ACCESS_TOKEN,
    'refreshTokenKey' => QUICKBOOKS_OAUTH_REFRESH_TOKEN,
    'baseUrl' => QUICKBOOKS_BASE_URL,
    'QBORealmID' => QUICKBOOKS_OAUTH_REALMID,
));


function getQBClient($dataService, $ucrmClientId) {
    $customers = $dataService->Query(
        sprintf('SELECT * FROM Customer WHERE DisplayName LIKE \'%%UCRMID-%d%%\' MAXRESULTS 1', $ucrmClientId)
    );

    if (! $customers) {
        return null;
    }

    return current($customers);
}

function getInvoicesFromUCRM() {
    return ucrmApiQuery('invoices');
}

function createQBLineFromItem($dataService, $item) {
    $item = Item::create([
        'Name' => sprintf('%s (UCRMID-%s)', $item['label'], $item['id']) ,
        'Type' => 'Service',
        'IncomeAccountRef' => [
            'value' => 30 // Number of Income Account
        ],
    ]);

    return $dataService->Add($item);
}

function createQBInvoice($dataService, $ucrmInvoice) {
    /** @var \QuickBooksOnline\API\Data\IPPCustomer $qbClient */
    if ($qbClient = getQBClient($dataService, $ucrmInvoice['clientId'])) {

        $lines = [];
        foreach ($ucrmInvoice['items'] as $item) {
            $qbItem = createQBLineFromItem($dataService, $item);
            $lines[] = [
                'Amount' => $item['quantity'],
                'Description' => $item['label'],
                'DetailType' => 'SalesItemLineDetail',
                'SalesItemLineDetail' => [
                    'ItemRef' => [
                        'value' => $qbItem->Id
                    ]
                ]
            ];
        }

        $theResourceObj = Invoice::create([
            'Line' => $lines,
            'CustomerRef'=> [
                'value'=> $qbClient->Id
            ],
        ]);

        $dataService->Add($theResourceObj);
    }
}

function importInvoices($dataService) {
    foreach (getInvoicesFromUCRM() as $ucrmInvoice) {
        createQBPayment($dataService, $ucrmInvoice);
    }
}

importPayments($dataService);
