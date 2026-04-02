<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Local\Currencies\Entity\CurrencyRateTable;

Loc::loadMessages(__FILE__);

class local_currencies extends CModule
{
	/**
	 * @return string
	 */
	public static function getModuleId()
	{
		return basename(dirname(__DIR__));
	}

	public function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__) . "/version.php");
		$this->MODULE_ID = self::getModuleId();
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = Loc::getMessage("LOCAL_CURRENCIES_MODULE_NAME");
		$this->MODULE_DESCRIPTION = Loc::getMessage("LOCAL_CURRENCIES_MODULE_DESC");

	}

	public function doInstall()
	{
		try
		{
			Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallDB();
            $this->InstallAgent();
		}
		catch (Exception $e)
		{
			global $APPLICATION;
			$APPLICATION->ThrowException($e->getMessage());

			return false;
		}

		return true;
	}

	public function doUninstall()
	{
		try
		{
            $this->UnInstallDB();
            $this->UnInstallAgent();
			Main\ModuleManager::unRegisterModule($this->MODULE_ID);
		}
		catch (Exception $e)
		{
			global $APPLICATION;
			$APPLICATION->ThrowException($e->getMessage());

			return false;
		}

		return true;
	}

    public function InstallDB()
    {
        Main\Loader::includeModule($this->MODULE_ID);
        $connection = Main\Application::getConnection();

        if (!$connection->isTableExists(CurrencyRateTable::getTableName())) {
            CurrencyRateTable::getEntity()->createDbTable();
        }

        return true;
    }

    public function UnInstallDB()
    {
        Main\Loader::includeModule($this->MODULE_ID);

        $connection = Main\Application::getConnection();

        if ($connection->isTableExists(CurrencyRateTable::getTableName())) {
            $connection->dropTable(CurrencyRateTable::getTableName());
        }

        return true;
    }

    public function InstallAgent()
    {
        \CAgent::AddAgent(
            'Local\Currencies\Agent\UpdateRatesAgent::run();',
            'local.currencies',
            'N',
            86400,
            '',
            'Y',
            '',
            100
        );
        return true;
    }

    public function UnInstallAgent()
    {
        \CAgent::RemoveModuleAgents('local.currencies');
        return true;
    }
}