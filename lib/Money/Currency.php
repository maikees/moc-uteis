<?php

namespace MOCUtils\Helpers\Money;

/**
 * Currency
 */
class Currency
{
    private $code;

    private static $currencies = [];

    public function __construct($currencyCode)
    {
        $this->loadCurrencies();

        if (!isset(self::$currencies[$currencyCode])) {
            $currencyCode = strtoupper($currencyCode);
        }

        if (!isset(self::$currencies[$currencyCode])) {
            throw new \InvalidArgumentException(
                sprintf('Unknown currency code "%s"', $currencyCode)
            );
        }

        $this->code = $currencyCode;
    }

    public function __toString()
    {
        return $this->code;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getDecimalSeparator()
    {
        return self::$currencies[$this->code]['decimals'];
    }

    public function getThousandsSeparator()
    {
        return self::$currencies[$this->code]['thousands'];
    }

    public function getFractionDigits()
    {
        return self::$currencies[$this->code]['default_fraction_digits'];
    }

    public function getSubunit()
    {
        return (int)('1' . str_pad('', self::$currencies[$this->code]['default_fraction_digits'], '0'));
    }

    public function loadCurrencies()
    {
        $path = __DIR__ . DS . 'Currencies' . DS . 'currencies.json';

        if (!file_exists($path)) {
            throw new \Exception("Json with currencies not found.");
        }

        $file = file_get_contents($path);
        $currencies = json_decode($file, true);

        if (json_last_error() != JSON_ERROR_NONE) {
            throw new \Exception(json_last_error_msg());
        }

        self::$currencies = $currencies;
    }

    public function equals(Currency $other)
    {
        return $this->code === $other->code;
    }

    public static function getCurrencies()
    {
        return self::$currencies;
    }
}
