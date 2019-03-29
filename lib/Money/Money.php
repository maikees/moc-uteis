<?php

namespace MOCUtils\Helpers\Money;

class Money
{
    private $amount;

    private $currency;

    private static $calculator;

    private static $roundingModes = [
        PHP_ROUND_HALF_UP,
        PHP_ROUND_HALF_DOWN,
        PHP_ROUND_HALF_EVEN,
        PHP_ROUND_HALF_ODD
    ];

    public function __construct($amount, $currency = 'USD')
    {
        $this->currency = new Currency($currency);
        $this->amount = $this->assertNumber($amount, $currency);

        return $this;
    }

    /**
     * Convenience factory method for a Money object.
     *
     * Allow to make dynamic parse with currencies.
     *
     * <code>
     * $fiveDollar = Money::USD(500);
     * </code>
     *
     * @param  string $method Equivalent currency code.
     * @param  [type] $arguments    Same as default constructor.
     *
     * @return \Money                Returns new instance.
     */
    public static function __callStatic($method, $arguments)
    {
        return new self($arguments[0], $method);
    }

    /**
     * Format before based on currency.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->format();
    }

    /**
     * Gets raw international PHP value.
     *
     * @return int|float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Gets current \Currency object.
     *
     * @return int|float
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Gets current calculator.
     *
     * @return \Calculator
     */
    public function getCalculator()
    {
        if (self::$calculator === null) {
            self::$calculator = new Calculator;
        }

        return self::$calculator;
    }

    /**
     * Get value and build to raw international php value.
     *
     * @param  int|float|string $value Amount to be asserted.
     * @param  string|\Currency $currency Accept string or \Currency instance.
     * @return int|float                    Return integer or float value.
     */
    private function assertNumber($value, $currency)
    {
        if (is_string($value)) {
            return Number::parse($value, $currency)->getAmount();
        }

        return $value;
    }

    /**
     * Checks if has currency and where precesion is not set
     * gets default fraction digits.
     *
     * @param  int $precision
     * @return int
     *
     * @throws \InvalidArgumentException
     */
    private function assertPrecision($precision)
    {
        if (!isset($this->currency)) {
            throw new Exception('Currency not set.');
        }

        return $precision ?: $this->currency->getFractionDigits();
    }


    /**
     * Check self instance.
     *
     * @param  int|float $value Value to be checked.
     * @return int|float            Return raw international php value.
     */
    private function checkInstance($value)
    {
        $class = get_class();

        if ($value instanceof $class) {
            return $value->getAmount();
        }

        return $value;
    }

    /**
     * Checks if has same currency.
     *
     * @param  \Money $other Other \Money instance.
     * @return boolean
     */
    private function isSameCurrency(Money $other)
    {
        return $this->currency->equals($other->currency);
    }

    /**
     * Asserts that a Money has the same currency as this.
     *
     * @param \Money $other
     *
     * @throws \InvalidArgumentException If $other has a different currency
     */
    private function assertSameCurrency(Money $other)
    {
        if (!$this->isSameCurrency($other)) {
            throw new \InvalidArgumentException('Currencies must be identical');
        }
    }

    /**
     * Returns an integer less than, equal to, or greater than zero
     * if the value of this object is considered to be respectively
     * less than, equal to, or greater than the other.
     *
     * @param \Money $other
     *
     * @return int
     */
    public function compare(Money $other)
    {
        $this->assertSameCurrency($other);
        return $this->getCalculator()->compare($this->amount, $other->amount);
    }

    /**
     * Checks whether the value represented by this object is greater than the other.
     *
     * @param \Money $other
     *
     * @return bool
     */
    public function greaterThan(Money $other)
    {
        return $this->compare($other) === 1;
    }

    /**
     * @param \Money $other
     *
     * @return bool
     */
    public function greaterThanOrEqual(Money $other)
    {
        return $this->compare($other) >= 0;
    }

    /**
     * Checks whether the value represented by this object is less than the other.
     *
     * @param \Money $other
     *
     * @return bool
     */
    public function lessThan(Money $other)
    {
        return $this->compare($other) === -1;
    }

    /**
     * @param \Money $other
     *
     * @return bool
     */
    public function lessThanOrEqual(Money $other)
    {
        return $this->compare($other) <= 0;
    }

