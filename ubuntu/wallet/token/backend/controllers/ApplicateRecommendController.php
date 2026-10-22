<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/16
 * Time: 20:44
 */

namespace backend\controllers;

use common\models\psources\CoinApplicateRecommend;
use common\models\psources\CoinApplication;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinImage;
use Yii;


class ApplicateRecommendController extends BaseController
{
    /**
     * @return array
     * 首页推荐添加
     */
    public function actionAdd()
    {

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $type = $request->get('type', '');
            if ($id && $type) {
                $recommend = CoinApplicateRecommend::getRecommend($id, $type);
                if ($recommend) {
                    return ['code' => 1, 'msg' => '分类/应用已经添加为首页推荐，不能再添加'];
                }
                $count = CoinApplicateRecommend::getRecommendCount();
                if ($count >= 8) {
                    return ['code' => 1, 'msg' => '首页推荐分类/应用数量已经8个,无法再添加'];
                }
                if ($type == 1) { //分类
                    $applicate_category = CoinApplicationCategory::getCategoryById($id);
                    $name = $applicate_category->name;
                    $user_platform_id = $applicate_category->platform_id;
                } else {  //应用
                    $applicate = CoinApplication::getApplicate($id);
                    $name = $applicate->name;
                    $user_platform_id = $applicate->platform_id;
                }
                $recommend = new CoinApplicateRecommend();
                $recommend->relate_id = $id;
                $recommend->type = $type;
                $recommend->name = $name;
                $recommend->platform_id = $user_platform_id;
                $recommend->save();
                return ['code' => 0, 'msg' => '首页推荐添加成功'];
            } else {
                return ['code' => 1, 'msg' => '首页推荐添加失败'];
            }
        }
    }

    /**
     * @return array
     * 首页推荐删除
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $type = $request->get('type', '');
            if ($id && $type) {
                $recommend = CoinApplicateRecommend::getRecommend($id, $type);
                if (!$recommend) {
                    return ['code' => 1, 'msg' => '首页推荐不存在,不能删除'];
                }
                $recommend->delete();
                return ['code' => 0, 'msg' => '首页推荐删除成功'];
            } else {
                return ['code' => 1, 'msg' => '首页推荐删除失败'];
            }
        }
    }

    /**
     * 应用/分类推荐列表
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
            $data = CoinApplicateRecommend::find()->joinWith('platform')->where($condition)->orderBy('sort asc')->asArray()->all();

            $icon_Items = array_column($data, 'icon');
            $icon_Infos = CoinImage::getItemsByIds($icon_Items);
            foreach ($data as &$value) {
                $value['coin_name'] = isset($value['platform']['name']) ? $value['platform']['name'] : '';
                if ($value['icon']) {
                    $value['icon_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_Infos[$value['icon']]['file_url'];
                } else {
                    $value['icon_url'] = '';
                }
            }
            return ['data' => $data, 'code' => 0];
        }
        return $this->render('index');
    }

    /**
     * @return array
     * 设置首页推荐排序
     */
    public function actionSetSort()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $type = $request->get('type', '');
            $sort = $request->get('sort', 1);
            if ($id && $type) {
                $recommend = CoinApplicateRecommend::getRecommend($id, $type);
                if (!$recommend) {
                    return ['code' => 1, 'msg' => '首页推荐不存在,不能设置排序'];
                }
                $recommend->sort = $sort;
                $recommend->save();
                return ['code' => 0, 'msg' => '首页推荐排序设置成功'];
            } else {
                return ['code' => 1, 'msg' => '首页推荐排序设置失败'];
            }
        }
    }

    /**
     * @return array
     * 设置首页推荐图标
     */
    public function actionSetIcon()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $id = Yii::$app->request->post('id', 0);
            $icon = Yii::$app->request->post('icon', 0);
            if ($id && $icon) {
                $recommend = CoinApplicateRecommend::getRecommendById($id);
                if ($recommend) {
                    $recommend->icon = $icon;
                    $recommend->save();
                    return ['code' => 0, 'msg' => '图片添加成功'];
                } else {
                    return ['code' => 1, 'msg' => '首页推荐不存在'];
                }
            } else {
                return ['code' => 1, 'msg' => '图片添加失败'];
            }
        }
    }


}