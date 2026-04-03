<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

global $USER;
$module_id = "local.currencies";

// Права доступа
if (!$USER->IsAdmin()) {
    return;
}

Loader::includeModule($module_id);

// Сохранение настроек
if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    COption::SetOptionString($module_id, "currencies_list", $_POST["currencies_list"]);
    COption::SetOptionString($module_id, "update_time", $_POST["update_time"]);
}

// Получение текущих настроек
$currenciesList = COption::GetOptionString($module_id, "currencies_list", "USD,EUR,GBP");
$updateTime = COption::GetOptionString($module_id, "update_time", "03:00");

// Форма для ввода
$tabControl = new CAdminTabControl("tabControl", [
    ["DIV" => "edit1", "TAB" => "Настройки", "TITLE" => "Основные параметры"]
]);

?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($module_id) ?>&lang=<?= LANGUAGE_ID ?>">
    <?= bitrix_sessid_post() ?>
    <?
    $tabControl->Begin();
    $tabControl->BeginNextTab();
    ?>
    <tr>
        <td width="40%">
            <label>Валюты для отслеживания (через запятую):</label>
        </td>
        <td width="60%">
            <input type="text" name="currencies_list" value="<?= htmlspecialcharsbx($currenciesList) ?>" size="50">
        </td>
    </tr>
    <tr>
        <td>
            <label>Время обновления (ЧЧ:ММ):</label>
        </td>
        <td>
            <input type="text" name="update_time" value="<?= htmlspecialcharsbx($updateTime) ?>" size="10">
        </td>
    </tr>
    <?
    $tabControl->Buttons();
    ?>
    <input type="submit" name="save" value="Сохранить">
    <?
    $tabControl->End();
    ?>
</form>