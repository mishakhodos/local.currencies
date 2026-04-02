<?php

return [
    'services' => [
        'value' => [
            // Провайдер курсов валют (по умолчанию — ЦБ РФ)
            'local.currencies.provider' => [
                'className' => \Local\Currencies\Api\CbrProvider::class,
            ],

            // Сервис обновления курсов
            'local.currencies.update.service' => [
                'className' => \Local\Currencies\Service\CurrencyUpdateService::class,
                'constructorParams' => static function () {
                    $provider = \Bitrix\Main\DI\ServiceLocator::getInstance()->get('local.currencies.provider');

                    return [$provider];
                },
            ],
        ],
        'readonly' => true,
    ],
];