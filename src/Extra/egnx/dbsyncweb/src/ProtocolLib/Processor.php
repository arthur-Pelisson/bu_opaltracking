<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\ProtocolLib;

//require_once (realpath(dirname(__FILE__).'/../packetlib/packet.php'));
//require_once (realpath(dirname(__FILE__).'/../protocollib/archivemanager.php'));
//require_once (realpath(dirname(__FILE__).'/../protocollib/command.php'));
//require_once (realpath(dirname(__FILE__).'/../others/customtools.php'));
//require_once (realpath(dirname(__FILE__).'/../others/jsonserializer.php'));

// use sqladapterlib\sqladapter;
// use packetlib\packet;
// use others\tools;
// use others\jsonserializer;
// use protocollib\archivemanager;
// use protocollib\command;
// use protocollib\OriginType;


use Egnx\DbSyncWeb\Others\CustomTools;
use Egnx\DbSyncWeb\Others\JsonSerializer;
use Egnx\DbSyncWeb\ProtocolLib\Command;

class Processor {
	
	const FOLDER_PREFIX ="#";
	
	private $archivemanagersource;
	private $archivemanagerdestination;
	private $sqladapter;
	
	
	private function proceedfolder($folder)
	{
		$result =array();
		$files = $this->archivemanagersource->getfiles($folder);
		$processelement= new ProcessElement($folder,$files);
		
		$commandcontent = $this->archivemanagersource->getfromname($processelement->commandfile);
		
		$packet = null;
		
		if (count($processelement->datasfiles)>0)
		{
			$packetcontent =  $this->archivemanagersource->getfromname($processelement->datasfiles[0]);
			$packet = JsonSerializer::deserialize($packetcontent);
		}
		$commandorrigin =  OriginType::Client;
		if($commandcontent!=null) {
			$command =
				CustomTools::cast(
						JsonSerializer::deserialize($commandcontent),
						//'protocollib\command'
						'\Egnx\DbSyncWeb\ProtocolLib\Command'
					);
		}
		else {
			
			if ($packet==null) {
				throw  new \Exception("no datas files found.");
			}

 			$command= new Command();
 			$command->Origin = 	OriginType::Client;
 			$command->TargetTable = $packet->TargetTable;
 			$command->SourceTable = $packet->SourceTable;
 			$command->PacketFieldDefinitions =$packet->PacketFieldDefinitions; 
			
		}
		if ($commandorrigin==$command->Origin)
		{
			$i= 0;
			while($packet!=null)
			{
				$packets = array($packet);
				$this->sqladapter->applyPackets($packets);
				$i++;
				$packet = null;
				if ($i<count($processelement->datasfiles)) {
					$packetcontent =  $this->archivemanagersource->getfromname($processelement->datasfiles[$i]);
					$packet = JsonSerializer::deserialize($packetcontent);
				
				}
			}
		}
		else {
			$funcdesc= array($this,'storepacket');
			$result = $this->sqladapter->getPackets(
					$command->CommandText,
					$command->SourceTable,
					$command->TargetTable,
					$command->TruncateMode,
					$command->PacketFieldDefinitions,
					$funcdesc
					);
		}
		
		return $result;
	}
	
	private $currentfolder=null; 
	public function storepacket($packet)
	{
		$foldername = self::getName($this->folderid);
		if ($this->currentfolder==null) {
		
			$this->archivemanagerdestination->addfolder($foldername);
			$this->currentfolder =$foldername;
			
		}
		$content = JsonSerializer::serialize($packet);
		$filename = $foldername.ArchiveManager::FOLDER_SEPARATOR."datas".self::getName($this->fileid).".json";
		$this->archivemanagerdestination->addfromstring($filename, $content);
		$this->fileid++;
	} 
	
	private $folderid = 0;
	private $fileid =0;
	
	private function proceedfolders($folders)
	{
		$this->folderid = 0;
		$this->currentfolder =null;
		foreach ($folders as $folder)
		{
			$this->fileid =0;
			$result = $this->proceedfolder($folder);
			
			if (count($result)) {
				$foldername = self::getName($this->folderid);
				$this->currentfolder =$foldername;
				$this->archivemanagerdestination->addfolder($foldername);
//				$fileid= 0;
				foreach ($result as $packet) {
					$content = JsonSerializer::serialize($packet);
					$filename = $foldername.ArchiveManager::FOLDER_SEPARATOR."datas".self::getName($this->fileid).".json";
					$this->archivemanagerdestination->addfromstring($filename, $content);
					$this->fileid++;
				}
//				$folderid++;
			}
			if ($this->currentfolder!=null) {
				$this->currentfolder =null;
				$this->folderid++;
			}
			$this->sqladapter->commit();
		}
	}
	
	
	private static function getName($index)
	{
		sprintf('%04d', $index);
		//$strarray = pack('n',$index);
		return "#".sprintf('%04d', $index);
		//bin2hex($strarray);
	}
	
	public function proceedarchive($sqladapter,$source,$destination)
	{
		$this->sqladapter =$sqladapter;
		$this->archivemanagersource = new ArchiveManager($source);
		$this->archivemanagerdestination = new ArchiveManager($destination);
		
		$folders = $this->archivemanagersource->getfolders();
		$this->proceedfolders($folders);
		
	}
	
}


