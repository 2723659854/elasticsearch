<?php


namespace Xiaosongshu\Elasticsearch;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;

/**
 * elasticsearch 客户端
 * @purpose elasticsearch 客户端
 * @package ESClient
 * @example 此类可以当做模型的基类使用，需要把这个类里面的index和type分别改成$this->index和$this->type,然后创建新的模型继承这一个类，并在模型中设置index和type，nodes
 */
class ESClient
{

    /**  @var Client $client php的elasticsearch客户端 */
    public Client $client;
    /** @var array|string[] $nodes es服务器节点  */
    protected array $nodes=['127.0.0.1:9200'];

    public function __construct(array $elasticsearch_config=[])
    {
        /** 兼容各平台框架 ，支持单应用 */
        if (!function_exists('config')){
            $config = $elasticsearch_config;
        }else{
            $config = config('elasticsearch');
        }
        if (empty($config)){
            $config = $elasticsearch_config;
        }
        if (empty($config)){
            throw new \RuntimeException("请配置elasticsearch服务器连接数据");
        }
        /** 获取配置 */
        $nodes = !empty($config['nodes'])?$config['nodes']:$this->nodes;
        $esUserName = !empty($config['username'])?$config['username']:'';
        $esPassword = !empty($config['password'])?$config['password']:'';

        /** 创建客户端 */
        $client = ClientBuilder::create();
        /** 鉴权 */
        if(!empty($esUserName) && !empty($esPassword)){
            $client->setBasicAuthentication($esUserName,$esPassword);
        }
        /** 连接服务器节点 */
        $this->client = $client->setRetries(2)->setHosts($nodes)->build();
    }

