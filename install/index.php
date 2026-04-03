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
            $this->InstallComponents();
            $this->InstallFiles();
            $this->InstallTestData();
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
            $this->UnInstallComponents();
            $this->UnInstallFiles();
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

    // внутри класса local_currencies

    public function InstallComponents()
    {
        $sourcePath = __DIR__ . '/components/local.currencies';
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/local.currencies';

        if (!is_dir($sourcePath)) {
            return false;
        }

        // Копируем папку рекурсивно
        \CopyDirFiles($sourcePath, $targetPath, true, true);

        return true;
    }

    public function UnInstallComponents()
    {
        $targetPath = $_SERVER['DOCUMENT_ROOT'] . '/local/components/local.currencies';
        if (is_dir($targetPath)) {
            \DeleteDirFilesEx('/local/components/local.currencies');
        }
        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin",
            true,
            true
        );
        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(
            __DIR__ . "/admin",
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin"
        );
        return true;
    }

    public function InstallTestData()
    {
        Main\Loader::includeModule($this->MODULE_ID);

        $provider = new \Local\Currencies\Api\CbrProvider();
        $date = new \Bitrix\Main\Type\DateTime();
        $dateFrom = clone $date;
        $dateFrom->add("-2 days");

        $currentDate = clone $dateFrom;
        $savedCount = 0;

        while ($currentDate <= $date) {
            try {
                $rates = $provider->fetchRates($currentDate);

                foreach ($rates as $rate) {
                    \Local\Currencies\Entity\CurrencyRateTable::add([
                        'CURRENCY_NAME' => $rate['NAME'],
                        'CURRENCY_CODE' => $rate['CODE'],
                        'RATE' => $rate['RATE'],
                        'RATE_DATE' => $currentDate,
                    ]);
                    $savedCount++;
                }
            } catch (\Exception $e) {
                \CEventLog::Add([
                    'SEVERITY' => 'WARNING',
                    'AUDIT_TYPE_ID' => 'LOCAL_CURRENCIES_INSTALL',
                    'MODULE_ID' => $this->MODULE_ID,
                    'DESCRIPTION' => Loc::getMessage('LOCAL_CURRENCIES_INSTALL_RATES_ERROR', ['#DATE#' => $currentDate->format('Y-m-d'), '#ERROR_MESSAGE#' => $e->getMessage()]),
                ]);
            }

            $currentDate->add("+1 day");
        }

        if ($savedCount > 0) {
            \CEventLog::Add([
                'SEVERITY' => 'INFO',
                'AUDIT_TYPE_ID' => 'LOCAL_CURRENCIES_INSTALL',
                'MODULE_ID' => $this->MODULE_ID,
                'DESCRIPTION' => Loc::getMessage('LOCAL_CURRENCIES_INSTALL_RATES_TEXT', ['#RATE_COUNT#' => $savedCount,'#DAYS_COUNT#' => 2]),
            ]);
        }
    }
}