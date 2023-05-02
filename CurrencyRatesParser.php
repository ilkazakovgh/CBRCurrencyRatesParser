<?php
class CurrencyRatesParser
{
    private array $rates = [];
    private object $xmlDoc;

    function __construct() {
        $this->xmlDoc = new DOMDocument();
    }
    public function loadRates() : bool
    {
        if ($this->xmlDoc->load(CURRENCY_RATES_URL . "?date_req=" .date('d.m.Y'))) {
            $this->rates = [];
            $root = $this->xmlDoc->documentElement;

            $currencies = $root->getElementsByTagName('Valute');

            foreach ($currencies as $c) {
                $charCode = trim($c->getElementsByTagName('CharCode')->item(0)->nodeValue);
                $this->rates[$charCode] = new CBRCurrencyRate();
                $this->rates[$charCode]->charCode = trim($c->getElementsByTagName('CharCode')->item(0)->nodeValue);
                $this->rates[$charCode]->numCode = trim($c->getElementsByTagName('NumCode')->item(0)->nodeValue);
                $this->rates[$charCode]->name = trim($c->getElementsByTagName('Name')->item(0)->nodeValue);
                $this->rates[$charCode]->nominal = intval($c->getElementsByTagName('Nominal')->item(0)->nodeValue);
                $this->rates[$charCode]->value = floatval(str_replace(',', '.', trim($c->getElementsByTagName('Value')->item(0)->nodeValue)));
            }
            return true;
        }
        return false;
    }

    public function getCurrencyRate(string $character_code) : ?CBRCurrencyRate
    {
        return $this->rates[$character_code] ?? null;
    }

    public function getRates() : array {
        return $this->rates;
    }

    public function saveRates() : bool {
        $result = true;
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_DATABASE_NAME . ";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
        ];
        try {
            $pdo = new PDO($dsn, DB_USERNAME, DB_PASSWORD, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }

        $stmtTruncate = $pdo->prepare("TRUNCATE TABLE `currency`");
        $result = $stmtTruncate->execute();

        if ($result) {
            $sql = "INSERT INTO `currency`(`name`, `rate`, `num_code`, `char_code`) VALUES (?,?,?,?)";
            $stmt = $pdo->prepare($sql);

            try {
                $pdo->beginTransaction();
                foreach ($this->rates as $rate) {
                    $params = [$rate->name];
                    if ($rate->nominal > 1) {
                        $params[] = round($rate->value / $rate->nominal, 3);
                    } else {
                        $params[] = round($rate->value);
                    }
                    $params[] = $rate->numCode;
                    $params[] = $rate->charCode;
                    $result = $result && $stmt->execute($params);
                    unset($params);
                }
                $pdo->commit();
            } catch (\PDOException $e) {
                $pdo->rollback();
                throw new \PDOException($e->getMessage(), (int)$e->getCode());
            }
        }
        $pdo = null;

        return $result;
    }

    function __destruct() {
        unset($this->rates);
        unset($this->xmlDoc);
    }
}
