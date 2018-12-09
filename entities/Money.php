<?php

/*
 * todo: this class is too toyish.
 * make long arithmetic
 * proper interface
 */

class Money
{
    public $amount;

    /**
     * @throws InvalidInputException If amount is not int
     */
    public function __construct($amount)
    {
        $ret = filter_var($amount, FILTER_VALIDATE_INT);
        if($ret === false) {
            throw new InvalidInputException();
        }
        $this->amount = $ret;
    }

    public function GetAmount(): int
    {
        return strval($this->amount);
    }

    public function Equals(Money $compared): bool
    {
        return $this->amount === $compared->amount;
    }

    public function GreaterThan(Money $compared): bool
    {
        return $this->amount > $compared->amount;
    }

    public function Add(Money $addendum): Money
    {
        return new Money($this->amount + $addendum->amount);
    }

    public function Sub(Money $subtrahend): Money
    {
        return new Money($this->amount - $subtrahend->amount);
    }

    public function Mul(Money $multiplier): Money
    {
        $ret = $this->amount * $multiplier->amount;
        return new Money($ret);
    }

    public function Div(Money $divisor): Money
    {
        $ret = intdiv($this->amount, $divisor);
        return new Money($ret);
    }

    public function RatePercents(int $percents): Money
    {
        $onePercent = intdiv($this->amount * $percents, 100);
        return new Money($onePercent);
    }
}