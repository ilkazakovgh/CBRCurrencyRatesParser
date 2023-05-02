# Реализация класса CurrencyRatesParser

Реализация классов на PHP для загузки курсов валют с сайта 
ЦБ РФ и сохранения их в БД MySQL.

Пример использования в файле updateCBRCurrencyRates.PHP

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

Структура таблицы в БД

    CREATE TABLE `currency` (
        `id` int(11) NOT NULL,
        `num_code` varchar(3) DEFAULT NULL,
        `char_code` varchar(3) DEFAULT NULL,
        `name` varchar(64) NOT NULL,
        `rate` float DEFAULT NULL,
        `update_date` datetime NOT NULL DEFAULT current_timestamp()
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;