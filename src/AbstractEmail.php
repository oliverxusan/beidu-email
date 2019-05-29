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
use Email\Exceptions\EmailException;

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
        'SMTP_AUTH'   => true,
        'SMTP_HOST'   => 'ssl://smtp.exmail.qq.com', //SMTP服务器
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
     * @param int $templateId 模板ID
     * @return void
     */
    abstract protected function addError(array $param, int $templateId);

    /**
     * 获取某模板列表数据 从DB获取数据 由派生类实现
     * @param int $id
     * @return array
     */
    abstract protected function getTemplate(int $id);

    /**
     * 获取发送日志信息 从DB获取数据 由派生类实现
     * @param int $id
     * @return array
     */
    abstract protected function getSentLogInfo($id);

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
     *  加锁 一般使用reids 进行加锁 幂等提交 默认返回类型必须是true 要不然重新发送会发不出
     * @param int $id
     * @return bool default true
     */
    abstract protected function acquireLock($id);

    /**
     *  解锁
     * @param int $id
     * @return void
     */
    abstract protected function releaseLock($id);
    /**
     * 更新记录 由派生类实现 DB操作
     * @param int $id
     * @param array $param
     * @return mixed
     */
    abstract protected function saveRecord(int $id, array $param);

    /**
     * 获取发送不同人的邮件和附件 分组发送
     * @return array
     */
    abstract protected function getGroupEmailAndAttach();

    /**
     * 发送邮件
     * @param int $id
     * @return mixed
     */
    public function send(int $id)
    {
        try {
            $temp = $this->getTemplate($id);
            if (empty($temp)) {
                $error = ['template_class'=>$this->getTemplateClass(),'reason'=>'找不到此模板列表数据','created_at'=>time(),'id'=>$id];
                $this->addError($error,0);
                return false;
            }
            $endPoint = $this->isCronTime($id,$temp['cron_day'],$temp['cron_hour'],$temp['cron_minute']);
            if ($endPoint && $this->acquireLock($id)) {

                //记录上一次发送时间
                $this->addLastSendTime($id);

                //获取邮件对象
                $emailObj = $this->getFactory();
                $errors = [];
                if (!$this->isStatus($temp['status'])) {
                    array_push($errors, "此邮件模板未开启");
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
                //当分组获取到数据之后 默认取分组中的数据不拿DB中的发送人邮件
                $group = $this->getGroupEmailAndAttach();
                if (!$group) {
                    if (is_null($temp['receivers']) || !($receivers = $this->parseReceivers($temp['receivers']))) {
                        array_push($errors, "邮件接收人不能为空");
                    }
                }

                //当有错误发生的时候就
                if (count($errors) > 0) {
                    $error = [
                        'template_class'=>$this->parseTemplate($this->getTemplateClass()),
                        'reason'=>implode(",",$errors),
                        'created_at'=>time()
                    ];
                    $this->addError($error,$id);
                    $this->releaseLock($id);
                    return false;
                }

                if ($group) {
                    $result = $this->handleBatch($id,$emailObj,$temp,$group,$endPoint);
                }else{
                    $result = $this->handleOne($id,$emailObj,$temp,$receivers,$endPoint);
                }
                return $result;
            }else{
                return "还未到发送时间";
            }

        }catch (\Exception $e){
            $error = ['template_class'=>$this->getTemplateClass(),'reason'=>'file:'.$e->getFile().';line:'.$e->getLine().';mesage:'.$e->getMessage(),'created_at'=>time()];
            $this->addError($error,$id);
            $this->releaseLock($id);
            return false;
        }

    }

    /**
     * 再一次发送邮件
     * @param int $id
     * @return mixed
     */
    public function sendAgain(int $id){
        try {
            $log = $this->getSentLogInfo($id);
            if (empty($log)) {
                $error = ['template_class'=>$this->getTemplateClass(),'reason'=>'找不到此日志数据','created_at'=>time()];
                $this->addError($error,0);
                return false;
            }
            if ($this->acquireLock($id)) {
                //记录上一次发送时间
                $this->addLastSendTime($id);

                //获取邮件对象
                $emailObj = $this->getFactory();
                $errors = [];
                if ($log['status'] == 1) {
                    array_push($errors, "此信息已经发送成功无需发送");
                }

                if (!is_null($log['receiver']) && $receivers = $this->parseReceivers($log['receiver'])) {
                    foreach ($receivers as $value) {
                        if (!empty($value['username']))
                            $emailObj->addAddress($value['email'], $value['username']);
                        else
                            $emailObj->addAddress($value['email']);
                    }
                } else {
                    array_push($errors, "邮件接收人不能为空");
                }

                //添加抄送人
                if (!is_null($log['cc']) && $cc = $this->parseReceivers($log['cc'])) {
                    foreach ($cc as $c) {
                        if (!empty($c['username']))
                            $emailObj->addCC($c['email'], $c['username']);
                        else
                            $emailObj->addCC($c['email']);
                    }
                }
                if ($this->isHtml($log['is_html'])) {
                    $emailObj->setIsHTML(true);
                } else {
                    $emailObj->setIsHTML(false);
                }
                if (!$this->isSubject($log['subject'])) {
                    array_push($errors, "邮件标题不能为空");
                } else {
                    $emailObj->setSubject($log['subject']);
                }
                if (!$this->isBody($log['body'])) {
                    array_push($errors, "邮件正文不能为空");
                } else {
                    $emailObj->setBody($log['body']);
                }

                //当有错误发生的时候就
                if (count($errors) > 0) {
                    $error = [
                        'template_class'=>$this->parseTemplate($this->getTemplateClass()),
                        'reason'=>implode(",",$errors),
                        'created_at'=>time(),
                    ];
                    $this->addError($error,0);
                    return false;
                }
                //如果附件存在则添加附件
                if ($attach = $this->parseAttachment($log['attachment'])) {
                    $emailObj->addAttachment($attach);
                }
                //记录消耗时间
                $time = new Timer();
                $time->start();
                $result = $emailObj->send();
                $time->stop();
                $params = [
                    'status' => $result ? "1":"2",
                    'send_time' => time(),
                ];

                //添加记录
                $this->saveRecord($id, $params);
                if ($result)
                {
                    $this->addSentOkNum($id);
                    $this->releaseLock($id);
                }else{
                    $this->addSentFailNum($id);
                    $this->releaseLock($id);
                }

                //释放内存
                $emailObj = null;
                $errors = null;
                $time = null;
                $params = null;
                return $result;
            }else{
                return "正在处理请稍等";
            }

        }catch (\Exception $e){
            $error = ['template_class'=>$this->getTemplateClass(),'reason'=>$e->getMessage(),'created_at'=>time()];
            $this->addError($error,0);
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
        }else{
            if (stristr($receivers,":")) {
                list($email,$username) = explode(":",$receivers);
                $data[] = [
                    'email'=>$email,
                    'username'=>$username
                ];
            }else{
                $data[] = [
                    'email'=>$receivers,
                    'username'=>''
                ];
            }
        }
        return $data;
    }
    /**
     * 解析附件
     * @param $attachment
     * @return null|array
     */
    public function parseAttachment($attachment){
        if (empty($attachment))
            return null;
        if (stristr($attachment,",")) {
            return explode(",",$attachment);
        }else{
            return [$attachment];
        }
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
     * 是否在指定的时间
     * @param $id
     * @param $day
     * @param $hour
     * @param $minute
     * @return bool
     */
    public function isCronTime($id, $day, $hour, $minute){
        if ($this->isEmpty($day) && $this->isEmpty($hour) && $this->isEmpty($minute)) {
            throw new EmailException("需要设定作业调度的时间");
        }

        $flag1 = true;
        $flag2 = true;
        $flag3 = true;
        if (!$this->isEmpty($day)) {
            if (stristr($day,",")) {
                $flag1 = in_array(intval(date("d")),array_map('intval',explode(",",$day))) ? true : false;
            }else{
                $flag1 = intval(date("d")) == intval($day) ? true : false;
            }
        }
        if (!$this->isEmpty($hour)){
            if (stristr($hour,",")) {
                $flag2 = in_array(intval(date("H")),array_map('intval',explode(",",$hour))) ? true : false;
            }else{
                $flag2 = intval(date("H")) == intval($hour) ? true : false;
            }
        }

        if (!$this->isEmpty($minute)){
            if (stristr($minute,",")) {
                $flag3 = in_array(intval(date("i")),array_map('intval',explode(",",$minute))) ? true : false;
            }else{
                $flag3 = intval(date("i")) == intval($minute) ? true : false;
            }
        }
        $endPoint = $this->getEndPoint($day, $hour, $minute);

        if ($flag1 && $flag2 && $flag3 && !$this->checkTodayIsSent($id,$endPoint)) {
            return $endPoint;
        }else{
            return false;
        }

    }

    /**
     * 获取一个发送时间点
     * @param $day
     * @param $hour
     * @param $minute
     * @return string
     */
    public function getEndPoint($day, $hour, $minute){
        $endPoint = date('Ym');
        $dayInt = (int) date('d');
        $hourInt = (int) date('H');
        $minuteInt = (int) date('i');

        if (!$this->isEmpty($day)) {
            $dayInt = $dayInt < 9 ? '0'.$dayInt:$dayInt;
            $endPoint .= $dayInt;
        }

        if (!$this->isEmpty($hour)) {
            if ($this->isEmpty($day)) {
                $endPoint = date('Ymd');
            }
            $hourInt = $hourInt < 9 ? '0'.$hourInt:$hourInt;
            $endPoint .= $hourInt;
        }

        if (!$this->isEmpty($minute)) {
            if ($this->isEmpty($hour)) {
                $endPoint = date('YmdH');
            }
            $minuteInt = $minuteInt < 9 ? '0'.$minuteInt:$minuteInt;
            $endPoint .= $minuteInt;
        }

        return $endPoint;
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
    /**
     * 判断值是否为空 为空则返回true 否则返回false
     * @param string $value
     * @return bool
     */
    public function isEmpty($value){
        return (is_null($value) || $value == "");
    }

    /**
     * 处理单个邮件发送
     * @param $id 模板id
     * @param $emailObj 邮件适配器对象
     * @param $temp 获取的数据
     * @param $receivers 接收人
     * @param $endPoint
     * @return bool|int
     */
    protected function handleOne($id,$emailObj,$temp,$receivers,$endPoint){
        try {
            //添加发送人
            foreach ($receivers as $value) {
                if (!empty($value['username']))
                    $emailObj->addAddress($value['email'], $value['username']);
                else
                    $emailObj->addAddress($value['email']);
            }
            //添加抄送人
            if (!is_null($temp['cc']) && $cc = $this->parseReceivers($temp['cc'])) {
                foreach ($cc as $c) {
                    if (!empty($c['username']))
                        $emailObj->addCC($c['email'], $c['username']);
                    else
                        $emailObj->addCC($c['email']);
                }
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
            if ((int)$temp['attempt_num'] > 0) {
                $result = $this->trySend($emailObj, $temp['attempt_num']);
                $attempt_num = $result ? $result : 0;
            } else {
                $result = $emailObj->send();
            }
            $time->stop();
            $params = [
                'template_id' => $id,
                'sender' => $this->config['FROM_EMAIL'],
                'receiver' => $temp['receivers'],
                'status' => $result ? "1" : "2",
                'cc' => $temp['cc'],
                'send_time' => time(),
                'cron_time' => $endPoint,
                'is_html' => $temp['is_html'],
                'subject' => $temp['subject'],
                'body' => $temp['body'],
                'send_template' => $this->getTemplateClass(),
                'runtime' => $time->spent(),
                'created_at' => time(),
                'attempt_num' => $attempt_num,
                'attachment' => ($attach && count($attach) > 0) ? implode(",", $attach) : '',
            ];

            //添加记录
            $this->addRecord($params);


            //记录发送成功与失败的数量
            if ($result) {
                $this->addSentOkNum($id);
            } else {
                $this->addSentFailNum($id);
            }
            $this->releaseLock($id);
            //释放内存
            $emailObj = null;
            $errors = null;
            $time = null;
            $params = null;
            return $result;
        }catch (\Exception $e){
            $error = ['template_class'=>$this->getTemplateClass(),'reason'=>'file:'.$e->getFile().';line:'.$e->getLine().';mesage:'.$e->getMessage(),'created_at'=>time()];
            $this->addError($error,$id);
            $this->releaseLock($id);
            return $error;
        }
    }

    /**
     * 处理多个邮件发送
     * @param $id
     * @param $emailObj
     * @param $temp
     * @param $group
     * @param $endPoint
     * @return bool|int
     */
    protected function handleBatch($id,$emailObj,$temp,$group,$endPoint){
        try{
            //添加发送人
            if (empty($group))
                throw new EmailException("分组数据未获取到");
            foreach ($group as $g){
                //添加收件人
                $receivers = isset($g['email']) ? $g['email'] : '';
                if (!$receivers) {
                    $error = [
                        'template_class'=>$this->parseTemplate($this->getTemplateClass()),
                        'reason'=> "分组邮件不存在",
                        'created_at'=>time()
                    ];
                    $this->addError($error,$id);
                    continue;
                }

                foreach ($receivers as $value) {
                    $emailObj->addAddress($value);
                }
                //添加邮件的标题
                $subject = isset($g['subject']) ? $g['subject'] : '';
                if ($subject) {
                    $emailObj->setSubject($subject);
                }
                //添加邮件的内容
                $body = isset($g['body']) ? $g['body'] : '';
                if ($body) {
                    $emailObj->setBody($body);
                }
                //添加抄送人
                if (!is_null($temp['cc']) && $cc = $this->parseReceivers($temp['cc'])) {
                    foreach ($cc as $c) {
                        if (!empty($c['username']))
                            $emailObj->addCC($c['email'], $c['username']);
                        else
                            $emailObj->addCC($c['email']);
                    }
                }
                //如果附件存在则添加附件
                $attach = isset($g['attach']) ? $g['attach'] : '';
                if ($attach) {
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
                    'receiver' => implode(",",$receivers),
                    'status' => $result ? "1":"2",
                    'cc' => $temp['cc'],
                    'send_time' => time(),
                    'cron_time' => $endPoint,
                    'is_html' => $temp['is_html'],
                    'subject' => !empty($subject) ? $subject : $temp['subject'],
                    'body' => !empty($body) ? $body : $temp['body'],
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
                {
                    $this->addSentOkNum($id);
                }else{
                    $this->addSentFailNum($id);
                }

                //释放内存
                //$emailObj = null;
                $errors = null;
                $time = null;
                $params = null;
                $emailObj->clearAddresses();
                $emailObj->clearAttachments();
            }
            $this->releaseLock($id);
            $emailObj = null;
            return $result;
        }catch (\Exception $e){
            $error = ['template_class'=>$this->getTemplateClass(),'reason'=>'file:'.$e->getFile().';line:'.$e->getLine().';mesage:'.$e->getMessage(),'created_at'=>time()];
            $this->addError($error,$id);
            $this->releaseLock($id);
            return $error;
        }
    }
}