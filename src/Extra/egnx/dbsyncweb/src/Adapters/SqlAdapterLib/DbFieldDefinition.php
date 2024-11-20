<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Adapters\SqlAdapterLib;

//require_once (realpath(dirname(__FILE__).'/../../others/typecodes.php'));

//use others\DotNetTypeCode;

class DbFieldDefinition {

	public $AllowDBNull=true;
	public $Ordinal=-1;
	public $DbDataType;
	public $PropertyType;
	public $PropertyTypeCode;
	public $ColumnName;
	public $MaxLength=0;
	public $DefaultValue=null;
	public $PrimaryKey = false;
	public $Updatable = false;

}
