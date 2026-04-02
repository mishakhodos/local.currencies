<?php

namespace Local\Currencies\Agent;

use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;
use Local\Currencies\Api\CbrProvider;
use Local\Currencies\Entity\CurrencyRateTable;
use Local\Currencies\Service\CurrencyUpdateService;

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
            $service = new CurrencyUpdateService();
            $savedCount = $service->updateTodayRates();

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