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

    $keywords = '山楂';
    $query = [
        'query' =>[
            "match"=>[
                //goods 字段名称
                //"goods"=>$keywords, //或者
                "goods"=>[
                    'query' => $keywords,
                    'boost' => 3, // 权重
                ],
            ],
        ],
        'from' => 0,
        'size' => 10
    ];

    $data = $elasticTool->searchDocByBodyQuery($query);

    echo json_encode($data) . PHP_EOL;

}catch (\Throwable $e){
    echo $e->getMessage() . PHP_EOL;
}
