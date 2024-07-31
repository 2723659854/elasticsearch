<?php
require_once 'vendor/autoload.php';

/** 实例化客户端 */
$client = new \Xiaosongshu\Elasticsearch\ESClient([
    /** 节点列表 */
    'nodes' => ['192.168.101.170:9200'],
    /** 用户名 */
    'username' => '',
    /** 密码 */
    'password' => '',
]);
///** 删除索引 */
//$client->deleteIndex('index');
///** 如果不存在index索引，则创建index索引 */
//if (!$client->IndexExists('index')) {
//    /** 创建索引 */
//    $client->createIndex('index', '_doc');
//}
//
///** 创建表 */
//$result = $client->createMappings('index', '_doc', [
//    'id' => ['type' => 'long',],
//    'title' => ['type' => 'text', "fielddata" => true,],
//    'content' => ['type' => 'text', 'fielddata' => true],
//    'create_time' => ['type' => 'text'],
//    'test_a' => ["type" => "integer"],
//    'test_b' => ["type" => "rank_feature", "positive_score_impact" => false],
//    'test_c' => ["type" => "rank_feature"],
//]);
/** 获取数据库所有数据 */
//$result = $client->all('index','_doc',0,15);


$res = $client->searchByWhere('index','_doc',[['title','=','测试'],['content','=','张三'],['title','>',8]]);
var_dump($res);