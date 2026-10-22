<?php

namespace backend\controllers;

use backend\models\coin\CoinPlatformWithholdForm;
use common\models\psources\CoinPlatformWithHold;
use Yii;

class CoinPlatformWithholdController extends BaseController
{

    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $platform = $request->get('platform', '');
            $where = [];
            if ($platform) {
                $where[] = ['platform' => $platform];
            }
            $data = CoinPlatformWithHold::getList($page, $limit, $where);
            return $data;
        }
        return $this->render('index');
    }

    public function actionAdd()
    {
        $model = new CoinPlatformWithholdForm();
        $model->scenario = 'add';
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->validate()) {
                $coin = Yii::createObject(CoinPlatformWithHold::className());
                $result = $coin->addOne($data);
                if ($result === true) {
                    $this->success('添加成功', '/admin/coin-platform-withhold/index');
                }
            }
            //表单验证失败
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            } elseif (isset($result) && $result['code'] != 0) {
                $errors = $result['message'];
            }
            $this->error($errors, Yii::$app->request->getReferrer());
        }
        return $this->render('add', ['model' => $model]);
    }

    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            $model = new CoinPlatformWithholdForm();
            $model->scenario = 'update';
            $data = Yii::$app->request->post();
            Yii::$app->response->format = 'json';
            if ($model->load($data, '') && $model->validate()) {
                $coin = Yii::createObject(CoinPlatformWithHold::className());
                $result = $coin->updateOne($data);
                if ($result === true) {
                    return ['code' => 0, 'msg' => 'succeed'];
                }
            }
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            } elseif (isset($result) && $result['code'] != 0) {
                $errors = $result['message'];
            }
            return ['code' => 1, 'msg' => $errors];
        } elseif (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id', null);
            if ($id) {
                $coin = CoinPlatformWithHold::findOne(['id' => $id]);
                $this->layout = false;
                return $this->render('edit', ['model' => $coin]);
            }
        }
    }
}