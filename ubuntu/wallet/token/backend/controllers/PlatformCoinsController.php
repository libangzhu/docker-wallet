<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/17
 * Time: 10:13
 */

namespace backend\controllers;

use backend\models\coin\CoinPlatformForm;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinPlatformCoins;
use Yii;


class PlatformCoinsController extends BaseController
{
    public function actionList()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $coins_address = $request->get('coins_address', '**');
            $where = [];
            if ($coins_address) {
                $where[] = ['coins_address' => $coins_address];
            }
            $data = CoinPlatformCoins::getList($page, $limit, $where);
            foreach ($data['data'] as &$val){
                unset($val['id']);
                unset($val['coins_name']);
                unset($val['type']);
                unset($val['platform_id']);
                unset($val['update_time']);
            }
            $data['code'] = 0;
            Yii::$app->response->format = 'json';
            return $data;
        }
        return $this->render('index');
    }
}