    /**
     * 创建索引
     *
     * @param string $index 索引
     * @param string $type 类型
     * @return array
     */
    public function createIndex(string $index, string $type): array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => []
        ];
        return $this->client->index($params);
    }

    /**
     * 创建表结构
     * @param string $index 表名称
     * @param string $type 表类型
     * @param array $properties =[
     * 'id' => ['type' => 'long',],
     * 'title' => ['type' => 'text', "fielddata" => true,],
     * 'content' => ['type' => 'text',],
     * 'create_time' => ['type' => 'text'],
     * 'test_a' => ["type" => "rank_feature"],
     * 'test_b' => ["type" => "rank_feature", "positive_score_impact" => false],
     * 'test_c' => ["type" => "rank_feature"],
     * ] 表结构
     * @return array
     */
    public function createMappings(string $index, string $type, array $properties = []): array
    {
        $params = [
            'index'             => $index,
            'type'              => $type,
            'include_type_name' => true,
            'body'              => [
                'properties' => $properties
            ]
        ];
        return $this->client->indices()->putMapping($params);
    }

    /**
     * 删除索引
     * @param string $index 索引
     * @return array
     */
    public function deleteIndex(string $index): array
    {
        $params['index'] = $index;
        return $this->client->indices()->delete($params);
    }

    /**
     * 获取索引的详情
     * @param array $indexes =[] 获取索引详情，为空则获取所有索引的详情
     * @return array
     */
    public function getIndex(array $indexes): array
    {
        $params = [
            'index' => $indexes,
        ];
        return $this->client->indices()->getSettings($params);
    }

    /**
     * 插入数据
     * @param string $index 索引
     * @param string $type 类型
     * @param array $body =['key1'=>'value1', 'key2'=>'value2',]
     * @return array
     */
    public function create(string $index, string $type, array $body): array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => $body
        ];
        return $this->client->index($params);
    }

    /**
     * 批量写入数据
     * @param string $index 索引
     * @param string $type 类型
     * @param array $array =[
     *  ['key1'=>'value1', 'key2'=>'value2',],
     *  ['key1'=>'value1', 'key2'=>'value2',],
     * ] 需要插入的值
     * @return array
     */
    public function insert(string $index, string $type, array $array): array
    {
        $params = [];
        foreach ($array as $v) {
            $params['body'][] = [
                'index' => [
                    '_index' => $index,
                    '_type'  => $type,
                ]
            ];
            $params['body'][] = $v;
        }
        return $this->client->bulk($params);
    }

    /**
     * 根据id批量删除数据
     * @param string $index 索引
     * @param string $type 类型
     * @param array $ids 需要删除的所有记录的ID
     * @return array
     */
    public function deleteMultipleByIds(string $index, string $type, array $ids): array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
        ];
        foreach ($ids as $v) {
            $params ['body'][] = array(
                'delete' => array(
                    '_index' => $index,
                    '_type'  => $type,
                    '_id'    => $v
                )
            );
        }
        return $this->client->bulk($params);
    }

    /**
     * 根据Id 删除一条记录
     * @param string $index 索引
     * @param string $type 类型
     * @param string $id 需要删除的记录id
     * @return array|callable
     */
    public function deleteById(string $index, string $type, string $id): array
    {
        $param = [
            'index' => $index,
            'type'  => $type,
            'id'    => $id,
        ];
        return $this->client->delete($param);
    }

    /**
     * 获取表结构
     * @param array $index = [] 要获取的表的结构，为空则获取所有的表结构
     * @return array
     */
    public function getMap(array $index): array
    {
        $params = ['index' => $index];
        return $this->client->indices()->getMapping($params);
    }

    /**
     * 根据id查询数据
     * @param string $index 索引
     * @param string $type 类型
     * @param string $id id
     * @return array
     */
    public function find(string $index, string $type, string $id): array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'id'    => $id
        ];
        return $this->client->get($params);
    }

    /**
     * 根据某一个关键字搜索
     * @param string $index 索引
     * @param string $type 类型
     * @param string $key 筛选的字段
     * @param string $keywords 筛选的值
     * @param int $from 起始位置
     * @param int $size 查询条数
     * @param array $order 排序
     * @return array|callable
     */
    public function search(string $index, string $type, string $key, string $keywords,array $mustNot = [], int $from = 0, int $size = 10, array $order = ['_id' => 'desc'],array $filterPath = [])
    {
        $sort = [];
        if (!empty($order)) {
            foreach ($order as $k => $v) {
                $sort[] = [$k => ['order' => $v]];
            }
        }
        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query'     => [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    $key => [
                                        'query' => $keywords,
                                    ]
                                ]
                            ],
                        ],
                    ],
                ],
                'sort'      => $sort,
                'from'      => $from,
                'size'      => $size
            ]
        ];
        if(!empty($filterPath)){
            $params['filter_path'] = $filterPath;
        }
        if(!empty($mustNot)){
            $params['body']['query']['bool']['must_not'] = $mustNot;
        }
        return $this->client->search($params);
    }




    /**
     * Notes:使用原生方式查询es的数据
     * @param string $index 索引
     * @param array $body 查询的内容
     * @param array $filterPath =[ 'hits.hits._source', 'hits.total', ]需要过滤的参数
     * @return array
     */
    public function nativeQuerySearch(string $index,array $body,array $filterPath = []):array
    {
        $queryData = [
            'index'=>$index,
            'body'=>$body
        ];
        if($filterPath){
            $queryData['filter_path'] = $filterPath;
        }
        return $this->client->search($queryData);
    }


    /**
     * and 查询，并且查询
     * 多个字段并列查询，多个字段同时满足需要查询的值,相当于and
     * @param string $index
     * @param string $type
     * @param array $key
     * @param string $keywords
     * @param int $from
     * @param int $size
     * @param array $order
     * @return array|callable
     */
    public function andSearch(string $index, string $type, array $key, string $keywords, int $from = 0, int $size = 10, array $order = ['_id' => 'desc'])
    {
        $sort = [];
        if (!empty($order)) {
            foreach ($order as $k => $v) {
                $sort[] = [$k => ['order' => $v]];
            }
        }
        $match = [];
        foreach ($key as $field) {
            $match[] = [
                'match' => [
                    $field => $keywords
                ]
            ];
        }

        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query'     => [
                    'bool' => [
                        'must' => $match,
                    ],
                ],
                'sort'      => $sort,
                'from'      => $from,
                'size'      => $size,
//                'highlight' => [
//                    'fields'    => [
//                        'title' => [
//                            'type' => 'unified'
//                        ],
//                    ],
//                    'pre_tags'  => ["<font color='red'>"],
//                    "post_tags" => ["</font>"]
//                ],

            ]
        ];
        return $this->client->search($params);
    }


    /**
     * or 查询 或者查询
     * 根据多个字段查询，只要有一个字段复合要求，则返回记录
     * @param string $index
     * @param string $type
     * @param array $key
     * @param string $keywords
     * @param int $from
     * @param int $size
     * @param array $order
     * @return array|callable
     */
    public function orSearch(string $index, string $type, array $key, string $keywords, int $from = 0, int $size = 10, array $order = ['_id' => 'desc'])
    {
        $sort = [];
        if (!empty($order)) {
            foreach ($order as $k => $v) {
                $sort[] = [$k => ['order' => $v]];
            }
        }
        $match = [];
        foreach ($key as $field) {
            $match[] = [
                'match' => [
                    $field => $keywords
                ]
            ];
        }

        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query'     => [
                    'bool' => [
                        'should' => $match,
                    ],
                ],
                'sort'      => $sort,
                'from'      => $from,
                'size'      => $size,
