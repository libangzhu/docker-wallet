<?php

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\psources\CoinClientParams;

class ClientParamsController extends BaseController
{
    public function actionIndex()
    {
        $code = 0;
        $msg = 'success';

        $data = CoinClientParams::find()->asArray()->all();
        if(empty($data)){
            $msg = '数据不存在';
            $data = null;
            $code = -1;
            goto doEnd;
        }
        foreach ($data as &$val) {
            if(strpos( $val['url'], ',') !== false) {
                $val['url'] = explode(',', $val['url']);
            } else {
                $val['url'] = [$val['url']];
            }
        }

        doEnd :
        return ['code' => $code, 'data' => $data, 'msg' => $msg];
    }

}