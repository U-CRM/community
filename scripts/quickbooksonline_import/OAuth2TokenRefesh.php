<?php

require_once 'vendor/autoload.php';

require_once '../../config/quickbooks.php';

use \QuickBooksOnline\API\DataService\DataService;


$dataService = DataService::Configure(array(
    'auth_mode' => 'oauth2',
    'ClientID' => QUICKBOOKS_OAUTH_CLIENTID,
    'ClientSecret' => QUICKBOOKS_OAUTH_CLIENTSECRET,
    'accessTokenKey' => QUICKBOOKS_OAUTH_ACCESS_TOKEN,
    'refreshTokenKey' => QUICKBOOKS_OAUTH_REFRESH_TOKEN,
    'baseUrl' => QUICKBOOKS_BASE_URL,
    'QBORealmID' => QUICKBOOKS_OAUTH_REALMID,
));

$OAuth2LoginHelper = $dataService->getOAuth2LoginHelper();


$accessToken = $OAuth2LoginHelper->refreshToken();


echo $accessToken->getAccessToken() . PHP_EOL;
echo $accessToken->getRefreshToken() . PHP_EOL;
