<?php
namespace Xiaosongshu\Elasticsearch;

use PHPUnit\Framework\Exception;
use Elasticsearch\ClientBuilder;

/**
 * @purpose elasticsearch客户端
 * @note 新版本兼容elasticsearch v8.15.5
 */
class EsClient
{

    /** 客户端 */
    protected $client;

    /** 节点列表 */
    protected $nodes = [];

    /** 账号 */
    protected $username ;

    /** 密码 */
    protected $password ;

    /**
     * 实例化客户端
     * <code>
     * $elasticsearchConfig = [
     *  'nodes' => ['127.0.0.1:9201'],
     *  'username' => 'elastic',
     *  'password' => '123456',
     * ]
     * </code>
     */
    public function __construct(array $elasticsearchConfig = [])
    {

        /** 兼容各平台框架 ，支持单应用 */
        if (!function_exists('config')) {
            $config = $elasticsearchConfig;
        } else {
            $config = config('elasticsearch');
        }
        if (empty($config)) {
            $config = $elasticsearchConfig;
        }

        if (empty($config)) {
            throw new \RuntimeException("the config of elasticsearch cannot be empty");
        }
        /** 获取配置 */
        $this->nodes = !empty($config['nodes']) ? $config['nodes'] : $this->nodes;
        $this->username = !empty($config['username']) ? $config['username'] : '';
        $this->password = !empty($config['password']) ? $config['password'] : '';
        /** 链接客户端 */
        if (empty($this->client)) {
            $client = ClientBuilder::create()
                ->setHosts($this->nodes);
            if (!empty($this->username) && !empty($this->password)) {
                $client->setBasicAuthentication($this->username, $this->password);
            }
            $this->client = $client->build();
        }
    }

    /**
     * 制定表名
     * @param string $table 表名称
     * @return $this
     */
    public function table(string $table)
    {
        $this->index = $table;
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        return $this;
    }


    /**
     * 创建表和结构
     * @param string $table = "my_index"
     * @param array $columns = []
     *  <code>
     *   $columns = [
     *      'first_name' => [
     *          'type' => 'text',
     *          'analyzer' => 'standard'
     *          ],
     *      'age' => [
     *          'type' => 'integer'
     *          ]
     *  ]
     *  </code>
     * @return array
     */
    public function createTable(string $table, array $columns)
    {
        if (empty($table)) {
            throw new \InvalidArgumentException('Table name cannot be empty');
        }
        if (empty($columns)) {
            throw new \InvalidArgumentException('Columns array cannot be empty');
        }
        if ($this->IndexExists($table)) {
            throw new \InvalidArgumentException('Table already exists');
        }
        $params = [
            'index' => $table,
            'body' => [
                'settings' => [
                    /** 主分片 */
                    'number_of_shards' => 3,
                    /** 负分片 */
                    'number_of_replicas' => 2
                ],
                'mappings' => [
                    '_source' => [
                        /** 保存原始文本 */
                        'enabled' => true
                    ],
                    /** 属性 */
                    'properties' => $columns
                ]
            ]
        ];
        return $this->client->indices()->create($params);
    }

    /**
     * 创建表以及表结构
     * @param string $table
     * @param string $type
     * @param array $columns
     * @return array
     */
    public function createMappings(string $table,string $type, array $columns)
    {
        return $this->createTable($table, $columns);
    }

    /**
     * @param string $index
     * @param string $type
     * @return array
     */
    public function createIndex(string $index, string $type = ''):array
    {
        $params = [
            'index' => $index,
            'body' => []
        ];
        return $this->client->indices()->create($params);
    }

