<?php
/**
 * Created by PhpStorm.
 * User: DIFF
 * Date: 2022/3/21
 * Time: 17:15
 */

include_once '../../vendor/autoload.php';
include_once '../elasticsearchTool/ElasticTool.php';


//修改自己的host 和 port
$config = [
    'host' => [
        [
            'host'=>'192.168.240.166',
            'port' => '9200',
            'scheme' => 'http',
            'user' => '',
            'pass' => '',
        ],
    ],
    'retries' => 10,
];

try {
    $indexName = 'ly-test-index-name-01'; //
    $type = 'doc';
    $elasticTool = new \diff0815\src\elasticsearchTool\ElasticTool($config);
    $elasticTool->set($indexName,$type);
    //$elasticTool->deleteIndex();exit;

    $keywords = '枇杷';
    $query = [
        'query' =>[
            'bool' => [
                //必须匹配
                "must"=>[
                    "match"=>[
                        "goods"=>$keywords,
                    ]
                ],
                //应该匹配
                'should' => [
                    [
                        'match' => [
                            'goods' => [
                                'query' => $keywords,
                                'boost' => 3, // 权重
                            ]
                        ]
                    ],
                    [
                        'match' => [
                            'goods' => [
                                'query' => $keywords,
                                'boost' => 2,
                            ]
                        ]
                    ],
                ],
                //复杂的搜索 限制年龄大于25岁
                'filter'=>[
                    "range"=>[
                        "price"=>["gt"=>25]
                    ]
                ]
            ],
        ],
        'highlight'=>[
            "fields"=>[
                //必须加object，要不然转json时，这里依然是数组，而不是对象
                "price"=>(object)[]
            ]
        ],
        'aggs'=>[
            "result"=>[
                //terms 桶 统计文档数量
                "terms"=>[
                    "field"=>"price"
                ]
            ],
            "avg"=>[
                //avg 平均值
                "avg"=>[
                    "field"=>"price"
                ]
            ],
            "max"=>[
                //max 最大值
                "max"=>[
                    "field"=>"price"
                ]
            ],
            "min"=>[
                //avg 最小值
                "min"=>[
                    "field"=>"price"
                ]
            ],
        ],
        'from' => 0,
        'size' => 10,

    ];

    $data = $elasticTool->searchDocByBodyQuery($query);

    echo json_encode($data) . PHP_EOL;

}catch (\Throwable $e){
    echo $e->getMessage() . PHP_EOL;
}
