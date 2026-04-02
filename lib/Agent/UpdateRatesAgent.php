<?php

namespace Local\Currencies\Agent;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Local\Currencies\Api\CbrProvider;
use Local\Currencies\Entity\CurrencyRateTable;

class UpdateRatesAgent
{
    /**
     * Основной метод агента
     *
     * @return string
     */
    public static function run(): string
    {
        if (!Loader::includeModule('local.currencies')) {
            return self::class.'::run();';
        }

        try {
            $provider = new CbrProvider();
            $rates = $provider->fetchRates(new DateTime());

            if (empty($rates)) {
                throw new SystemException('Не удалось получить курсы валют с API ЦБ РФ');
            }

            $date = new DateTime();
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
                    continue; // уже есть курс на эту дату
                }

                CurrencyRateTable::add([
                    'CURRENCY_NAME' => $rate['NAME'],
                    'CURRENCY_CODE' => $rate['CODE'],
                    'RATE' => $rate['RATE'],
                    'RATE_DATE' => $date,
                ]);

                $savedCount++;
            }

            // Логируем результат
            \CEventLog::Add([
                'SEVERITY' => 'INFO',
                'AUDIT_TYPE_ID' => 'LOCAL_CURRENCIES_AGENT',
                'MODULE_ID' => 'local.currencies',
                'DESCRIPTION' => "Обновление курсов: сохранено {$savedCount} записей",
            ]);
        } catch (\Exception $e) {
            \CEventLog::Add([
                'SEVERITY' => 'ERROR',
                'AUDIT_TYPE_ID' => 'LOCAL_CURRENCIES_AGENT_ERROR',
                'MODULE_ID' => 'local.currencies',
                'DESCRIPTION' => $e->getMessage(),
            ]);
        }

        return self::class.'::run();';
    }
}