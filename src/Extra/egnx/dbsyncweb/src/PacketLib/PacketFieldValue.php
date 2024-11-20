<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\PacketLib;

class PacketFieldValue {
	
	public $FieldName;
	public $Value;
	
	public function getLength()
	{
		return strlen($this->FieldName)+strlen($this->Value);
	}
}
