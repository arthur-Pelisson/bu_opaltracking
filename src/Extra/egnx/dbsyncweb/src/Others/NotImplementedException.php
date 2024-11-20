<?php
/**
 * DbSyncWeb
 * Copyright (c) 2016  EGNX
 * @author EGNX <info@egnx.com>
 * International Registered Trademark & Property of EGNX
 */

namespace Egnx\DbSyncWeb\Others;

//class notimplementedexception extends \Exception {
class NotImplementedException extends \Exception {

    public function __construct($message, $code = 0, \Throwable $previous = null) {

        parent::__construct($message, $code, $previous);
    }

}
