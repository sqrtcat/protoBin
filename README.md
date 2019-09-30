# protoBin
a binary protocol pack text data to binary / unpack binary data to text, simple and efficent
一套使用 php 实现的基于二进制协议的数据序列化/反序列化库，尽可能的将数据压缩，将数据打包至二进制，从二进制解包数据，简单高效。

## 数据类型支持

支持标量 对象 数组 灵活组合各种数据类型场景

char/byte int16 int32 int64 string text array longtext object bigObject

```php
// 定长类数据 参数位固定
const TYPE_TINYINT = 'tinyint';// 1byte paramLen
const TYPE_INT16   = 'int16';// 2bytes paramLen
const TYPE_INT32   = 'int32';// 4bytes paramLen
const TYPE_INT64   = 'int64';// 8bytes paramLen
const TYPE_BOOL    = 'bool'; // 1byte paramLen
const TYPE_FLOAT   = 'float';// 4byte paramLen
const TYPE_DOUBLE  = 'double';// 8byte paramLen

// 变长类数据 固定长度位 数据位变长
const TYPE_STRING     = 'string';// 1bytes length and 0 ~ 255 bytes data
const TYPE_TEXT       = 'text'; // 2bytes length and 0 ~ 2^16 - 1 bytes data
const TYPE_LONG_TEXT  = 'longText'; // 4bytes length and 0 ~ 2^32 - 1 bytes data
const TYPE_REPEATED   = 'repeated'; // mixed[] 8bytes length and 0 ~ 2^64 - 1bytes data
const TYPE_OBJECT     = 'object'; // 2bytes length and 0 ~ 2^16 - 1 bytes data
const TYPE_BIG_OBJECT = 'bigObject'; // 4bytes length and 0 ~ 2^32 - 1 bytes data
```

## 二进制传输协议
何为二进制传输协议？既然传输协议有二进制方式，那与之对应的方式是什么？

简单来说，二进制传输即根据协议约定，截取相应的字节段，获得相应数据的模式，比如最常用的 tcp 协议，它就是一套二进制协议，N ~ N+M 个字节代表什么，这都是 tcp 协议规定好的，我们不需要传递参数名，直接截取字节段，解析，按协议约定便得到了代表的数据项。

日常开发中我们是用的 json xml formData 都属于文本传输模式，数据具有自我描述性，发送端和接收端不需要事先约定协议，服务容器会帮我们自动解析，我们直接拿到的数据也都是很直接的，account=sqrtcat&password=123456。思考一下，既然是登陆请求，自然要发送账号和密码，那我为何还要传递参数名呢，直接传递两个参数值不就可以了，但账号密码可能是变长的，而后更全方位的考虑，比如账号如果是手机号，文本传输11bytes，打包至短整型只需要2bytes即可。

二进制传输数据体量并非绝对的小于文本传输，但把覆盖场景级适当的加密考虑进来，二进制便完胜了。

## 示例

### 注册请求消息体

继承 ProtoBin\Message，定义属性参数和参数类型

```php
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
    // 消息体包含的字段
    public $account;
    public $password;
    public $age;

    // 参数项位序
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
    //propertySetterGetterFuncCluster
}
```
### 发送/解析 对比

同文本协议做了下对比
```php
require_once __DIR__ . '/vendor/autoload.php';

// register data
$regData = [
    'account'  => 'sqrtcat',
    'password' => '123456',
    'age'      => 29,
];

// ----------------文本传输-------------------
// 文本表单传输
$dataForm = http_build_query($regData);
echo "dataForm:" . $dataForm . PHP_EOL;
echo "dataLen:" . strlen($dataForm) . PHP_EOL;
echo PHP_EOL;

// 文本json传输
$dataJson = json_encode($regData);
echo "dataJson:" . $dataJson . PHP_EOL;
echo "dataLen:" . strlen($dataJson) . PHP_EOL;
echo PHP_EOL;

// ----------------二进制序列化----------------

try {
    // 二进制协议 序列化
    $registerRequest = new \App\RegisterRequest();
    $registerRequest->setAccount('sqrtcat');
    $registerRequest->setPassword('123456');
    $registerRequest->setAge(29);
    $dataBin = $registerRequest->packToBinStream();
} catch (Exception $e) {
}

echo "dataBin: " . $dataBin . PHP_EOL;
echo "dataBin len: " . strlen($dataBin) . PHP_EOL;
echo PHP_EOL;

// binData send

// 二进制协议 反序列化
try {
    $registerRequest = new \App\RegisterRequest();
    $registerRequest->unpackFromBinStream($dataBin);
    echo $registerRequest->getAccount() . PHP_EOL;
    echo $registerRequest->getPassword() . PHP_EOL;
    echo $registerRequest->getAge() . PHP_EOL;
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
```
## 生产场景实例

