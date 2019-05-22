<?php
/**
 * Created by Oliver xu.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:47
 */

namespace Email\Adapter;

use Email\Exceptions\EmailException;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Email\Contract\AdapterInterface;

class PHPEmailAdapter implements AdapterInterface
{

    protected $phpMail = null;

    public function __construct($config)
    {
        $this->phpMail = new PHPMailer(true);
        $this->setConfig($config);
    }


    /**
     * 设置配置
     * @param array $config
     * @return mixed
     */
    protected function setConfig(array $config)
    {
        $this->phpMail->SMTPDebug = $config['DEBUG'] ? $config['DEBUG'] : 0;                                       // Enable verbose debug output
        if ($config['ISSMTP']) {
            $this->phpMail->isSMTP();// Set mailer to use SMTP
        }
        $this->phpMail->Host       = $config['SMTP_HOST'];  // Specify main and backup SMTP servers
        if ($config['SMTP_AUTH']) {
            $this->phpMail->SMTPAuth   = $config['SMTP_AUTH'];                                   // Enable SMTP authentication
        }
        $this->phpMail->Username   = $config['SMTP_USER'];                // SMTP username
        $this->phpMail->Password   = $config['SMTP_PASS'];                               // SMTP password
        if ($config['SECURE']) {
            $this->phpMail->SMTPSecure = $config['SECURE'];
        }                                // Enable TLS encryption, `ssl` also accepted
        $this->phpMail->Port       = $config['SMTP_PORT'] ? $config['SMTP_PORT'] : 587;

        $this->setFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
        $replyEmail = $config['REPLY_EMAIL'] ? $config['REPLY_EMAIL'] : $config['FROM_EMAIL'];
        $replyName = $config['REPLY_NAME'] ? $config['REPLY_NAME']: $config['FROM_NAME'];
        $this->addReplyTo($replyEmail, $replyName);
    }

    /**
     * 添加抄送人
     * @param array $cc
     * @return object
     * @throws EmailException
     */
    public function addCC(array $cc)
    {
        if (count($cc) > 0) {
            foreach ($cc as $c) {
                $this->phpMail->addCC($c);
            }
        }else{
            throw new EmailException("抄送人不能为空");
        }
        return $this;
    }

    /**
     * 添加附件
     * @param array $attach
     * @return object
     * @throws EmailException
     */
    public function addAttachment(array $attach)
    {
        if (count($attach) > 0) {
            foreach ($attach as $a) {
                if (is_array($a)) {
                    $this->phpMail->addAttachment($a[0],$a[1]);
                }elseif (is_string($a)){
                    $this->phpMail->addAttachment($a);
                }
            }
        }else{
            throw new EmailException("添加附件不能为空");
        }
        return $this;
    }

    /**
     * 设置是否为html
     * @param bool $bool
     * @return object
     */
    public function setIsHTML(bool $bool)
    {
        if ($bool == null)
            $bool = false;
        $this->phpMail->isHTML($bool);
        return $this;
    }

    /**
     * 设置邮件标题
     * @param string $subject
     * @return object
     * @throws EmailException
     */
    public function setSubject($subject)
    {
        if (empty($subject))
            throw new EmailException("邮件标题不能为空");
        $this->phpMail->Subject = $subject;
        return $this;
    }

    /**
     *  设置邮件主体
     * @param string $body
     * @return object
     * @throws EmailException
     */
    public function setBody($body)
    {
        if (empty($body))
            throw new EmailException("邮件正文不能为空");
        $this->phpMail->Body = $body;
        return $this;
    }

    /**
     * 邮件发送
     * @return bool
     */
    public function send()
    {
        return $this->phpMail->send();
    }

    /**
     * 设置接收人
     * @param string $email
     * @param string $username
     * @return mixed
     */
    public function addAddress($email, $username = '')
    {
        $this->phpMail->addAddress($email, $username);
        return $this;
    }

    /**
     * 设置发送者
     * @param string $email
     * @param string $username
     * @return mixed
     */
    public function setFrom($email, $username = '')
    {
        $this->phpMail->setFrom($email, $username);
    }

    /**
     * 设置回复者
     * @param string $email
     * @param string $username
     * @return mixed
     */
    public function addReplyTo($email, $username = '')
    {
        $this->phpMail->addReplyTo($email, $username);
    }
}