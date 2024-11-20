<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Adapters\SqlAdapterLib;

use Egnx\DbSyncWeb\Others\XmlConvert;
use Egnx\DbSyncWeb\PacketLib\Packet;
use Egnx\DbSyncWeb\PacketLib\PacketFieldDefinition;
use Egnx\DbSyncWeb\PacketLib\PacketFieldValue;
use Egnx\DbSyncWeb\PacketLib\PacketRow;
use Egnx\DbSyncWeb\Others\DotNetTypeCode;
use Egnx\DbSyncWeb\Share\TruncateModeType;


//require_once (realpath(dirname(__FILE__).'/dbfielddefinition.php'));
//require_once (realpath(dirname(__FILE__).'/../../others/typecodes.php'));
//require_once (realpath(dirname(__FILE__).'/../../others/xmlconvert.php'));
//require_once (realpath(dirname(__FILE__).'/../../others/notimplementedexception.php'));
//require_once (realpath(dirname(__FILE__).'/../../packetlib/packet.php'));
//require_once (realpath(dirname(__FILE__).'/../../packetlib/packetfielddefinition.php'));
//require_once (realpath(dirname(__FILE__).'/../../packetlib/packetfieldvalue.php'));
//require_once (realpath(dirname(__FILE__).'/../../packetlib/packetrow.php'));
//require_once (realpath(dirname(__FILE__).'/../../share/constants.php'));

// use share\TruncateModeType;
// use sqladapterlib\dbfielddefinition;
// use others\DotNetTypeCode;
// use others\DotNetTypeCodeHelper;
// use others\xmlconvert;
// use others\notimplementedexception;
// use packetlib\packet;
// use packetlib\packetfielddefinition;
// use packetlib\packetfieldvalue;
// use packetlib\packetrow;

abstract class SqlAdapter {

	const NULL_VALUE = "NULL";
	const MAX_COMMAND_LENGH = 0x80000;
	const MAX_PACKET_SIZE =0x100000;
	const SEPARATOR = ",";
	const DECIMALS = 6;

	private $colums_cache =array();

	protected $CommandSeparator;
	protected $ColumnFormat;
	protected $InsertFormat;
	protected $ValueFormat;
	protected $InsertOrUpdateFormat;

	public $ConnectionString;
	public $MaxCommandLength=self::MAX_COMMAND_LENGH;
	public $MaxPacketSize=0;

	abstract public  function getDeleteCommand($tablename);
	abstract public  function getTruncateCommand($tablename);

	abstract protected  function getInsertOrUpdateFormat();
	abstract protected  function generateSqlCommand($tablename,$fielddefinitions);
	abstract protected function getTableDefinition($tablename);
	abstract public function executeQuery($commandtext);
	abstract protected function sql_escape_string($value);
	abstract public function commit();
	abstract protected function fieldsinfostodbfielddefinitions($fieldsinfos);
	abstract protected function createTemporaryTable($tablename,$packetfielddefinitions);

	protected function init()
	{

		$this->CommandSeparator =$this->getCommandSeparator();
		$this->ColumnFormat =$this->getColumnFormat();
		$this->InsertFormat =$this->getInsertFormat();
		$this->ValueFormat = $this->getValueFormat();
		$this->InsertOrUpdateFormat =$this->getInsertOrUpdateFormat();
	}

	protected function getCommandSeparator()
	{
		return "\r\n";
	}

	protected function getValueFormat()
	{
		return "'%s'";
	}

	protected function  getColumnFormat()
	{
		return "%s";
	}

	protected  function getSelectFormat()
	{
		return "SELECT %s FROM %s";
	}

	protected function getInsertFormat()
	{
		return "INSERT INTO %s (%s) VALUES (%s)";
	}

	protected function dateTimeToString($value)
	{
		return gmdate('Y-m-d H:i:s',$value);
	}

	protected function reset()
	{
		$this->colums_cache = array();
	}

	protected function getColumnAsSql(&$value)
	{
		if (!array_key_exists($value,$this->colums_cache)) {
			$this->colums_cache[$value] = sprintf($this->ColumnFormat,$value);
		}

		return $this->colums_cache[$value];
	}