    /**
     * 更新表结构
     * @param string $table = "my_index"
     * @param array $columns = []
     *  <code>
     *   $columns = [
     *      'first_name' => [
     *          'type' => 'text',
     *          'analyzer' => 'standard'
     *          ],
     *      'age' => [
     *          'type' => 'integer'
     *          ]
     *  ]
     *  </code>
     * @return array
     */
    public function updateTable(string $table, array $columns)
    {
        if (empty($table)) {
            throw new \InvalidArgumentException('Table name cannot be empty');
        }
        if (empty($columns)) {
            throw new \InvalidArgumentException('Columns array cannot be empty');
        }
        if (!$this->tableExists($table)) {
            throw new \InvalidArgumentException('Table does not exist');
        }
        $params = [
            'index' => $table,
            'body' => [
                'settings' => [
                    /** 主分片 */
                    'number_of_shards' => 3,
                    /** 负分片 */
                    'number_of_replicas' => 2
                ],
                'mappings' => [
                    '_source' => [
                        /** 保存原始文本 */
                        'enabled' => true
                    ],
                    /** 属性 */
                    'properties' => $columns
                ]
            ]
        ];
        return $this->client->indices()->putMapping($params);
    }

    /**
     * 删除索引
     * @param string $index 索引
     * @return array
     * <code>
     *     $client->deleteIndex('index')
     * </code>
     */
    public function deleteIndex(string $index): array
    {
        $params['index'] = $index;
        return $this->client->indices()->delete($params);
    }

    /**
     * 删除表
     * @param string $table
     * @return array
     */
    public function deleteTable(string $table)
    {
        return $this->deleteIndex($table);
    }

    /**
     * 获取表结构信息
     * @param array $tables
     * <code>
     *     $tables = ['my_index','my_index2']
     * </code>
     * @return array
     */
    public function getMap(array $tables = [])
    {
        $params = [];
        if (!empty($tables)) {
            $params['index'] = $tables;
        }
        return $this->client->indices()->getMapping($params);
    }

    /**
     * 获取表结构
     * @param array $tables
     * @return array
     */
    public function getTableInfo(array $tables = [])
    {
        return $this->getMap($tables);
    }

