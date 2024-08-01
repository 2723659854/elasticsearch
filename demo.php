<?php
require_once 'vendor/autoload.php';

/** 实例化客户端 elasticsearch链式操作演示 */
$client = new \Xiaosongshu\Elasticsearch\ESClient([
    /** 节点列表 */
    'nodes' => ['192.168.101.170:9200'],
    /** 用户名 */
    'username' => '',
    /** 密码 */
    'password' => '',
]);

/** 查询数据链式操作 */
$result = $client
    /** 设置表名 */
    ->table('index','_doc')
    /** must限制条件 */
    ->where(['title','=','测试'])
    /** should限制条件 当must和should冲突的时候，must条件优先 */
    ->orWhere(['test_a','>',8])
    /** 排序 */
    ->orderBy('test_a','asc')
    /** 分页 */
    ->limit(0,10)
    /** 筛选查询字段 */
    ->select(['name'])
    /** 执行查询操作 */
    ->getAll();

/** 根据条件更新所有数据链式操作 */
$result = $client->table('index','_doc')->where(['test_a','>',2])->updateAll(['name'=>'陈圆圆']);
/** 根据条件删除数据链式操作 */
$result = $client->table('index','_doc')->where(['test_a','>',2])->deleteAll();
/** 插入数据链式操作 */
$result = $client->table('index','_doc')->insertAll([[
    'id' => rand(1,99999),
    'title' => '我只是一个测试呢',
    'content' => '你说什么',
    'create_time' => date('Y-m-d H:i:s'),
    'test_a' => rand(1,10),
    'test_b' => rand(1,10),
    'test_c' => rand(1,10),
]]);
/** 调用elasticsearch原生方法 */
$params = [
    'index' => 'my_index',
    'type' =>"_doc",
    'id' => "demo",
];
/** 使用原生方法统计满足某条件的数据 */
$result = $client->count($params);
/** 使用原生方法判断是否存在某一条数据 */
$result = $client->exists($params);

/** 打印处理 结果*/
var_dump($result);