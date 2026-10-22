<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-21
 * Time: 上午11:16
 */

namespace h5\controllers;

use common\models\psources\Coin;
use common\models\psources\CoinExtract;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseCheck;
use common\models\psources\CoinReleaseList;
use common\models\psources\CoinReleaseMember;
use h5\base\ResponseBuild;
use Yii;
use h5\base\BaseController;
use common\business\ZhaobiBusiness;
use common\models\psources\CoinPublish;

class GuodunController extends BaseController
{
    /**
     * @return ResponseBuild
     * 用户资产
     */
    public function actionGetUserAssets()
    {
        $post   = Yii::$app->request->post();
        $mobile = $post['mobile'] ?? '';
        $page   = $post['page'] ?? 1;
        $limit  = $post['limit'] ?? 10;

        $response_ = new ResponseBuild();

        if (empty($mobile)) {
            $response_->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE);
            return $response_;
        }
        $data = CoinReleaseMember::getList2($page, $limit, ['mobile' => $mobile], ['create_time' => SORT_DESC]);
        if ($data) {
            $infos     = &$data['data'];
            $names     = array_unique(array_column($infos, 'coin'));
            $nicknames = Coin::getNicknamesByNames($names);//获取昵称

            $rule_ids = array_unique(array_column($infos, 'rule_id'));
            $rules    = CoinPublishRule::find()->where(['in', 'id', $rule_ids])->asArray()->all(); //获取锁仓规则
            $rules    = array_column($rules, null, 'id');

            //构造返回数据
            foreach ($infos as $key => &$item) {
                $rule             = $rules[$item['rule_id']];
                $item['nickname'] = $nicknames[$item['coin']] ?? '';
                $item['repeat']   = $rule['repeat'];
                $item['date']     = date(CoinPublishRule::DATE_FORMAT[$rule['repeat']], strtotime($rule['release_time']));
                unset($item['rule_id'], $item['user_id'], $item['platform_id']);
            }
            $response_->build(ResponseBuild::STATUS_SUCCEED, '', $data);
            return $response_;
        } else {
            $response_->build(-1, '数据为空');
            return $response_;
        }
    }

    /**
     * 提币申请
     */
    public function actionRequestCoin()
    {
        $response = new ResponseBuild();

        $post  = Yii::$app->request->post();
        $field = ['id', 'address', 'amount', 'uid', 'type', 'code', 'codetype'];
        foreach ($field as $item) {
            if (isset($post[$item]) && !empty($post[$item])) {
                $$item = $post[$item];
            } else {
                $response->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE, $item . ' can not be blank');
                return $response;
            }
        }

        !YII_DEBUG && $validate = ZhaobiBusiness::validateCode($uid, $codetype, $code, $type);
        if (!YII_DEBUG && $validate['code'] != 200) {
            $response->build($validate['code'], $validate['error']);
            return $response;
        }

        //开启事务
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user_asset = CoinReleaseMember::findOne([$id]);
            if (!$user_asset) {
                throw new \Exception('资产不存在', -1);
            }
            //判断余额是否充足
            $amount *= 1e8;
            if ($user_asset->release < $amount) {
                throw new \Exception('余额不足', -1);
            }

            //添加提币申请
            $request_coin               = new CoinReleaseCheck();
            $request_coin->uid          = $user_asset->user_id;
            $request_coin->mid          = $id;
            $request_coin->mobile       = $user_asset->mobile;
            $request_coin->amount       = $amount;
            $request_coin->coin         = $user_asset->coin;
            $request_coin->to_address   = $address;
            $request_coin->company_name = $user_asset->company_name;
            //减少用户资产
            $user_asset->release -= $amount;
            $user_asset->output  += $amount;
            //获取发币地址
            $from              = '';
            $coin_publish_rule = CoinPublishRule::findOne($user_asset->rule_id);
            if ($coin_publish_rule) {
                $coin_publish = CoinPublish::findOne($coin_publish_rule->pid);
                if ($coin_publish) {
                    $from = $coin_publish->address;
                }
            }
            if (empty($from)) {
                throw new \Exception('获取发币地址失败', -1);
            }
            //添加提币记录
            $coin_release_list         = new CoinReleaseList();
            $coin_release_list->mid    = $id;
            $coin_release_list->uid    = $user_asset->user_id;
            $coin_release_list->mobile = $user_asset->mobile;
            $coin_release_list->amount = $amount;
            $coin_release_list->coin   = $user_asset->coin;
            $coin_release_list->type   = 1;
            $coin_release_list->from   = $from;
            $coin_release_list->to     = $address;
            $coin_release_list->status = 3;//未完成

            if ($user_asset->save(false)) {
                if ($coin_release_list->save()) {
                    $request_coin->list_id = $coin_release_list->id;
                    if ($request_coin->save()) {
                        $transaction->commit();
                        $response->build(ResponseBuild::STATUS_SUCCEED);
                    } else {
                        throw new \Exception('保存提币申请失败', -1);
                    }
                } else {
                    throw new \Exception('保存提币记录失败', -1);
                }
            } else {
                throw new \Exception('提币失败', -1);
            }
        } catch (\Exception $exception) {
            $transaction->rollBack();
            $response->build($exception->getCode(), $exception->getMessage());
        }
        //记录提币申请
        $extract = CoinExtract::findOne(['address' => $address]);
        if (!$extract) {
            $extract = new CoinExtract();
        }
        $extract->address = $address;
        $extract->total   += 1;
        if (strtotime($extract->update_time) < strtotime(date('Y-m-d', time()))) {
            $extract->today = 1;
        } else {
            $extract->today += 1;
        }
        $extract->save();
        return $response;
    }

    /**
     * @return ResponseBuild
     * 提币/锁仓释放 列表
     */
    public function actionBillList()
    {
        $response = new ResponseBuild();
        $post     = Yii::$app->request->post();
        $page     = $post['page'] ?? 1;
        $limit    = $post['limit'] ?? 10;
        $type     = $post['type'] ?? ''; //类型,1提币,2锁仓释放
        $mobile   = $post['mobile'] ?? '';
        $id       = $post['id'] ?? 0;
        if (empty($id)) {
            if (empty($mobile)) {
                $response->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE);
                return $response;
            }
            $conditions = ['mobile' => $mobile];
            if ($type) {
                $conditions['type'] = $type;
            }
            $datas = CoinReleaseList::getList2($page, $limit, $conditions, ['create_time' => SORT_ASC]);
            $response->build(ResponseBuild::STATUS_SUCCEED, '', $datas);
        } else {
            $conditions = ['id' => $id];
            $datas      = CoinReleaseList::getList2(1, 1, $conditions, ['create_time' => SORT_ASC]);
            $response->build(ResponseBuild::STATUS_SUCCEED, '', $datas);
        }
        return $response;
    }
}