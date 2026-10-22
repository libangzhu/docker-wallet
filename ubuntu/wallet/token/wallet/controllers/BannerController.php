<?php

namespace wallet\controllers;

use Yii;
use yii\data\Pagination;
use wallet\base\BaseController;
use common\models\psources\CoinBannerItem;

class BannerController extends BaseController
{
    public function actionType()
    {
        $data = array_unique(Yii::$app->params['banner_type']);

        return ['code' => 0, 'msg' => 'success', 'data' => $data];
    }

    public function actionIndex()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        if (Yii::$app->request->isGet) {
            $type = Yii::$app->request->get('type', 0);
            $page = Yii::$app->request->get('page', 1);
            $size = Yii::$app->request->get('size', 10);
            $platform_id = Yii::$app->request->get('platform_id', 0);
            if (false == $platform_id) {
                $msg = '参数错误';
                $code = -1;
                goto doEnd;
            }

            $query = CoinBannerItem::find()->where(['platform_id' => $platform_id])->orderBy('sort');
            if (false != $type) {
                $query->andWhere(['type' => $type]);
            }
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $size]);
            $models = $query->offset($pages->offset)
                ->limit($pages->limit)
                ->all();

            $data = [
                'list' => $models,
                'page' => [
                    'pageCount' => $pages->pageCount,
                    'pageSize' => $size,
                    'currentPage' => (int)$page,
                ]
            ];
        }
        if (Yii::$app->request->isPost) {
            $model = new CoinBannerItem();
            $model->setScenario(CoinBannerItem::SCENARIOS_CREATE);
            $model->load(Yii::$app->request->post(), '');
            if (!$model->validate()) {
                $msg = $model->errors;
                $code = -1;
                goto doEnd;
            }
            $model->save();
        }

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionUpdate()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->getPlatformId();
        if (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id');
            if ('administrator' == $group) {
                $data = CoinBannerItem::find()->where(['id' => $id])->asArray()->one();
            } else {
                $data = CoinBannerItem::find()->where(['platform_id' => $platform_id, 'id' => $id])->asArray()->one();
            }
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
            if ('administrator' != $group && $model->platform_id != $platform_id) {
                $msg = '无权修改';
                $code = -1;
                goto doEnd;
            }

            $model->setScenario(CoinBannerItem::SCENARIOS_UPDATE);
            $model->load(array_merge(Yii::$app->request->post(), ['platform_id' => $model->platform_id]), '');
            if (!$model->validate()) {
                $msg = $model->errors;
                $code = -1;
                goto doEnd;
            }
            $model->save();
        }
        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionRemove()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->getPlatformId();
        $id = Yii::$app->request->get('id');
        if (false == $id) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }
        if (Yii::$app->request->isDelete) {
            $model = CoinBannerItem::findOne($id);
            if ('administrator' != $group && $model->platform_id != $platform_id) {
                $msg = '无权删除';
                $code = -1;
                goto doEnd;
            }
            $model->delete();
        }

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}