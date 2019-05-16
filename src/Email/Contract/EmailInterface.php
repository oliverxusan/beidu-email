<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:42
 */

namespace Email\Contract;


interface EmailInterface
{
    public function send();

    public function config();

    public function logger();
}