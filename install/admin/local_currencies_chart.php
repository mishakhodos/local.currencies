<?php
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");

$APPLICATION->SetTitle("Динамика курсов валют");

$currency = $_GET['currency'] ?? 'USD';
$days = (int)($_GET['days'] ?? 30);
?>

<h2>Динамика курсов валют</h2>

<form method="get">
    <label>Валюта: <input type="text" name="currency" value="<?= htmlspecialcharsbx($currency) ?>"></label>
    <label>Дней: <input type="number" name="days" value="<?= $days ?>"></label>
    <input type="submit" value="Показать">
</form>

<?
$APPLICATION->IncludeComponent(
        "local.currencies:rates.chart",
        ".default",
        [
                "CURRENCY" => $currency,
                "DAYS" => $days,
        ]
);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");