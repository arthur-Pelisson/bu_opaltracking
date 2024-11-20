<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\PacketLib;

class PacketFieldDefinition {

	public $SourceField;
	public $SourceFieldType;

	public $TargetField;
	public $TargetFieldType;

	public $DefaultValue;

	public $Updatable = false;
	public $PrimaryKey = false;
	public $Length = 0;
	public $NumericPrecision = 0;
	public $NumericScale = 0;

}
