<?php

namespace Shantilab\YandexDirect;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Jwt\Jwt;
use Jwt\Algorithm\HS256Algorithm;

class UserTable extends Entity\Datamanager
{
    const SECRET_KEY = 'some-secret-key-for-application';

    public static function getTableName()
    {
        return 'shantilab_yandexdirect_users';
    }

    public static function getConnectionName()
    {
        return 'default';
    }

    public static function getMap()
    {
        return [
            
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),

            new Entity\DatetimeField('CREATE_DATE', [
                'required' => true,
                'default_value' => new Type\DateTime
            ]),

            new Entity\DatetimeField('UPDATE_DATE', [
                'required' => true,
                'default_value' => new Type\DateTime
            ]),

            new Entity\IntegerField('USER_ID', [
                'required' => true
            ]),

            new Entity\StringField('LOGIN', [
                'required' => true
            ]),

            new Entity\StringField('ACCESS_TOKEN', [
                'required' => true,
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            $secretKey = self::SECRET_KEY;
                            $value = Jwt::encode($value, $alg = new HS256Algorithm($secretKey));
                            return $value;
                        }
                    ];
                },
                'fetch_data_modification' => function () {
                    return [
                        function ($value) {
                            $secretKey = self::SECRET_KEY;
                            $decoded = Jwt::decode($value, ['algorithm' =>  new HS256Algorithm($secretKey)]);
                            return $decoded['data'];
                        }
                    ];
                }
            ]),

            new Entity\IntegerField('TOKEN_EXPIRES_IN', [
                'required' => true
            ]),

            new Entity\StringField('TOKEN_TYPE'),

            new Entity\DatetimeField('TOKEN_FINAL_DATE', [
                'required' => true,
            ]),
        ];
    }

    public static function onBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $data = $event->getParameter('fields');

        if (isset($data['TOKEN_EXPIRES_IN'])){
            $dateTime = new Type\DateTime();
            $dateTime->add('+ ' . $data['TOKEN_EXPIRES_IN']. ' sec');
            //$result->modifyFields(array('TOKEN_FINAL_DATE' => $dateTime));
        }

        $result->modifyFields(['UPDATE_DATE' => new Type\DateTime]);

        return $result;
    }

    public static function onBeforeAdd(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $data = $event->getParameter('fields');

        if (isset($data['TOKEN_EXPIRES_IN'])){
            $dateTime = new Type\DateTime('+ ' . $data['TOKEN_EXPIRES_IN']. ' sec');
            //$dateTime->add('+ ' . $data['TOKEN_EXPIRES_IN']. ' sec');
            $result->modifyFields(['TOKEN_FINAL_DATE' => $dateTime]);
        }

        return $result;
    }

}