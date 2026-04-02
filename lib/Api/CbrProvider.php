<?php

namespace Local\Currencies\Api;

use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

/**
 * Провайдер для получения курсов валют с сайта Центрального Банка РФ
 *
 * @link https://cbr.ru/development/SXML/
 */
class CbrProvider implements ProviderInterface
{
    private const API_URL = 'https://cbr.ru/scripts/XML_daily.asp';

    public function fetchRates(DateTime $date): array
    {
        $httpClient = new HttpClient([
            'socketTimeout' => 10,
            'streamTimeout' => 10,
        ]);

        $url = self::API_URL . '?date_req=' . $date->format('d/m/Y');
        $response = $httpClient->get($url);

        if (!$response || $httpClient->getStatus() !== 200) {
            throw new SystemException('Ошибка при запросе к API ЦБ РФ. Код статуса: ' . $httpClient->getStatus());
        }

        // Парсим XML через DOMDocument
        $dom = new \DOMDocument();
        if (!$dom->loadXML($response)) {
            throw new SystemException('Ошибка парсинга XML-ответа от ЦБ РФ');
        }

        $valutes = $dom->getElementsByTagName('Valute');
        $result = [];

        foreach ($valutes as $valute) {
            $charCode = $this->getNodeValue($valute, 'CharCode');
            $name = $this->getNodeValue($valute, 'Name');
            $value = $this->getNodeValue($valute, 'Value');

            if (!$charCode || !$value) {
                continue;
            }

            $rate = (float) str_replace(',', '.', $value);

            $result[] = [
                'CODE' => $charCode,
                'NAME' => $name,
                'RATE' => $rate,
            ];
        }

        return $result;
    }

    private function getNodeValue(\DOMElement $parent, string $tagName): string
    {
        $nodes = $parent->getElementsByTagName($tagName);
        return $nodes->length > 0 ? trim($nodes->item(0)->textContent) : '';
    }
}