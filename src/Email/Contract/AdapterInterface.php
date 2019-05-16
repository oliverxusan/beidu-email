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

    /**
     * 添加抄送人
     * @param array $cc
     * @return mixed
     */
    public function addCC(array $cc);

    /**
     * 添加附件
     * @param array $attach
     * @return mixed
     */
    public function addAttachment(array $attach);

    /**
     * 设置邮件标题
     * @param string $subject
     * @return void
     */
    public function setSubject(string $subject);

    /**
     *  设置邮件主体
     * @param string $body
     * @return void
     */
    public function setBody(string $body);

    /**
     * 邮件发送
     * @return bool
     */
    public function send();
}