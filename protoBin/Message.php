<?php
/**
 * @author sqrtcat 3244873@qq.com
 * @version 2019-9-29
 * 协议消息类
 */
namespace ProtoBin;

use Exception;
use ReflectionException;
use stdClass;

/**
 * 协议消息打包解包
 */
abstract class Message
{
    /**
     * 作为repeated成员时标识自身的类体量
     * object    length bytes 2
     * bigObject length bytes 4
     * @var string
     */
    protected static $objectType = Type::TYPE_OBJECT;

    /**
     * 二进制协议流
     * @var [type]
     */
    protected $dataBinaryStream;

    /**
     * [paramName1, paramName2, paramName3]
     * @var array
     */
    protected static $paramNameMapping = [];

    /**
     * 参数类型定义
     * paramName => Type
     * @var array
     */
    protected static $paramProtocolTypeMapping = [];

    /**
     * 如果是 repeated 类型的参数
     * 需映射出相应的实体类名
     * [
     *     paramName1 => entityClassName,
     *     paramName2 => scalar,
     * ]
     * @var array
     */
    protected static $paramRepeatedTypeSymbol = [];

    /**
     * 获取参数的协议数据类型
     * @param  [type] $param [description]
     * @return mixed [type] [description]
     */
    public static function getParamType($param)
    {
        return static::$paramProtocolTypeMapping[$param];
    }

    /**
     * 按参数位序依次打包
     * @return string [type] [description]
     * @throws Exception
     */
    public function packToBinStream()
    {
        $this->dataBinaryStream = "";
        // 按参数位序
        foreach (static::$paramNameMapping as $key => $paramName) {
            $paramType                  = static::getParamType($paramName);
            $this->{$paramName . 'Bin'} = self::pack($paramName, $this->{$paramName}, $paramType);
            $this->dataBinaryStream     .= $this->{$paramName . 'Bin'};
        }

        return $this->dataBinaryStream;
    }

    /**
     * 按参数位序依次解包
     * @param  [type] $dataBin [description]
     * @return void [type] [description]
     * @throws ReflectionException
     */
    public function unpackFromBinStream($dataBin)
    {
        foreach (static::$paramNameMapping as $key => $paramName) {
            $paramType          = static::getParamType($paramName);
            $this->{$paramName} = self::unPack(
                $dataBin,
                $paramName,
                $paramType,
                static::$paramRepeatedTypeSymbol
            );
        }
    }

    /**
     * 打包二进制数据
     * @param string $paramName 参数名
     * @param string|stdClass[] $paramData 参数数据
     * @param string $paramType 参数协议类型
     * @return false|string [type]            [description]
     * @throws Exception
     */
    protected static function pack($paramName, $paramData, $paramType)
    {
        $packSymbol = Type::TYPE_PACK_SYMBOL[$paramType];

        if (Type::isFixedLenType($paramType)) {
            // 定长类型 直接打包数据至相应的二进制
            if (Type::TYPE_BOOL == $paramType) {
                // 转化下bool类型
                $paramData = false == $paramData ? 0 : 1;
            }
            $paramProtocolDataBin = pack($packSymbol, $paramData);
        } else {
            if (Type::TYPE_REPEATED == $paramType) {
                if (empty($paramData)) {
                    // 变长类型 数据长度位 + 数据位
                    return $paramProtocolDataBin = pack($packSymbol, 0);
                }
                // 对象类型的重复数据
                if (is_object($paramData[0])) {
                    $paramProtocolDataBin = static::packRepeatedClassEntity($paramData, $packSymbol);
                } else {
                    // 标量类型数据
                    $paramProtocolDataBin = static::packRepeatedScalar($paramName, $paramData, $packSymbol);
                }
            } else {
                // 变长类型 数据长度位 + 数据位
                $paramProtocolDataBin = pack($packSymbol, strlen($paramData)) . $paramData;
            }
        }
        return $paramProtocolDataBin;
    }

