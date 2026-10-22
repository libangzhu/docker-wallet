<?php

namespace api\controllers;

use backend\models\coin\ArticleForm;
use common\models\pwallet\News;
use common\service\bishijie\BishijieService;
use linslin\yii2\curl\Curl;
use Yii;
use api\base\BaseController;
use yii\data\Pagination;

class CoinDogController extends BaseController
{
    public function actionArticle()
    {
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 20);

        $bishijie = Yii::$app->params['bishijie'];
        $appid = $bishijie['Appid'];
        $appSecret = $bishijie['AppSecret'];
        $bishijie_service = new BishijieService($appid, $appSecret);
        $article = $bishijie_service->getArticleList($page, $size);
        foreach ($article['list'] as &$val) {
            if (isset($val['news_id']) && !isset($val['id'])) {
                $val['id'] = $val['news_id'];
            }
            $val['share_url'] = $bishijie['Url'] . '/shendu_' . $val['id'];
            $val['source'] = '币世界';
        }
        $data = [
            'list' => isset($article['list']) ? $article['list'] : [],
            'page' => [
                'pageCount' => isset($article['allPage']) ? $article['allPage'] : 0,
                'pageSize' => $size,
                'currentPage' => $page,
            ]
        ];

        return ['code' => 200, 'data' => $data, 'msg' => 'ok'];
    }

    public function actionArticleInfo()
    {
        $id = Yii::$app->request->get('id', 0);

        if (false == $id) {
            $msg = '参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }
        $bishijie = Yii::$app->params['bishijie'];
        $appid = $bishijie['Appid'];
        $appSecret = $bishijie['AppSecret'];
        $bishijie_service = new BishijieService($appid, $appSecret);
        $article = $bishijie_service->getArticleDetail($id);
        $article['share_url'] = $bishijie['Url'] . '/shendu_' . $id;
        $article['source'] = '币世界';
        if (@$article['code']) {
            $msg = $article['message'];
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $data = $article;
        $code = 0;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'data' => $data, 'msg' => $msg];
    }

    public function actionLive()
    {
        $limit = Yii::$app->request->get('limit', 20);
        $id = Yii::$app->request->get('id', 0);
        $flag = Yii::$app->request->get('flag', 'down');


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
            'limit' => $limit,
            'id' => $id,
            'flag' => $flag
        ];

        $url = $coindog['live'] . '?' . http_build_query($httpParams);
        $curl = new Curl();
        $resp = $curl->get($url, false);
        if (!isset($resp['list'])) {
            $msg = '数据不存在';
            $code = -1;
            $data = null;
            goto doEnd;
        }
        $list = $resp['list'][0]['lives'];
        foreach ($list as &$item) {
            preg_match_all("/\【(.+?)\】/", $item['content'], $match);
            $item['title'] = $match[1][0];
            $item['content'] = str_replace('【' . $item['title'] . '】', '', $item['content']);
            $item['source'] = '金色财经';
        }
        $code = 0;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'data' => $list, 'msg' => $msg];
    }

    public function actionArticleBanner()
    {
        $type = Yii::$app->request->get('type', 1);
        $data = News::find()
            ->select('id, image_url ,title, url')
            ->where(['type' => $type])
            ->orderBy('update_at desc')
            ->limit(5)
            ->asArray()
            ->all();

        $code = 0;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'data' => $data, 'msg' => $msg];

    }
}