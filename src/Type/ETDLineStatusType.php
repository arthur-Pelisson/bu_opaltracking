<?php

namespace App\Type;

class ETDLineStatusType extends EnumType
{
    public const INITIAL = 'INITIAL';
    public const APPROVED = 'APPROVED';
    public const REJECTED = 'REJECTED';
    public const WAITING_FOR_APPROVAL = 'WAITING_FOR_APPROVAL';

    protected $name = 'etdlinestatustype';
    protected $values = [
        self::INITIAL,
        self::APPROVED,
        self::REJECTED,
        self::WAITING_FOR_APPROVAL
    ];

    public static function getValues(): array
    {
        return [
            self::INITIAL,
            self::APPROVED,
            self::REJECTED,
            self::WAITING_FOR_APPROVAL
        ];
    }
}