    /**
     * 解包二进制数据
     * @param string $dataBin 二进制数据
     * @param string $paramName 参数名称
     * @param string $paramType 参数类型
     * @param array $paramsRepeatedTypeSymbol repeated 字段的元素类型
     * @return array|bool|string
     * @throws ReflectionException
     * @throws Exception
     */
    protected static function unPack(&$dataBin, $paramName, $paramType, $paramsRepeatedTypeSymbol)
    {
        $packSymbol = Type::TYPE_PACK_SYMBOL[$paramType];

        // 定长数据直接读取对应的字节数解包
        if (Type::isFixedLenType($paramType)) {
            // 参数的字节数
            $paramBytes = Type::TYPE_FIXED_LEN_BYTES[$paramType];
            $paramBin   = substr($dataBin, 0, $paramBytes);
            // 定长类型 直接打包数据至相应的二进制
            $paramData = unpack($packSymbol, $paramBin)[1];
            // bool 类型的做相应转换
            if (Type::TYPE_BOOL == $paramType) {
                $paramData = 0 == $paramData ? false : true;
            }
        } else {
            // 类型的长度位字节数
            $typeLenBytes = Type::TYPE_VARIABLE_LEN_BYTES[$paramType];
            // 数据长度位
            $paramLenBytes = substr($dataBin, 0, $typeLenBytes);
            // 解析二进制的数据长度
            $paramDataLen = unpack($packSymbol, $paramLenBytes)[1];
            // 读取变长的数据内容
            $paramData = substr($dataBin, $typeLenBytes, $paramDataLen);

            // repeated类型数据解析
            if (Type::TYPE_REPEATED == $paramType) {
                // 数据字段为空 返回空数组
                if (0 == strlen($paramData)) {
                    return [];
                }
                $repeatedDataBin = $paramData;
                $repeatedDataArr = [];

                $paramType = $paramsRepeatedTypeSymbol[$paramName];
                if (in_array($paramType, Type::TYPE_SYMBOL)) {
                    // 标量解析
                    self::unPackRepeatedScalar(
                        $repeatedDataBin,
                        $paramName,
                        $paramType,
                        $repeatedDataArr
                    );

                    $paramData = $repeatedDataArr;
                    unset($repeatedDataArr);
                } else {
                    /* @var $paramClassName stdClass */
                    $entityClassName = $paramType;
                    // 实体对象类类型 object bigObject 来决定长度位字节数
                    $entityObjectType = (new \ReflectionClass($entityClassName))->getStaticPropertyValue('objectType');
                    if (!$entityObjectType) {
                        throw new Exception("entity should define static objectType property!", 400);
                    }
                    // 对象的长度位字节数
                    $entityLenBytes = Type::TYPE_VARIABLE_LEN_BYTES[$entityObjectType];// object bigObject
                    // 对象的长度位的解包方式
                    $entityLenUnpackSymbol = Type::TYPE_PACK_SYMBOL[$entityObjectType];// object bigObject

                    self::unPackRepeatedEntity(
                        $entityClassName,
                        $entityLenBytes,
                        $entityLenUnpackSymbol,
                        $repeatedDataBin,
                        $repeatedDataArr
                    );

                    $paramData = $repeatedDataArr;
                    unset($repeatedDataArr);
                }

            }
            // 参数项的总字节数
            $paramBytes = $typeLenBytes + $paramDataLen;
        }

        // 剩余待处理的数据
        $dataBin = substr($dataBin, $paramBytes);

        return $paramData;
    }

