<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-05 17:13:46
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace backend\controllers;

use backend\jobs\QueryTransJob;
use common\business\Chain33Business;
use common\models\psources\CoinExtract;
use common\models\psources\CoinPublish;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseCheck;
use common\models\psources\CoinReleaseList;
use common\models\psources\CoinReleaseMember;
use common\modelsgii\AuthItemChild;
use Yii;

class CoinReleaseCheckController extends BaseController
{
    const STEP_CHECK_FIRST  = 1; //初审
    const STEP_CHECK_SECOND = 2; //复核
    const STEP_CANCEL       = 3; //撤提

    public $enableCsrfValidation = false;

    public function actionIndex()
    {
        $admins = $this->getAccessAdmins();
        //获取所有币种
        $coins = CoinPublish::find()->select('coin_name_en')->asArray()->all();
        $coins = array_unique(array_column($coins, 'coin_name_en'));
        return $this->render('index', [
            'coins'         => $coins,
            'check_firsts'  => $admins,
            'check_seconds' => $admins,
        ]);
    }

    /**
     * 提币审核列表
     *
     * @return array
     */
    public function actionCheckList()
    {
        $user_id        = Yii::$app->user->identity->id;
        $auth           = Yii::$app->authManager;
        $user_role      = current($auth->getRolesByUser($user_id));
        $user_role_name = $user_role->name;
        //超级管理员与国盾管理员可以审核所有企业用户
        if ($user_id == Yii::$app->params['admin'] || $user_role_name == '国盾') {
            $user_role_name = '';
        } else {
            // 判断该用户组是否有监控币种
            $all_owners = CoinPublish::find()->select('owner')->asArray()->all();
            $all_owners = array_unique(array_column($all_owners, 'owner'));
            while (!in_array($user_role_name, $all_owners) && $user_role_name != '国盾') {
                $user_role_name = AuthItemChild::find()->select('parent')->where(['child' => $user_role_name])->asArray()->One();
                $user_role_name = $user_role_name['parent'];
            }
        }

        Yii::$app->response->format = 'json';

        $get             = Yii::$app->request->get();
        $model           = new CoinReleaseCheck();
        $model->scenario = CoinReleaseCheck::SCENARIOS_CHECK;
        if ($model->load($get) && $model->validate()) {
            $page  = $get['page'] ?? 1;
            $limit = $page['limit'] ?? 10;
            $datas = CoinReleaseCheck::find()->andFilterWhere(['status' => 0, 'coin' => $model->coin, 'mobile' => $model->mobile]);
            if ($user_role_name) {
                $datas = $datas->andWhere(['company_name' => $user_role_name]);
            }
            if ($model->start_time) {
                $datas = $datas->andFilterWhere(['>=', 'create_time', $model->start_time]);
            }
            if ($model->end_time) {
                $datas = $datas->andFilterWhere(['<=', 'create_time', $model->end_time]);
            }
            $count = $datas->count();
            $datas = $datas->limit($limit)->offset(($page - 1) * $limit)->orderBy('id desc')->asArray()->all();
            //获取操作次数
            //提出地址
            $addresses     = array_values(array_unique(array_column($datas, 'to_address')));
            $address_datas = CoinExtract::getAllByAddress($addresses);
            foreach ($datas as $key => &$value) {
                if(!isset($address_datas[$value['to_address']])) continue;
                $value['today'] = $address_datas[$value['to_address']]['today'];
                $value['total'] = $address_datas[$value['to_address']]['total'];
            }
            return ['code' => 0, 'count' => $count, 'data' => $datas];
        } else {
            return ['code' => -1, 'msg' => current($model->firstErrors)];
        }
    }

