<?php

// @see https://www.fio.cz/docs/cz/API_Bankovnictvi.pdf
const FIO_CZ_API_TOKEN = '';

// Payments older than this date will be ignored.
const FIO_CZ_START_DATE = '2017-01-01';

// Can be 'invoiceNumber', 'clientId', 'clientUserIdent' or a custom attribute key.
const PAYMENT_MATCH_ATTRIBUTE = 'invoiceNumber';
