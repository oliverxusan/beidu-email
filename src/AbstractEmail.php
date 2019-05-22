<?php
/**
 * Created by Oliver xu.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:48
 */

namespace Email;


use Email\Adapter\PHPEmailAdapter;
use Email\Contract\EmailInterface;
use Email\Date\Timer;

abstract class AbstractEmail implements EmailInterface
{
    /**
     * 默认适配器
     * @var string
     */
    private $defaultAdapter = PHPEmailAdapter::class;

    /**
     * 邮件配置数组
     * @var array
     */
    private $config = [
        'DEBUG'       => 0,
        'ISSMTP'      => true,
        'SMTP_AUTH'   => false,
        'SMTP_HOST'   => 'smtp.qq.com', //SMTP服务器
        'SMTP_PORT'   => '465', //SMTP服务器端口
        'SMTP_USER'   => 'email@beidukeji.com', //'1309772731@qq.com', //SMTP服务器用户名
        'SMTP_PASS'   => 'ZXCV123asdf456',//'igidmfxbpyusjjhf', //SMTP服务器密码
        'SECURE'      => '',
        'FROM_EMAIL'  => 'email@beidukeji.com', //发件人EMAIL
        'FROM_NAME'   => '贝嘟科技', //发件人名称
        'REPLY_EMAIL' => '', //回复EMAIL（留空则为发件人EMAIL）
        'REPLY_NAME'  => '贝嘟科技', //回复名称（留空则为发件人名称）
    ];

    /**
     * 添加错误日志 写入到DB 由派生类实现
     * @param array $param
     * @return void
     */
    abstract protected function addError(array $param);

    /**
     * 获取某模板列表数据 从DB获取数据 由派生类实现
     * @param int $id
     * @return array
     */
    abstract protected function getTemplate(int $id);

    /**
     * 获取模板类名称
     * @return mixed
     */
    abstract protected function getTemplateClass();

    /**
     * 添加发送记录 写入到DB 由派生类实现
     * @param array $param
     * @return void
     */
    abstract protected function addRecord(array $param);

    /**
     * 添加发送成功记录数和总数
     * @param $templateId
     * @return mixed
     */
    abstract protected function addSentOkNum($templateId);

    /**
     * 添加发送失败记录数
     * @param $templateId
     * @return mixed
     */
    abstract protected function addSentFailNum($templateId);

    /**
     * 获取附件 格式数组['filename','filename1'] 自由生成附件 由派生类实现
     * @return array | null
     */
    abstract protected function getAttachments();

    /**
     * 检查当天同一个模板在同一个发送时间点有没有发送 如果已发送返回true 否则返回false 从DB库检查 由派生类实现
     * @param $template_id
     * @param $cron_time
     * @return bool
     */
    abstract protected function checkTodayIsSent($template_id, $cron_time);

    /**
     * 记录上一次发送时间
     * @param $templateId
     * @return mixed
     */
    abstract protected function addLastSendTime($templateId);

    /**
     * 发送邮件
     * @param int $id
     * @return mixed
     */
    public function send(int $id)
    {
        $temp = $this->getTemplate($id);
        if (empty($temp)) {
            $error = ['template_class'=>$this->getTemplateClass(),'reason'=>'找不到此模板列表数据','created_at'=>time()];
            $this->addError($error);
            return false;
        }
        try {
            if ($this->isCronTime($temp['cron_time']) && !$this->checkTodayIsSent($temp['template_id'],$temp['cron_time'])) {
                //记录上一次发送时间
                $this->addLastSendTime($id);

                //获取邮件对象
                $emailObj = $this->getFactory();
                $errors = [];
                if (!$this->isStatus($temp['status'])) {
                    array_push($errors, "此邮件模板未开启");
                }

                if ($receivers = $this->parseReceivers($temp['receivers'])) {
                    foreach ($receivers as $value) {
                        if (!empty($value['username']))
                            $emailObj->addAddress($value['email'], $value['username']);
                        else
                            $emailObj->addAddress($value['email']);
                    }
                } else {
                    array_push($errors, "邮件接收人不能为空");
                }

                if ($this->isHtml($temp['is_html'])) {
                    $emailObj->setIsHTML(true);
                } else {
                    $emailObj->setIsHTML(false);
                }
                if (!$this->isSubject($temp['subject'])) {
                    array_push($errors, "邮件标题不能为空");
                } else {
                    $emailObj->setSubject($temp['subject']);
                }
                if (!$this->isBody($temp['body'])) {
                    array_push($errors, "邮件正文不能为空");
                } else {
                    $emailObj->setBody($temp['body']);
                }

                //当有错误发生的时候就
                if (count($errors) > 0) {
                    $error = [
                        'template_class'=>$this->parseTemplate($this->getTemplateClass()),
                        'reason'=>implode(",",$errors),
                        'created_at'=>time()
                    ];
                    $this->addError($error);
                    return false;
                }
                //如果附件存在则添加附件
                if ($attach = $this->getAttachments()) {
                    $emailObj->addAttachment($attach);
                }
                //记录消耗时间
                $time = new Timer();
                $time->start();
                //开启尝试机制进行发送失败重发
                $attempt_num = 0;
                if ((int) $temp['attempt_num'] > 0) {
                    $result = $this->trySend($emailObj, $temp['attempt_num']);
                    $attempt_num = $result ? $result : 0;
                }else{
                    $result = $emailObj->send();
                }
                $time->stop();
                $params = [
                    'template_id' => $id,
                    'sender' => $this->config['FROM_EMAIL'],
                    'receiver' => $temp['receivers'],
                    'status' => $result ? "1":"0",
                    'cron_time' => date("YmdH"),
                    'send_template' => $this->getTemplateClass(),
                    'runtime' => $time->spent(),
                    'created_at' => time(),
                    'attempt_num'=> $attempt_num,
                    'attachment'=> ($attach && count($attach) > 0) ? implode(",",$attach) : '',
                ];

                //添加记录
                $this->addRecord($params);
                //记录发送成功与失败的数量
                if ($result)
                    $this->addSentOkNum($id);
                else
                    $this->addSentFailNum($id);

                //释放内存
                $emailObj = null;
                $errors = null;
                $time = null;
                $params = null;
                return $result;
            }else{
                return "还未到发送时间";
            }

        }catch (\Exception $e){
            $error = ['template_class'=>$this->getTemplateClass(),'reason'=>$e->getMessage(),'created_at'=>time()];
            $this->addError($error);
            return false;
        }

    }

