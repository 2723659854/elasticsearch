<?php

// 推导路径（与安装脚本一致，确保兼容自定义 vendor 目录）
$scriptPath = realpath(__FILE__);
$libraryRoot = dirname($scriptPath,2);
$projectRoot = dirname($libraryRoot,2) . '/..';
$targetConfigFile = $projectRoot . '/config/elasticsearch.php';

// 检查并删除配置文件
if (file_exists($targetConfigFile)) {
    if (unlink($targetConfigFile)) {
        echo "[成功] 已删除配置文件：{$targetConfigFile}\n";
    } else {
        echo "[警告] 配置文件删除失败（可能权限不足）：{$targetConfigFile}\n";
    }
} else {
    echo "[提示] 未找到配置文件，无需删除：{$targetConfigFile}\n";
}
