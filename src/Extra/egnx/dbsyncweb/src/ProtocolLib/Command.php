<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\ProtocolLib;

//require_once (realpath(dirname(__FILE__).'/../packetlib/packetfielddefinition.php'));
//require_once (realpath(dirname(__FILE__).'/../share/constants.php'));


// use share\TruncateModeType;
use Egnx\DbSyncWeb\ProtocolLib\OriginType;
use Egnx\DbSyncWeb\Share\TruncateModeType;


class Command {
	
	public $Origin=OriginType::Client;
	
	public $SourceTable;
	public $TargetTable;
	
	public $TruncateMode=TruncateModeType::None;
	
	public $PacketFieldDefinitions;
	public $CommandText;
	
}