比如返回一个用户列表消息体给客户端

### 用户实体类

```php
<?php
/**
 * @author sqrtcat 32448732@qq.com
 * @version 2019-9-29
 * 用户实体类
 */

namespace App;

use ProtoBin\Message;
use ProtoBin\Type;

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
    //propertySetterGetterFuncCluster
}

```
### 用户列表消息类
```php
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
        'err'       => Type::TYPE_TINYINT,
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
    //propertySetterGetterFuncCluster
}
```
### 打包发送
```php
// ---------- 生产场景 数据类型多元化 ---------------
$user = new \App\UserEntity();
$user->setAccount("sqrtcat");
$user->setAge(2);

$userResponse = new \App\UsersResponse();
$userResponse->setErr(98);// tinyint
$userResponse->setMsg("success");// string
$userResponse->setData([$user, $user, $user]); // objectArr
$userResponse->setIdArr([1, 2, 3]); // tinyintArr
$userResponse->setNameArr(["james", "sqrtcat", "lucy"]);//stringArr
$userResponse->setBoolArr([true, false, false]);//boolArr
$userResponse->setFloatArr([1.22, 2.33, 3.44]);// floatArr
$userResponse->setDoubleArr([1.222222222222222, 2.33333333333333, 3.4444444444444444]);
try {
    echo $dataJson = json_encode($userResponse) . PHP_EOL;
    echo "dataJson len: " . strlen($dataJson) . PHP_EOL . PHP_EOL;
    // 二进制协议序列化
    echo $dataBin = $userResponse->packToBinStream() . PHP_EOL;
    echo "dataBin len: " . strlen($dataBin) . PHP_EOL . PHP_EOL;
} catch (Exception $e) {
}

// binData send

try {
    $userResponse = new \App\UsersResponse();
    // 二进制协议反序列化
    $userResponse->unpackFromBinStream($dataBin);
    var_dump($userResponse->getMsg());
    var_dump($userResponse->getErr());
    var_dump($userResponse->getData());
    var_dump($userResponse->getIdArr());
    var_dump($userResponse->getNameArr());
    var_dump($userResponse->getBoolArr());
    var_dump($userResponse->getFloatArr());
    var_dump($userResponse->getDoubleArr());
} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
```
### 对比/结果
```bash
dataForm:account=sqrtcat&password=123456&age=29
dataLen:38

dataJson:{"account":"sqrtcat","password":"123456","age":29}
dataLen:50

dataBin: sqrtcat123456
dataBin len: 16

sqrtcat
123456
29
{"err":98,"msg":"success","data":[{"account":"sqrtcat","age":2},{"account":"sqrtcat","age":2},{"account":"sqrtcat","age":2}],"idArr":[1,2,3],"nameArr":["james","sqrtcat","lucy"],"boolArr":[true,false,false],"floatArr":[1.22,2.33,3.44],"doubleArr":[1.222222222222222,2.33333333333333,3.444444444444444]}
dataJson len: 304

bsuccess       ! 	sqrtcat 	sqrtcat 	sqrtcat                       jamessqrtcatlucy                �(�?�@�(\@       �8��8��?������@�8��8�@
dataBin len: 162

string(7) "success"
int(98)
array(3) {
  [0]=>
  object(App\UserEntity)#4 (3) {
    ["account"]=>
    string(7) "sqrtcat"
    ["age"]=>
    int(2)
    ["dataBinaryStream":protected]=>
    NULL
  }
  [1]=>
  object(App\UserEntity)#6 (3) {
    ["account"]=>
    string(7) "sqrtcat"
    ["age"]=>
    int(2)
    ["dataBinaryStream":protected]=>
    NULL
  }
  [2]=>
  object(App\UserEntity)#7 (3) {
    ["account"]=>
    string(7) "sqrtcat"
    ["age"]=>
    int(2)
    ["dataBinaryStream":protected]=>
    NULL
  }
}
array(3) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
}
array(3) {
  [0]=>
  string(5) "james"
  [1]=>
  string(7) "sqrtcat"
  [2]=>
  string(4) "lucy"
}
array(3) {
  [0]=>
  bool(true)
  [1]=>
  bool(false)
  [2]=>
  bool(false)
}
array(3) {
  [0]=>
  float(1.2200000286102)
  [1]=>
  float(2.3299999237061)
  [2]=>
  float(3.4400000572205)
}
array(3) {
  [0]=>
  float(1.2222222222222)
  [1]=>
  float(2.3333333333333)
  [2]=>
  float(3.4444444444444)
}
```
