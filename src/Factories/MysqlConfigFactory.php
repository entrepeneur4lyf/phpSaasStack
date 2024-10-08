<?php

namespace Src\Factories;

use Swoole\Database\MysqlConfig;

class MysqlConfigFactory
{
    public static function create(array $config): MysqlConfig
    {
        $mysqlConfig = new MysqlConfig();
        $mysqlConfig->withHost($config['host'])
            ->withPort($config['port'])
            ->withDbName($config['database'])
            ->withCharset($config['charset'])
            ->withUsername($config['username'])
            ->withPassword($config['password']);
        
        return $mysqlConfig;
    }
}
