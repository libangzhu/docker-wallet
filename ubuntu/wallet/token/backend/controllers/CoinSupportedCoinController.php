<?php

namespace backend\controllers;

use common\models\psources\CoinSupportedCoin;
use Yii;


class CoinSupportedCoinController extends BaseController
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
            $data = CoinSupportedCoin::getList($page, $limit, $where);
            $data['code'] = 0;
            Yii::$app->response->format = 'json';
            return $data;
        }

        return $this->render('index', ['platform_id' => $platform_id]);
    }

    public function actionAdd()
    {
        $this->layout = false;
        $model = new CoinSupportedCoin();
        $model->setScenario(CoinSupportedCoin::SCENARIOS_CREATE);
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->save()) {
                return ['code' => 0, 'msg' => 'succeed'];
            }
            $error = $model->errors;
            if ($error) {
                return ['code' => -1, 'msg' => current($model->firstErrors)];
            }
        }
        $platform_id = Yii::$app->request->get('platform_id');
        return $this->render('add', ['model' => $model, 'platform_id' => $platform_id]);
    }

    public function actionDelete()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->get('id', 0);
        if ($id) {
            $model = CoinSupportedCoin::findOne(['id' => $id]);
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