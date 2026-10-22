<?php

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\psources\CoinPlatformBanner;

class CoinPlatformBannerController extends BaseController
{
    public function actionIndex()
    {
        $code = 0;
        $msg = 'success';
        $platform_id = Yii::$app->request->get('platform_id', '');
        $type = Yii::$app->request->get('type', '');
        if (empty($platform_id) || empty($type)) {
            $msg = '参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $data = CoinPlatformBanner::find()->where(['platform_id' => $platform_id, 'type' => $type])->asArray()->all();

        doEnd :
        return ['code' => $code, 'data' => $data, 'msg' => $msg];
    }
}