<?php

require_once 'vendor/autoload.php';

require_once '../../config/quickbooks.php';

use \QuickBooksOnline\API\DataService\DataService;

$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => QUICKBOOKS_OAUTH_CLIENTID,
    'scope' => "com.intuit.quickbooks.accounting",
    'ClientSecret' => QUICKBOOKS_OAUTH_CLIENTSECRET,
    'RedirectURI' => QUICKBOOKS_OAUTH_REDIRECT_URL,
    'baseUrl' => QUICKBOOKS_BASE_URL,
    'state' => 'csrf_preventing_string'
));


$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();


/**
 * Generates URL of confirmation page. Confirm and use "code" and "realmId" parameters from URL after redirect.
 */
echo $OAuth2LoginHelper->getAuthorizationCodeURL() . PHP_EOL;
