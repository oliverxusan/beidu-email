<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 17:54
 */

namespace Email\Template;


use Email\AbstractEmail;

class GroupOrderTemplate extends AbstractEmail
{

    public function build()
    {
        return "haha group order";
    }
}