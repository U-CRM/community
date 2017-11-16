<?php

// Compatibility: This script is compatible with UCRM 2.8.0 and newer.
// Purpose:       One-time script to recalculate all taxable prices after you switched to tax inclusive pricing mode.
// Warning:       It is highly recommended that you make a backup of your UCRM database before running this script.
// Limitations:   This script is unable to correctly change prices for services with a planned deferred change.
//                Also it doesn't change prices for existing uninvoiced fees.

// Copy these config files from config.dist to config directory and change to constants to your needs.
require __DIR__ . '/../../config/ucrm_api.php';



require __DIR__ . '/../../sdk.php';

function loadTaxes()
{
    $results = ucrmApiQuery('taxes');

    $taxes = [];
    foreach ($results as $tax) {
        $taxes[$tax['id']] = $tax['rate'] / 100;
    }

    return $taxes;
}

function calculatePrice($price, $tax)
{
    return $price * (1 + $tax);
}

function updateServicePlans(array $taxes)
{
    $results = ucrmApiQuery('service-plans');

    foreach ($results as $servicePlan) {
        if (
            ! $servicePlan['taxable']
            || $servicePlan['taxId'] === null
            || ! array_key_exists($servicePlan['taxId'], $taxes)
        ) {
            continue;
        }

        $periods = [];
        foreach ($servicePlan['periods'] as $period) {
            if ($period['price'] === null) {
                continue;
            }

            $periods[] = (object) [
                'period' => $period['period'],
                'price' => calculatePrice($period['price'], $taxes[$servicePlan['taxId']]),
            ];
        }

        ucrmApiCommand('service-plans/' . $servicePlan['id'], 'PATCH', ['periods' => $periods]);
    }
}

function updateProducts(array $taxes)
{
    $results = ucrmApiQuery('products');

    foreach ($results as $product) {
        if (
            ! $product['taxable']
            || $product['taxId'] === null
            || ! array_key_exists($product['taxId'], $taxes)
        ) {
            continue;
        }

        $price = calculatePrice($product['price'], $taxes[$product['taxId']]);

        ucrmApiCommand('products/' . $product['id'], 'PATCH', ['price' => $price]);
    }
}

function updateSurcharges(array $taxes)
{
    $results = ucrmApiQuery('surcharges');

    foreach ($results as $surcharge) {
        if (
            ! $surcharge['taxable']
            || $surcharge['taxId'] === null
            || ! array_key_exists($surcharge['taxId'], $taxes)
        ) {
            continue;
        }

        $price = calculatePrice($surcharge['price'], $taxes[$surcharge['taxId']]);

        ucrmApiCommand('surcharges/' . $surcharge['id'], 'PATCH', ['price' => $price]);
    }
}

function updateServices(array $taxes)
{
    $results = ucrmApiQuery('clients/services');

    foreach ($results as $service) {
        if (
            $service['tax1Id'] === null
            || ! array_key_exists($service['tax1Id'], $taxes)
        ) {
            continue;
        }

        updateServiceSurcharges($service, $taxes);

        if (
            ! array_key_exists('hasIndividualPrice', $service)
            || ! $service['hasIndividualPrice']
        ) {
            continue;
        }

        $price = calculatePrice($service['price'], $taxes[$service['tax1Id']]);

        ucrmApiCommand('clients/services/' . $service['id'], 'PATCH', ['price' => $price]);
    }
}

function updateServiceSurcharges(array $service, array $taxes)
{
    $results = ucrmApiQuery(sprintf('clients/services/%s/service-surcharges', $service['id']));

    foreach ($results as $serviceSurcharge) {
        if (! $serviceSurcharge['taxable'] || $serviceSurcharge['price'] === null) {
            continue;
        }

        $price = calculatePrice($serviceSurcharge['price'], $taxes[$serviceSurcharge['tax1Id']]);

        ucrmApiCommand('clients/services/service-surcharges/' . $serviceSurcharge['id'], 'PATCH', ['price' => $price]);
    }
}

$taxes = loadTaxes();
updateServicePlans($taxes);
updateProducts($taxes);
updateSurcharges($taxes);
updateServices($taxes);
