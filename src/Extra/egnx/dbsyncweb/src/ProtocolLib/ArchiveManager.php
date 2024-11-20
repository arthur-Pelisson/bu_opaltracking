<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\ProtocolLib;

//require_once (realpath(dirname(__FILE__).'/../others/customtools.php'));

//use others\tools;

use Egnx\DbSyncWeb\Others\CustomTools;
use phpDocumentor\Reflection\Types\Self_;


class ArchiveManager {
	
	private $archivename;
	const FOLDER_SEPARATOR ="/";
	
	public function __construct($archivename) {
	
		$this->archivename = $archivename;
	
	}
	
	private function intopen($create=false)
	{
		$flags = 0;
		//if (!file_exists($this->archivename)) $flags = \ZipArchive::CREATE;
		if ($create) $flags = \ZipArchive::CREATE;
	
		$ziparchive = new \ZipArchive();
		$res =$ziparchive->open($this->archivename,$flags);
	
		if ($res!==TRUE){

			throw new \Exception("ziparchive->open failed, code:".$res);
	
		}
	
		return $ziparchive;
	}
	
	public function getfromname($name)
	{
		$result =null;
		if ($name==null) {
			return $result;
		}
		
		$ziparchive = $this->intopen();

		$result = $ziparchive->getFromName($name);
		
		$ziparchive->close();
		
		return $result;
	}
	
	public function getfiles($folder)
	{
		$result = array();
		$ziparchive = $this->intopen();
		
		for ($i=0; $i<$ziparchive->numFiles;$i++) {
		
			$array = $ziparchive->statIndex($i);
			$name =$array["name"];
			$crc = $array["crc"];

			if (($folder!=$name) && (CustomTools::startsWith($name, $folder))) {
				$result[] = $name;
			}
				
		}
		
		$ziparchive->close();
		
		sort($result);
		
		return $result;
	}
	
	public function addfromfile($filename,$localname)
	{
		$ziparchive = $this->intopen(true);
		
		$res = $ziparchive->addFile($filename,$localname);
		
		$ziparchive->close();
		
		if ($res==false)
		{
			throw new \Exception("archivemanager->addfromfile ,failed");
		}
	}
	
	public function addfromstring($filename,$content)
	{
		$ziparchive = $this->intopen(true);
		
		$res = $ziparchive->addFromString($filename,$content);

		$ziparchive->close();
		
		if ($res==false)
		{
			throw new \Exception("archivemanager->addFromString ,failed");
		}
	}
	
	public function addfolder($folder)
	{
		$ziparchive = $this->intopen(true);
		
		$ziparchive->addEmptyDir($folder);
		
		$ziparchive->close();
	}
	
	public function getfolders()
	{
		$result = array();
		
		$ziparchive = $this->intopen();
		
		for ($i=0; $i<$ziparchive->numFiles;$i++) {

			$array = $ziparchive->statIndex($i);
			$name =$array["name"];
			$crc = $array["crc"];
			if (($crc==0) && (CustomTools::endsWith($name, self::FOLDER_SEPARATOR))){
				$result[$name]=$name;
			}
			else {
				$values = explode( self::FOLDER_SEPARATOR,$name);
				$values = array_slice($values, 0, count($values)-1);
				$path = implode(self::FOLDER_SEPARATOR, $values).self::FOLDER_SEPARATOR;
				$result[$path]=$path;
				
			}
			
		}
		
		$ziparchive->close();
		$_result =  array_keys ($result);
		sort($_result);
		
		return $_result;
	}
}
