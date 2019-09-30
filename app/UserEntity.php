<?php
/**
 * @author sqrtcat 32448732@qq.com
 * @version 2019-9-29
 * 用户实体类
 */

namespace App;

use Protocol\Message;
use Protocol\Type;

/**
 * Class RegisterRequest
 * @package App
 */
class UserEntity extends Message
{
    public static $objectType = Type::TYPE_OBJECT;

    public $account;
    public $age;

    // 参数项位序 accountBin passwordBin ageBin
    public static $paramNameMapping = [
        0 => 'account',
        1 => 'age',
    ];

    // 参数类型
    public static $paramProtocolTypeMapping = [
        'account' => Type::TYPE_STRING,
        'age'     => Type::TYPE_TINYINT,
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
