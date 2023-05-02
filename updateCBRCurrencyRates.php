<?php
require_once __DIR__ . "/config.php";
require_once __DIR__ . "/CBRCurrencyRate.php";
require_once __DIR__ . "/CurrencyRatesParser.php";

$currencyRatesParser = new CurrencyRatesParser();
if ($currencyRatesParser->loadRates()) {
    $usd_rate = $currencyRatesParser->getCurrencyRate('USD');
    $result = $currencyRatesParser->saveRates();
    if ($result !== FALSE) {
        echo "Currency rates where successfully updated".PHP_EOL;
        print_r($currencyRatesParser->getRates());
    } else {
        echo "Unable to update currency rates".PHP_EOL;
    }
}
unset($currencyRatesParser);
