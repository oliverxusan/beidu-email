<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 11:02
 */

namespace Email\Exceptions;


class ConfigException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        $this->message = $message;
        $this->code = $code;
    }
}