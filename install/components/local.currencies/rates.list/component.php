<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Local\Currencies\Entity\CurrencyRateTable;

if (!Loader::includeModule('local.currencies')) {
    ShowError('Модуль local.currencies не установлен');

    return;
}

$limit = (int)($arParams['LIMIT'] ?? 10);
if ($limit < 1) {
    $limit = 10;
}

$cacheId = 'local_currencies_rates_list_'.$limit;
$cache = \Bitrix\Main\Data\Cache::createInstance();

if ($cache->initCache($arParams['CACHE_TIME'], $cacheId, '/local.currencies/rates')) {
    $arResult = $cache->getVars();
} else {
    $rates = CurrencyRateTable::getList([
        'select' => ['ID', 'CURRENCY_NAME', 'CURRENCY_CODE', 'RATE', 'RATE_DATE'],
        'order' => ['RATE_DATE' => 'DESC', 'ID' => 'DESC'],
        'limit' => $limit,
    ])->fetchAll();

    $arResult = [
        'ITEMS' => $rates,
        'CACHE_ID' => $cacheId,
    ];

    $cache->startDataCache();
    $cache->endDataCache($arResult);
}

$this->includeComponentTemplate();