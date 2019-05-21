<?php
/**
 * Created by Oliver xu.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 11:42
 */

namespace Email\Template;


use Email\AbstractEmail;

class NormalTemplate extends AbstractEmail
{

    /**
     * 获取某模板列表数据
     * @param int $id
     * @return array
     */
    public function getTemplate(int $id)
    {
        return [
            'id' => 1,
            'title' => '模板标题',
            'subject' => '邮件标题',
            'body' => '邮件正文',
            'status' => 1,
            'is_html' => 0,
            'receivers' => 'xdchebe@qq.com:皮卡丘,xudongchao@beidukeji.com',
            'last_send_time' => 1558080796,
            'template_class' => 'NormalTemplate',
            'template_namespace' => "\Email\Template\NormalTemplate",
            'cron_time' => '18'
        ];
    }

    /**
     * 添加错误日志 写入到DB 由派生类实现
     * @param array $param
     * @return void
     */
    protected function addError(array $param)
    {
        // TODO: Implement addError() method.
    }

    /**
     * 获取模板类名称
     * @return mixed
     */
    protected function getTemplateClass()
    {
        return get_class();
    }

    /**
     * 添加发送记录 写入到DB 由派生类实现
     * @param array $param
     * @return void
     */
    protected function addRecord(array $param)
    {
        // TODO: Implement addRecord() method.
    }

    /**
     * 获取附件 格式数组['filename','filename1'] 自由生成附件 由派生类实现
     * @return array | null
     */
    protected function getAttachments()
    {
        return null;
    }

    /**
     * 检查当天同一个账户在同一个发送时间点有没有发送 如果已发送返回true 否则返回false 从DB库检查 由派生类实现
     * @param $template_id
     * @param $receiver
     * @param $cron_time
     * @return bool
     */
    protected function checkTodayIsSent($template_id, $receiver, $cron_time)
    {
        return false;
    }
}