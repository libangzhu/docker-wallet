<?php

namespace backend\controllers;

use backend\models\coin\CoinSupportedSymbolForm;
use common\models\psources\Coin;
use common\models\psources\CoinSupportedSymbol;
use Yii;


class CoinSupportedSymbolController extends BaseController
{
    public function actionList()
    {
        $platform_id = Yii::$app->request->get('id');
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $id = $request->get('id', '');
            $where = [];
            if ($id) {
                $where[] = ['platform_id' => $id];
            }
            $data = CoinSupportedSymbol::getList($page, $limit, $where);
            foreach ($data['data'] as &$val) {
                $val->currency = $val->coinInfo->name;
                $val->base_currency = $val->baseCurrencyInfo->name;
            }

            Yii::$app->response->format = 'json';
            return $data;
        }

        return $this->render('index', ['platform_id' => $platform_id]);
    }

    public function actionAdd()
    {
        $this->layout = false;
        $model = new CoinSupportedSymbolForm();
        $model->scenario = 'add';
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->validate()) {
                $currency = Coin::find()->select('id')->where(['name' => $data['currency']])->asArray()->one();
                $base_currency = Coin::find()->select('id')->where(['name' => $data['base_currency']])->asArray()->one();
                $coinSupportedSymbol = new CoinSupportedSymbol();
                $coinSupportedSymbol->platform_id = $data['platform_id'];
                $coinSupportedSymbol->currency = $currency['id'];
                $coinSupportedSymbol->base_currency = $base_currency['id'];
                $result = $coinSupportedSymbol->save();
                if ($result != true) {
                    return ['code' => -1, 'msg' => current($model->firstErrors)];
                }
            }
            return ['code' => 0, 'msg' => 'succeed'];
        }
        $platform_id = Yii::$app->request->get('platform_id');
        return $this->render('add', ['model' => $model, 'platform_id' => $platform_id]);
    }

    public function actionDelete()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->get('id', 0);
        if ($id) {
            $model = CoinSupportedSymbol::findOne(['id' => $id]);
            if ($model) {
                try {
                    $model->delete();
                    return ['code' => 0, 'msg' => 'succeed'];
                } catch (\Throwable $t) {
                    return ['code' => $t->getCode(), 'msg' => $t->getMessage()];
                }
            }
        }
        return ['code' => -1, 'msg' => '删除失败'];
    }
}