    /**
     * 插入数据
     * @param array $data
     * <code>
     *     $data = ['username'=>"张三",'age'=>15]
     * </code>
     * @return array|callable
     */
    public function insert(array $data)
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Table name cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        $params = [
            'index' => $this->index,
            'body' => []
        ];
        if (isset($data['id'])) {
            $params['id'] = $data['id'];
            unset($data['id']);
        }
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }
        $params['body'] = $data;

        /** 清空上一轮查询的限制条件 */
        $this->clearCondition();
        return $this->client->index($params);
    }

    /**
     * 添加数据
     * @param array $data
     * @return array|callable
     */
    public function add(array $data)
    {
        return $this->insert($data);
    }



    /**
     * 通过id查询数据
     * @param string $id
     * @return array|callable
     */
    public function findById(string $id)
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Table name cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        if (empty($id)) {
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        $params = [
            'index' => $this->index,
            'id' => $id
        ];
        /** 清空上一轮查询的限制条件 */
        $this->clearCondition();
        return $this->client->get($params);
    }

    /**
     * 通过id更新数据
     * @param string $id 索引id
     * @param array $data 需要更新的数据
     * <code>
     *     $data = ['username'=>'张三','age'=>12]
     * </code>
     * @return array|callable
     */
    public function updateById(string $id, array $data)
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Table name cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        if (empty($id)) {
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }
        $params = [
            'index' => $this->index,
            'id' => $id,
            'body' => [
                'doc' => $data
            ]
        ];
        /** 清空上一轮查询的限制条件 */
        $this->clearCondition();
        return $this->client->update($params);
    }

    /**
     * 通过id删除数据
     * @param string $id 索引id
     * @return array|callable
     */
    public function deleteById(string $id)
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Table name cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        if (empty($id)) {
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        $params = [
            'index' => $this->index,
            'id' => $id
        ];
        /** 清空上一轮查询的限制条件 */
        $this->clearCondition();
        return $this->client->delete($params);
    }


    /**
     * 原生查询
     * @param array $body 查询的内容
     * @return array
     * <code>
     * $client->query([
     *      'index'=>'index',
     *      'body'=>[
     *           'query'=>[
     *              'bool'=>[
     *                  'must'=>[
     *                      [
     *                          'match_phrase'=>[
     *                                  'title'=>'风云'
     *                                          ],
     *                      ],
     *                  [
     *          'script'=>[
     *              'script'=>"doc['content'].size() != 0"
     *                      ]
     *                  ]
     *              ]
     *          ]
     *      ]
     *  ]
     * ]);
     * </code>
     * @note 请自行构建完整的请求体
     */
    public function query(array $body): array
    {

        $params = [
            'index' => $this->index,
        ];
        if (!$this->IndexExists($params['index'])) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        return $this->client->search(array_merge($params, $body));
    }

    /**
     * 添加脚本
     * @param string $id 脚本id
     * @param string $scriptContent 脚本内容（ "doc['title'].value+'_'+'谁不说按家乡好'" ）
     * @return array|callable
     * <code>
     *     $client->addScript('update_content2',"(doc['content'].value)+'_'+'demo'")
     * </code>
     * @note 操作字段有ctx和doc两种方法，并且不可频繁添加脚本，否则es一直编译脚本，负担过重会抛出异常
     */
    public function addScript(string $id, string $scriptContent): array
    {
        if (empty($id)){
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        if (empty($scriptContent)){
            throw new \InvalidArgumentException('Script content cannot be empty');
        }
        $params = [
            'id' => $id,
            'body' => [
                'script' => [
                    'lang' => 'painless',
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
     * <code>
     *     $client->deleteScript('update_content');
     * </code>
     */
    public function deleteScript(string $id): bool
    {
        if (empty($id)){
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        try {
            $this->client->deleteScript(['id' => $id]);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * 获取脚本
     * @param string $id 脚本id
     * @return array|callable
     * <code>
     *     $client->getScript('update_content')
     * </code>
     */
    public function getScript(string $id): array
    {
        if (empty($id)){
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        $params = [
            'id' => $id
        ];
        try {
            return $this->client->getScript($params);
        } catch (\Exception $exception) {
            return ['_id' => null, 'found' => 0, 'script' => []];
        }
    }

    /**
     * 需要调用的脚本
     * @var array
     */
    private  $script = [];

    /**
     * 调用脚本
     * @param string $id
     * @return $this
     * <code>
     *     $client->table('index','_doc')->withScript('update_content11')->getAll();
     * </code>
     */
    public function withScript(string $id)
    {
        if (empty($id)){
            throw new \InvalidArgumentException('Id cannot be empty');
        }
        $this->script[] = $id;
        return $this;
    }

    /**
     * 索引是否存在
     * @param string $index
     * @return bool
     * <code>
     *      $client->IndexExists('index')
     * </code>
     */
    public function IndexExists(string $index): bool
    {
        if (empty($index)) {
            throw new \InvalidArgumentException('Index name cannot be empty');
        }
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
     * 表是否存在
     * @param string $table
     * @return bool
     */
    public function tableExists(string $table): bool
    {
        return $this->IndexExists($table);
    }


    /**
     * 需要排除条件的字段
     * @var array
     */
    private  $mustNot = [];

    /**
     * must必须满足查询条件的字段
     * @var array
     */
    private  $must = [];

    /**
     * where查询条件
     * @param array $condition
     * @return $this
     * @throws \Exception
     * <code>
     *     $client->table('index','_doc')->where(['title','=','测试']);
     * </code>
     */
    public function where(array $condition)
    {
        if (count($condition) != 3) {
            throw new \Exception("condition 条件必须包含 字段 比较字符 值 三个元素");
        }
        $field = $condition[0];
        $operation = $condition[1];
        $value = $condition[2];
        switch ($operation) {
            case ">":
                $this->must[] = [
                    'range' => [
                        $field => [
                            'gt' => $value
                        ]
                    ]
                ];
                break;
            case ">=":
                $this->must[] = [
                    'range' => [
                        $field => [
                            'gte' => $value
                        ]
                    ]
                ];
                break;
            case "<":
                $this->must[] = [
                    'range' => [
                        $field => [
                            'lt' => $value
                        ]
                    ]
                ];
                break;
            case "<=":
                $this->must[] = [
                    'range' => [
                        $field => [
                            'lte' => $value
                        ]
                    ]
                ];
                break;
            case "=":
            case "==":
            case "===":
                $this->must[] = [
                    'match' => [
                        $field => $value
                    ]
                ];
                break;

            case "!=":
            case "<>":
                $this->mustNot[] = [
                    'term' => [
                        $field => $value
                    ]
                ];
                break;
            default:
                throw new \Exception("未识别的操作符");
        }
        return $this;
    }

    /**
     * 或者查询的字段
     * @var array
     */
    private  $should = [];

    /**
     * orwhere 或者查询
     * @param array $condition
     * @return $this
     * @throws \Exception
     * <code>
     *     $client->table('index','_doc')->orWhere(['test_a','>',8]);
     * </code>
     */
    public function orWhere(array $condition)
    {
        if (count($condition) != 3) {
            throw new \Exception("condition 条件必须包含 字段 比较字符 值 三个元素");
        }
        $field = $condition[0];
        $operation = $condition[1];
        $value = $condition[2];
        switch ($operation) {
            case ">":
                $this->should[] = [
                    'range' => [
                        $field => [
                            'gt' => $value
                        ]
                    ]
                ];
                break;
            case ">=":
                $this->should[] = [
                    'range' => [
                        $field => [
                            'gte' => $value
                        ]
                    ]
                ];
                break;
            case "<":
                $this->should[] = [
                    'range' => [
                        $field => [
                            'lt' => $value
                        ]
                    ]
                ];
                break;
            case "<=":
                $this->should[] = [
                    'range' => [
                        $field => [
                            'lte' => $value
                        ]
                    ]
                ];
                break;
            case "=":
                $this->should[] = [
                    'match' => [
                        $field => $value
                    ]
                ];
                break;
            default:
                $this->should[] = [
                    'match' => [
                        $field => $value
                    ]
                ];
        }
        return $this;
    }

    /**
     * 翻页偏移量
     * @var int
     */
    private  $from = 0;

    /**
     * 分页查询数据条数
     * @var int
     */
    private  $limit = 1000;

    /**
     * 数据分页
     * @param int $from
     * @param int $limit
     * @return $this
     * <code>
     *     $client->table('index','_doc')->limit(0,10);
     * </code>
     */
    public function limit(int $from = 0, int $limit = 100)
    {
        $this->from = $from;
        $this->limit = $limit;
        return $this;
    }

    /**
     * 分页
     * @param int $page 当前页
     * @param int $size 每页条数
     * @return $this
     */
    public function page(int $page = 1,int $size = 100)
    {
        $from  = ($page - 1) * $size;
        return $this->limit($from, $size);
    }

    /**
     * 需要排序的字段
     * @var array
     */
    private  $order = [];

    /**
     * 查询数据排序
     * @param string $field 排序字段
     * @param string $direction asc|desc
     * @return $this
     * <code>
     *     $client->table('index','_doc')->orderBy('test_a','asc');
     * </code>
     */
    public function orderBy(string $field, string $direction = "desc")
    {
        if (empty($field)){
            throw new \InvalidArgumentException('Field cannot be empty');
        }
        if (empty($direction)){
            $direction = "desc";
        }

        $this->order[] = [
            $field => ['order' => $direction]
        ];

        return $this;
    }

    /**
     * 表名称
     * @var string
     */
    private  $index ;

    /**
     * 构建query
     * @return array
     */
    private function buildQuery()
    {
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'match_all' => new \stdClass()
                ]
            ],
            'from' => $this->from,
            'size' => $this->limit,
        ];
        /** 必须满足的条件 */
        if ($this->must) {
            $params['body']['query']['bool']['must'] = $this->must;
        }
        /** 可以满足的条件 */
        if ($this->should) {
            $params['body']['query']['bool']['should'] = $this->should;
        }
        /** 必须排除的条件 */
        if ($this->mustNot) {
            $params['body']['query']['bool']['must_not'] = $this->mustNot;
        }

        /** whereIn 查询 */
        if ($this->whereIn) {
            $params['body']['query']['bool']['must'][] = ['terms' => $this->whereIn];
        }

        /** whereNotIn查询 */
        if ($this->whereNotIn) {
            $params['body']['query']['bool']['must_not'][] = ['terms' => $this->whereNotIn];
        }
        /** 调用脚本处理查询字段 ，目前只有查询支持脚本，而删除和更新直接操作不建议使用脚本，如果有需要可以自己构建 */
        if ($this->script) {
            foreach ($this->script as $name) {
                $params['body']['script_fields']['modify_' . $name] = ['script' => ['id' => $name]];
            }
        }
        return $params;
    }


    /**
     * 查询所有数据
     * @return array|callable
     * @throws \Exception
     * <code>
     *     $result = $client->table('index','_doc')->getAll();
     * </code>
     */
    public function getAll()
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Index cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        /** 构建query */
        $params = $this->buildQuery();

        /** 排序 */
        if ($this->order) {
            $params['body']['sort'] = $this->order;
        }
        /** 筛选需要查询的字段 */
        if ($this->select) {
            /** 如果是查询所有就不需要过滤字段 */
            if (!in_array('*', $this->select)) {
                $params['body']['_source'] = $this->select;
            }
        }
        /** 处理聚合查询的数据 */
        $agg = array_merge($this->sumData, $this->aveData, $this->maxData, $this->minData);

        /** 数据分组 */
        if ($this->groupBy) {
            /** 分页不再生效 */
            $params['from'] = 0;
            $params['size'] = 0;
            /** 不返回顶部的原始文档，只返回聚合结果 */
            $params['body']['size'] = 0;
            /** 聚合查询 */
            $params['body']['aggs'] = [
                'groupBy' => [
                    'composite' => [
                        'sources' => $this->groupBy,
                        /** 使用字段进行分组，进行排列组合处理，每一次返回的组合数 */
                        'size' => 100  // 每次返回的分组数量 这个不动
                    ],
                    'aggregations' => [
                        'top_documents' => [
                            'top_hits' => [
                                /** 对处理结果进行分页，考虑到分片的性能问题，最大设置为1000 ，否则复杂度呈指数级上升 */
                                'size' => 100,  // 每个分组返回的文档数量 ,分页的时候变更这里，作为内存数据库，咱不考虑分页
                                '_source' => [
                                    /** 对分组后的数据进行筛选 */
                                    'includes' => $this->select,
                                ],
                                'sort' => $this->order
                            ]
                        ]
                    ]
                ]
            ];
            /** 存在聚合查询，聚合查询是在分组里面 */
            if ($agg) {
                foreach ($agg as $value) {
                    foreach ($value as $key => $item) {
                        $params['body']['aggs']['groupBy']['aggregations'][$key] = $item;
                    }
                }
            }


        } else {
            /** 没有分组，直接聚合查询 */
            if ($agg) {
                /** 不返回顶部的原始文档，只返回聚合结果 */
                $params['body']['size'] = 0;
                $params['size'] = 0;
                foreach ($agg as $value) {
                    foreach ($value as $key => $item) {
                        $params['body']['aggs'][$key] = $item;
                    }
                }
                /** 字段筛选 */
                $params['body']['_source'] = ['includes' => $this->select];
                /** 排序 */
                $params['body']['sort'] = $this->order;
            }
        }
        /** 清空上一轮查询的限制条件 */
        $this->clearCondition();


        $operation = array_keys($params['body']['query']);
        /** 存在其它查询条件，则去掉查询所有这个限制 */
        if (array_diff($operation, ['match_all'])) {
            /** 不查询所有值 */
            unset($params['body']['query']['match_all']);
        }
        return $this->client->search($params);
    }

    /**
     * 需要求和的字段
     * @var array
     */
    private  $sumData = [];

    /**
     * 求和查询
     * @param array $data
     * @return $this
     * <code>
     *     $client->table('index','_doc')->sum(['age']);
     * </code>
     */
    public function sum(array $data)
    {
        foreach ($data as $value) {
            $this->sumData[] = [
                'sum_' . $value => [
                    'sum' => [
                        'field' => $value
                    ]
                ]
            ];
        }

        return $this;
    }

    /**
     * 需要求平均值的字段
     * @var array
     */
    private  $aveData = [];

    /**
     * 求平均值
     * @param array $data
     * @return $this
     * <code>
     *     $client->table('index','_doc')->ave(['age']);
     * </code>
     */
    public function ave(array $data)
    {
        foreach ($data as $value) {
            $this->aveData[] = [
                'ave_' . $value => [
                    'ave' => [
                        'field' => $value
                    ]
                ]
            ];
        }
        return $this;
    }

    /**
     * 需要求最大值的字段
     * @var array
     */
    private  $maxData = [];

    /**
     * 求最大值
     * @param array $data
     * @return $this
     * <code>
     *     $client->table('index','_doc')->max(['age']);
     * </code>
     */
    public function max(array $data)
    {
        foreach ($data as $value) {
            $this->maxData[] = [
                'max_' . $value => [
                    'max' => [
                        'field' => $value
                    ]
                ]
            ];
        }
        return $this;
    }

    /** 取最小值的字段 */
    private  $minData = [];

    /**
     * 取最小值
     * @param array $data
     * @return $this
     * <code>
     *     $client->table('index','_doc')->min(['age']);
     * </code>
     */
    public function min(array $data)
    {
        foreach ($data as $value) {
            $this->minData[] = [
                'min_' . $value => [
                    'min' => [
                        'field' => $value
                    ]
                ]
            ];
        }
        return $this;
    }

    /**
     * 需要筛选的字段
     * @var array
     */
    private  $select = [];

    /**
     * 筛选需要查询的字段
     * @param array $fields
     * @return $this
     * <code>
     *     $client->table('index','_doc')->select(['name','age']);
     * </code>
     */
    public function select(array $fields = [])
    {
        $this->select = $fields;
        return $this;
    }

    /**
     * 清空上一轮的条件
     * @return void
     */
    private function clearCondition()
    {
        /** 清空上一轮的查询条件 */
        $this->must = [];
        $this->should = [];
        $this->mustNot = [];
        $this->order = [];
        $this->select = [];
        $this->index = null;
        $this->groupBy = [];
        $this->sumData = [];
        $this->maxData = [];
        $this->minData = [];
        $this->aveData = [];
        $this->whereIn = [];
        $this->whereNotIn = [];
    }

    /**
     * 批量写入数据
     * @param array $data
     * @return array|callable
     * @throws \Exception
     * <code>
     *  $client->table('index', '_doc')->insertAll([
     * [
     * 'id' => rand(1, 99999),
     * 'title' => '天有不测风云',
     * 'content' => '月有阴晴圆缺',
     * 'create_time' => date('Y-m-d H:i:s'),
     * 'test_a' => rand(1, 10),
     * 'test_b' => rand(1, 10),
     * 'test_c' => rand(1, 10),
     * 'name' => '张三',
     * 'age' => 27,
     * 'sex' => 1
     * ]
     * ])
     * </code>
     */
    public function insertAll(array $data)
    {
        if (empty($this->index) ) {
            throw new \InvalidArgumentException('Index cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        if (empty($data)) {
            throw new \Exception("数据不能为空");
        }
        $params = [];
        foreach ($data as $v) {
            $params['body'][] = [
                'index' => [
                    '_index' => $this->index,
                ]
            ];
            $params['body'][] = $v;
        }
        return $this->client->bulk($params);
    }

    /**
     * 更新所有数据
     * @param array $data
     * @return array|callable
     * @throws \Exception]
     * <code>
     *     $client->table('index','_doc')->where(['test_a','>',2])->updateAll(['name'=>'陈圆圆']);
     * </code>
     */
    public function updateAll(array $data)
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Index cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        /** 构建query */
        $params = $this->buildQuery();
        unset($params['body']['query']['match_all'], $params['size'], $params['from'], $params['body']['script_fields']);
        $this->clearCondition();
        /** 需要被更新的数据 */
        $source = '';
        foreach ($data as $key => $value) {
            $source .= "ctx._source." . $key . " = params." . $key;
        }
        /** 构建脚本 */
        $params['body']['script'] = [
            'source' => $source,
            'params' => $data
        ];

        return $this->client->updateByQuery($params);
    }

    /**
     * 删除所有数据
     * @return array|callable
     * @throws \Exception
     * <code>
     *     $client->table('index')->where(['test_a','>',2])->deleteAll();
     * </code>
     */
    public function deleteAll()
    {
        if (empty($this->index)) {
            throw new \InvalidArgumentException('Index cannot be empty');
        }
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }

        /** 构建query */
        $params = $this->buildQuery();
        unset($params['body']['query']['match_all'], $params['size'], $params['from'], $params['body']['script_fields']);
        $this->clearCondition();
        return $this->client->deleteByQuery($params);
    }

    /**
     * 删除数据
     * @return array|callable
     * @throws Exception
     */
    public function delete()
    {
        return $this->deleteAll();
    }

    /**
     * 更新表字段
     * @param array $data
     * @return array|callable
     * @throws Exception
     */
    public function update(array $data)
    {
        return $this->updateAll($data);
    }


    /**
     * 原生查询
     * @param $name
     * @param $arguments
     * @return mixed
     * @note 使用魔术方法调用客户端
     */
    public function __call($name, $arguments)
    {
        return $this->client->$name(...$arguments);
    }

    /**
     * 分组查询数据
     * @var array
     */
    private  $groupBy = [];

    /**
     * groupBy分组查询
     * @param array $array
     * @return $this
     * <code>
     *     $client->table('index','_doc')->groupBy(['age','sex'])
     * </code>
     */
    public function groupBy(array $array)
    {
        foreach ($array as $value) {
            $this->groupBy[] = [$value => ['terms' => ['field' => $value]]];
        }
        return $this;
    }

    /**
     * whereIn数据查询数据
     * @var array
     */
    private  $whereIn = [];

    /**
     * whereIn 查询
     * @param string $key
     * @param array $data
     * @return $this
     * <code>
     *     $client->table('index','_doc')->whereIn('age',[28]);
     * </code>
     */
    public function whereIn(string $key, array $data)
    {
        if (empty($data)) {
            throw new \Exception("数据不能为空");
        }
        $this->whereIn[$key] = $data;
        return $this;
    }

    /**
     * whereNotIn查询条件
     * @var array
     */
    private  $whereNotIn = [];

    /**
     * whereNotIn 查询
     * @param string $key
     * @param array $data
     * @return $this
     * @throws \Exception
     * <code>
     *     $client->table('index','_doc')->whereNotIn('age',[28]);
     * </code>
     */
    public function whereNotIn(string $key, array $data)
    {
        if (empty($data)) {
            throw new \Exception("数据不能为空");
        }
        $this->whereNotIn[$key] = $data;
        return $this;
    }


    /**
     * 获取索引的详情
     * @param array $indexes =[] 获取索引详情，为空则获取所有索引的详情
     * @return array
     * <code>
     *     $client->getIndex(['index']);
     * </code>
     */
    public function getIndex(array $indexes): array
    {
        $params = [
            'index' => $indexes,
        ];
        return $this->client->indices()->getSettings($params);
    }

    /**
     * 根据id批量删除数据
     * @param array $ids 需要删除的所有记录的ID
     * @return array
     * <code>
     *     $client->table('index','_doc')->deleteByIds(['kmXADJEBegXAJ580Qqp6']);
     * </code>
     */
    public function deleteByIds(array $ids): array
    {
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        $params = [
            'index' => $this->index,
        ];
        foreach ($ids as $v) {
            $params ['body'][] = array(
                'delete' => array(
                    '_index' => $this->index,
                    '_id' => $v
                )
            );
        }
        return $this->client->bulk($params);
    }

    /**
     * 使用IDs 批量获取数据
     * @param array $ids
     * @return array|callable
     * <code>
     *     $client->table('index')->getByIds(['kmXADJEBegXAJ580Qqp6']);
     * </code>
     */
    public function getByIds(array $ids)
    {
        if (!$this->IndexExists($this->index)) {
            throw new \InvalidArgumentException('Index does not exist');
        }
        $params = [
            'index' => $this->index,
            'body' => [
                'query' => [
                    'terms' => [
                        '_id' => $ids
                    ]
                ]
            ]
        ];

        return $this->client->search($params);
    }
}