<?php

namespace Local\Currencies\Service;

use Bitrix\Main\Type\DateTime;

interface CurrencyUpdateServiceInterface
{
    /**
     * Обновляет курсы валют за сегодня
     *
     * @return int количество сохранённых записей
     * @throws \Bitrix\Main\SystemException
     */
    public function updateTodayRates(): int;

    /**
     * Обновляет курсы валют за указанную дату
     *
     * @param \DateTimeInterface $date
     * @return int
     * @throws \Bitrix\Main\SystemException
     */
    public function updateRatesForDate(DateTime $date): int;
}