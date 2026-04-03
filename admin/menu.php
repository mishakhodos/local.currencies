<?php
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loader::includeModule('local.currencies');
Loc::loadMessages(__FILE__);

global $USER;

if ($USER->IsAdmin()) {
    $aMenu = [
        'parent_menu' => 'global_menu_services',
        'sort' => 100,
        'url' => 'local_currencies_chart.php',
        'text' => Loc::getMessage('LOCAL_CURRENCIES_MENU_ITEM_TEXT'),
        'title' => Loc::getMessage('LOCAL_CURRENCIES_MENU_ITEM_TITLE'),
        'icon' => 'form_menu_icon',
        'page_icon' => 'form_page_icon',
        'module_id' => 'local.currencies',
        'items_id' => 'local_currencies',
    ];

    return $aMenu;
}

return false;