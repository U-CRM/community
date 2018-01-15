<?php

require_once 'vendor/autoload.php';

require_once '../../config/quickbooks.php';

require_once '../../config/ucrm_api.php';

require_once '../../sdk.php';

use \QuickBooksOnline\API\DataService\DataService;
use \QuickBooksOnline\API\Facades\Customer;


$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => QUICKBOOKS_OAUTH_CLIENTID,
    'ClientSecret' => QUICKBOOKS_OAUTH_CLIENTSECRET,
    'accessTokenKey' => QUICKBOOKS_OAUTH_ACCESS_TOKEN,
    'refreshTokenKey' => QUICKBOOKS_OAUTH_REFRESH_TOKEN,
    'baseUrl' => QUICKBOOKS_BASE_URL,
    'QBORealmID' => QUICKBOOKS_OAUTH_REALMID,
));


function createQBCustomer($dataService, $ucrmClient) {
    $entities = $dataService->Query(
        sprintf('SELECT * FROM Customer WHERE DisplayName LIKE \'%%UCRMID-%d%%\'', $ucrmClient['id'])
    );

    if (! $entities) {
        if ($ucrmClient['clientType'] === 1) {
            $nameForView = sprintf(
                '%s %s',
                $ucrmClient['firstName'],
                $ucrmClient['lastName']
            );
        } else {
            $nameForView = $ucrmClient['companyName'];
        }

        $customerData = [
            'DisplayName' =>  sprintf(
                '%s (UCRMID-%d)',
                $nameForView,
                $ucrmClient['id']
            ),
            'PrintOnCheckName' => $nameForView,
            'GivenName' => $ucrmClient['firstName'],
            'FamilyName' => $ucrmClient['lastName'],
            'ShipAddr' => [
                'Line1' => $ucrmClient['street1'],
                'Line2' => $ucrmClient['street2'],
                'City' => $ucrmClient['city'],
                'PostalCode' => $ucrmClient['zipCode'],
            ],
            'BillAddr' => [
                'Line1' => $ucrmClient['invoiceStreet1'],
                'Line2' => $ucrmClient['invoiceStreet2'],
                'City' => $ucrmClient['invoiceCity'],
                'PostalCode' => $ucrmClient['invoiceZipCode'],
            ],
        ];

        $dataService->Add(Customer::create($customerData));
    }
}


function getClientsFromUCRM() {
    return ucrmApiQuery('clients');
}

function importCustomers($dataService) {
    foreach (getClientsFromUCRM() as $ucrmClient) {
        createQBCustomer($dataService, $ucrmClient);
    }
}

importCustomers($dataService);

