<?php

namespace Local\Currencies\Agent;

use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;

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
            $service = ServiceLocator::getInstance()->get('local.currencies.update.service');
            $savedCount = $service->updateTodayRates();
            $deletedCount = $service->cleanOldRates();
            // Логируем результат
            \CEventLog::Add([
                'SEVERITY' => 'INFO',
                'AUDIT_TYPE_ID' => 'LOCAL_CURRENCIES_AGENT',
                'MODULE_ID' => 'local.currencies',
                'DESCRIPTION' => "Обновление: +{$savedCount} записей, удалено старых: {$deletedCount}",
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