    /**
     * 打包repeated entity
     * @param $entityArr
     * @param $packSymbol
     * @return string
     * @throws Exception
     */
    protected static function packRepeatedClassEntity($entityArr, $packSymbol)
    {
        $tempBinStream = '';
        foreach ($entityArr as $entity) {
            if (!$entity instanceof Message) {
                throw new Exception(
                    'data should be extends ' . Message::class,
                    400
                );
            }
            $entityBinStream = $entity->packToBinStream();
            // 对象的打包类型
            $entityPackSymbol = Type::TYPE_PACK_SYMBOL[$entity::$objectType];
            $tempBinStream    .= pack($entityPackSymbol, strlen($entityBinStream)) . $entityBinStream;
        }
        //|--totalLen--|--e1Len--|--e1Data|--e2Len|--e2Data--|
        $paramProtocolDataBin = pack($packSymbol, strlen($tempBinStream)) . $tempBinStream;
        return $paramProtocolDataBin;
    }

    /**
     * 打包repeated scalar
     * @param $paramName
     * @param $scalarArr
     * @param $packSymbol
     * @return string
     * @throws Exception
     */
    protected static function packRepeatedScalar($paramName, $scalarArr, $packSymbol)
    {
        $tempBinStream = '';
        foreach ($scalarArr as $scalar) {
            $scalarArrType   = static::$paramRepeatedTypeSymbol[$paramName];
            $scalarBinStream = static::pack($paramName, $scalar, $scalarArrType);
            $tempBinStream   .= $scalarBinStream;
        }
        //|--totalLen--|--e1Len--|--e1Data|--e2Len|--e2Data--|
        $paramProtocolDataBin = pack($packSymbol, strlen($tempBinStream)) . $tempBinStream;
        return $paramProtocolDataBin;
    }

    /**
     * 解析可重复对象
     * @param $entityClassName
     * @param $entityLenBytes
     * @param $entityLenUnpackSymbol
     * @param string $repeatedEntityBinData 重复对象二进制流数据
     * @param array $repeatedDataArr 引用类型 存放迭代解包出来的实体类数组
     * @return bool
     * @throws ReflectionException
     */
    protected static function unPackRepeatedEntity(
        $entityClassName,
        $entityLenBytes,
        $entityLenUnpackSymbol,
        $repeatedEntityBinData,
        &$repeatedDataArr
    )
    {
        // 数据解析完成 直接返回
        if (0 == strlen($repeatedEntityBinData)) {
            return true;
        }

        $entityDataLen = unpack($entityLenUnpackSymbol, substr($repeatedEntityBinData, 0, $entityLenBytes))[1];
        $entityData    = substr($repeatedEntityBinData, $entityLenBytes, $entityDataLen);

        /* @var Message $protocolMessageInstance */
        $protocolMessageInstance = new $entityClassName;
        $protocolMessageInstance->unpackFromBinStream($entityData);
        $repeatedDataArr[] = $protocolMessageInstance;
        // 参数项的总字节数
        $entityBytes = $entityLenBytes + $entityDataLen;
        // 剩余待处理的数据
        $repeatedEntityBinData = substr($repeatedEntityBinData, $entityBytes);

        self::unPackRepeatedEntity(
            $entityClassName,
            $entityLenBytes,
            $entityLenUnpackSymbol,
            $repeatedEntityBinData,
            $repeatedDataArr
        );

        return true;
    }

    /**
     * 解析可重复标量
     * @param string $repeatedScalarBinData 重复对象二进制流数据
     * @param int $paramType TYPE_INT TYPE_STRING
     * @param string $paramName
     * @param array $repeatedDataArr 引用类型 存放迭代解包出来的实体类数组
     * @return bool
     * @throws ReflectionException
     */
    protected static function unPackRepeatedScalar(
        $repeatedScalarBinData,
        $paramName,
        $paramType,
        &$repeatedDataArr
    )
    {
        // 数据解析完成 直接返回
        if (0 == strlen($repeatedScalarBinData)) {
            return true;
        }

        $repeatedDataArr[] = self::unPack($repeatedScalarBinData, $paramName, $paramType, []);

        if (0 != strlen($repeatedScalarBinData)) {
            self::unPackRepeatedScalar(
                $repeatedScalarBinData,
                $paramName,
                $paramType,
                $repeatedDataArr
            );
        }

        return true;
    }
}

