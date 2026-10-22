<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/25
 * Time: 14:28
 */

namespace console\controllers;
use common\models\psources\CoinPublish;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseList;
use common\models\psources\CoinReleaseMember;
use yii\console\Controller;


class AbnormalController extends Controller
{

    //补发解冻
    public function actionFreeze($member_id)
    {
        $coin_release_member = CoinReleaseMember::find()->where(['id' => $member_id])->one();
        if($coin_release_member){
            $coin_publish_rule = CoinPublishRule::find()->where(['id' => $coin_release_member->rule_id])->one();
            if (!$coin_publish_rule) {
                // 计划任务已经被删除
                echo $this->id.'计划任务已删除'.PHP_EOL;
                exit;
            }
            $coin_publish = CoinPublish::findOne($coin_publish_rule->pid);
            $address      = $coin_publish->address;
            $change = CoinPublishRule::calReleaseAmount($coin_publish_rule->lock, $coin_publish_rule->release, $coin_publish_rule->type, $coin_release_member->amount, $coin_release_member->freeze, 0);
            if ($change > $coin_release_member->freeze) {
                $change = $coin_release_member->freeze;
            }
            try{
                $trans           = CoinPublish::getDb()->beginTransaction();

                $coin_release_member->release += $change;
                $coin_release_member->freeze  -= $change;
                //记录保存到数据库
                $list         = new CoinReleaseList();
                $list->amount = $change;
                $list->mobile = $coin_release_member->mobile;
                $list->coin   = $coin_release_member->coin;
                $list->type   = 2;
                $list->status = 1;
                $list->from   = $address;
                $list->mid    = $coin_release_member->id;
                if ($coin_release_member->save(false) && $list->save(false)) {
                    $trans->commit();
                }
                echo date('Y-m-d H:i:s').': 释放币成功 [Coin_release_member_id]: ' . $coin_release_member->id.PHP_EOL;
            }catch (\Exception $e){
                $trans->rollBack();
                Yii::info('释放币失败, [Coin_release_member_id]: ' . $coin_release_member->id, __CLASS__);
                echo date('Y-m-d H:i:s').': 释放币失败:'.$e->getMessage().PHP_EOL;
            }
        }else{
            echo "用户不存在".PHP_EOL;
        }
    }
}