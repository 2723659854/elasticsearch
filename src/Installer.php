<?php

namespace Xiaosongshu\Elasticsearch;

/**
 * @purpose 扩展安装器
 * @author yanglong
 * @time 2025年11月10日18:19:25
 */
class Installer
{

    /**
     * 安装配置
     * @return void
     */
    public static function install()
    {
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
        $scriptPath = __DIR__ . '/../scripts/uninstall.php';
        if (file_exists($scriptPath)) {
            require $scriptPath;
        } else {
            echo "Uninstall script not found: " . $scriptPath . "\n";
        }
    }
}