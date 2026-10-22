<?php

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\psources\ExploreApp;
use common\models\psources\CoinBannerItem;
use common\models\psources\ExploreAppCategory;

class ExploreController extends BaseController
{
    public function actionIndex()
    {
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $data = null;

        if (false == $platform_id) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }

        $app_category_model = ExploreAppCategory::find()->where(['platform_id' => $platform_id, 'status' => ExploreAppCategory::STATUS_ON])->orderBy('sort')->all();
        if (false == $app_category_model) {
            $msg = 'success';
            $code = 0;
            goto doEnd;
        }
        foreach ($app_category_model as $key => $val) {
            unset($val->apps);
            $val->name = isset($val->name[$this->lang]) ? $val->name[$this->lang] : '';
            $apps_model = ExploreApp::find()->select('id, name, icon, type, app_url, slogan, email, phone, real_name')
                ->where(['app_category_id' => (int)$val->id, 'status' => ExploreApp::STATUS_ON])
                ->orderBy('sort')
                ->limit($val->limit)
                ->all();
            if (empty($apps_model)) {
                unset($app_category_model[$key]);
                continue;
            }
            foreach ($apps_model as &$app) {
                $app->name = isset($app->name[$this->lang]) ? $app->name[$this->lang] : '';
            }
            $val->apps = $apps_model;
            unset($val->sort);
            unset($val->limit);
            unset($val->platform_id);
        }
        foreach ($app_category_model as $val) {
            $data[] = $val;
        }
        $msg = 'success';
        $code = 0;
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionCategory()
    {
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $category_id = Yii::$app->request->get('id', 0);
        if (false == $platform_id || false == $category_id) {
            $msg = '参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $app_category_model = ExploreAppCategory::find()->where(['id' => (int)$category_id, 'platform_id' => $platform_id])->all();
        if (false == $app_category_model) {
            $msg = 'success';
            $code = 0;
            $data = null;
            goto doEnd;
        }
        foreach ($app_category_model as &$val) {
            foreach ($val->applications as $app) {
                $app->name = isset($app->name[$this->lang]) ? $app->name[$this->lang] : '';
            }
            $val->name = isset($val->name[$this->lang]) ? $val->name[$this->lang] : '';
            $val->apps = $val->applications;
            unset($val->id);
            unset($val->sort);
            unset($val->style);
            unset($val->limit);
            unset($val->platform_id);
        }
        $data = $app_category_model;
        $msg = 'success';
        $code = 0;
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionSearch()
    {
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $ids = Yii::$app->request->get('ids', '');
        if (false == $platform_id || false == $ids) {
            $msg = '参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $id_arr = explode(',', $ids);
        $order = "FIELD(`id`,$ids)";
        $apps_model = ExploreApp::find()->select('id, name, icon, type, app_url, slogan, email, phone, real_name')
            ->where(['status' => ExploreApp::STATUS_ON])
            ->andWhere(['in', 'id', $id_arr])
            ->orderBy([$order => true])
            ->limit(4)
            ->all();
        if (false == $apps_model) {
            $msg = 'success';
            $code = 0;
            $data = null;
            goto doEnd;
        }
        foreach ($apps_model as &$app) {
            $app->name = isset($app->name[$this->lang]) ? $app->name[$this->lang] : '';
        }
        $data = $apps_model;
        $msg = 'success';
        $code = 0;
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionBanner()
    {
        $msg = 'ok';
        $code = 0;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        if (false == $platform_id) {
            $msg = '参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $coin_banner = CoinBannerItem::find()->select('banner_url, image_url, title')->where(['platform_id' => $platform_id])->asArray()->all();
        $data = $coin_banner;

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}