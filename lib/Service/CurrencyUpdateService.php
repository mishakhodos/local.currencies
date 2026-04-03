<?php

namespace Local\Currencies\Service;

use Bitrix\Main\Config\Option;
use Bitrix\Main\SystemException;
use Local\Currencies\Api\ProviderInterface;
use Local\Currencies\Api\CbrProvider;
use Local\Currencies\Entity\CurrencyRateTable;
use Bitrix\Main\Type\DateTime;

class CurrencyUpdateService implements CurrencyUpdateServiceInterface
{
    private ProviderInterface $provider;

    public function __construct(ProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    /**
     * Обновляет курсы валют за сегодня
     *
     * @return int количество сохранённых записей
     * @throws SystemException
     */
    public function updateTodayRates(): int
    {
        $date = new DateTime();
        return $this->updateRatesForDate($date);
    }

    /**
     * Обновляет курсы валют за указанную дату
     *
     * @param DateTime $date
     * @return int
     * @throws SystemException
     */
    public function updateRatesForDate(DateTime $date): int
    {
        $rates = $this->provider->fetchRates($date);
        if (empty($rates)) {
            throw new SystemException('Не удалось получить курсы валют за ' . $date->format('Y-m-d'));
        }

        $savedCount = 0;

        foreach ($rates as $rate) {
            $exists = CurrencyRateTable::getList([
                'filter' => [
                    '=CURRENCY_CODE' => $rate['CODE'],
                    '=RATE_DATE' => $date,
                ],
                'limit' => 1,
            ])->fetch();

            if ($exists) {
                continue;
            }

            CurrencyRateTable::add([
                'CURRENCY_NAME' => $rate['NAME'],
                'CURRENCY_CODE' => $rate['CODE'],
                'RATE' => $rate['RATE'],
                'RATE_DATE' => $date,
            ]);

            $savedCount++;
        }

        return $savedCount;
    }

    /**
     * Получает список валют из настроек модуля
     *
     * @return array
     */
    private function getSelectedCurrencies(): array
    {
        $currenciesStr = Option::get($this->moduleId, 'currencies_list', 'USD,EUR,GBP');
        $currencies = array_map('trim', explode(',', $currenciesStr));
        return array_filter($currencies);
    }

    /**
     * Удаляет курсы старше заданного количества дней
     *
     * @return int количество удалённых записей
     */
    public function cleanOldRates(): int
    {
        $retentionDays = (int)\Bitrix\Main\Config\Option::get('local.currencies', 'retention_days', 30);
        $expireDate = new \Bitrix\Main\Type\DateTime();
        $expireDate->add("-{$retentionDays} days");

        $deleted = 0;

        $rates = CurrencyRateTable::getList([
            'select' => ['ID'],
            'filter' => [
                '<RATE_DATE' => $expireDate,
            ],
        ])->fetchAll();

        foreach ($rates as $rate) {
            CurrencyRateTable::delete($rate['ID']);
            $deleted++;
        }

        return $deleted;
    }
}