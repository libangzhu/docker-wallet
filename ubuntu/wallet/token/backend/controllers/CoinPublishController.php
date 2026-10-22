<?php
/**
 * Created By Sublime Text 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

namespace backend\controllers;

use common\business\Chain33Business;
use common\models\psources\CoinPublishRule;
use Yii;
use backend\models\coin\CoinPublishForm;
use common\models\psources\CoinPublish;
use common\models\psources\Coin;

class CoinPublishController extends BaseController
{
    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 发币管理列表
     */
    public function actionList()
    {
        $is_show_button = false;
        $auth           = Yii::$app->authManager;
        $role_self      = current($auth->getRolesByUser(Yii::$app->user->id));
        if ($role_self->name == '国盾' || Yii::$app->user->id == Yii::$app->params['admin']) {
            $is_show_button = true;
        }

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $get                        = Yii::$app->request->get();
            $page                       = $get['page'] ?? 1;
            $limit                      = $get['limit'] ?? 10;
            $child_roles                = $auth->getChildRoles($role_self->name);
            $userful_roles              = array_keys($child_roles);
            $condition[]                = ['in', 'owner', $userful_roles];
            $data                       = CoinPublish::getList($page, $limit, $condition);
            //获取热钱包数量
            if ($data['code'] == 0 && !empty($data['data'])) {
                $list   = &$data['data'];
                $amount = Chain33Business::getMulAccountTokenAssets($list);
                foreach ($list as $k => &$v) {
                    $v['amount'] = $amount[$v['id']];
                }
            }
            return $data;
        }
        return $this->render('list', ['is_show_button' => $is_show_button]);
    }

    /**
     * @return array|string
     * 添加币种监控
     */
    public function actionAdd()
    {
        if (Yii::$app->request->isGet) {
            $this->layout = false;
            $id           = Yii::$app->request->get('id', null);
            if ($id) {
                $model = CoinPublish::findOne($id);
            } else {
                $model = new CoinPublishForm();
            }
            $auth       = Yii::$app->authManager;
            $role       = current($auth->getRolesByUser(Yii::$app->user->id));
            $childRoles = array_keys($auth->getChildRoles($role->name));
            // $coins = Coin::getAllByPlatformId(Yii::$app->user->identity->platform_id, 0, 999);
            return $this->render('form', ['model' => $model, 'roles' => $childRoles]);
        } elseif (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $post                       = Yii::$app->request->post();
            $model                      = new CoinPublishForm();
            if (isset($post['id']) && !empty($post['id'])) {
                $model->scenario = CoinPublishForm::SCENARIOS_UPDATE;
            } else {
                $model->scenario = CoinPublishForm::SCENARIOS_ADD;
            }
            if ($model->load($post) && $model->validate()) {
                if ($model->id) {
                    $CoinPublish = CoinPublish::findOne($model->id);
                } else {
                    $CoinPublish = new CoinPublish();
                }
                $CoinPublish->company_name = $model->company_name;
                $CoinPublish->coin_name_zh = $model->coin_name_zh;
                $CoinPublish->coin_name_en = $model->coin_name_en;
                $CoinPublish->address      = $model->address;
                $CoinPublish->owner        = $model->owner;
                $CoinPublish->platform_id  = Yii::$app->user->identity->platform_id;
                if ($CoinPublish->save(false)) {
                    return ['code' => 0, 'msg' => 'succeed', 'data' => ''];
                } else {
                    return ['code' => -1, 'msg' => 'failed'];
                }
            } else {
                return ['code' => -1, 'msg' => current($model->firstErrors)];
            }
        }
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * 删除币种监控
     */
    public function actionDel()
    {
        Yii::$app->response->format = 'json';
        $id                         = Yii::$app->request->get('id', null);
        if ($id) {
            $model = CoinPublish::findOne(['id' => $id]);
            $ruleCount = CoinPublishRule::getRuleCountByPid($id);
            if($ruleCount > 0){
                return ['code' => -1, 'msg' => '存在锁仓规则,无法删除币种监控'];
            }
            if ($model->delete()) {
                return ['code' => 0, 'msg' => 'succeed'];
            }
        }
        return ['code' => -1, 'msg' => 'failed'];
    }

    /**
     * @return array
     * 设置是否自动审核
     */
    public function actionSetAutoCheck()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id    = $request->get('id', '');
            $enable  = $request->get('enable',1);
            if($id){
                $publish = CoinPublish::getPublish($id);
                if(!$publish){
                    return ['code' => 1,'msg' =>'币种监控不存在,不能设置自动审核'];
                }
                $publish->auto_check = $enable;
                $publish->save();
                return ['code' => 0,'msg' => '自动审核设置成功'];
            }else{
                return ['code' => 1 ,'msg' => '自动审核设置失败'];
            }
        }
    }

    /**
     * @return array
     * 设置自动审核上限
     */
    public function actionSetLimit()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id    = $request->get('id', '');
            $auto_check_limit  = $request->get('auto_check_limit');
            if($id && $auto_check_limit){
                $publish = CoinPublish::getPublish($id);
                if(!$publish){
                    return ['code' => 1,'msg' =>'币种监控不存在,不能设置自动审核'];
                }
                $publish->auto_check_limit = $auto_check_limit;
                $publish->save();
                return ['code' => 0,'msg' => '自动审核上限值设置成功'];
            }else{
                return ['code' => 1 ,'msg' => '自动审核上限值设置失败'];
            }
        }
    }
}