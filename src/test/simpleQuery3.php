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

    $keywords = '枇杷';
    $query = [
        'query' =>[
            "match_phrase"=>[
                //goods 字段名称
                //"goods"=>$keywords, //或者
                "goods"=>[
                    'query' => $keywords,
                    'boost' => 3, // 权重s
                ],
            ],
        ],
        'highlight'=>[
            "fields"=>[
                //必须加object，要不然转json时，这里依然是数组，而不是对象
                "goods" => (object)[],
                //"goods" => (new ArrayObject()),
            ]
        ],
        'sort' => [
            //'_id' => 'desc',
            'time' => 'desc',
            //'date' => 'desc', //错误: 聚合所依据的字段用单独的数据结构(fielddata)缓存到内存里了，但是在text字段上默认是禁用的，这样做的目的是为了节省内存空间。所以如果需要进行聚合操作，需要单独开启 fielddata => true,

        ],
        'from' => 0,
        'size' => 10
    ];

    $data = $elasticTool->searchDocByBodyQuery($query);

    echo json_encode($data) . PHP_EOL;

}catch (\Throwable $e){
    echo $e->getMessage() . PHP_EOL;
}
