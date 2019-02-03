<?php

namespace Rest;

use Rest\RestCountries\RestCountries as restCountries;
use Rest\RestCountries\ResponseHandler as responseHandler;

require_once __DIR__ . '/vendor/autoload.php';

$responseHandler = new responseHandler();
$responseString = '';
$responseCount = 0;

$secondArg = null;

$isCli = ( php_sapi_name() == 'cli' );

if ($isCli and isset($argv)) {
    if (isset($argv[1])) {
        $firstArg = $argv[1];

        if ($firstArg === '--help') {
            $responseString .= $responseHandler->help();
            $responseCount += 1;
        }

    } else {
        $responseString .= $responseHandler->help();
        $responseCount += 1;
    }

    if (isset($argv[2])) {
        $secondArg = $argv[2];
    }

    if ($responseCount) {
        print_r($responseString);

    } else {
        $rest = new restCountries($argv);
        $result = $rest->getResult();

        print_r($result);
    }
} else {
    print_r($responseHandler->noCli(true));

}