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
            'cc' => '471066925@qq.com:奥特曼',
            'template_id' => 1,
            'attempt_num' => 0,
            'sender' => 'system',
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
     * 检查当天同一个模板在同一个发送时间点有没有发送 如果已发送返回true 否则返回false 从DB库检查 由派生类实现
     * @param $template_id
     * @param $cron_time
     * @return bool
     */
    protected function checkTodayIsSent($template_id, $cron_time)
    {
        return false;
    }

    /**
     * 添加发送成功记录数和总数
     * @param $templateId
     * @return mixed
     */
    protected function addSentOkNum($templateId)
    {
        // TODO: Implement addSentOkNum() method.
    }

    /**
     * 添加发送失败记录数
     * @param $templateId
     * @return mixed
     */
    protected function addSentFailNum($templateId)
    {
        // TODO: Implement addSentFailNum() method.
    }

    /**
     * 记录上一次发送时间
     * @param $templateId
     * @return mixed
     */
    protected function addLastSendTime($templateId)
    {
        // TODO: Implement addLastSendTime() method.
    }

    /**
     * 获取发送日志信息 从DB获取数据 由派生类实现
     * @param int $id
     * @return array
     */
    protected function getSentLogInfo($id)
    {
        // TODO: Implement getSentLogInfo() method.
    }

    /**
     *  加锁 一般使用reids 进行加锁 幂等提交 默认返回类型必须是true 要不然重新发送会发不出
     * @return bool default true
     */
    protected function acquireLock()
    {
        // TODO: Implement acquireLock() method.
    }

    /**
     *  解锁
     * @param int $id
     * @return void
     */
    protected function releaseLock($id)
    {
        // TODO: Implement acquireLock() method.
    }

    /**
     * 更新记录 由派生类实现 DB操作
     * @param int $id
     * @param array $param
     * @return mixed
     */
    protected function saveRecord(int $id, array $param)
    {
        // TODO: Implement saveRecord() method.
    }

    /**
     * 获取发送不同人的邮件和附件 分组发送
     * @return array
     */
    protected function getGroupEmailAndAttach()
    {
        return null;
    }
}