    /**
     * 获取工厂实例
     * @return mixed
     */
    public function getFactory()
    {
        return new $this->defaultAdapter($this->config);
    }
    /**
     * 设置配置文件
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config) {
        if (!empty($config)){
            $this->config = array_merge($this->config,$config);
        }
    }

    /**
     * 获取配置文件
     * @return array
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * 解析模板类
     * @param string $name
     * @return string
     */
    public function parseTemplate(string $name){
        if (empty($name))
            throw new EmailException("解析模板的名字为空");
        $fileArray = explode("\\",$name);
        $length = count($fileArray);
        if ($length == 1) {
            return $fileArray[0];
        }
        return $fileArray[$length-1];
    }

    /**
     * 检查邮件标题是否为空
     * @param string $subject
     * @return bool
     */
    public function isSubject(string $subject) {
        return !empty($subject);
    }
    /**
     * 检查邮件正文是否为空
     * @param string $body
     * @return bool
     */
    public function isBody(string $body) {
        return !empty($body);
    }
    /**
     * 状态是否开启
     * @param int $status
     * @return bool
     */
    public function isStatus(int $status) {
        return $status == 1;
    }
    /**
     * 解析接收者
     * @param string $receivers
     * @return mixed
     */
    public function parseReceivers(string $receivers) {
        $data = [];
        if (empty($receivers))
            return $data;

        if (stristr($receivers,",")) {
            $receiverArray = explode(",",$receivers);
            foreach ($receiverArray as $receiver) {
                if (stristr($receiver,":")) {
                    list($email,$username) = explode(":",$receiver);
                    $data[] = [
                        'email'=>$email,
                        'username'=>$username
                    ];
                }else{
                    $data[] = [
                        'email'=>$receiver,
                        'username'=>''
                    ];
                }
            }
        }
        return $data;
    }
    /**
     * 实例化命名空间
     * @param string $namespace
     * @return object
     */
    public function instanceNamespace(string $namespace) {
        $namespace = "\\".$namespace;
        return new $namespace;
    }

    /**
     * 是否以html格式发送邮件
     * @param $isHtml
     * @return bool
     */
    public function isHtml($isHtml){
        return $isHtml == 1;
    }

    /**
     *  获取定时发送时间
     * @param $time
     * @return bool
     */
    public function isCronTime($time) {
        if (empty($time))
            throw new EmailException("获取定时任务时间为空");
        if (stristr($time,",")) {
            return in_array(date("H"),explode(",",$time));
        }else{
            return date("H") == $time;
        }
    }

    /**
     * 尝试发送 返回布尔型或者整型
     * @param PHPEmailAdapter $object
     * @param int $num
     * @return bool| int
     */
    public function trySend($object, $num){
        $i = 0;
        $result = false;
        while (true) {
            $result = $object->send();
            if ($i > $num) {
                break;
            }
            if (!$result) {
                $i++;
            } else {
                break;
            }
        }
        if ($result) {
            return $i;
        }
        return $result;
    }
}