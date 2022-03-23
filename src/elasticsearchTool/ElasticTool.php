<?php
/**
 * Created by PhpStorm.
 * User: DIFF
 * Date: 2022/3/17
 * Time: 10:56
 */

namespace diff0815\src\elasticsearchTool;


use Elasticsearch\ClientBuilder;

class ElasticTool
{
    /**
     * 配置
     * @var array
     */
    protected array $config = [
        'host' => [],
        'retries' => 10,
    ];

    /**
     * es操作客户端
     * @var \Elasticsearch\Client
     */
    public \Elasticsearch\Client $client;

    /**
     * 索引名称
     * @var string
     */
    protected string $indexName;

    /**
     * 类型
     * @var string
     */
    protected string $type = 'doc';


    public function __construct($config = [])
    {

        $this->config = array_merge($this->config,$config);

        $this->client = ClientBuilder::create()
                        ->setHosts($this->config['host'])
                        //->setConnectionPool('Elasticsearch\ConnectionPool\SimpleConnectionPool')
                        ->setRetries($this->config['retries'])->build();

    }

    /**
     * @param $index
     * @param $type
     */
    public function set($index,$type)
    {
        $this->indexName = $index;
        $this->type = $type;
    }

    /**
     * 创建索引
     * @return array|callable
     */
    public function createIndex()
    {
        // 只能创建一次
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'body'=>[]
        ];
        return $this->client->index($params);
    }

    /**
     * 删除索引
     * @return array
     */
    public function deleteIndex(): array
    {
        $params = [
            'index' => $this->indexName,
        ];
        return $this->client->indices()->delete($params);
    }

    /**
     * 创建文档模板
     * @param $properties
     * @return array
     */
    public function createMappings($properties): array
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'include_type_name' => true,//7.0以上版本必须有
            'body' => [
                'properties' => $properties,
                /*'properties' => [
                    'id' => [
                        'type' => 'long', // 整型
                    ],
                    'name' => [
                        //5.x以上已经没有string类型。如果需要分词的话使用text，不需要分词使用keyword。
                        'type' => 'text', // 字符串型
                    ],
                    'profile' => [
                        'type' => 'text',
                    ],
                    'age' => [
                        'type' => 'long',
                    ],
                    'job' => [
                        'type' => 'text',
                    ],
                ]*/
            ]
        ];
        return $this->client->indices()->putMapping($params);
    }

    /**
     * 查看文档模板映射
     * @return array
     */
    public function getMappings(): array
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'include_type_name' => true,//7.0以上版本必须有
        ];
        return $this->client->indices()->getMapping($params);
    }

    /**
     * 添加文档
     * @param array $doc 跟创建文档结构时properties的字段一致
     * @param string $id
     * @return array|callable
     */
    public function addDoc(array $doc, string $id = '')
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'id' => $id,
            'body' => $doc
        ];
        return $this->client->index($params);
    }

    /**
     * 更新文档
     * @param $id
     * @param $key
     * @param $value
     * @return array|callable
     */
    public function updateDoc($id,$key,$value)
    {
        // 可以灵活添加新字段,最好不要乱添加
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'id' => $id,
            'body' => [
                'doc' => [
                    $key => $value
                ]
            ]
        ];

        return $this->client->update($params);
    }

    /**
     * 删除文档
     * @param $id
     * @return array|callable
     */
    public function deleteDoc($id)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'id' => $id
        ];
        return $this->client->delete($params);
    }

    /**
     * 判断文档是否存在
     * @param $id
     * @return bool
     */
    public function existsDoc($id): bool
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'id' => $id
        ];
        return $this->client->exists($params);
    }

    /**
     * 查询文档
     * @param $bodyQuery
     * @return array|callable
     */
    public function searchDocByBodyQuery($bodyQuery)
    {
        $params = [
            'index' => $this->indexName,
            'type' => $this->type,
            'body' => $bodyQuery
        ];

        return $this->client->search($params);
    }

    /** 查询文档 (分页，排序，权重，过滤)
     * @param $fun
     * @param $keywords
     * @param int $from
     * @param int $size
     * @return array|callable
     */
    public function searchDocByFunc($fun,$keywords,$from = 0,$size = 12): array
    {
        if(method_exists($this,$fun)){
            //$query=$this->searchBodySql5($keywords,$from,$size);
            $query=$this->$fun($keywords,$from,$size);

            return $this->searchDocByBodyQuery($query);
        }

        return [];
    }



    //========================================================================================
    /**
     * 格式查询表达式格式
     * 示例
     * properties 格式如下
     */
    /*
     'properties' => [
        'id' => [
            'type' => 'long', // 整型
        ],
        'name' => [
            //5.x以上已经没有string类型。如果需要分词的话使用text，不需要分词使用keyword。
            'type' => 'text', // 字符串型
        ],
        'profile' => [
            'type' => 'text',
        ],
        'age' => [
            'type' => 'long',
        ],
        'job' => [
            'type' => 'text',
        ],
    ]*/

    /**查询表达式搜索
     * @param $keywords
     * @param $from
     * @param $size
     * @return array
     */
    public function searchBodySql1($keywords,$from,$size): array
    {
        return [
            'query' =>[
                "match"=>[
                    //"name"=>$keywords, 或者
                    "name"=>[
                        'query' => $keywords,
                        'boost' => 3, // 权重
                    ],
                ],
            ],
            'from' => $from,
            'size' => $size
        ];
    }

    /**使用过滤器 filter
     * @param $keywords
     * @param $from
     * @param $size
     * @return array
     */
    public function searchBodySql2($keywords,$from,$size): array
    {
        return  [
            'query' => [
                'bool' => [
                    //必须匹配
                    "must"=>[
                        "match"=>[
                            "name"=>$keywords,
                        ]
                    ],
                    //应该匹配
                    'should' => [
                        [
                            'match' => [
                                'profile' => [
                                    'query' => $keywords,
                                    'boost' => 3, // 权重
                                ]
                            ]
                        ],
                        [ 'match' =>
                            [
                                'name' => [
                                    'query' => $keywords,
                                    'boost' => 2,
                                ]
                            ]
                        ],
                    ],
                    //复杂的搜索 限制年龄大于25岁
                    'filter'=>[
                        "range"=>[
                            "age"=>["gt"=>25]
                        ]
                    ]
                ],
            ],
            //  'sort' => ['age'=>['order'=>'desc']],
            'from' => $from,
            'size' => $size
        ];
    }

    /**短语搜索
     * @param $keywords
     * @param $from
     * @param $size
     * @return array
     */
    public function searchBodySql3($keywords,$from,$size): array
    {
        return [
            'query' =>[
                "match_phrase"=>[
                    //"name"=>$keywords, //或者
                    "name"=>[
                        'query' => $keywords,
                        'boost' => 3, // 权重
                    ],
                ],


            ],
            'from' => $from,
            'size' => $size
        ];
    }

    /**高亮搜索
     * @param string $keywords
     * @param $from
     * @param $size
     * @return array
     */
    public function searchBodySql4(string $keywords,$from,$size):array
    {
        return [
            'query' =>[
                "match_phrase"=>[
                    //"name"=>$keywords, 或者
                    "name"=>[
                        'query' => $keywords,
                        'boost' => 3, // 权重
                    ],
                ],
            ],
            'highlight'=>[
                "fields"=>[
                    //必须加object，要不然转json时，这里依然是数组，而不是对象
                    "name"=>(object)[]
                ]
            ],
            'from' => $from,
            'size' => $size
        ];
    }

    /**搜索结果增加分析
     * @param string $keywords
     * @param int $from
     * @param int $size
     * @return array
     */
    public function searchBodySql5(string $keywords,int $from,int $size): array
    {
        return [
            'query' =>[
                'bool' => [
                    //必须匹配
                    "must"=>[
                        "match"=>[
                            "profile"=>$keywords,
                        ]
                    ],
                    //应该匹配
                    'should' => [
                        [
                            'match' => [
                                'profile' => [
                                    'query' => $keywords,
                                    'boost' => 3, // 权重
                                ]
                            ]
                        ],
                        [
                            'match' => [
                                'name' => [
                                    'query' => $keywords,
                                    'boost' => 2,
                                ]
                            ]
                        ],
                    ],
                    //复杂的搜索 限制年龄大于25岁
                    'filter'=>[
                        "range"=>[
                            "age"=>["gt"=>25]
                        ]
                    ]
                ],
            ],
            'highlight'=>[
                "fields"=>[
                    //必须加object，要不然转json时，这里依然是数组，而不是对象
                    "name"=>(object)[]
                ]
            ],
            'aggs'=>[
                "result"=>[
                    //terms 桶 统计文档数量
                    "terms"=>[
                        "field"=>"age"
                    ]
                ],
                "avg"=>[
                    //avg 平均值
                    "avg"=>[
                        "field"=>"age"
                    ]
                ],
                "max"=>[
                    //max 最大值
                    "max"=>[
                        "field"=>"age"
                    ]
                ],
                "min"=>[
                    //avg 最小值
                    "min"=>[
                        "field"=>"age"
                    ]
                ],
            ],
            'from' => $from,
            'size' => $size,
        ];
    }

}
