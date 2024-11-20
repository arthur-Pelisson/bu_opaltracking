<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Adapters\MysqlAdapterLib;

//require_once (realpath(dirname(__FILE__).'/../sqladapterlib/sqladapter.php'));
//require_once (realpath(dirname(__FILE__).'/../sqladapterlib/dbfielddefinition.php'));
//require_once (realpath(dirname(__FILE__).'/../../others/typecodes.php'));

use Egnx\DbSyncWeb\Adapters\SqlAdapterLib\DbFieldDefinition;
use Egnx\DbSyncWeb\Adapters\SqlAdapterLib\SqlAdapter;
use Egnx\DbSyncWeb\Others\DotNetTypeCode;
use Egnx\DbSyncWeb\Others\DotNetTypeCodeHelper;
//use Egnx\DbSyncWeb\ProtocolLib\OriginType;

//if (strnatcmp(phpversion(),'5.3') >= 0)
//{
//	use sqladapterlib\dbfielddefinition;
//	use sqladapterlib\sqladapter;
//	use others\DotNetTypeCode;
//	use others\DotNetTypeCodeHelper;
//}

class MysqlAdapter extends SqlAdapter {

	const MYSQL_DESCRIBE_COMMAND = "DESCRIBE `%s`";
//	const MYSQL_CREATE_TABLE_COMMAND = "CREATE TEMPORARY TABLE IF NOT EXISTS `%s` (%s) ENGINE=MEMORY;";
	const MYSQL_CREATE_TABLE_COMMAND = "CREATE TEMPORARY TABLE IF NOT EXISTS `%s` (%s);";
//	const MYSQL_CREATE_TABLE_COMMAND = "CREATE TABLE IF NOT EXISTS `%s` (%s);";
	private static $mysqltype2dotnetypecodemappings = array(
		"float"=>DotNetTypeCode::DotNetSingle,
		"double"=>DotNetTypeCode::DotNetDouble,
		"decimal"=>DotNetTypeCode::DotNetDecimal,
		"tinyint"=>DotNetTypeCode::DotNetSByte,
		"smallint"=>DotNetTypeCode::DotNetInt16,
		"mediumint"=>DotNetTypeCode::DotNetInt32,
		"int"=>DotNetTypeCode::DotNetInt32,
		"bigint"=>DotNetTypeCode::DotNetInt64,
		"bit"=>DotNetTypeCode::DotNetUInt64,
		"boolean"=>DotNetTypeCode::DotNetBoolean,
		"char"=>DotNetTypeCode::DotNetChar,
		"varchar"=>DotNetTypeCode::DotNetString,
		"tinytext"=>DotNetTypeCode::DotNetString,
		"text"=>DotNetTypeCode::DotNetString,
		"mediumtext"=>DotNetTypeCode::DotNetString,
		"longtext"=>DotNetTypeCode::DotNetString,
		"varbinary"=>DotNetTypeCode::DotNetObject,
		"binary"=>DotNetTypeCode::DotNetObject,
		"tinyblob"=>DotNetTypeCode::DotNetObject,
		"blob"=>DotNetTypeCode::DotNetObject,
		"mediumblob"=>DotNetTypeCode::DotNetObject,
		"longblob"=>DotNetTypeCode::DotNetObject,
		"enum"=>DotNetTypeCode::DotNetString,
		"set"=>DotNetTypeCode::DotNetString,
		"datetime"=>DotNetTypeCode::DotNetDateTime,
		"date"=>DotNetTypeCode::DotNetDateTime,
		"time"=>DotNetTypeCode::DotNetString,
		"year"=>DotNetTypeCode::DotNetInt32,
		"timestamp"=>DotNetTypeCode::DotNetDateTime,
		"point"=>DotNetTypeCode::DotNetString,
		"linestring"=>DotNetTypeCode::DotNetString,
		"polygon"=>DotNetTypeCode::DotNetString,
		"geometry"=>DotNetTypeCode::DotNetString,
		"multipoint"=>DotNetTypeCode::DotNetString,
		"multilinestring"=>DotNetTypeCode::DotNetString,
		"multipolygon"=>DotNetTypeCode::DotNetString,
		"geometrycollection"=>DotNetTypeCode::DotNetString
	);

