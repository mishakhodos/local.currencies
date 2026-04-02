<?php

namespace Local\Currencies\Api;

/**
 * Контракт для провайдера курсов валют
 */
interface ProviderInterface
{
    /**
     * Возвращает курсы валют за указанную дату
     *
     * @param \DateTimeInterface $date
     * @return array массив вида [['CODE' => 'USD', 'NAME' => 'Доллар США', 'RATE' => 91.23], ...]
     */
    public function fetchRates(\DateTimeInterface $date): array;
}