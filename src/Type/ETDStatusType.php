<?php

namespace App\Type;

class ETDStatusType extends EnumType
{
    public const WAITING_PURCHASER = 'WAITING_PURCHASER';
    public const WAITING_VENDOR = 'WAITING_VENDOR';
    public const CLOSED = 'CLOSED';

    protected $name = 'etdstatustype';
    protected $values = [
        self::WAITING_PURCHASER,
        self::WAITING_VENDOR,
        self::CLOSED
    ];

    public static function getValues(): array
    {
        return [
            self::WAITING_PURCHASER,
            self::WAITING_VENDOR,
            self::CLOSED
        ];
    }
}
