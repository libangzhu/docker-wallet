<?php

namespace api\controllers;

use Yii;
use api\base\BaseController;

class ValidateController extends BaseController
{
    /**
     * 黑名单列表
     * @return array
     */
    public function actionBlackList()
    {
        $msg = 'ok';
        $code = 0;
        $redis_ticker = \Yii::$app->redis_es;
        $list = $redis_ticker->keys('*');
        return ['code' => $code, 'msg' => $msg, 'data' => $list];
    }
}