<?php

namespace Local\Currencies\Api;

use Bitrix\Main\Type\DateTime;

/**
 * Контракт для провайдера курсов валют
 */
interface ProviderInterface
{
    /**
     * Возвращает курсы валют за указанную дату
     *
     * @param DateTime $date
     * @return array массив вида [['CODE' => 'USD', 'NAME' => 'Доллар США', 'RATE' => 91.23], ...]
     */
    public function fetchRates(DateTime $date): array;
}