	protected static function valueToString($value,$dbfielddefinition)
	{

		$result = null;
		if ($value==null) return $result;

		switch ($dbfielddefinition->PropertyTypeCode)
		{
			case DotNetTypeCode::DotNetDouble:
			case DotNetTypeCode::DotNetSingle:
			case DotNetTypeCode::DotNetDecimal:
			case DotNetTypeCode::DotNetSByte:
			case DotNetTypeCode::DotNetByte:
			case DotNetTypeCode::DotNetUInt16:
			case DotNetTypeCode::DotNetInt16:
			case DotNetTypeCode::DotNetUInt32:
			case DotNetTypeCode::DotNetInt32:
			case DotNetTypeCode::DotNetUInt64:
			case DotNetTypeCode::DotNetInt64:
			case DotNetTypeCode::DotNetDateTime:
			case DotNetTypeCode::DoetNetBoolean:
				$result = XmlConvert::toString($value, $dbfielddefinition->PropertyTypeCode);
				break;
			case TypeCode.Object:
				$result =base64_encode($value);
// Todo : Add binary support
// 				if (dbfielddefinition.PropertyType == typeof(byte[]))
// 					result =  Convert.ToBase64String((byte[])value);
//				throw new notimplementedexception();
				break;

			default:
				$result = (string)$value;
				break;
		}
		return $result;
	}

	protected function getValueAsSql(&$value,&$dbfielddefinition)
	{

		if (is_null($value)) return self::NULL_VALUE;

		$result = $value;

		switch ($dbfielddefinition->PropertyTypeCode)
		{
			case DotNetTypeCode::DotNetDouble:
			case DotNetTypeCode::DotNetSingle:
			case DotNetTypeCode::DotNetDecimal:
				$result = number_format($value,self::DECIMALS, '.', '');
				break;
			case DotNetTypeCode::DotNetSByte:
			case DotNetTypeCode::DotNetByte:
			case DotNetTypeCode::DotNetUInt16:
			case DotNetTypeCode::DotNetInt16:
			case DotNetTypeCode::DotNetUInt32:
			case DotNetTypeCode::DotNetInt32:
			case DotNetTypeCode::DotNetUInt64:
			case DotNetTypeCode::DotNetInt64:
				break;

			case DotNetTypeCode::DotNetDateTime:
				$datetimevalue = XmlConvert::toDateTime($value);
				$result =  sprintf($this->ValueFormat,$this->dateTimeToString($datetimevalue));

				break;

			case DotNetTypeCode::DotNetBoolean:
				$boolvalue = XmlConvert::toBoolean($value);
				$result = $boolvalue ? "1" : "0";
				break;
			case DotNetTypeCode::DotNetObject:
				$binvalues = base64_decode($value);
				if (empty($binvalues)){
					$result ="X''";
				}
				else {
					$result ="X'".bin2hex($binvalues)."'";
				}

				break;

			default:
				if ($dbfielddefinition->MaxLength>0)
					if (strlen($value)> $dbfielddefinition->MaxLength)
					{
						$result = substr($value,0, $dbfielddefinition->MaxLength);
					}
					$result = sprintf($this->ValueFormat,
							$this->sql_escape_string($result)
							);
					break;
		}
		return $result;
	}



	protected function intGetSqlSentence(&$tablename,&$columnvaluedico,$updatemode)
	{

		if (!$updatemode) {
		    $columns= array();
		    $values=array();

		    foreach ($columnvaluedico as $vals)
    		{

    			$key =$vals[0];
    			$value =$vals[1];

    			$SqlColumnName =$this->getColumnAsSql($key->ColumnName);
    			$SqlValue = $this->getValueAsSql($value,$key);

    			$columns[]=$SqlColumnName;
    			$values[]=$SqlValue;
    		}

    		return sprintf(
    			$this->InsertFormat,
    			$tablename,
    			implode(self::SEPARATOR, $columns),
    			implode(self::SEPARATOR, $values)

    		);
		}
		else {
		    $columns= array();
		    $values=array();
		    $updateassignments =array();
		    foreach ($columnvaluedico as $vals)
		    {

		        $key =$vals[0];
		        $value =$vals[1];

		        $SqlColumnName =$this->getColumnAsSql($key->ColumnName);
		        $SqlValue = $this->getValueAsSql($value,$key);

    			$columns[]=$SqlColumnName;
    			$values[]=$SqlValue;
		        if ($key->Updatable) {
		            $updateassignments[] = sprintf("%s=%s",$SqlColumnName,$SqlValue);
		        }
		    }
		    return sprintf(
		        $this->InsertOrUpdateFormat,
		        $tablename,
		        implode(self::SEPARATOR, $columns),
		        implode(self::SEPARATOR, $values),
		        implode(self::SEPARATOR, $updateassignments)
		        );
		}
	}

