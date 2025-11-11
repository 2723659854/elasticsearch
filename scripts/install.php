<?php

# # 在用户项目根目录执行
# php vendor/xiaosongshu/elasticsearch/scripts/install.php
// 1. 推导路径（关键：适配用户项目结构）
$scriptPath = realpath(__FILE__); // 当前脚本绝对路径
$libraryPath = dirname($scriptPath,2); // 库根目录
$projectRoot = dirname($libraryPath,2) . '/..'; // 项目根目录
$sourceConfig = $libraryPath . '/config/elasticsearch.php'; // 源配置文件
$targetDir = $projectRoot . '/config'; // 目标配置目录（用户项目的 config 目录）
$targetConfig = $targetDir . '/elasticsearch.php'; // 目标文件
// 2. 检查源文件是否存在
if (!file_exists($sourceConfig)) {
    fwrite(STDERR, "错误：配置文件不存在 -> {$sourceConfig}\n");
    return;
}

// 3. 创建目标目录（不存在则创建）
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// 4. 复制文件（避免覆盖用户已修改的配置）
if (file_exists($targetConfig)) {
    fwrite(STDOUT, "提示：配置文件已存在，跳过复制 -> {$targetConfig}\n");
    return;
}

// 5. 执行复制
if (copy($sourceConfig, $targetConfig)) {
    fwrite(STDOUT, "成功：配置文件已自动复制到 -> {$targetConfig}\n");
    return;
} else {
    fwrite(STDERR, "错误：配置文件复制失败 -> {$targetConfig}\n");
    return;
}