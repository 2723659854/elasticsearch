<?php

namespace Xiaosongshu\Elasticsearch;

/**
 * @purpose 扩展安装器
 * @author yanglong
 * @time 2025年11月10日18:19:25
 */
class Install
{

    /**
     * 安装配置
     * @return void
     */
    public static function install()
    {
        echo "安装扩展xiaosongshu/elasticsearch\r\n";
        $scriptPath = __DIR__ . '/../scripts/install.php';
        if (file_exists($scriptPath)) {
            require $scriptPath;
        } else {
            echo "Install script not found: " . $scriptPath . "\n";
        }
    }

    /**
     * 卸载配置
     * @return void
     */
    public static function uninstall()
    {
        echo "卸载扩展xiaosongshu/elasticsearch\r\n";
        $scriptPath = __DIR__ . '/../scripts/uninstall.php';
        if (file_exists($scriptPath)) {
            require $scriptPath;
        } else {
            echo "Uninstall script not found: " . $scriptPath . "\n";
        }
    }
}


//Installer::uninstall();