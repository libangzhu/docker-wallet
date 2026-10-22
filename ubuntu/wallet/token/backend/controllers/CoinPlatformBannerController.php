<?php

namespace backend\controllers;

use Yii;
use common\models\psources\CoinPlatformBanner;

class CoinPlatformBannerController extends BaseController
{
    public function actionAdd()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $image_url = $request->post('image_url', '');
            $type = $request->post('banner_type', 1);
            if ($image_url) {
                $banner_item = new CoinPlatformBanner();
                $banner_item->image_url = $image_url;
                $banner_item->platform_id = $user_platform_id;
                $banner_item->type = $type;
                $banner_item->save();
                return ['code' => 0, 'msg' => 'banner添加成功'];
            } else {
                return ['code' => 1, 'msg' => 'banner添加加失败'];
            }
        }
    }

    /**
     * @return array
     * banner删除
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            if ($id) {
                $banner = CoinPlatformBanner::getBanner($id);
                if (!$banner) {
                    return ['code' => 1, 'msg' => 'banner不存在,不能删除'];
                }
                $banner->delete();
                return ['code' => 0, 'msg' => 'banner删除成功'];
            } else {
                return ['code' => 1, 'msg' => 'banner删除失败'];
            }
        }
    }

    /**
     * banner列表
     */
    public function actionIndex()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $condition = [];
            if (1 !== $user_platform_id) {
                $condition = ['platform_id' => $user_platform_id];
            }
            $data = CoinPlatformBanner::getItems($condition);
            foreach ($data as $key => &$val) {
                $val['coin_name'] = isset($val['platform']['name']) ? $val['platform']['name'] : '';
            }
            return ['data' => $data, 'code' => 0];
        }
        return $this->render('index');
    }

}