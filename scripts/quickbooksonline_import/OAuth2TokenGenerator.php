<?php

require_once 'vendor/autoload.php';

require_once '../../config/quickbooks.php';

use \QuickBooksOnline\API\DataService\DataService;


$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => QUICKBOOKS_OAUTH_CLIENTID,
    'ClientSecret' => QUICKBOOKS_OAUTH_CLIENTSECRET,
    'baseUrl' => QUICKBOOKS_BASE_URL,
    'RedirectURI' => QUICKBOOKS_OAUTH_REDIRECT_URL,
));

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();


$accessToken = $OAuth2LoginHelper->exchangeAuthorizationCodeForToken(
    QUICKBOOKS_OAUTH_CODE,
    QUICKBOOKS_OAUTH_REALMID
);

echo $accessToken->getAccessToken() . PHP_EOL;
echo $accessToken->getRefreshToken() . PHP_EOL;
