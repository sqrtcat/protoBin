<?php
/**
 * 手动撸一个二进制编码协议 就像 protobuf 一样
 * 嘛 二进制协议也大都如此
 */

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