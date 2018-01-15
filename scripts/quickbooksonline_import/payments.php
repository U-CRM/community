<?php

require_once 'vendor/autoload.php';

require_once '../../config/quickbooks.php';

require_once '../../config/ucrm_api.php';

require_once '../../sdk.php';

use \QuickBooksOnline\API\DataService\DataService;
use \QuickBooksOnline\API\Facades\Payment;


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
        sprintf('SELECT * FROM Customer WHERE DisplayName LIKE \'%%UCRMID-%d%%\'', $ucrmClientId)
    );

    if (! $customers) {
        return null;
    }

    return current($customers);
}

function getPaymentsFromUCRM() {
    return ucrmApiQuery('payments');
}


function importPayments($dataService) {
    foreach (getPaymentsFromUCRM() as $ucrmPayment) {
        if ($qbClient = getQBClient($dataService, $ucrmPayment['clientId'])) {
            $theResourceObj = Payment::create([
                'CustomerRef' => [
                    'value' => $qbClient->Id
                ],
                'TotalAmt' => $ucrmPayment['amount']
            ]);

            $dataService->Add($theResourceObj);
        }
    }
}

importPayments($dataService);
