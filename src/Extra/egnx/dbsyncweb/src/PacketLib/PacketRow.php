<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\PacketLib;

//require_once (realpath(dirname(__FILE__).'/packetfieldvalue.php'));

//use packetlib\packetfieldvalue;

class PacketRow {
		
	public $Values=null;
	
	public function getLength()
	{
		$result = 0;
		
		if ($this->Values!=null) 
		{
			foreach ($this->Values as $fieldvalue) 
			{
				$result += $fieldvalue->getLength();
			}
		}
		
		return $result;
	}
	
}