    public function actionSearchList()
    {
        Yii::$app->response->format = 'json';

        $get             = Yii::$app->request->get();
        $model           = new CoinReleaseCheck();
        $model->scenario = CoinReleaseCheck::SCENARIOS_SEARCH;
        if ($model->load($get) && $model->validate()) {
            $page  = $get['page'] ?? 1;
            $limit = $get['limit'] ?? 10;
            $mobile = $get['mobile'] ?? '';
            $query = CoinReleaseCheck::find()->andFilterWhere(['!=', 'status', 0])->andFilterWhere([
                'status'           => $model->status,
                'coin'             => $model->coin,
                'check_first_uid'  => $model->check_first_uid,
                'check_second_uid' => $model->check_second_uid,
            ]);
            $user_id        = Yii::$app->user->identity->id;
            $auth           = Yii::$app->authManager;
            $user_role      = current($auth->getRolesByUser($user_id));
            $user_role_name = $user_role->name;
            //超级管理员与国盾管理员可以审核所有企业用户
            if ($user_id == Yii::$app->params['admin'] || $user_role_name == '国盾') {
                $user_role_name = '';
            } else {
                // 判断该用户组是否有监控币种
                $all_owners = CoinPublish::find()->select('owner')->asArray()->all();
                $all_owners = array_unique(array_column($all_owners, 'owner'));
                while (!in_array($user_role_name, $all_owners) && $user_role_name != '国盾') {
                    $user_role_name = AuthItemChild::find()->select('parent')->where(['child' => $user_role_name])->asArray()->One();
                    $user_role_name = $user_role_name['parent'];
                }
            }
            if ($user_role_name) {
                $query = $query->andWhere(['company_name' => $user_role_name]);
            }
            if ($model->start_time) {
                $query = $query->andFilterWhere(['>=', 'create_time', $model->start_time]);
            }
            if ($model->end_time) {
                $query = $query->andFilterWhere(['<=', 'create_time', $model->end_time]);
            }

            if($mobile){
                $query = $query->andFilterWhere(['mobile' => $mobile]);
            }
            $count = $query->count();
            $datas = $query->limit($limit)->offset(($page - 1) * $limit)->orderBy('id desc')->asArray()->all();
            //获取审核员id
            $check_ids = array_column($datas, 'check_first_uid');
            $check_ids = array_unique(array_values(array_merge($check_ids, array_column($datas, 'check_second_uid'))));
            //获取所有管理员
            $admins = $this->getAccessAdmins();
            foreach ($datas as $key => &$value) {
                $value['check_first_uid']  = isset($admins[$value['check_first_uid']]) ? $admins[$value['check_first_uid']] : '自动审核';
                $value['check_second_uid'] = isset($admins[$value['check_second_uid']]) ? $admins[$value['check_second_uid']] : '自动审核';
            }

            return ['code' => 0, 'count' => $count, 'data' => $datas];
        } else {
            return ['code' => -1, 'msg' => current($model->firstErrors)];
        }
    }

    /**
     * @return array
     * @throws \yii\db\Exception
     * 提币审核
     */
    public function actionCheck()
    {
        Yii::$app->response->format = 'json';
        $id                         = Yii::$app->request->get('id', null);
        $status                     = Yii::$app->request->get('status', null);
        $step                       = Yii::$app->request->get('step', null);
        if (!empty($id) && !empty($status) && !empty($step)) {
            if (ctype_digit($id) && ctype_digit($status) && ctype_digit($step)) {
                if (($status == 2 || $status == 3) && ($step >= 1 || $step <= 3)) {
                    $model = CoinReleaseCheck::findOne($id);
                    if ($model) {
                        if (CoinReleaseCheck::STATUS_NORMAL != $model->status) {
                            return ['code' => -1, 'msg' => '不允许进行审核!'];
                        }
                        try {
                            if (self::STEP_CHECK_FIRST == $step) {
                                //初审
                                if ($model->check_first($status)) {
                                    if ($model->check_first_status == CoinReleaseCheck::CHECK_FIRST_STATUS_FAILD) {
                                        $message = '操作成功! 该申请已被撤提';
                                    } else {
                                        $message = '审核通过!';
                                    }
                                    return ['code' => 0, 'msg' => $message];
                                } else {
                                    return ['code' => -1, 'msg' => '初审失败!'];
                                }
                            } elseif (self::STEP_CHECK_SECOND == $step) {
                                //复审
                                $from   = '';
                                $member = CoinReleaseMember::findOne($model->mid);
                                if ($member) {
                                    $coin_publish_rule = CoinPublishRule::findOne($member->rule_id);
                                    if ($coin_publish_rule) {
                                        $coin_publish = CoinPublish::findOne($coin_publish_rule->pid);
                                        if ($coin_publish) {
                                            $from = $coin_publish->address;
                                        }
                                    }
                                }
                                if (empty($from)) {
                                    return ['code' => -1, 'msg' => '获取提币地址失败!'];
                                }
                                if ($model->check_second($status, $from)) {
                                    if ($model->check_second_status == CoinReleaseCheck::CHECK_SECOND_STATUS_FAILD) {
                                        $message = '操作成功! 该申请已被撤提';
                                    } else {
                                        $message = '审核通过!';
                                    }
                                    return ['code' => 0, 'msg' => $message];
                                } else {
                                    return ['code' => -1, 'msg' => '复审失败!'];
                                }
                            } elseif (self::STEP_CANCEL == $step) {
                                if ($model->check_cancel()) {
                                    return ['code' => 0, 'msg' => '操作成功'];
                                }
                            }
                        } catch (\Exception $exception) {
                            return ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
                        }
                    }
                }
            }
        }
        return ['code' => -1, 'msg' => '操作失败'];
    }

}
