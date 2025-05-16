<?php

namespace App\Service;

use App\Utils\ExchangeRateProvider;
use DateTime;
use DateInterval;

class ComissionCalculator
{
    private const FREE_WEEKLY_AMOUNT_EUR = 1000.00;
    private const FREE_OPERATIONS_PER_WEEK = 3;
    private const PERCENT_WITHDRAW_PRIVATE = 0.003; // 0.3%
    private const PERCENT_WITHDRAW_BUSINESS = 0.005; // 0.5%
    private const PERCENT_DEPOSIT = 0.0003; // 0.03%

    /** @var ExchangeRateProvider */
    private $rates;

    public function __construct(ExchangeRateProvider $rates)
    {
        $this->rates = $rates;
    }

    public function calculate(
        string $userType,
        string $operationType,
        float  $amount,
        string $currency,
        array  $history = []
    ): float
    {
        if ($operationType === 'deposit') {
            return $this->roundUp($amount * self::PERCENT_DEPOSIT, $currency);
        }

        if ($operationType === 'withdraw') {
            if ($userType === 'business') {
                return $this->roundUp($amount * self::PERCENT_WITHDRAW_BUSINESS, $currency);
            }

            return $this->calculatePrivate($amount, $currency, $history);
        }

        throw new \InvalidArgumentException('Invalid operation type');
    }

    private function calculatePrivate(float $amount, string $currency, array $history): float
    {
        $today = new DateTime(); // presupunem că history include și operația curentă
        $weekStart = (clone $today)->modify('monday this week');
        $weekEnd = (clone $weekStart)->add(new DateInterval('P6D'));

        $withdrawsThisWeek = array_filter($history, function ($op) use ($weekStart, $weekEnd) {
            if ($op['type'] !== 'withdraw') {
                return false;
            }
            $d = new DateTime($op['date']);
            return $d >= $weekStart && $d <= $weekEnd;
        });

        $countThisWeek = count($withdrawsThisWeek);

        $usedFreeEur = 0.0;
        foreach ($withdrawsThisWeek as $op) {
            $amtEur = $op['currency'] !== 'EUR'
                ? $op['amount'] / $this->rates->getRate('EUR', $op['currency'])
                : $op['amount'];
            $usedFreeEur += $amtEur;
        }

        $amountEur = $currency !== 'EUR'
            ? $amount / $this->rates->getRate('EUR', $currency)
            : $amount;

        if ($countThisWeek >= self::FREE_OPERATIONS_PER_WEEK) {
            return $this->roundUp($amount * self::PERCENT_WITHDRAW_PRIVATE, $currency);
        }

        $remainingFree = max(self::FREE_WEEKLY_AMOUNT_EUR - $usedFreeEur, 0.0);

        if ($amountEur <= $remainingFree) {
            return 0.0;
        }

        $chargeableEur = $amountEur - $remainingFree;
        $chargeableInCurrency = $currency !== 'EUR'
            ? $chargeableEur * $this->rates->getRate('EUR', $currency)
            : $chargeableEur;

        return $this->roundUp($chargeableInCurrency * self::PERCENT_WITHDRAW_PRIVATE, $currency);
    }

    private function roundUp(float $fee, string $currency): float
    {
        switch ($currency) {
            case 'JPY':
                $precision = 0;
                break;
            default:
                $precision = 2;
        }

        $factor = pow(10, $precision);
        $rounded = ceil($fee * $factor) / $factor;

        return $rounded;
    }
}