	private static $mysqltype2stringmappings=array(
			MYSQLI_TYPE_DECIMAL=>"decimal",
			MYSQLI_TYPE_TINY=>"tinyint",
			MYSQLI_TYPE_SHORT=>"smallint",
			MYSQLI_TYPE_LONG=>"int",
			MYSQLI_TYPE_FLOAT=>"float",
			MYSQLI_TYPE_DOUBLE=>"double",
			MYSQLI_TYPE_NULL=>"DEFAULT NULL",
			MYSQLI_TYPE_TIMESTAMP=>"timestamp",
			MYSQLI_TYPE_LONGLONG=>"bigint",
			MYSQLI_TYPE_INT24=>"mediumint",
			MYSQLI_TYPE_DATE=>"date",
			MYSQLI_TYPE_TIME=>"time",
			MYSQLI_TYPE_DATETIME=>"datetime",
			MYSQLI_TYPE_YEAR=>"year",
			MYSQLI_TYPE_NEWDATE=>"date",
			MYSQLI_TYPE_ENUM=>"enum",
			MYSQLI_TYPE_SET=>"set",
			MYSQLI_TYPE_TINY_BLOB=>"tinyblob",
			MYSQLI_TYPE_MEDIUM_BLOB=>"mediumblob",
			MYSQLI_TYPE_LONG_BLOB=>"longblob",
			MYSQLI_TYPE_BLOB=>"blob",
			MYSQLI_TYPE_VAR_STRING=>"varchar",
			MYSQLI_TYPE_STRING=>"varchar",
			MYSQLI_TYPE_CHAR=>"char",
			MYSQLI_TYPE_INTERVAL=>"interval",
			MYSQLI_TYPE_GEOMETRY=>"geometry",
			MYSQLI_TYPE_NEWDECIMAL=>"decimal",
			MYSQLI_TYPE_BIT=>"bit",
		);

	private static $dotnetype2mysqltypemappings=array(
			DotNetTypeCode::DotNetSingle=>"float",
			DotNetTypeCode::DotNetDouble=>"double",
			DotNetTypeCode::DotNetDecimal=>"decimal",
			DotNetTypeCode::DotNetSByte=>"tinyint",
			DotNetTypeCode::DotNetByte=>"tinyint",
			DotNetTypeCode::DotNetInt16=>"smallint",
			DotNetTypeCode::DotNetUInt16=>"smallint",
			DotNetTypeCode::DotNetInt32=>"int",
			DotNetTypeCode::DotNetUInt32=>"int",
			DotNetTypeCode::DotNetInt64=>"bigint",
			DotNetTypeCode::DotNetUInt64=>"bigint",
			DotNetTypeCode::DotNetBoolean=>"boolean",
			DotNetTypeCode::DotNetChar=>"char",
			DotNetTypeCode::DotNetString=>"varchar",
			//DotNetTypeCode::DotNetString=>"longtext",
			DotNetTypeCode::DotNetObject=>"longblob",
			DotNetTypeCode::DotNetDateTime=>"datetime",
	);

	private $dbconnection;

	public function __construct() {

		$this->init();

	}


	public function getDbconnection() {
		return $this->dbconnection;
	}

	public function setDbconnection($dbconnection) {
		$this->dbconnection = $dbconnection;
	}

	protected function sql_escape_string($value)
	{
		return $this->dbconnection->real_escape_string($value);
	}

	public  function getDeleteCommand($tablename)
	{
		return sprintf("DELETE FROM `%s`",$tablename);
	}

	public  function getTruncateCommand($tablename)
	{
		return sprintf("TRUNCATE TABLE `%s`",$tablename);
	}

	protected  function generateSqlCommand($tablename,$fielddefinitions)
	{
		$columns ="*";
		if 	($fielddefinitions!=null) {
			$array=array();
			foreach ($fielddefinitions as $fielddefinition)
			{
				$array[] = $this->getColumnAsSql($fielddefinition->SourceField);
			}
			$columns = implode(SqlAdapter::SEPARATOR, $array);
		}
		return sprintf($this->getSelectFormat(),$columns,$tablename);
	}

	protected function getCommandSeparator()
	{
		return ";";
		//return ";\r\n";
	}

	protected  function getSelectFormat()
	{
		return "SELECT %s FROM `%s`";
	}


	protected function  getColumnFormat()
	{
		return "`%s`";
	}

	protected function getInsertFormat()
	{
		return "INSERT INTO `%s` (%s) VALUES (%s)";
	}

	protected function getInsertOrUpdateFormat()
	{
	    return "INSERT INTO `%s` (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s";
	}

