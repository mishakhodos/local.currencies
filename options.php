<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;

Loc::loadMessages(__FILE__);

$module_id = "local.currencies";

if (!$USER->IsAdmin()) {
    return;
}

Loader::includeModule($module_id);

if ($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid()) {
    $days = (int)$_POST["retention_days"];
    if ($days < 1) $days = 30;
    COption::SetOptionString($module_id, "retention_days", $days);
}

$retentionDays = (int)COption::GetOptionString($module_id, "retention_days", 30);

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
            <label>Срок хранения курсов (дней):</label>
        </td>
        <td width="60%">
            <input type="number" name="retention_days" value="<?= $retentionDays ?>" min="1" max="365">
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