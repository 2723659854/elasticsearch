<?php
require_once 'vendor/autoload.php';

/** 实例化客户端 elasticsearch链式操作演示 */
$client = new \Xiaosongshu\Elasticsearch\ESClient(
    [
        'nodes' => ['127.0.0.1:9200'],
        'username' => '',
        'password' => '',
    ]
);


$result = $client->table('index','_doc')->getAll();

var_dump($result);
