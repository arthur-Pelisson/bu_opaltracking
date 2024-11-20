<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Adapters\MysqlAdapterLib;

class MysqlFactory {
	
	public static  function  getconnection($db_hostname,$db_port,$db_user,$db_password,$db_name,$db_charset = null)
    {
		$port = $db_port;
		$hostname = $db_hostname;
		if (!empty($port)) $hostname = "$hostname:$port";
	
		$result =  new \mysqli(
				$hostname,
                $db_user,
                $db_password,
                $db_name
		);
			
		if (mysqli_connect_error()) {
			throw new \Exception("Conn. error :"  . mysqli_connect_error());
		}
		
		$result->autocommit(false);

		if (!empty($db_charset)) $result->set_charset($db_charset);
		
		return  $result;
	}
	
}
