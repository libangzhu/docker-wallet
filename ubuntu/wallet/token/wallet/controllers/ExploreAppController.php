<?php

namespace wallet\controllers;

use Yii;
use yii\data\Pagination;
use wallet\base\BaseController;
use common\models\psources\ExploreApp;
use common\models\psources\ExploreAppCategory;

class ExploreAppController extends BaseController
{

    public function actionCategory()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 10);

        $group = Yii::$app->request->getGroup();

        if (Yii::$app->request->isPost) {
            $params = Yii::$app->request->post();
            if ('administrator' == $group) {
                $params['platform_id'] = isset($params['platform_id']) ? $params['platform_id'] : Yii::$app->request->getPlatformId();
            } else {
                $params['platform_id'] = Yii::$app->request->getPlatformId();
            }

            $model = new ExploreAppCategory();
            $model->setScenario(ExploreAppCategory::SCENARIOS_CREATE);
            $name_arr = $params['name'];
            $name = [];

            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }
            unset($params['name']);
            $params['name'] = $name;
            if ($model->load($params, '') && $model->save()) {
                goto doEnd;
            }
            $msg = $model->errors;
            $code = -1;
            goto doEnd;
        }

        if (Yii::$app->request->isGet) {
            $platform_id = Yii::$app->request->getPlatformId();
            if ('administrator' == $group) {
                $platform_id = Yii::$app->request->get('platform_id', 1);
            }

            $query = ExploreAppCategory::find()->where(['platform_id' => $platform_id])->asArray();
            $app_category_model = $query->offset(($page - 1) * $size)->orderBy('sort')->limit($size)->asArray()->all();
            foreach ($app_category_model as &$val) {
                $name = json_decode($val['name'], true);
                $val['name'] = $name;
                unset($val['platform_id']);
            }
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => '10']);
            $data = [
                'list' => $app_category_model,
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

    public function actionCategoryUpdate()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();

        if (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id');
            $data = ExploreAppCategory::find()->select('id, name, sort, limit, style, status')->where(['platform_id' => $platform_id, 'id' => $id])->asArray()->one();
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
            $model = ExploreAppCategory::findOne($id);

            $group = Yii::$app->request->getGroup();
            if ('administrator' != $group && $model->platform_id != $platform_id) {
                $msg = '无权修改';
                $code = -1;
                goto doEnd;
            }

            $model->setScenario(ExploreAppCategory::SCENARIOS_UPDATE);
            unset($params['id']);
            $name_arr = $params['name'];
            $name = [];

            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }
            unset($params['name']);
            $params['name'] = $name;
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

    public function actionCategoryRemove()
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
            $model_app = ExploreApp::find()->where(['app_category_id' => (int)$id])->asArray()->all();
            if (false != $model_app) {
                $msg = '请先删除该类别下的应用';
                $code = -1;
                goto doEnd;
            }
            $model = ExploreAppCategory::find()->where(['id' => $id])->one();

            $group = Yii::$app->request->getGroup();
            if ('administrator' != $group && $model->platform_id != $platform_id) {
                $msg = '无权修改';
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

    public function actionApps()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();
        if (Yii::$app->request->isGet) {
            $category_id = Yii::$app->request->get('id', 0);
            $page = Yii::$app->request->get('page', 1);
            $size = Yii::$app->request->get('size', 10);
            if (false == $platform_id || false == $category_id) {
                $msg = '参数错误';
                $code = -1;
                $data = null;
                goto doEnd;
            }
            $query = ExploreApp::find()
                ->where(['app_category_id' => $category_id])
                ->andWhere(['platform_id' => $platform_id])
                ->asArray();
            $app_model = $query->offset(($page - 1) * $size)->orderBy('sort')->limit($size)->asArray()->all();
            foreach ($app_model as &$val) {
                $name = json_decode($val['name'], true);
                $val['name'] = $name;
            }
            $countQuery = clone $query;
            $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => '10']);
            $data = [
                'list' => $app_model,
                'page' => [
                    'pageCount' => $pages->pageCount,
                    'pageSize' => $size,
                    'currentPage' => $page,
                ]
            ];
            goto doEnd;
        }

        if (Yii::$app->request->isPost) {
            $model = new ExploreApp();
            $model->setScenario(ExploreApp::SCENARIOS_CREATE);
            $params = Yii::$app->request->post();
            $AppCategory = ExploreAppCategory::find()->select('platform_id')->where(['id' => $params['app_category_id']])->asArray()->one();
            $params['platform_id'] = (int)$AppCategory['platform_id'] ?? 0;
            $params['email'] = isset($params['email']) ? $params['email'] : 0;
            $params['phone'] = isset($params['phone']) ? $params['phone'] : 0;
            $params['real_name'] = isset($params['real_name']) ? $params['real_name'] : 0;
            $name_arr = $params['name'];
            $name = [];

            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }
            unset($params['name']);
            $params['name'] = $name;

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

    public function actionAppUpdate()
    {
        $msg = 'ok';
        $code = 0;
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();

        if (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id');
            $data = ExploreApp::find()->select('id, name, icon, app_url, slogan, type, sort, status, email, phone, real_name')->where(['platform_id' => $platform_id, 'id' => $id])->asArray()->one();
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
            $model = ExploreApp::findOne($id);

            $group = Yii::$app->request->getGroup();
            if ('administrator' != $group && $model->platform_id != $platform_id) {
                $msg = '无权修改';
                $code = -1;
                goto doEnd;
            }

            $model->setScenario(ExploreApp::SCENARIOS_UPDATE);
            unset($params['id']);
            $params['platform_id'] = $platform_id;
            $params['email'] = isset($params['email']) ? $params['email'] : 0;
            $params['phone'] = isset($params['phone']) ? $params['phone'] : 0;
            $params['real_name'] = isset($params['real_name']) ? $params['real_name'] : 0;
            $name_arr = $params['name'];
            $name = [];

            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }
            unset($params['name']);
            $params['name'] = $name;
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

    public function actionAppRemove()
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
            $model = ExploreApp::find()->where(['id' => $id])->one();

            $group = Yii::$app->request->getGroup();
            if ('administrator' != $group && $model->platform_id != $platform_id) {
                $msg = '无权修改';
                $code = -1;
                goto doEnd;
            }

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