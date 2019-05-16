<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 11:42
 */

namespace Email\Template;


use Email\AbstractEmail;

class NormalTemplate extends AbstractEmail
{

    public function build()
    {
        return "hello world";
    }
}