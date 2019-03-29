<?php

namespace MOCUtils\Helpers\Money;

class Number
{
    const NUMBER_PATTERN = "/^(?P<HEAD>0|(?P<SIGN>\-)?[1-9]{1}[0-9]{0,2})(?P<MIDDLE>(?:@THOUSANDS[0-9]{3})*)(?:@DECIMAL(?P<FRACTIONAL>[0-9]{2}))?$/";

    private $integerPart;

    private $fractionalPart;

    private $decimalSeparator;

    private $thousandsSeparator;

    private $amount;

    public function __construct($integerPart, $fractionalPart = '', $decimalSeparator = '.', $thousandsSeparator = ',')
    {
        $this->integerPart = $integerPart;
        $this->fractionalPart = $fractionalPart;
        $this->decimalSeparator = $decimalSeparator;
        $this->thousandsSeparator = $thousandsSeparator;

        $this->amount = $this->build();

        return $this;
    }

    public function __toString()
    {
        return (string)$this->amount;
    }

    /**
     * Build a string amount as international number recognized by PHP.
     *
     * @return float|int Number converted.
     */
    public function build()
    {
        $this->integerPart = str_replace($this->thousandsSeparator, '', $this->integerPart);

        $fractionDigits = strlen($this->fractionalPart);
        $subunit = (int)('1' . str_pad('', $fractionDigits, '0'));

        return ((int)$this->integerPart . $this->fractionalPart) / $subunit;
    }

    /**
     * @return float|int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return bool
     */
    public function hasFractionPart()
    {
        return $this->fractionalPart === '';
    }

    /**
     * @return bool
     */
    public function isDecimal()
    {
        return $this->fractionalPart !== '';
    }

    /**
     * @return bool
     */
    public function isInteger()
    {
        return $this->fractionalPart === '';
    }

    /**
     * @return bool
     */
    public function isHalf()
    {
        return $this->fractionalPart === '5';
    }

    /**
     * Creates a new instance with same arguments.
     *
     * @return \Number
     */
    public function newInstance()
    {
        return new self($this->integerPart, $this->fractionalPart, $this->decimalSeparator, $this->thousandsSeparator);
    }

    /**
     * Parse a string amount to \Number object.
     *
     * @param  string $amount Receives an string amount.
     * @param  string|\Currency $currency Accepts an string value or \Currency object.
     *
     * @return \Number                    Returns an instance of \Number object.
     */
    public static function parse($amount, $currency)
    {
        $currency = self::handleCurrencyArg($currency);

        if (is_string($amount)) {
            return self::fromString($amount, $currency);
        }
        return $amount;
    }

    /**
     * Build an \Number from string.
     *
     * @param  string $number Accepts a string amount.
     * @param  string|\Currency $currency Accepts a string or \Currency object.
     *
     * @return \Number                    Returns an \Number instance.
     */
    public static function fromString($value, $currency)
    {
        $decimalSeparator = $currency->getDecimalSeparator();
        $thousandsSeparator = $currency->getThousandsSeparator();

        if (is_numeric($value)) {
            $decimalSeparatorPosition = strpos($value, '.');
            if ($decimalSeparatorPosition === false) {
                return new self($value, '', $decimalSeparator, $thousandsSeparator);
            }

            return new self(
                substr($value, 0, $decimalSeparatorPosition),
                rtrim(substr($value, $decimalSeparatorPosition + 1), '0')
            );
        }

        if (!is_string($value)) {
            throw new \InvalidArgumentException('$value must be a string');
        }

        $pattern = str_replace('@DECIMAL', '\\' . $decimalSeparator, self::NUMBER_PATTERN);
        $pattern = str_replace('@THOUSANDS', '\\' . $thousandsSeparator, $pattern);

        if (preg_match($pattern, $value, $matches)) {
            array_shift($matches);

            $integerPart = str_replace($thousandsSeparator, '', $matches['HEAD'] . $matches['MIDDLE']);
            $fractionalPart = isset($matches['FRACTIONAL']) ? $matches['FRACTIONAL'] : '';

            return new self($integerPart, $fractionalPart, $decimalSeparator, $thousandsSeparator);
        }

        $currencyCode = $currency->getCode();
        throw new \InvalidArgumentException("Invalid format to '$currencyCode' currency.");
    }

    /**
     * Handle a currency value.
     *
     * @param  string|\Currency $currency Accepts an string value or \Currency object.
     *
     * @return \Currency                    Returns an instance of \Number object.
     */
    public static function handleCurrencyArg($currency)
    {
        if (!$currency instanceof Currency && !is_string($currency)) {
            throw new InvalidArgumentException('$currency must be an object of type Currency or a string');
        }

        if (is_string($currency)) {
            $currency = new Currency($currency);
        }
        return $currency;
    }
}