	protected function getRowAsSqlSentence(
			&$tablename,
			&$row,
			&$fieldnametodbfielddefinition,
	        $updatemode
	)
	{
		$values = $row->Values;
		$columnvaluedico = array();

		for ($i=0;$i<count($values);$i++) {
			$value = $values[$i];

			if (array_key_exists($value->FieldName,$fieldnametodbfielddefinition)) {
				$dbfielddefinition = $fieldnametodbfielddefinition[$value->FieldName];
				$columnvaluedico[] =  array($dbfielddefinition,$value->Value);

			}
		}

		return $this->intGetSqlSentence($tablename, $columnvaluedico,$updatemode);
	}

	protected function dbfielddefinitiontopacketfielddefinition($dbfielddefinition)
	{

		$result = new PacketFieldDefinition();

		$result->SourceField = $dbfielddefinition->ColumnName;
		$result->SourceFieldType = $dbfielddefinition->PropertyTypeCode;
		$result->TargetField = $dbfielddefinition->ColumnName;
		$result->TargetFieldType = $dbfielddefinition->PropertyTypeCode;

		return $result;
	}


	protected function dbfielddefinitionstopacketfielddefinitions($dbfielddefinitions)
	{

		$result = array();

		foreach ($dbfielddefinitions as $dbfielddefinition)
		{
			$result[] = $this->dbfielddefinitiontopacketfielddefinition($dbfielddefinition);
		}

		return $result;
	}

	protected function intApplyPacket($packet)
	{
		$tablename = $packet->TargetTable;
		$dbfielddefinitions = $this->getTableDefinition($tablename);

		$dbfielddefinitionsdico= array();
		$fieldnametodbfielddefinition = array();

		for ($i=0;$i<count($dbfielddefinitions);$i++)
		{
			$dbfielddefinition = $dbfielddefinitions[$i];
			$dbfielddefinitionsdico[$dbfielddefinition->ColumnName] = $dbfielddefinition;
		}
		$updatemode =false;
		for ($i=0;$i<count($packet->PacketFieldDefinitions);$i++)
		{
			$fielddefinition = $packet->PacketFieldDefinitions[$i];

			if (array_key_exists($fielddefinition->TargetField,$dbfielddefinitionsdico)) {
                $updatemode = $updatemode || $fielddefinition->Updatable;
                $_dbfielddefinition = $dbfielddefinitionsdico[$fielddefinition->TargetField];
                $_dbfielddefinition->Updatable = $fielddefinition->Updatable;
                $fieldnametodbfielddefinition[$fielddefinition->SourceField] = $_dbfielddefinition;
			}
		}


		$length =0;
		$commands = array();

		if ($packet->PacketNo==0) {
			switch($packet->TruncateMode)
			{
				case TruncateModeType::DeleteCommand:
					$commands[] = $this->getDeleteCommand($tablename);
					break;
				case TruncateModeType::TruncateCommand:
					$commands[] = $this->getTruncateCommand($tablename);
					$this->executeQuery(implode($this->CommandSeparator, $commands));
					$commands = array();
					break;
			}
		}

		foreach ($packet->PacketRows as $row)
			{
				$command = $this->getRowAsSqlSentence($tablename, $row, $fieldnametodbfielddefinition,$updatemode);
				$commands[] = $command;
				$length += strlen($command);

				if ($length>self::MAX_COMMAND_LENGH)
				{
					$this->executeQuery(implode($this->CommandSeparator, $commands));
					$commands = array();
					$length = 0;
	//				$this->commit();
				}

			}

		if (count($commands) > 0)
 		{
			$this->executeQuery(implode($this->CommandSeparator, $commands));
//			$this->commit();
 		}

	}




