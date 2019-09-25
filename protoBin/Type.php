<?php
/**
 * @author sqrtcat 32448732@qq.com
 * @version 2019-9-29
 * 协议支持的数据类型
 */

namespace ProtoBin;

/**
 * 协议数据类型
 * //|    参数位1(变长数据)      |  参数位2(定长类型) |      参数位3(变长数据)     |
 * //| param1Len | param1Data |    param3Data    |  param3Len | param3Data  |
 */
class Type
{
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

    // 数据类型
    const TYPE_SYMBOL = [
        self::TYPE_TINYINT,
        self::TYPE_INT16,
        self::TYPE_INT32,
        self::TYPE_INT64,
        self::TYPE_BOOL,
        self::TYPE_FLOAT,
        self::TYPE_DOUBLE,
        self::TYPE_STRING,
        self::TYPE_TEXT,
        self::TYPE_LONG_TEXT,
        self::TYPE_REPEATED,
        self::TYPE_OBJECT,
        self::TYPE_BIG_OBJECT
    ];

    /**
     * 数据类型是否为定长
     */
    const TYPE_FIXED_LEN = [
        self::TYPE_TINYINT    => true,
        self::TYPE_INT16      => true,
        self::TYPE_INT32      => true,
        self::TYPE_INT64      => true,
        self::TYPE_BOOL       => true,
        self::TYPE_FLOAT      => true,
        self::TYPE_DOUBLE     => true,
        self::TYPE_STRING     => false,
        self::TYPE_TEXT       => false,
        self::TYPE_LONG_TEXT  => false,
        self::TYPE_REPEATED   => false,
        self::TYPE_OBJECT     => false,
        self::TYPE_BIG_OBJECT => false
    ];

    // 定长数据类型的字节数 paramBytes = dataBytes
    const TYPE_FIXED_LEN_BYTES = [
        self::TYPE_TINYINT => 1, // tinyint 固定1字节 不需要长度表征 追求极致
        self::TYPE_INT16   => 2, // int16 固定2字节 不需要长度表征 追求极致
        self::TYPE_INT32   => 4, // int32 固定4字节 不需要长度表征 追求极致
        self::TYPE_INT64   => 8, // int64 固定8字节 不需要长度表征 追求极致
        self::TYPE_BOOL    => 1, // bool 固定1字节 不需要长度表征 追求极致
        self::TYPE_FLOAT   => 4, // float 固定4字节 不需要长度表征 追求极致
        self::TYPE_DOUBLE  => 8, // double 固定8字节 不需要长度表征 追求极致
    ];

    /**
     * 变长数据类型长度位字节数 paramBytes = dataLenBytes . dataBytes
     */
    const TYPE_VARIABLE_LEN_BYTES = [
        self::TYPE_STRING     => 1, // string 用 1bytes 表征数据长度 0 ~ 255 个字符长度
        self::TYPE_TEXT       => 2, // text 用 2bytes 表征数据长度 能表征 2 ^ 32 - 1个字符长度 1PB的数据 噗
        self::TYPE_LONG_TEXT  => 4, // longText 用 4bytes 表征数据长度 能表征 2 ^ 32 - 1个字符长度 1PB的数据 噗
        self::TYPE_REPEATED   => 8, // repeated 用 8bytes 表征数据长度 能表征 2 ^ 32 - 1个字符长度 1PB的数据 噗
        self::TYPE_OBJECT     => 2, // object 用 2bytes 表征数据长度 能表征 2 ^ 16 - 1个字符长度 1PB的数据 噗
        self::TYPE_BIG_OBJECT => 4, // bigObject 用 4bytes 表征数据长度 能表征 2 ^ 32 - 1个字符长度 1PB的数据 噗
    ];

    /**
     * 数据类型对应的打包方式
     */
    const TYPE_PACK_SYMBOL = [
        self::TYPE_TINYINT    => 'C', // tinyint 固定1字节 不需要长度表征 追求极致 无符号字节
        self::TYPE_INT16      => 'n', // int16 固定2字节 不需要长度表征 追求极致 大端无符号短整形
        self::TYPE_INT32      => 'N', // int32 固定4字节 不需要长度表征 追求极致 大端无符号整形
        self::TYPE_INT64      => 'J', // int64 固定8字节 不需要长度表征 追求极致 大端无符号长整形
        self::TYPE_BOOL       => 'C', // bool 固定1字节 不需要长度表征 追求极致 无符号字节
        self::TYPE_FLOAT      => 'f', // float 固定4字节 不需要长度表征 追求极致 无符号字节
        self::TYPE_DOUBLE     => 'd', // double 固定8字节 不需要长度表征 追求极致 无符号字节
        self::TYPE_STRING     => 'C', // string 用 1bytes 表征数据长度 0 ~ 255 个字符长度
        self::TYPE_TEXT       => 'n', // text 用 2bytes 表征数据长度 能表征 2 ^ 16 - 1个字符长度 64KB 的数据 噗
        self::TYPE_LONG_TEXT  => 'N', // longText 用 4bytes 表征数据长度 能表征 2 ^ 32 - 1个字符长度 4GB 的数据 噗
        self::TYPE_REPEATED   => 'J', // repeated 用 8bytes 表征数据长度 能表征 2 ^ 64 - 1个字符长度
        self::TYPE_OBJECT     => 'n', // object 用 2bytes 表征数据长度 能表征 2 ^ 16 - 1个字符长度
        self::TYPE_BIG_OBJECT => 'N', // bigObject 用 4bytes 表征数据长度 能表征 2 ^ 32 - 1个字符长度
    ];

    /**
     * 是否为定长类型
     * @param  [type]  $type [description]
     * @return boolean       [description]
     */
    public static function isFixedLenType($type)
    {
        return self::TYPE_FIXED_LEN[$type];
    }

    /**
     * 定长获得字节数
     * 变长获得数据长度为字节数
     * @param  [type] $type [description]
     * @return mixed [type]       [description]
     */
    public static function getTypeOrTypeLenBytes($type)
    {
        if (self::isFixedLenType($type)) {
            return self::TYPE_FIXED_LEN_BYTES[$type];
        } else {
            return self::TYPE_VARIABLE_LEN_BYTES[$type];
        }
    }
}