<?php
/**
 * Created by PhpStorm.
 * User: DIFF
 * Date: 2022/3/18
 * Time: 15:54
 */

include_once '../../vendor/autoload.php';
include_once '../elasticsearchTool/ElasticTool.php';

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

$indexName = 'ly-test-index-name-01'; //
$type = 'doc';
$elasticTool = new \diff0815\src\elasticsearchTool\ElasticTool($config);
$elasticTool->set($indexName,$type);

genData($elasticTool);


echo 'success'.PHP_EOL;



function genData($elasticTool){
    $elasticTool->createIndex();

    //
    $properties = [
        //5.x以上已经没有string类型。如果需要分词的话使用text，不需要分词使用 keyword。
        'time' => [
            'type' => 'text', //
        ],
        'date' => [
            'type' => 'text', // 字符串型
        ],
        'goods' => [
            'type' => 'text',
        ],
        'cate' => [
            'type' => 'text',
        ],
        'price' => [
            'type' => 'text',
        ],
        'color' => [
            'type' => 'text',
        ],

    ];
    $elasticTool->createMappings($properties);

    $limitNum = 1000;

    //苹果、沙果、海棠、野樱莓、枇杷、欧楂、山楂、梨
    $goodsArr = [
        '苹果',
        '沙果',
        '海棠',
        '野樱莓',
        '枇杷',
        '欧楂',
        '山楂',
        '梨',
    ];
    $priceArr = [
        10,
        15,
        8,
        20,
        99,
        40,
        50,
        77,
    ];

    $colorArr = [
        'red',
        'green',
        'pink',
        'black',
        'black',
        'green',
        'pink',
        'red',
    ];
    shuffle($colorArr);

    for($i=1;$i<=$limitNum;$i++){

        $rand = rand(1,50);
        $timeStr = strtotime("-{$rand} day");
        $time = date('Y-m-d H:i:s',$timeStr);
        $date = date('Y-m-d',$timeStr);

        $rand = rand(0,15);

        $goodRand = $rand%8;

        $colorRand = $rand%8;

        $doc = [
            'time' => $time,
            'date' => $date,
            'goods' => $goodsArr[$goodRand],
            'cate' => $goodRand,
            'price' => $priceArr[$goodRand]*($rand+1),
            'color' => $colorArr[$colorRand],
        ];
        $elasticTool->addDoc($doc);
    }


}
