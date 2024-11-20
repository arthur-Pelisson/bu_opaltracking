<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Others;

class JsonSerializer {

	public static function serializetofile($filename,$obj)
	{
		$handle = fopen($filename,"w+");
		try
		{
			fwrite($handle,self::serialize($obj));
			fclose($handle);
		}
		catch(\Exception $ex)
		{
			fclose($handle);
			throw $ex;
		}
	}
	
	public static function serialize($obj)
	{
		return json_encode($obj);
	}
	
	public static function deserializefromfile($filename)
	{
		$raw = readfile($filename);
		return self::deserialize($raw);
	}
	
	public static function deserialize(&$raw)
	{
		$response = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $raw);
		return json_decode($response);
	}
	
}
