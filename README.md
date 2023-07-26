## elasticsearch 客户端

###安装方法
```bash 
composer require xiaosongshu/elasticsearch
```
### 基本配置
 支持thinkPHP，laravel，webman等常用框架，需要创建配置文件elasticsearch.php ，放到config/目录下。
 配置内容如下所示：
 ```php 
 return [
    /** 节点列表 */
    'nodes' => ['127.0.0.1:9200'],
    /** 用户名 */
    'username'=>'',
    /** 密码 */
    'password'=>'',
];
 ```
###基本用法
实例化客户端
```php 
$client = new \Xiaosongshu\Elasticsearch\ESClient();
```
### 普通查询方法
```php 
$client = new \Xiaosongshu\Elasticsearch\ESClient();
$result = $client->search(
    'index',
    '_doc',
    'nickname',
    'fool',
    ['term' => ['userid' => 123]],
    0,
    10,
    ['_id' => 'desc'], [
    'hits.hits._source',
    'hits.total',
]);
```
### 客户端支持的所有方法
~~~
创建索引：createIndex
创建表结构：createMappings
删除索引：deleteIndex
获取索引详情：getIndex
新增一行数据：create
批量写入数据：insert
根据id批量删除数据：deleteMultipleByIds
根据Id 删除一条记录：deleteById
获取表结构：getMap
根据id查询数据：find
根据某一个关键字搜索：search
使用原生方式查询es的数据：nativeQuerySearch
多个字段并列查询，多个字段同时满足需要查询的值：andSearch
or查询  多字段或者查询：orSearch
根据条件删除数据：deleteByQuery
根据权重查询：searchByRank
获取所有数据：all
添加脚本：addScript
获取脚本：getScript
使用脚本查询：searchByScript
使用脚本更新文档：updateByScript
索引是否存在：IndexExists
根据id更新数据：updateById
~~~
### 如果单独使用本插件，则需要实例化的时候传入elasticsearch的连接配置

##### elasticsearch客户端使用实例
```php 
<?php
require_once 'vendor/autoload.php';

/** 实例化客户端 */
$client = new \Xiaosongshu\Elasticsearch\ESClient([
    /** 节点列表 */
    'nodes' => ['192.168.4.128:9200'],
    /** 用户名 */
    'username' => '',
    /** 密码 */
    'password' => '',
]);
/** 删除索引 */
$client->deleteIndex('index');
/** 如果不存在index索引，则创建index索引 */
if (!$client->IndexExists('index')) {
    /** 创建索引 */
    $client->createIndex('index', '_doc');
}

/** 创建表 */
$result = $client->createMappings('index', '_doc', [
    'id' => ['type' => 'long',],
    'title' => ['type' => 'text', "fielddata" => true,],
    'content' => ['type' => 'text', 'fielddata' => true],
    'create_time' => ['type' => 'text'],
    'test_a' => ["type" => "rank_feature"],
    'test_b' => ["type" => "rank_feature", "positive_score_impact" => false],
    'test_c' => ["type" => "rank_feature"],
]);
/** 获取数据库所有数据 */
$result = $client->all('index','_doc',0,15);

/** 写入单条数据 */
$result = $client->create('index', '_doc', [
    'id' => rand(1,99999),
    'title' => '我只是一个测试呢',
    'content' => '123456789',
    'create_time' => date('Y-m-d H:i:s'),
    'test_a' => 1,
    'test_b' => 2,
    'test_c' => 3,
]);
/** 批量写入数据 */
$result = $client->insert('index','_doc',[
    [
        'id' => rand(1,99999),
        'title' => '我只是一个测试呢',
        'content' => '你说什么',
        'create_time' => date('Y-m-d H:i:s'),
        'test_a' => rand(1,10),
        'test_b' => rand(1,10),
        'test_c' => rand(1,10),
    ],
    [
        'id' => rand(1,99999),
        'title' => '我只是一个测试呢',
        'content' => '你说什么',
        'create_time' => date('Y-m-d H:i:s'),
        'test_a' => rand(1,10),
        'test_b' => rand(1,10),
        'test_c' => rand(1,10),
    ],
    [
        'id' => rand(1,99999),
        'title' => '我只是一个测试呢',
        'content' => '你说什么',
        'create_time' => date('Y-m-d H:i:s'),
        'test_a' => rand(1,10),
        'test_b' => rand(1,10),
        'test_c' => rand(1,10),
    ],
]);
/** 使用关键字搜索 */
$result = $client->search('index','_doc','title','测试')['hits']['hits'];

/** 使用id更新数据 */
$result1 = $client->updateById('index','_doc',$result[0]['_id'],['content'=>'今天你测试了吗']);
/** 使用id 删除数据 */
$result = $client->deleteById('index','_doc',$result[0]['_id']);
/** 使用条件删除 */
$client->deleteByQuery('index','_doc','title','测试');
/** 使用关键字搜索 */
$result = $client->search('index','_doc','title','测试')['hits']['hits'];
/** 使用条件更新 */
$result = $client->updateByQuery('index','_doc','title','测试',['content'=>'哇了个哇，这么大的种子，这么大的花']);
/** 添加脚本 */
$result = $client->addScript('update_content',"doc['content'].value+'_'+'谁不说按家乡好'");
/** 添加脚本 */
$result = $client->addScript('update_content2',"(doc['content'].value)+'_'+'abcdefg'");
/** 获取脚本内容 */
$result = $client->getScript('update_content');
/** 使用脚本搜索 */
$result = $client->searchByScript('index', '_doc', 'update_content', 'title', '测试');
/** 删除脚本*/
$result = $client->deleteScript('update_content2');
/** 使用id查询 */
$result = $client->find('index','_doc','7fitkYkBktWURd5Uqckg');
/** 原生查询 */
$result = $client->nativeQuerySearch('index',[
    'query'=>[
        'bool'=>[
            'must'=>[
                [
                    'match_phrase'=>[
                        'title'=>'测试'
                    ],
                ],
                [
                    'script'=>[
                        'script'=>"doc['content'].value.length()>2"
                    ]
                ]
            ]
        ]
    ]

]);
/** and并且查询 */
$result = $client->andSearch('index','_doc',['title','content'],'测试');
/** or或者查询 */
$result = $client->orSearch('index','_doc',['title','content'],'今天');

```

### 联系作者
2723659854@qq.com