	protected function createTemporaryTable($tablename,$packetfielddefinitions)
	{
		$primarykeyfields = array();
		$fields = array();
		foreach ($packetfielddefinitions as $packetfielddefinition )
		{
			$fieldname = sprintf($this->getColumnFormat(),$packetfielddefinition->TargetField);
			$fieldtype = null;
			switch ($packetfielddefinition->TargetFieldType) {
				case DotNetTypeCode::DotNetDecimal:
					$fieldtype =
						sprintf('%s(%d,%d)',
								self::$dotnetype2mysqltypemappings[$packetfielddefinition->TargetFieldType],
								$packetfielddefinition->NumericPrecision,
								$packetfielddefinition->NumericScale
						);
					break;
				case DotNetTypeCode::DotNetString:
					if ($packetfielddefinition->Length>1024) {
						$fieldtype ='longtext';
					} else {

						$fieldtype = sprintf('%s(%d)',
								self::$dotnetype2mysqltypemappings[$packetfielddefinition->TargetFieldType],
								$packetfielddefinition->Length
							);
					}
					break;
				default:
					$fieldtype = self::$dotnetype2mysqltypemappings[$packetfielddefinition->TargetFieldType];
			}
			if ($packetfielddefinition->PrimaryKey) {
				$primarykeyfields[] =$fieldname;
			}
			$fields[] = sprintf("%s %s",$fieldname,$fieldtype);
		}

		if (count($primarykeyfields)>0) {
			$fields[] =sprintf('PRIMARY KEY (%s)',join(',',$primarykeyfields));
		}

		$strsql = sprintf(self::MYSQL_CREATE_TABLE_COMMAND,$tablename,join(',',$fields));
		$this->executeQuery($strsql);
	}
	//private function getIntTableDefinition(\mysqli $_dbconnection,$tablename)
	private function getIntTableDefinition($_dbconnection,$tablename)
	{

		$result =array();
		//Field 	Type 	Null 	Key 	Default 	Extra

		$resultsets = $this->intexecuteQuery(sprintf(self::MYSQL_DESCRIBE_COMMAND,$tablename),true);
		if (count($resultsets)>0) {
			foreach ($resultsets[0][1] as $row) {

				$maxlength=0;
				$matchcount = preg_match_all('/([\w]*)?(.*)/', $row["Type"], $matches,PREG_PATTERN_ORDER );
				if ($matchcount>0){
					$dbdatatype = $matches[1][0];

					$params = $matches[2][0];

					$matchcount=preg_match_all('/^\((.*)\)$/', $params, $matches,PREG_PATTERN_ORDER );
					if ($matchcount>0) {
						$paramstr = $matches[1][0];

						$array = explode (",",$paramstr);
						$maxlength= intval($array[0]);
					}
				}

				$allowdbnull = strtolower($row["Null"]);

				$dbfielddefinition = new DbFieldDefinition();
				$dbfielddefinition->AllowDBNull = ($allowdbnull=="yes");
				$dbfielddefinition->DefaultValue = $row["Default"];
				$dbfielddefinition->ColumnName = $row["Field"];
				$dbfielddefinition->DbDataType = $dbdatatype;


				$dbfielddefinition->PropertyType =  DotNetTypeCodeHelper::dotNetTypeCodeToPhpType(self::$mysqltype2dotnetypecodemappings[$dbdatatype]);
				$dbfielddefinition->PropertyTypeCode= self::$mysqltype2dotnetypecodemappings[$dbdatatype];
				$dbfielddefinition->MaxLength = $maxlength;
				$dbfielddefinition->PrimaryKey = strcasecmp ($row['Key'],'PRI')==0?true:false;
				$result[] = $dbfielddefinition;
			}


		}
		return $result;
	}

	public function commit()
	{
		$this->dbconnection->commit();
	}

	private function intexecuteQuery($commandtext,$fetchassoc=false)
	{
		//echo "executeNonQuery >".gmdate('Y-m-d H:i:s',time())."</br>";
		$resultsets = array();
		if ($this->dbconnection->multi_query($commandtext))
		{
			$more_results= true;
			do {
				$result =$this->dbconnection->store_result();
				if ($result) {
					$finfos = $result->fetch_fields();
					$array=array();
					if ($fetchassoc){
						while ($row = $result->fetch_assoc()) {
							$array[] = $row;
						}

					}
					else {
						while ($row = $result->fetch_row()) {
							$array[] =  $row;
						}
					}
					$resultsets[] = array($finfos,$array);
					$result->free();
				}
				$more_results = $this->dbconnection->more_results();
				if ($more_results){
					$this->dbconnection->next_result();
				}
			}
			while ($more_results);
		}
		else //throw new \Exception($this->dbconnection->error);
			throw new \Exception($this->dbconnection->error);
		return $resultsets;

	}

	public function executeQuery($commandtext)
	{
		return $this->intexecuteQuery($commandtext,false);
	}

	protected function getTableDefinition($tablename) {
		return $this->getIntTableDefinition($this->dbconnection, $tablename);
	}

	protected function fieldinfotodbfielddefinition($fieldinfo,$ordinal=-1)
	{
		$result = new DbFieldDefinition();

		$dbdatatype = self::$mysqltype2stringmappings[$fieldinfo->type];
		$dotnetypecode = self::$mysqltype2dotnetypecodemappings[$dbdatatype];

		$result->AllowDBNull =($fieldinfo->flags & MYSQLI_NOT_NULL_FLAG)==0;
		$result->ColumnName = $fieldinfo->name;
		$result->MaxLength = $fieldinfo->max_length;
		$result->Ordinal =$ordinal;
		$result->DbDataType = $dbdatatype;
		$result->PropertyType = DotNetTypeCodeHelper::dotNetTypeCodeToPhpType($dotnetypecode);
		$result->PropertyTypeCode= self::$mysqltype2dotnetypecodemappings[$dbdatatype];

		if ($result->AllowDBNull) {
			$result->DefaultValue = null;
		}

		return $result;
	}

	protected function fieldsinfostodbfielddefinitions($fieldsinfos)
	{
		$result =array();

		for ($i=0;$i<count($fieldsinfos);$i++)
		{
			$fieldinfo = $fieldsinfos[$i];
			$result[] = $this->fieldinfotodbfielddefinition($fieldinfo,$i);
		}

		return $result;
	}
}
