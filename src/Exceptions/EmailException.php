<?php
/**
 * Created by Oliver xu.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:53
 */

namespace Email\Exceptions;


class EmailException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        $this->message = $message;
        $this->code = $code;
    }
}