//                'highlight' => [
//                    'fields'    => [
//                        'title' => [
//                            'type' => 'unified'
//                        ],
//                    ],
//                    'pre_tags'  => ["<font color='red'>"],
//                    "post_tags" => ["</font>"]
//                ],

            ]
        ];
        return $this->client->search($params);
    }


    /**
     * 多字段合并查询
     * 根据多个字段查询，使用多个字段查询，然后合并结果，
     * @param string $index
     * @param string $type
     * @param array $keys
     * @param string $keywords
     * @param int $from
     * @param int $size
     * @param array $order
     * @return array
     */
    public function mergeSearch(string $index, string $type, array $keys, string $keywords, int $from = 0, int $size = 10, array $order = ['_id' => 'desc']):array{
        if (empty($keys))return[];
        $result=[];
        foreach ($keys as $key){
            $tempData=$this->search($index,$type,$key,$keywords,[],$from,$size);
            $result=array_merge($result,$tempData['hits']['hits']);
        }
        return array_column($result,'_source');
    }

    /**
     * 根据条件删除数据
     * @param string $index 索引
     * @param string $type 类型
     * @param string $key 筛选条件的属性
     * @param string $val 筛选条件的值
     * @return array|callable
     */
    public function deleteByQuery(string $index, string $type, string $key, string $val):?array
    {
        $param = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query' => [
                    'match' => [
                        $key => $val
                    ]
                ]
            ]
        ];
        return $this->client->deleteByQuery($param);
    }

    /**
     * 使用条件更新数据
     * @param string $index
     * @param string $type
     * @param string $key
     * @param string $value
     * @param array $data = ['key1'=>'value1','key2'=>'value2']
     * @return array|null
     */
    public function updateByQuery(string $index, string $type, string $key,  $value,array $data):?array{
        return $this->updateByScript( $index,  $type,  $key, $value,  $data);
    }

    /**
     * 根据权重查询
     * @param string $index 索引
     * @param string $type 类型
     * @param string $key 要查询的字段
     * @param string $value 需要匹配的值
     * @param array $rank =[
     * 'key1'=>'boost1',
     * 'key2'=>'boost2',
     * ] 权重设置
     * @return array
     */
    public function searchByRank(string $index, string $type, string $key, string $value, array $rank = []): array
    {
        $feature = [];
        if (!empty($rank)) {
            foreach ($rank as $k => $v) {
                $feature[] = [
                    "rank_feature" => ['field' => $k, "boost" => $v]
                ];
            }
        }
        $param = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query' => [
                    'bool' => [
                        "must"   => [
                            'match' => [
                                $key => $value
                            ]
                        ],
                        'should' => $feature
                    ],
                ],
            ]
        ];
        return $this->client->search($param);
    }

    /**
     * 获取所有数据
     * @param string $index 索引
     * @param string $type 类型
     * @param int $from 起始位置
     * @param int $size 长度
     * @return array|callable
     * @note 不建议查询偏移量超过1000的数据。比如elasticsearch查询偏移量1000，查询10条数据，那么会在每一个分片查询1010条数据，然后返回给
     * 协调节点，如果有5个分片，那就是5050条数据。对于各个分片来说都是有很大压力的，偏移量越大，复杂度成指数级上升。
     */
    public function all(string $index, string $type, int $from = 0, int $size = 1000): ?array
    {
        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ],
            'from'  => $from,
            'size'  => $size,
        ];
        return $this->client->search($params);
    }

    /**
     * 添加脚本
     * @param string $id 脚本id
     * @param string $scriptContent 脚本内容（ "doc['title'].value+'_'+'谁不说按家乡好'" ）
     * @return array|callable
     * @example 操作字段有ctx和doc两种方法，并且不可频繁添加脚本，否则es一直编译脚本，负担过重会抛出异常
     */
    public function addScript(string $id, string $scriptContent):? array
    {
        $params = [
            'id'   => $id,
            'body' => [
                'script' => [
                    'lang'   => 'painless',
                    'source' => $scriptContent
                ]
            ]
        ];

        return $this->client->putScript($params);
    }

    /**
     * 删除脚本
     * @param string $id
     * @return bool
     */
    public function deleteScript(string $id):bool{
        try {
             $this->client->deleteScript(['id'=>$id]);
             return true;
        }catch (\Exception $exception){
            return false;
        }

    }

    /**
     * 获取脚本
     * @param string $id 脚本id
     * @return array|callable
     */
    public function getScript(string $id): array
    {
        $params = [
            'id' => $id
        ];
        try {
            return $this->client->getScript($params);
        }catch (\Exception $exception){
            return ['_id'=>null,'found'=>0,'script'=>[]];
        }
    }

    /**
     * 使用脚本查询
     * @param string $index
     * @param string $type
     * @param string $scriptId
     * @param string $key
     * @param mixed $value
     * @return array
     */
    public function searchByScript(string $index, string $type, string $scriptId, string $key, $value): array
    {
        $params = [
            'index'   => $index,
            'type'    => $type,
            'body'    => [
                'query'         => [
                    'match' => [
                        $key => $value,
                    ]
                ],
                'script_fields' => [
                    '_script_field' => [
                        'script' => [
                            'id' => $scriptId
                        ]
                    ],
                ]
            ],
            "_source" => ['*']
        ];
        return $this->client->search($params);
    }

    /**
     * 使用脚本更新文档
     * @param string $index 索引
     * @param string $type 类型
     * @param string $key 筛选的字段
     * @param mixed $value 筛选的值
     * @param array $data 更新的值 = ['key1'=>'value1','key2'=>'value2']
     * @return array
     */
    public function updateByScript(string $index, string $type, string $key, $value, array $data): array
    {
        $fields = '';
        foreach ($data as $k => $v) {
            $fields .= 'ctx._source.' . $k . ' = "' . $v . '";';
        }
        $fields = trim($fields, ';');
        $params = [
            'index' => $index,
            'type'  => $type,
            'body'  => [
                'query'  => [
                    'match' => [
                        $key => $value
                    ]
                ],
                'script' => [
                    "inline" => $fields,
                    'lang'   => 'painless'
                ]
            ]
        ];
        return $this->client->updateByQuery($params);
    }

    /**
     * 索引是否存在
     * @param $index
     * @return bool
     */
    public function IndexExists($index):bool
    {
        $params = [
            'index' => $index
        ];
        //索引检测
        $exists = $this->client->indices()->exists($params);
        if ($exists) {
            return true;
        }
        return false;
    }

    /**
     * 根据id更新数据
     * @param string $index 索引
     * @param string $type 类型
     * @param string $id 必须是doc文档的_id 才可以
     * @param array $data 需要修改的数据
     * @return array
     */
    public function updateById(string $index,string $type,string $id, array $data):array
    {
        $params = [
            'index' => $index,
            'type' => $type,
            'id' => $id,
            'body' => [
                'doc' => $data
            ]
        ];
        return $this->client->update($params);
    }

    /**
     * 根据某一个关键字搜索数据
     * @param string $index
     * @param string $type
     * @param string $key
     * @param string $keywords
     * @param int $from 起始位置
     * @param int $size 查询条数
     * @return array
     * @note 给更新操作提供_id用的
     */
    public function searchForUpdate(string $index,string $type, string $key, string $keywords, int $from = 0, int $size = 10):array
    {
        $params = [
            'index' => $index,
            'type' => $type,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            [
                                'match' => [
                                    $key => [
                                        'query' => $keywords,
                                    ]
                                ]
                            ],
                        ],
                    ],
                ],
                'sort' => ['_id' => ['order' => 'desc']],
                'from' => $from,
                'size' => $size,
            ]
        ];
        return $this->client->search($params)['hits']['hits'];
    }

    /**
     * es的运算符切换
     * @var array|string[]
     */
    public static array $comparative = [
        '>'=>'',
        '>='=>'',
        '<'=>'',
        '<='=>'',
        '!='=>''
    ];

    /**
     * 根据用户自定义的条件查询
     * @param string $index
     * @param string $type
     * @param array $where
     * @return array
     */
    public function searchByWhere(string $index,string $type, array $where):array
    {

        // 构建查询条件
        $params = [
            'index' => $index,  // 替换为实际的索引名称
            'type'=>$type,
            'body'  => [
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match' => [
                                    'title' => '测试'
                                ]
                            ],
                            [
                                'match' => [
                                    'content' => '张三'
                                ]
                            ],
                            [
                                'range'=>[
                                    'test_a'=>[
                                        'gt'=>9
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        return $this->client->search($params);
    }

}
