<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:48
 */

namespace Email;


use Email\Contract\EmailInterface;

abstract class AbstractEmail implements EmailInterface
{
    abstract public function build();

    public function send()
    {
        // TODO: Implement send() method.
    }

    public function config()
    {
        // TODO: Implement config() method.
    }

    public function logger()
    {
        // TODO: Implement logger() method.
    }
}