<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Others;



class CustomTools {
	
	public static function startsWith($haystack, $needle)
	{
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}
	
	public static function endsWith($haystack, $needle)
	{
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}
		return (substr($haystack, -$length) === $needle);
	}
	
	public static function cast($object, $class) {

		if( !is_object($object) )
			throw new \InvalidArgumentException('$object must be an object.');
		if( !is_string($class) )
			throw new \InvalidArgumentException('$class must be a string.');
		if( !class_exists($class) )
			throw new \InvalidArgumentException(sprintf('Unknown class: %s.', $class));
// 		if( !is_subclass_of($class, get_class($object)) )
// 			throw new \InvalidArgumentException(sprintf(
// 					'%s is not a descendant of $object class: %s.',
// 					$class, get_class($object)
// 			));
	
		return unserialize(
				preg_replace(
						'/^O:\d+:"[^"]++"/',
						'O:'.strlen($class).':"'.$class.'"',
						serialize($object)
				)
		);
	}
}
