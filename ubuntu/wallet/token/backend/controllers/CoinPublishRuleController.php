<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

namespace backend\controllers;

use Yii;
use backend\jobs\FreezeJob;
use common\models\psources\CoinPublish;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseMember;
use common\business\Chain33Business;

class CoinPublishRuleController extends BaseController
{
    const DATE_FORMAT = [
        'Y-m-d H:i:s',
        'm月d号 H:i:s', //每年
        'd号 H:i:s', //每月
    ];
    const SHOW_FORMAT = [
        'Y-m-d H:i:s',
        'm月d号 H:i:s', //每年
        'd号 H:i:s', //每月
    ];

    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            $auth          = Yii::$app->authManager;
            $user_identity = Yii::$app->user->identity;
            $get           = Yii::$app->request->get();
            $page          = $get['page'] ?? 1;
            $limit         = $page['limit'] ?? 10;
            $role          = current($auth->getRolesByUser($user_identity->id));

            Yii::$app->response->format = 'json';
            // 获取用户组的所有锁仓规则
            if (!$role) {
                return ['code' => -1, 'msg' => 'failed'];
            }
            $condition = [['owner' => $role->name]];
            $datas     = CoinPublish::getList($page, $limit, $condition);
            return $datas;
        }

        return $this->render('index');
    }

    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 锁仓规则列表
     */
    public function actionList()
    {
        $get       = Yii::$app->request->get();
        $page      = $get['page'] ?? 1;
        $limit     = $get['limit'] ?? 10;
        $id        = $get['id'] ?? null;
        $pid       = $get['pid'] ?? $this->getPid();
        $condition = [];
        if ($pid) {
            $condition[] = ['pid' => $pid];
        }
        if ($id) {
            $condition[] = ['id' => $id];
        }
        if (Yii::$app->request->isAjax) {
            $data                       = CoinPublishRule::getList($page, $limit, $condition);
            Yii::$app->response->format = 'json';
            $models                     = $data['data'];
            // 获取锁仓人数
            $ids          = array_column($models, 'id');
            $member_count = CoinReleaseMember::getCountByRuleIds($ids);

            foreach ($models as $key => &$model) {
                $model['release_time'] = date(self::SHOW_FORMAT[$model['repeat']], strtotime($model['release_time']));
                $model['amount']       = $member_count[$model['id']] ?? 0;
            }
            $data['data'] = $models;
            return $data;
        }

        $coin   = CoinPublish::findOne($pid);
        $amount = Chain33Business::getAccountTokenAssets($coin->address, $coin->coin_name_en);
//        var_dump($amount);die();
        if ($amount['code'] == 0) {
            $amount = $amount['amount'];
        } else {
            $amount = 0;
        }
        //获取热钱包数量
        return $this->render('list',
            [
                'coin_name'      => $coin->coin_name_en ?? '',
                'amount'         => $amount,
                'release_amount' => CoinPublishRule::getReleaseAmountThisMonthById($pid),
                'address'        => $coin->address ?? '',
            ]);
    }

    /**
     * @return array|string
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * 创建锁仓规则
     */
    public function actionSet()
    {
        if (Yii::$app->request->isGet) {
            $this->layout = false;
            if (!empty(Yii::$app->request->get('id', null))) {
                return $this->render('form');
            } else {
                return $this->render('form_add');
            }
        } elseif (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $post                       = Yii::$app->request->post();
            if (isset($post['id']) && !empty($post['id'])) {
                $model           = CoinPublishRule::findOne($post['id']);
                $model->scenario = CoinPublishRule::SCENARIOS_UPDAET;
                unset($post['id']);
            } else {
                $model           = new CoinPublishRule();
                $model->scenario = CoinPublishRule::SCENARIOS_ADD;
                // 编号
                $prefix        = date('Ymd', time());
                $form_database = CoinPublishRule::find()->where(['like', 'sid', $prefix])->orderBy(['sid' => SORT_DESC])->asArray()->One();
                if ($form_database) {
                    $sid = (int)$form_database['sid'] + 1;
                } else {
                    $sid = $prefix . '001';
                }

                $model->platform_id = 2;
            }
            if ($model->load($post) && $model->validate()) {
                if (CoinPublishRule::SCENARIOS_ADD == $model->scenario) {
                    $model->sid = (string)$sid;
                }
                $model->release_time = date_create_from_format(self::DATE_FORMAT[$model->repeat], $model->release_time)->format('Y-m-d H:i:s');
                empty($model->pid) && $model->pid = $this->getPid();
                $rule = CoinPublish::findOne($model->pid);
                if (empty($rule)) {
                    return ['code' => -1, 'msg' => '锁仓规则不存在'];
                } else {
                    $model->company_name = $rule->owner;
                }
                if ($model->save()) {
                    if ($model->qid) {
                        //删除原有的任务
                        Yii::$app->queue->remove($model->qid);
                    }
                    // 计算延时
                    $now        = time();
                    $delay_time = CoinPublishRule::getDelay($now, $model->repeat, strtotime($model->release_time));
                    // 保存数据到任务队列
                    $id = Yii::$app->queue->delay($delay_time)->push(new FreezeJob(['id' => $model->id]));
                    if (!$id) {
                        $model->delete();
                        Yii::info("CoinPublishRule_id[{$model->id}]: queue id[$id] , push failed!", __CLASS__);
                        return ['code' => -1, 'msg' => '保存失败'];
                    } else {
                        $model->qid = $id;
                        $model->save();
                        Yii::info("CoinPublishRule_id[{$model->id}]: queue id[$id] , push succeed!", __CLASS__);
                    }
                    return ['code' => 0, 'msg' => 'succeed'];
                }
            } else {
                return ['code' => -1, 'msg' => current($model->firstErrors)];
            }
            return ['code' => -1, 'msg' => '保存失败'];
        }
    }

    /**
     * @return array
     * @throws \Exception
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * 删除锁仓规则
     */
    public function actionDel()
    {
        $id                         = Yii::$app->request->get('id', null);
        Yii::$app->response->format = 'json';
        if ($id) {
            $model = CoinPublishRule::findOne($id);
            if ($model) {
                if ($model->getMemberCount() > 0) {
                    return ['code' => -1, 'msg' => '规则内有用户,不能删除!'];
                } else {
                    // 删除队列任务
                    Yii::$app->queue->remove($model->qid);
                    if ($model->delete()) {
                        return ['code' => 0, 'msg' => 'succeed'];
                    }
                }
            }
        }
        return ['code' => -1, 'msg' => '删除失败'];
    }

    /**
     * 获取id
     *
     * @return int
     */
    private function getPid()
    {
        $referer = Yii::$app->request->getReferrer();
        $referer = parse_url($referer, PHP_URL_QUERY);
        parse_str($referer, $query);
        if (!isset($query['pid']) || empty($query['pid'])) {
            return false;
        }
        return $query['pid'];
    }

    /**
     * @return arraysho
     * 获取币种下用户列表
     */
    public function actionGetRememberList()
    {
        $mobile                         = Yii::$app->request->get('mobile', '');
        Yii::$app->response->format = 'json';
        $pid       =  $this->getPid();
        if($pid && $mobile){
            $ruleItems = CoinPublishRule::getPublishRuleItemsByPid($pid);
            $ruleIds = array_column($ruleItems,'id');
            $ruleInfos = array_column($ruleItems,'sid','id');
            $data = CoinReleaseMember::getMemberListByMobile($ruleIds,$mobile);
            foreach($data as &$value){
                $value['rule_sid'] = $ruleInfos[$value['rule_id']];
                $value['amount'] /= 1e8;
                $value['release'] /= 1e8;
                $value['freeze'] /= 1e8;
                $value['output'] /= 1e8;
            }
            $data = $data ?? [];
            return ['code' => 0,'data' => $data,'count' => count($data)];
        }else{
            return ['code' => -1, 'msg' => 'pid获取失败或手机号不能为空'];
        }
    }
}
