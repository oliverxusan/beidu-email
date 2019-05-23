<?php
/**
 * Created by Oliver xu.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:42
 */

namespace Email\Contract;


interface EmailInterface
{
    /**
     * 发送邮件
     * @param int $id
     * @return mixed
     */
    public function send(int $id);

    /**
     * 再一次发送邮件
     * @param int $id
     * @return mixed
     */
    public function sendAgain(int $id);

    /**
     * 获取工厂实例
     * @return mixed
     */
    public function getFactory();

    /**
     * 设置配置文件
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config);

    /**
     * 解析模板类
     * @param string $name
     * @return string
     */
    public function parseTemplate(string $name);

    /**
     * 检查邮件标题是否为空
     * @param string $subject
     * @return bool
     */
    public function isSubject(string $subject);

    /**
     * 检查邮件正文是否为空
     * @param string $body
     * @return bool
     */
    public function isBody(string $body);

    /**
     * 状态是否开启
     * @param int $status
     * @return bool
     */
    public function isStatus(int $status);

    /**
     * 解析接收者
     * @param string $receivers
     * @return mixed
     */
    public function parseReceivers(string $receivers);


    /**
     * 是否以html格式发送邮件
     * @param $isHtml
     * @return bool
     */
    public function isHtml($isHtml);

    /**
     *  获取定时发送时间
     * @param $time
     * @return bool
     */
    public function isCronTime($time);

    /**
     * 解析附件
     * @param $attachment
     * @return null|array
     */
    public function parseAttachment($attachment);
}