	public function getPackets(
			$commandtext,
			$tablename,
			$targettable,
			$truncatemode,
			$fielddefinitions,
			$callback
			)
	{
		$packets = array();
		$_fielddefinitions =$fielddefinitions;
		$_commandtext = $commandtext;

		if ($_commandtext==null) {
			$_commandtext=$this->generateSqlCommand($tablename, $fielddefinitions);
		}

		$resultsets = $this->executeQuery($_commandtext);

		if (count($resultsets)==0) return $packets;

		$finfos = $resultsets[0][0];
		$rows = $resultsets[0][1];

		$dbfielddefinitions = $this->fieldsinfostodbfielddefinitions($finfos);

		if ($_fielddefinitions==null) {
			$_fielddefinitions = $this->dbfielddefinitionstopacketfielddefinitions($dbfielddefinitions);
		}

		$dbfielddefinitionsdico = array();

		foreach ($dbfielddefinitions as $dbfielddefinition)
		{
			$dbfielddefinitionsdico[$dbfielddefinition->ColumnName] = $dbfielddefinition;
		}

		$mappings = array();

		foreach ($_fielddefinitions as $fielddefinition)
		{
			$mappings[]= $dbfielddefinitionsdico[$fielddefinition->SourceField];
		}


		$packet = null;
		$packetrows = array();
		$packetno = 0;
		$length = 0;
		$packetgroupid = uniqid();
		$packetrows =0;

		for ($i=0;$i<count($rows);$i++)
		{
			$row = $rows[$i];
			if ($packet==null){

				$packet = new Packet();
				$packet->PacketFieldDefinitions = $_fielddefinitions;
				$packet->PacketGroupId =$packetgroupid;
				$packet->PacketRows = array();
				$packet->PacketNo =$packetno;
				$packet->LastPacket=true;
				$packet->SourceTable=$tablename;
				$packet->TargetTable=$targettable;
				$packet->TruncateMode = $truncatemode;

				if ($callback==null){
					$packets[] =  $packet;
				}

			}

			$packetrow = new PacketRow();
			$packetrow->Values = array();

			foreach ($mappings as $mapping)
			{
				$value = $row[$mapping->Ordinal];
				if ($value!=null) {
					$packetfieldvalue = new PacketFieldValue();
					$packetfieldvalue->FieldName = $mapping->ColumnName;
					$packetfieldvalue->Value = XmlConvert::toString($value, $mapping->PropertyTypeCode);
					$length=$length+strlen($packetfieldvalue->FieldName)+strlen($packetfieldvalue->Value);
					$packetrow->Values[] = $packetfieldvalue;
				}
			}
			$packet->PacketRows[] =$packetrow;
			$packetrows++;
			if ($length>self::MAX_PACKET_SIZE)
			{
				if ($i==(count($rows)-1)) {
					$packet->LastPacket = true;
				}

				if ($callback!=null){
					call_user_func($callback,$packet);
				}

				$length =0;
				$packetrows=0;
				$packetno++;
				$packet=null;
			}


		}

		if ($packetrows>0) {
			$packet->LastPacket=true;
			if ($callback!=null){
				call_user_func($callback,$packet);
			}
		}



		return $packets;
// 		foreach (var row in datatable.Rows.OfType<DataRow>())
// 		{
// 			if (packet == null)
// 			{
// 				packet = new Packet() { PacketFieldDefinitions = fielddefinitions };
// 				packet.PacketGroupId = packetgroupid;
// 				packets.Add(packet);
// 			}

// 			var packetrow = new PacketRow()
// 			{
// 				Values = (from i in dbfielddefinitions
// 				where row[i.Ordinal]!=DBNull.Value
// 				select new PacketFieldValue()
// 				{
// 					FieldName = i.ColumnName,
// 					Value = ValueToString(row[i.Ordinal], i)
// 				}).ToArray()
// 			};

// 			packetrows.Add(packetrow);
// 			length += packetrow.GetLength();

// 			if (length > MaxPacketSize && MaxPacketSize>0)
// 			{
// 				packet.PacketRows = packetrows.ToArray();
// 				packetrows.Clear();
// 				packet = null;
// 				length = 0;
// 				packetno++;
// 			}

// 		}

// 		if (packetrows.Count > 0)
// 		{
// 			packet.PacketNo = packetno;
// 			packet.PacketRows = packetrows.ToArray();
// 		}

// 		packet = packets.LastOrDefault();
// 		if (packet != null) packet.LastPacket = true;


	}

	public function applyPackets($packets)
	{
		$this->reset();

		foreach($packets as $packet)
		{
			if ($packet->TemporaryTable) {
				$this->createTemporaryTable($packet->TargetTable,$packet->PacketFieldDefinitions);
			}
			$this->intApplyPacket($packet);
		}

		$this->commit();
	}

}
