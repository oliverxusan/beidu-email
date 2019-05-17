<?php
/**
 * Created by Oliver xu.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:45
 */

namespace Email\Contract;


interface AdapterInterface
{

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

    /**
     * 设置接收人
     * @param string $email
     * @param string $username
     * @return mixed
     */
    public function addAddress($email, $username = '');

    /**
     * 设置发送者
     * @param string $email
     * @param string $username
     * @return mixed
     */
    public function setFrom($email, $username = '');

    /**
     * 设置回复者
     * @param string $email
     * @param string $username
     * @return mixed
     */
    public function addReplyTo($email, $username = '');
}