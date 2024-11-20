<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Others;


class DotNetTypeCodeHelper {

    private static $dotnettypesmappings= array(

        DotNetTypeCode::DotNetEmpty=>"NULL",
        DotNetTypeCode::DotNetObject=>"object",
        DotNetTypeCode::DotNetDBNull=>"NULL",
        DotNetTypeCode::DotNetBoolean=>"boolean",
        DotNetTypeCode::DotNetChar=>"string",
        DotNetTypeCode::DotNetSByte=>"integer",
        DotNetTypeCode::DotNetByte=>"integer",
        DotNetTypeCode::DotNetInt16=>"integer",
        DotNetTypeCode::DotNetUInt16=>"integer",
        DotNetTypeCode::DotNetInt32=>"integer",
        DotNetTypeCode::DotNetUInt32=>"integer",
        DotNetTypeCode::DotNetInt64=>"integer",
        DotNetTypeCode::DotNetUInt64=>"integer",
        DotNetTypeCode::DotNetSingle=>"double",
        DotNetTypeCode::DotNetDouble=>"double",
        DotNetTypeCode::DotNetDecimal=>"double",
        DotNetTypeCode::DotNetDateTime=>"string",
        DotNetTypeCode::DotNetString=>"string"
    );

    public static function dotNetTypeCodeToPhpType($dotnettypecode)
    {
        return self::$dotnettypesmappings[$dotnettypecode];
    }
}
