<?php

namespace wallet\controllers;

use Yii;
use yii\data\Pagination;
use wallet\base\BaseController;
use common\models\psources\CoinBannerItem;

class ExploreBannerController extends BaseController
{
    public function actionIndex()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();
        $page = Yii::$app->request->get('page', 1);
        if (Yii::$app->request->isGet) {
            $query = CoinBannerItem::find()
                ->select('id, banner_url, image_url, title')
                ->where(['platform_id' => $platform_id])
                ->asArray();
            $banner_model = $query->offset(($page - 1) * 10)->limit(10)->asArray()->all();
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => '10']);
            $data = [
                'list' => $banner_model,
                'page' => [
                    'pageCount' => $pages->pageCount,
                    'pageSize' => 10,
                    'currentPage' => $page,
                ]
            ];
            goto doEnd;
        }
        if (Yii::$app->request->isPost) {
            $model = new CoinBannerItem();
            $model->setScenario(CoinBannerItem::SCENARIOS_CREATE);
            $params = Yii::$app->request->post();
            $params['platform_id'] = $platform_id;
            if ($model->load($params, '') && $model->save()) {
                goto doEnd;
            }
            $msg = $model->errors;
            $code = -1;
            goto doEnd;
        }
        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionUpdate()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();

        if (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id');
            $data = CoinBannerItem::find()->select('id, banner_url, image_url, title')->where(['platform_id' => $platform_id, 'id' => $id])->asArray()->one();
            goto doEnd;
        }

        if (Yii::$app->request->isPut) {
            $params = Yii::$app->request->post();
            $id = isset($params['id']) ? $params['id'] : null;
            if (false == $id) {
                $msg = '参数错误';
                $code = -1;
                goto doEnd;
            }
            $model = CoinBannerItem::findOne($id);
            $model->setScenario(CoinBannerItem::SCENARIOS_UPDATE);
            unset($params['id']);

            if ($model->load($params, '') && $model->save()) {
                goto doEnd;
            }
            $msg = $model->errors;
            $code = -1;
            goto doEnd;
        }
        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionRemove()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();
        $id = Yii::$app->request->get('id');
        if (false == $id) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }
        if (Yii::$app->request->isDelete) {
            $model = CoinBannerItem::find()->where(['id' => $id, 'platform_id' => $platform_id])->one();
            if (false == $model || !$model->delete()) {
                $msg = '删除失败';
                $code = -1;
                goto doEnd;
            }
        }

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}