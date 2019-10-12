<?php
/**
 * @author sqrtcat 32448732@qq.com
 * 尽可能多的演示了支持的数据类型
 */
namespace App;

use ProtoBin\Message;
use ProtoBin\Type;

/**
 * 集成协议消息类
 * 定义包含的数据项 public foo
 * 定义数据项的位序 指定就好 类似 protoBuf 的 string name = 1
 * 定义数据项的类型
 * 定义数组类型的数据项的成员类型  repeated string names = 2
 * 客户端/服务端引用同一套消息类
 * 打包 传输 解包
 * Class RegisterRequest
 * @package App
 * @method packToBinStream()
 * @method unpackFromBinStream($dataBin)
 */
class UsersResponse extends Message
{
    // 数据项
    public $err;
    public $msg;
    public $data;
    public $idArr;
    public $nameArr;
    public $boolArr;
    public $floatArr;
    public $doubleArr;

    // 数据项的位序
    public static $paramNameMapping = [
        0 => 'err',
        1 => 'msg',
        2 => 'data',
        3 => 'idArr',
        4 => 'nameArr',
        5 => 'boolArr',
        6 => 'floatArr',
        7 => 'doubleArr'
    ];

    // 数据项的类型
    public static $paramProtocolTypeMapping = [
        'err'       => Type::TYPE_INT16,
        'msg'       => Type::TYPE_STRING,
        'data'      => Type::TYPE_REPEATED,
        'idArr'     => Type::TYPE_REPEATED,
        'nameArr'   => Type::TYPE_REPEATED,
        'boolArr'   => Type::TYPE_REPEATED,
        'floatArr'  => Type::TYPE_REPEATED,
        'doubleArr' => Type::TYPE_REPEATED
    ];

    /**
     * 重复类型数据项成员的类型
     * @var array
     */
    public static $paramRepeatedTypeSymbol = [
        'data'      => UserEntity::class, //实体类
        'idArr'     => Type::TYPE_INT32, //标量
        'nameArr'   => Type::TYPE_STRING,
        'boolArr'   => Type::TYPE_BOOL,
        'floatArr'  => Type::TYPE_FLOAT,
        'doubleArr' => Type::TYPE_DOUBLE
    ];

    /**
     * @return mixed
     */
    public function getErr()
    {
        return $this->err;
    }

    /**
     * @param mixed $err
     */
    public function setErr($err)
    {
        $this->err = $err;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getIdArr()
    {
        return $this->idArr;
    }

    /**
     * @param mixed $idArr
     */
    public function setIdArr($idArr)
    {
        $this->idArr = $idArr;
    }

    /**
     * @return mixed
     */
    public function getNameArr()
    {
        return $this->nameArr;
    }

    /**
     * @param mixed $nameArr
     */
    public function setNameArr($nameArr)
    {
        $this->nameArr = $nameArr;
    }

    /**
     * @return mixed
     */
    public function getBoolArr()
    {
        return $this->boolArr;
    }

    /**
     * @param mixed $boolArr
     */
    public function setBoolArr($boolArr)
    {
        $this->boolArr = $boolArr;
    }

    /**
     * @return mixed
     */
    public function getFloatArr()
    {
        return $this->floatArr;
    }

    /**
     * @param mixed $floatArr
     */
    public function setFloatArr($floatArr)
    {
        $this->floatArr = $floatArr;
    }

    /**
     * @return mixed
     */
    public function getDoubleArr()
    {
        return $this->doubleArr;
    }

    /**
     * @param mixed $doubleArr
     */
    public function setDoubleArr($doubleArr)
    {
        $this->doubleArr = $doubleArr;
    }
}
