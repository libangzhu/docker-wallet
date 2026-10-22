<?php

namespace console\controllers;

use common\models\pwallet\Article;
use Yii;
use yii\console\Controller;
use common\components\Tools;
use linslin\yii2\curl\Curl;

class ArticleController extends Controller
{
    public function actionIndex()
    {
        $coindog = Yii::$app->params['coindog'];
        $httpParams = array(
            'access_key' => $coindog['accessKey'],
            'date' => time()
        );

        $signParams = array_merge($httpParams, array('secret_key' => $coindog['secretKey']));

        ksort($signParams);
        $signString = http_build_query($signParams);

        $httpParams['sign'] = strtolower(md5($signString));
        $url = $coindog['article'] . '?' . http_build_query($httpParams);
        $curl = new Curl();
        $resp = $curl->get($url, false);
        if (empty($resp)) {
            return 0;
        }
        $max_create_time = Article::find()->where(['platform_id' => -1])->max('create_time');
        if ($max_create_time >= strtotime($resp[0]['published_time'])) {
            return 0;
        }

        $list = Tools::arraySort($resp, 'id', SORT_ASC);
        $article = new Article();
        foreach ($list as &$item) {
            go(function () use ($item, $article, $max_create_time) {
                \Co::sleep(0.5);
                if (strtotime($item['published_time']) > $max_create_time) {
                    $article->setIsNewRecord(true);
                    $article->title = $item['title'];
                    $article->content = $item['content'];
                    $article->logo_pic = $item['thumbnail'];
                    $article->img_url = $item['thumbnail'];
                    $article->source = $item['resource'];
                    $article->platform_id = -1;
                    $article->create_time = strtotime($item['published_time']);
                    $article->save() && $article->id = 0;;
                }
            });
        }
        return 0;
    }
}
