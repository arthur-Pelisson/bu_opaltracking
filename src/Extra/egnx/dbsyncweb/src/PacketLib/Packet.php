<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\PacketLib;

//require_once (realpath(dirname(__FILE__).'/packetfielddefinition.php'));
//require_once (realpath(dirname(__FILE__).'/packetrow.php'));
//require_once (realpath(dirname(__FILE__).'/packetfieldvalue.php'));
//require_once (realpath(dirname(__FILE__).'/../share/constants.php'));

use Egnx\DbSyncWeb\Share\TruncateModeType;
// use share\TruncateModeType;
// use packetlib\packetfielddefinition;
// use packetlib\packetrow;
// use packetlib\packetfieldvalue;

class Packet {
	
	public $SourceTable;
	public $TargetTable;
	
	public $TruncateMode=TruncateModeType::None;
	
	public $PacketNo=0;
	public $PacketGroupId;
	public $LastPacket=false;
	public $TemporaryTable=false;

	public $PacketFieldDefinitions;
	public $PacketRows;
	
}
