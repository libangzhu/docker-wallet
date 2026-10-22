<?php

namespace wallet\controllers;

use Yii;
use wallet\base\BaseController;
use common\models\psources\WalletCoin;

class CoinRelationController extends BaseController
{
    public function actionIndex()
    {
        if (Yii::$app->request->isGet) {
            $page = \Yii::$app->request->get('page', 1);
            $size = \Yii::$app->request->get('size', 50);
            $name = \Yii::$app->request->get('name', '');
            $chain = \Yii::$app->request->get('chain', '');
            $platform = \Yii::$app->request->get('platform', '');
            $platform_id = \Yii::$app->request->get('platform_id', '');
            $query = WalletCoin::find()
                ->select('id, name, nickname, icon, introduce, official, paper, platform, chain, release, area_search, publish_count, circulate_count, decimals, address, treaty, platform_id')
                ->where("JSON_CONTAINS(platform_id,'$platform_id')");
            if (false != $name) {
                $query->andWhere(['name' => $name]);
            }

            if (false != $chain) {
                $query->andWhere(['chain' => $chain]);
            }

            if (false != $platform) {
                $query->andWhere(['platform' => $platform]);
            }

            $model = $query->offset(($page - 1) * $size)->orderBy('id desc')->limit($size)->all();

            $countQuery = clone $query;
            $this->data = [
                'items' => $model,
                'total' => $countQuery->count()
            ];

            goto doEnd;
        }

        if (Yii::$app->request->isPost) {
            $params = Yii::$app->request->post();
            $coin_id = isset($params['coin_id']) ? $params['coin_id'] : '';
            $platform_id = isset($params['platform_id']) ? (int)$params['platform_id'] : 0;

            if (false == $coin_id || false == $platform_id) {
                $this->code = 20001;
                $this->msg = 'Validation failed.';
                goto doEnd;
            }

            $coin_id_arr = explode(',', $coin_id);
            foreach ($coin_id_arr as $coin_id) {
                $coin = WalletCoin::find()->where(['id' => $coin_id])->one();
                if (empty($coin)) {
                    continue;
                }
                if (strpos($coin->platform_id, ",$platform_id,") !== false) {
                    goto doEnd;
                }
                $coin->platform_id = $coin->platform_id . ",$platform_id";
                $coin->save();
            }
            goto doEnd;
        }

        if (Yii::$app->request->isDelete) {
            $coin_id = \Yii::$app->request->get('coin_id', 0);
            $platform_id = \Yii::$app->request->get('platform_id', 0);
            if (false == $coin_id || false == $platform_id) {
                $this->code = 20001;
                $this->msg = 'Validation failed.';
                goto doEnd;
            }

            $coin = WalletCoin::find()->where(['id' => $coin_id])->one();
            if (empty($coin)) {
                goto doEnd;
            }
            $platform_id_arr = explode(',', $coin->platform_id);
            $temp = '';
            foreach ($platform_id_arr as $val){
                if ($platform_id == $val) continue;
                $temp .= $val.',';
            }
            $coin->platform_id = rtrim($temp, ',');
            $coin->save();
            goto doEnd;
        }

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }
}