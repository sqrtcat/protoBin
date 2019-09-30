<?php
/**
 * @author sqrtcat 32448732@qq.com
 * 注册请求体
 */
namespace App;

use ProtoBin\Message;
use ProtoBin\Type;

/**
 * Class RegisterRequest
 * @package App
 */
class RegisterRequest extends Message
{
    public $account;
    public $password;
    public $age;

    // 消息体包含的字段
    public static $paramNameMapping = [
        0 => 'account',
        1 => 'password',
        2 => 'age',
    ];

    // 参数类型
    public static $paramProtocolTypeMapping = [
        'account'  => Type::TYPE_STRING,
        'password' => Type::TYPE_STRING,
        'age'      => Type::TYPE_TINYINT,
    ];

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param mixed $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * @param mixed $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }
}