    /**
     * Add value to current amount.
     *
     * @param int|float|\Money $value Accept any kind of value.
     *
     * @return \Money                    Return new instance.
     */
    public function add($value)
    {
        $value = $this->assertNumber($value, $this->currency);
        $value = $this->checkInstance($value);

        $result = $this->getCalculator()->add($this->amount, $value);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Subtract value from current amount.
     *
     * @param int|float|\Money $value Accept any kind of value.
     *
     * @return \Money                    Return new instance.
     */
    public function subtract($value)
    {
        $value = $this->assertNumber($value, $this->currency);
        $value = $this->checkInstance($value);

        $result = $this->getCalculator()->subtract($this->amount, $value);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Multiply value from current amount.
     *
     * @param int|float|\Money $value Accept any kind of value.
     *
     * @return \Money                    Return new instance.
     */
    public function multiply($value)
    {
        $value = $this->assertNumber($value, $this->currency);
        $value = $this->checkInstance($value);

        $result = $this->getCalculator()->multiply($this->amount, $value);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Divide value from current amount.
     *
     * @param int|float|\Money $value Accept any kind of value.
     *
     * @return \Money                    Return new instance.
     */
    public function divide($value)
    {
        $value = $this->assertNumber($value, $this->currency);
        $value = $this->checkInstance($value);

        $result = $this->getCalculator()->divide($this->amount, $value);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Get percentual value from current amount.
     *
     * @param int|float $value Accept interger or float value.
     *
     * @return \Money                    Return new instance.
     */
    public function percent($value)
    {
        $value = $this->assertNumber($value, $this->currency);
        $value = $this->checkInstance($value);

        $result = $this->getCalculator()->percent($this->amount, $value);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Get percentual from another amount.
     *
     * @param int|float $value Accept interger or float value.
     *
     * @return \Money                    Return new instance.
     */
    public function percentFrom($value)
    {
        $value = $this->assertNumber($value, $this->currency);
        $value = $this->checkInstance($value);

        $result = $this->getCalculator()->percentFrom($this->amount, $value);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Negate current amount.
     *
     * @return \Money                    Return new instance.
     */
    public function negate()
    {
        $result = $this->getCalculator()->negate($this->amount);

        return new self($result, $this->currency->getCode());
    }

    /**
     * Checks if the value represented by this object is zero.
     *
     * @return bool
     */
    public function isZero()
    {
        return $this->getCalculator()->compare($this->amount, 0) === 0;
    }

    /**
     * Checks if the value represented by this object is positive.
     *
     * @return bool
     */
    public function isPositive()
    {
        return $this->getCalculator()->compare($this->amount, 0) === 1;
    }

    /**
     * Checks if the value represented by this object is negative.
     *
     * @return bool
     */
    public function isNegative()
    {
        return $this->getCalculator()->compare($this->amount, 0) === -1;
    }

    /**
     * Checks if the value represented by this object is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->amount);
    }

    /**
     * Generate installments from amount based on quantity passed.
     *
     * @param  int $quantity How many installments desired.
     * @param  int $precision Arithmetic precision.
     * @param  int $mode Round mode behavior.
     *
     * @return array                Returns an array with data calculated.
     */
    public function installments($quantity, $precision = null)
    {
        return $this->getCalculator()->installments($this->amount, $quantity, $this->currency->getFractionDigits());
    }

    /**
     * Format amount to another currency.
     *
     * @param  string|\Currency $currencyCode Valid currency code based on ISO 4217.
     * @param  integer $precision Calculator precision.
     *
     * @return string                                Formatted currency number.
     */
    public function format($currencyCode = null, $precision = null)
    {
        $currency = $currencyCode ? new Currency($currencyCode) : $this->currency;

        $precision = $this->assertPrecision($precision);

        return number_format($this->amount, $precision, $currency->getDecimalSeparator(), $currency->getThousandsSeparator());
    }

    /**
     * Check is currency is string or \Currency object.
     *
     * @param  string|\Currency $currency Accept string or \Currency object.
     * @return \Currency                    Return new \Currency instance.
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

    /**
     * Instance factory.
     *
     * @param  int|string $amount Amount to init instance.
     * @param  string $currency Valid currency.
     *
     * @return \Money            Returns new instance.
     */
    public static function parse($amount, $currency = 'USD')
    {
        return new self($amount, $currency);
    }
}
