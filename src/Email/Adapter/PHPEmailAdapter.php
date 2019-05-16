<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:47
 */

namespace Email\Adapter;

use Email\Exceptions\ConfigException;
use Email\Exceptions\EmailException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Email\Contract\AdapterInterface;

class PHPEmailAdapter implements AdapterInterface
{
    private static $instance = null;
    private function __construct()
    {
    }

    public function getInstance(array $config = []){
        if (is_null(static::$instance)) {
            static::$instance = new PHPMailer(true);
            if (!$config)
                throw new ConfigException("缺少默认值");
            $this->setConfig($config);
        }
        return static::$instance;
    }

    /**
     * 设置配置
     * @param array $config
     * @return mixed
     */
    public function setConfig(array $config)
    {
        static::$instance->SMTPDebug = $config['debug'] ? $config['debug'] : 0;                                       // Enable verbose debug output
        if ($config['isSMTP']) {
            static::$instance->isSMTP();// Set mailer to use SMTP
        }
        static::$instance->Host       = $config['host'];  // Specify main and backup SMTP servers
        static::$instance->SMTPAuth   = $config['auth'];                                   // Enable SMTP authentication
        static::$instance->Username   = $config['username'];                // SMTP username
        static::$instance->Password   = $config['password'];                               // SMTP password
        static::$instance->SMTPSecure = $config['secure'] ? $config['secure'] : 'tls';                                  // Enable TLS encryption, `ssl` also accepted
        static::$instance->Port       = $config['port'] ? $config['port'] : 587;
    }

    /**
     * 添加抄送人
     * @param array $cc
     * @return mixed
     * @throws EmailException
     */
    public function addCC(array $cc)
    {
        if (count($cc) > 0) {
            foreach ($cc as $c) {
                static::$instance->addCC($c);
            }
        }else{
            throw new EmailException("抄送人不能为空");
        }
    }

    /**
     * 添加附件
     * @param array $attach
     * @return mixed
     * @throws EmailException
     */
    public function addAttachment(array $attach)
    {
        if (count($attach) > 0) {
            foreach ($attach as $a) {
                if (in_array($a)) {
                    static::$instance->addAttachment($a[0],$a[1]);
                }elseif (is_string($a)){
                    static::$instance->addAttachment($a);
                }
            }
        }else{
            throw new EmailException("添加附件不能为空");
        }
    }

    /**
     * 设置是否为html
     * @param bool $bool
     * @return void
     */
    public function setIsHTML(bool $bool)
    {
        if ($bool == null)
            $bool = false;
        static::$instance->isHTML($bool);
    }

    /**
     * 设置邮件标题
     * @param string $subject
     * @return void
     * @throws EmailException
     */
    public function setSubject(string $subject)
    {
        if (empty($subject))
            throw new EmailException("邮件标题不能为空");
        static::$instance->Subject = $subject;
    }

    /**
     *  设置邮件主体
     * @param string $body
     * @return void
     */
    public function setBody(string $body)
    {
        if (empty($body))
            throw new EmailException("邮件正文不能为空");
        static::$instance->Body = $body;
    }

    /**
     * 邮件发送
     * @return bool
     */
    public function send()
    {
        return static::$instance->send();
    }
}