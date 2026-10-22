<?php

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\psources\ExploreApp;
use common\models\psources\CoinBannerItem;
use common\models\psources\ExploreAppCategory;

class BannerController extends BaseController
{
    public function actionIndex()
    {
        $header = Yii::$app->request->headers;
        $type = Yii::$app->request->get('type', 0);
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $data = null;

        if (false == $platform_id || false == $type) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }

        $model = CoinBannerItem::find()
            ->select('banner_url, image_url, title, sort')
            ->where(['platform_id' => $platform_id, 'type' => $type, 'status' => CoinBannerItem::STATUS_ON])
            ->orderBy('sort')
            ->asArray()
            ->all();
        $data = $model;
        $msg = 'success';
        $code = 0;
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}