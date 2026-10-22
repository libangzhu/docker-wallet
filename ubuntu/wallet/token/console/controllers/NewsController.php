<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;
use linslin\yii2\curl\Curl;
use common\components\Tools;
use common\models\pwallet\News;

class NewsController extends Controller
{
    public function actionIndex()
    {
        $coindog = Yii::$app->params['coindog'];

        $accessKey = $coindog['accessKey'];
        $secretKey = $coindog['secretKey'];
        $httpParams = array(
            'access_key' => $accessKey,
            'date' => time()
        );

        $signParams = array_merge($httpParams, array('secret_key' => $secretKey));

        ksort($signParams);
        $signString = http_build_query($signParams);

        $httpParams = [
            'sign' => strtolower(md5($signString)),
        ];

        $url = $coindog['live'] . '?' . http_build_query($httpParams);
        $curl = new Curl();
        $resp = $curl->get($url, false);
        if (!isset($resp['list'])) {
            return 0;
        }
        $create_time = News::find()->where(['platform_id' => -1])->max('create_time');

        $list = $resp['list'][0]['lives'];
        if ($create_time >= $list[0]['created_at']) {
            return 0;
        }

        $list = Tools::arraySort($list, 'created_at', SORT_ASC);

        $article = new News();
        foreach ($list as &$item) {
            go(function () use ($item, $article, $create_time) {
                \Co::sleep(0.5);
                if ($item['created_at'] > $create_time) {
                    preg_match_all("/\【(.+?)\】/", $item['content'], $match);
                    $title = $match[1][0];
                    $content = str_replace('【' . $title . '】', '', $item['content']);

                    $article->setIsNewRecord(true);
                    $article->title = $title;
                    $article->content = $content;
                    $article->platform_id = -1;
                    $article->create_time = $item['created_at'];
                    $article->save() && $article->id = 0;;
                }
            });
        }
        return 0;
    }
}
