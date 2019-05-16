<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 10:47
 */

namespace Email\Adapter;

use Email\Exceptions\ConfigException;
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
        static::$instance->SMTPDebug = 2;                                       // Enable verbose debug output
        static::$instance->isSMTP();                                            // Set mailer to use SMTP
        static::$instance->Host       = $config['host'];  // Specify main and backup SMTP servers
        static::$instance->SMTPAuth   = $config['auth'];                                   // Enable SMTP authentication
        static::$instance->Username   = $config['username'];                // SMTP username
        static::$instance->Password   = $config['password'];                               // SMTP password
        static::$instance->SMTPSecure = $config['secure'] ? $config['secure'] : 'tls';                                  // Enable TLS encryption, `ssl` also accepted
        static::$instance->Port       = $config['port'] ? $config['port'] : 587;
    }
}