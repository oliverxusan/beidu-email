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
     * @return array
     */
    public function getTemplate()
    {
        return [
            'id' => 1,
            'title' =>'模板标题',
            'subject' => '邮件标题',
            'body' => '邮件正文',
            'status' => 1,
            'is_html' => 0,
            'receivers' => 'xdchebe@qq.com:皮卡丘,xudongchao@beidukeji.com',
            'last_send_time'=> 1558080796,
            'template_class'=> 'NormalTemplate',
            'template_namespace' => "\Email\Template\NormalTemplate",
            'cron_time' => '18'
        ];
    }
    /**
     * 添加错误日志
     * @param array $param
     * @return void
     */
    public function addError(array $param)
    {
        // TODO: Implement addError() method.
    }

    /**
     * 添加发送记录
     * @param array $param
     * @return void
     */
    public function addRecord(array $param)
    {
        // TODO: Implement addRecord() method.
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
     * 设置附件
     * @param array $attachments
     * @return mixed
     */
    protected function setAttachments(array $attachments)
    {
        if (!empty($attachments))
            $this->attachments = array_merge($this->attachments, $attachments);
    }
}