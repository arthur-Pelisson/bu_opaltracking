<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Others;

use Egnx\DbSyncWeb\Others\DotNetTypeCode;
//require_once (realpath(dirname(__FILE__).'/../others/typecodes.php'));

//use others\DotNetTypeCode;

class XmlConvert {
	
	const DECIMALS = 6;
	const DECIMAL_SEPARATOR = ".";
	const XML_DATE_TIME_FORMAT = 'Y-m-d\TH:i:s';
	const XML_DATE_TIME_SEPARATOR = "T";
	
	public static function toBoolean($value)
	{
		return (bool) $value;							
	}
	
	public static function toDateTime($value)
	{
		$_value = str_replace(self::XML_DATE_TIME_SEPARATOR, " ",$value);
		$array= date_parse($_value);

		$result = gmmktime($array["hour"],$array["minute"],$array["second"],$array["month"],$array["day"],$array["year"]);

        return $result;
	}
	
	public static function toString(&$value,$typecode)
	{
		$result= null;
		switch ($typecode)
		{
			case DotNetTypeCode::DotNetDouble:
			case DotNetTypeCode::DotNetSingle:
			case DotNetTypeCode::DotNetDecimal:
				$result = number_format($value,self::DECIMALS,self::DECIMAL_SEPARATOR,"");
				break;
			case DotNetTypeCode::DotNetObject:
				$result = base64_encode($value);
				break;
			case DotNetTypeCode::DotNetSByte:
			case DotNetTypeCode::DotNetByte:
			case DotNetTypeCode::DotNetUInt16:
			case DotNetTypeCode::DotNetInt16:
			case DotNetTypeCode::DotNetUInt32:
			case DotNetTypeCode::DotNetInt32:
			case DotNetTypeCode::DotNetUInt64:
			case DotNetTypeCode::DotNetInt64:
				$result = $value; 
				break;
			case DotNetTypeCode::DotNetDateTime:
				$array= date_parse($value);
				$time = gmmktime($array["hour"],$array["minute"],$array["second"],$array["month"],$array["day"],$array["year"]);
				$result = gmdate(self::XML_DATE_TIME_FORMAT,$time);
				break;
			case DotNetTypeCode::DotNetBoolean:
				$result = (string) $value;
			default:
				$result = $value;// mb_convert_encoding ($value,'UTF-8');
				break;
		}
		return $result;
	}
}
