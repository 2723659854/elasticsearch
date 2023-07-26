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
根据id更新数据：update
~~~
### 如果单独使用本插件，则需要实例化的时候传入elasticsearch的连接配置



