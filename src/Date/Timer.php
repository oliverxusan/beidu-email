<?php
/**
 * Created by oliver xu.
 * User: Administrator
 * Date: 2019/5/21
 * Time: 16:38
 */

namespace Email\Date;


class Timer
{
    private $startTime = 0; //保存脚本开始执行时的时间（以微秒的形式保存）
    private $stopTime = 0; //保存脚本结束执行时的时间（以微秒的形式保存）

    public function start(){
        $this->startTime = microtime(true); //将获取的时间赋值给成员属性$startTime
    }
    public function stop(){
        $this->stopTime = microtime(true); //将获取的时间赋给成员属性$stopTime
    }
    public function spent(){
        return round(($this->stopTime-$this->startTime),4);
    }
}