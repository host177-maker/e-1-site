<?php

namespace Absteam\Orm;

use Bitrix\Main\Entity\DataManager;
use Bitrix\Main\Entity\IntegerField;
use Bitrix\Main\Entity\StringField;

/**
 * b_form_result_answer
 */
class FormResultAnswerTable extends DataManager
{

    /**
     * @return string
     */
    public static function getFilePath(): string
    {
        return __FILE__;
    }

    /**
     * @return string
     */
    public static function getTableName(): string
    {
        return 'b_form_result_answer';
    }

    /**
     * @return string
     */
    public static function getConnectionName(): string
    {
        return 'default';
    }

    /**
     * @return array
     */
    public static function getMap(): array
    {
        return [
            new IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true,
            ]),
            new IntegerField('RESULT_ID'),
            new IntegerField('FORM_ID'),
            new IntegerField('FIELD_ID'),
            new IntegerField('USER_FILE_ID'),
            new StringField('USER_FILE_NAME'),
        ];
    }



}