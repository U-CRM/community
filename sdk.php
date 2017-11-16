<?php

// Shared functions that can be used by all scripts.

define('TEMP_DIR', __DIR__ . '/temp');

function curlCommand($url, $method, array $headers = [], $data = null)
{
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_CUSTOMREQUEST, $method);

    if ($data) {
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    }

    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);

    $result = curl_exec($c);

    $error = curl_error($c);
    $errno = curl_errno($c);

    if ($errno || $error) {
        throw new \Exception("Error for request $url. Curl error $errno: $error");
    }

    $httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new \Exception("Error for request $url. HTTP error ($httpCode): $result");
    }

    curl_close($c);
}

function curlQuery($url, array $headers = [], array $parameters = [])
{
    if ($parameters) {
        $url .= '?' . http_build_query($parameters);
    }

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);

    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 2);

    $result = curl_exec($c);

    $error = curl_error($c);
    $errno = curl_errno($c);

    if ($errno || $error) {
        throw new \Exception("Error for request $url. Curl error $errno: $error");
    }

    $httpCode = curl_getinfo($c, CURLINFO_HTTP_CODE);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new \Exception("Error for request $url. HTTP error ($httpCode): $result");
    }

    curl_close($c);

    if (! $result) {
        throw new \Exception("Error for request $url. Empty result.");
    }

    return json_decode($result, true);
}

function ucrmApiCommand($endpoint, $method, array $data)
{
    curlCommand(
        sprintf('%s/api/v%s/%s', UCRM_API_URL, UCRM_API_VERSION, $endpoint),
        $method,
        [
            'Content-Type: application/json',
            'X-Auth-App-Key: ' . UCRM_API_KEY,
        ],
        json_encode((object) $data)
    );
}

function ucrmApiQuery($endpoint, array $parameters = [])
{
    return curlQuery(
        sprintf('%s/api/v%s/%s', UCRM_API_URL, UCRM_API_VERSION, $endpoint),
        [
            'Content-Type: application/json',
            'X-Auth-App-Key: ' . UCRM_API_KEY,
        ],
        $parameters
    );
}
