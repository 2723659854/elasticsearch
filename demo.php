<?php
require_once 'vendor/autoload.php';

/** 实例化客户端 elasticsearch链式操作演示 */
$client = new \Xiaosongshu\Elasticsearch\ESClient([
    /** 节点列表 */
    'nodes' => ['127.0.0.1:9200'],
    /** 用户名 */
    'username' => '',
    /** 密码 */
    'password' => '',
]);


var_dump($response = $client->createIndex('index4','type'));


