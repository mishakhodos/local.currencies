<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;
use Local\Currencies\Entity\CurrencyRateTable;

if (!Loader::includeModule('local.currencies')) {
    ShowError('Модуль local.currencies не установлен');
    return;
}

$currency = $arParams['CURRENCY'] ?? 'USD';
$days = (int)($arParams['DAYS'] ?? 30);

$arResult['DATA'] = [];
$dateFrom = new \Bitrix\Main\Type\Date();
$dateFrom->add("-{$days} days");

$rates = CurrencyRateTable::getList([
    'select' => ['RATE_DATE', 'RATE'],
    'filter' => [
        '=CURRENCY_CODE' => $currency,
        '>=RATE_DATE' => $dateFrom
    ],
    'order' => ['RATE_DATE' => 'ASC']
])->fetchAll();

foreach ($rates as $rate) {
    $arResult['DATA'][] = [
        'date' => $rate['RATE_DATE']->format('Y-m-d'),
        'rate' => $rate['RATE']
    ];
}

$this->includeComponentTemplate();