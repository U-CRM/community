<?php

/**
 * Authorizing keys example. You must generate new in your app on https://developer.intuit.com
 * @see https://developer.intuit.com/docs/00_quickbooks_online/2_build/40_sdks/03_php/0020_authorization
 */

// ClientID
const QUICKBOOKS_OAUTH_CLIENTID = 'PUT YOUR CLIENT ID HERE';

// Client Secret
const QUICKBOOKS_OAUTH_CLIENTSECRET = 'PUT YOUR CLIENT SECRET HERE';

// OAuth2 Redirect URI
const QUICKBOOKS_OAUTH_REDIRECT_URI = 'PUT REDIRECT URI HERE';

// BaseURL "Development" or "Production"
const QUICKBOOKS_BASE_URL = 'Production';


// Values returned by QB after confirming connection on URL generated with OAuth2UrlGenerator.php
const QUICKBOOKS_OAUTH_CODE = 'L011514360516YCAj1da2FEllWjYUBkVbnyH7ARuNWmJEp9eqN';
const QUICKBOOKS_OAUTH_REALMID = '123145911993709';

// Access and refresh token returned with OAuth2TokenGenerator.php
// Access token expires in 3600 seconds. New can be obtained with OAuth2TokenRefresh.php and
// Refresh token expires in 100 days
const QUICKBOOKS_OAUTH_ACCESS_TOKEN = 'eyJlbmMiOiJBMTI4Q0JDLUhTMjU2IiwiYWxnIjoiZGlyIn0..7dg7tsGa5YQ-_8ltvdkZWg.815H6BJ5LockOlX4OmS7jDTyI2s9wjfBpQYV8jsFAwVWxMt5XYEnWh0dY-1pvcxyuWlnKt7v_glI-vukSjObYHI11R8Kop3V7LYp3uuOLpUwRr0ebEBlpyHcxDfMqcJKWhOt6YIDHpD-fFdI2izggWM41gY8XgLDzZMj6HBhmMPnnlJ3rxrTZesmkUVTQ_XkUPZjFOXTvC5OCrqii9yf0lxB6izD9zfecNfZGtIa7ntBZRPttnQvNMDv-c2KN_Cyt4HksV62Y47gw6uCd1hUvc8_sfIBQ1ynOOr6slsmijmZxwEH23Kfh7ibR_f64tALx4c6rNldlkVq78u17wiut-WNuRHWTNJTen6TX-dEdHTRJLg4qMCW9-xIV2JyCAlK4AmDBLLbroT1WkZhOYHydcfpZz_t1qKnXchfXmo2Lk3Y7WyqpOH01nKpKRFXIw-UUOwqn28_lYIf7QR-M9K8WiMOY__aQ-cS59m1ghY9vy0o2ECG56CUQbsdKglQS9gvucW8b6MrjI49QqxCk_6pKjXOwu2DBe25rq4Fr8l0AT_kqHlcjHRHGfSdm7N5n_9fztuDNeRjPYjhjLMATSc-b2PCX78Hk55bGBPeo3HnMqUX2x0oMXOHb7G_mtt8J0IK16GfzLQEyJBMnfsZvAsmVZz9MYxfBQfHuzGdY6kktHtlxKvLSaWz-UuUJ0rrHA6n.6VK9LOr3eER2qTJdoeKhSQ';
const QUICKBOOKS_OAUTH_REFRESH_TOKEN = 'Q011523087446CqosEz391jK9eTpauSoxPsO6m4pPcPW7sLL4e';
