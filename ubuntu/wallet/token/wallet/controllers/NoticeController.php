<?php

namespace wallet\controllers;

use common\models\pwallet\Notice;
use common\models\User;
use Yii;
use yii\data\Pagination;
use wallet\base\BaseController;

class NoticeController extends BaseController
{

    public function actionIndex()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 10);
        $group = Yii::$app->request->getGroup();

        if (Yii::$app->request->isPost) {
            if (User::AUTH_SUPER == $group) {
                $platform_id = Yii::$app->request->post('platform_id', Yii::$app->request->getPlatformId());
            } else {
                $platform_id = Yii::$app->request->getPlatformId();
            }

            $model = new Notice();
            $model->setScenario(Notice::SCENARIOS_CREATE);
            $params = Yii::$app->request->post();
            $params['platform_id'] = $platform_id;
            $model->load($params, '');
            if (!$model->validate()) {
                $msg = $model->errors;
                $code = -1;
                goto doEnd;
            }
            $model->save();
            goto doEnd;
        }

        if (Yii::$app->request->isGet) {
            if (User::AUTH_SUPER == $group) {
                $platform_id = Yii::$app->request->get('platform_id', Yii::$app->request->getPlatformId());
            } else {
                $platform_id = Yii::$app->request->getPlatformId();
            }
            $status = Yii::$app->request->get('status', 0);
            $type = Yii::$app->request->get('type', 0);
            $query = Notice::find()->where(['platform_id' => $platform_id]);

            if (false != $status) {
                $query->andWhere(['status' => (int)$status]);
            }
            if (false != $type) {
                $query->andWhere(['type' => (int)$type]);
            }
            $model = $query->offset(($page - 1) * $size)->orderBy('create_time desc')->limit($size)->asArray()->all();

            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $size]);
            $data = [
                'list' => $model,
                'page' => [
                    'pageCount' => $pages->pageCount,
                    'pageSize' => $size,
                    'currentPage' => $page,
                ]
            ];
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
            $data = Notice::find()->select('id, title, content, author, status, type, is_top')->where(['platform_id' => $platform_id, 'id' => $id])->asArray()->one();
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
            $model = Notice::findOne($id);
            if (empty($model)) {
                $msg = '数据错误';
                $code = -1;
                goto doEnd;
            }
            if (User::AUTH_SUPER != $group && $platform_id != $model->platform_id){
                $msg = '无权操作';
                $code = -1;
                goto doEnd;
            }
            $model->setScenario(Notice::SCENARIOS_UPDATE);
            $model->load($params, '');
            if (!$model->validate()) {
                $msg = $model->errors;
                $code = -1;
                goto doEnd;
            }
            $model->save();
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
        $group = Yii::$app->request->getGroup();
        $platform_id = Yii::$app->request->getPlatformId();
        $id = Yii::$app->request->get('id');
        if (false == $id) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }
        if (Yii::$app->request->isDelete) {
            $model = Notice::findOne($id);
            if (User::AUTH_SUPER != $group && $platform_id != $model->platform_id){
                $msg = '无权删除';
                $code = -1;
                goto doEnd;
            }

            if (!$model->delete()) {
                $msg = '删除失败';
                $code = -1;
                goto doEnd;
            }
        }

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}