<?php
require_once 'vendor/autoload.php';

/** 实例化客户端 elasticsearch链式操作演示 */
$client = new \Xiaosongshu\Elasticsearch\ESClient(
    [
        'nodes' => [
            "192.168.110.72:9210",
//            "127.0.0.1:9210",
        ],
        'username' => 'elastic',
        'password' => 'elastic',
        'proxy' => [
            'client' => [
                'curl' => [
                    CURLOPT_PROXY => '', // 明确禁用代理
                    CURLOPT_PROXYUSERPWD => '', // 清除代理认证
                    CURLOPT_NOPROXY => '*' // 对所有主机禁用代理
                ]
            ]
        ]
    ]
);

/** 如果不存在index索引，则创建index索引 */
if (!$client->IndexExists('index')) {
    /** 创建索引 */
    $client->createIndex('index', '_doc');
    /** 创建表 */
    $result = $client->createMappings('index', '_doc', [
        'id' => ['type' => 'long',],
        'title' => ['type' => 'text', "fielddata" => true,],
        'content' => ['type' => 'text', 'fielddata' => true],
        'create_time' => ['type' => 'text'],
        'test_a' => ["type" => "integer"],
        'test_b' => ["type" => "rank_feature", "positive_score_impact" => false],
        'test_c' => ["type" => "rank_feature"],
        'name' => ['type' => 'text', "fielddata" => true,],
        'age' => ['type' => 'integer'],
        'sex' => ['type' => 'integer'],
    ]);
}
/** 批量插入数据链式操作 */
$result = $client->table('index', '_doc')->insertAll([
    [
        'id' => rand(1, 99999),
        'title' => '天有不测风云',
        'content' => '月有阴晴圆缺',
        'create_time' => date('Y-m-d H:i:s'),
        'test_a' => rand(1, 10),
        'test_b' => rand(1, 10),
        'test_c' => rand(1, 10),
        'name' => '张三',
        'age' => 27,
        'sex' => 1
    ]
]);

$result = $client->table('index', '_doc')->getAll();
print_r($result);


