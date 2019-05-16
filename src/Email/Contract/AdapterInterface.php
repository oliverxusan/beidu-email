<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:45
 */

namespace Email\Contract;


interface AdapterInterface
{
    /**
     * 单例
     * @param array $config
     * @return mixed
     */
    public function getInstance(array $config);

    /**
     * 设置配置
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config);
}