<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-11
 * Time: 下午5:04
 */

namespace backend\controllers;

use common\models\psources\Coin;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinRecommend;
use Yii;
use yii\web\Response;

/**
 * Class CoinRecommendController
 *
 * 推介币种管理
 *
 * @package backend\controllers
 */
class CoinRecommendController extends BaseController
{
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get         = Yii::$app->request->get();
            $platform    = $get['platform_id'] ?? 1;
            $page        = $get['page'] ?? 1;
            $limit       = $get['limit'] ?? 10;
            $platform_id = $get['platform_id'] ?? '';
            $recommend   = $get['recommend'] ?? '';

            $user_platform_id = Yii::$app->user->identity->platform_id;
            if (1 != $user_platform_id) {
                $platform_id = $user_platform_id;
            }

            $condition = [];
            if (!empty($recommend)) {
                $condition['recommend'] = $recommend;
            }
            if (!empty($platform_id)) {
                $condition['platform_id'] = $platform_id;
            }
            //todo 获取推介币种
            $datas = CoinRecommend::getList($page, $limit, $condition, ['sort' => SORT_DESC]);
            return $datas;
        }
        $user_platform_id = Yii::$app->user->identity->platform_id;
        if ($user_platform_id == 1) {
            $platforms = CoinPlatform::find()->asArray()->all();
        } else {
            $platforms = CoinPlatform::find()->where(['id' => $user_platform_id])->asArray()->all();
        }
        $chains = Coin::getPlatformList();
        return $this->render('index', ['platforms' => $platforms, 'chains' => $chains]);
    }

    public function actionAdd()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get = Yii::$app->request->get();

            $platform_id = empty($get['platform_id']) ? 1 : $get['platform_id'];

            $user_platform_id = Yii::$app->user->identity->platform_id;
            if (1 != $user_platform_id && $user_platform_id != $platform_id) {
                return ['code' => -1, 'msg' => '没有权限修改'];
            }
            return CoinRecommend::addRecommendCoin($get);
        }
    }

    public function actionDel()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id = Yii::$app->request->get('id', null);
            if (empty($id)) {
                return ['code' => -1, 'msg' => '删除失败'];
            }
            $coin_recommend = CoinRecommend::findOne($id);
            if (empty($coin_recommend)) {
                return ['code' => -1, 'msg' => '推荐币种不存在'];
            }
            $coin_platform_id = $coin_recommend->platform_id;
            $user_platform_id = Yii::$app->user->identity->platform_id;
            if ($user_platform_id == 1 || $coin_platform_id == $user_platform_id) {
                if ($coin_recommend->delete()) {
                    return ['code' => 0, 'msg' => '删除成功'];
                }
            } else {
                return ['code' => -1, 'msg' => '没有权限'];
            }
            return ['code' => -1, 'msg' => '删除失败'];
        }
    }
}
