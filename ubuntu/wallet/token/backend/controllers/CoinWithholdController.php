<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-12 16:10:28
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace backend\controllers;

use common\models\psources\Coin;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinWithhold;
use common\models\psources\CoinWithholdToken;
use Yii;
use yii\web\Response;

class CoinWithholdController extends BaseController
{
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get   = Yii::$app->request->get();
            $page  = $get['page'] ?? 1;
            $limit = $get['limit'] ?? 10;
            if (Yii::$app->params['admin'] == Yii::$app->user->id) {
                $datas = CoinWithhold::getList2($page, $limit, []);
            } else {
                $datas = CoinWithhold::getList2($page, $limit, ['platform_id' => Yii::$app->user->identity->platform_id]);
            }
            $withholds  = &$datas['data'];
            $coin_ids   = array_column($withholds, 'cid');
            $coin_infos = Coin::getCoinInfoByIds($coin_ids, ['id', 'name'], 'id');
            $platforms  = CoinPlatform::find()->asArray()->all();
            $platforms  = array_column($platforms, 'name', 'id');
            foreach ($withholds as $key => &$value) {
                $value['name']     = $coin_infos[$value['cid']]['name'];
                $value['platform'] = $platforms[$value['platform_id']];
            }
            return $datas;
            return ['code' => 0, 'msg' => 'null'];
        }
        return $this->render('index');
    }

    public function actionSet()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $coin = strtoupper(Yii::$app->request->get('coin', ''));
            if (CoinWithhold::isExists($coin, Yii::$app->user->identity->platform_id)) {
                return ['code' => -1, 'msg' => '代扣币种已经存在'];
            }
            try {
                if (CoinWithhold::saveByName($coin, 1, '', '', Yii::$app->user->identity->platform_id)) {
                    return ['code' => 0, 'msg' => '保存成功'];
                }
            } catch (\Exception $e) {
                return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
            }
            return ['code' => -1, 'msg' => '保存失败'];
        }
    }

    public function actionDel()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id = Yii::$app->request->get('id', 0);
            if ($id) {
                //判断是否有代扣token
                $count = CoinWithholdToken::find()->where(['wid' => $id])->count();
                if ($count > 0) {
                    return ['code' => -1, 'msg' => '存在代扣Token,无法删除'];
                }
                if (CoinWithhold::findOne($id)->delete()) {
                    return ['code' => 0, 'msg' => '删除成功'];
                }
            }
            return ['code' => -1, 'msg' => '删除失败'];
        }
    }

    public function actionDetial()
    {
        $wid = Yii::$app->request->get('wid', 1);
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get   = Yii::$app->request->get();
            $page  = $get['page'] ?? 1;
            $limit = $get['limit'] ?? 10;
            $datas = CoinWithholdToken::getList2($page, $limit, ['wid' => $wid]);
            return $datas;
        }
        return $this->render('detial', ['wid' => $wid]);
    }

    public function actionAddToken()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get = Yii::$app->request->get();
            if (isset($get['id']) && !empty($get['id'])) {
                $model           = CoinWithholdToken::findOne($get['id']);
                $model->scenario = CoinWithholdToken::SCENARIOS_UPDATE;
            } else {
                $model           = new CoinWithholdToken();
                $model->scenario = CoinWithholdToken::SCENARIOS_ADD;
            }
            if ($model->load($get) && $model->validate()) {
                $model->token = strtoupper($model->token);
                if ($model->save()) {
                    return ['code' => 0, 'msg' => '保存成功'];
                }
            } else {
                return ['code' => -1, 'msg' => current($model->firstErrors)];
            }
            return ['code' => -1, 'msg' => '保存失败'];
        }
    }

    public function actionDelToken()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get('id', 0);
            if (ctype_digit($id)) {
                $model = CoinWithholdToken::findOne($id);
                if ($model->platform_id != Yii::$app->user->identity->platform_id) {
                    return ['code' => -1, 'msg' => '没有权限删除'];
                }
                if ($model && $model->delete()) {
                    return ['code' => 0, 'msg' => '删除成功'];
                }
            }
            return ['code' => -1, 'msg' => '删除失败'];
        }
    }
}
