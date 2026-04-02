<?php

namespace Local\Currencies\Entity;

use Bitrix\Main\ORM\Data\DataManager;
use Bitrix\Main\ORM\Fields\DateField;
use Bitrix\Main\ORM\Fields\DatetimeField;
use Bitrix\Main\ORM\Fields\FloatField;
use Bitrix\Main\ORM\Fields\IntegerField;
use Bitrix\Main\ORM\Fields\StringField;
use Bitrix\Main\ORM\Fields\Validators\LengthValidator;

class CurrencyRateTable extends DataManager
{
    public static function getTableName()
    {
        return 'loc_curr_currency_rate';
    }

    public static function getMap()
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),
            new StringField('CURRENCY_NAME', [
                'required' => true,
                'validation' => function () {
                    return [new LengthValidator(2, 64)];
                }
            ]),
            new StringField('CURRENCY_CODE', [
                'required' => true,
                'validation' => function () {
                    return [new LengthValidator(3, 3)];
                }
            ]),
            new FloatField('RATE', [
                'required' => true
            ]),
            new DateField('RATE_DATE', [
                'required' => true
            ]),
            new DatetimeField('CREATED_AT', [
                'default_value' => function () {
                    return new \Bitrix\Main\Type\DateTime();
                }
            ])
        ];
    }
}