<?php

namespace App\Type;

class UserType extends EnumType
{
    public const VENDOR = 'VENDOR';
    public const PURCHASER = 'PURCHASER';

    protected $name = 'usertype';
    protected $values = [
        self::VENDOR,
        self::PURCHASER
    ];

    public static function getValues(): array
    {
        return [
            self::VENDOR,
            self::PURCHASER
        ];
    }
}
