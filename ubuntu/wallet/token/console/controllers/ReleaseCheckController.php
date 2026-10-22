<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2019/1/15
 * Time: 10:35
 */
namespace console\controllers;
use common\business\Chain33Business;
use common\models\psources\CoinPublish;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseCheck;
use common\models\psources\CoinReleaseList;
use common\models\psources\CoinReleaseMember;
use yii\console\Controller;
use Yii;

class ReleaseCheckController extends Controller
{

    //自动审核
    public function actionCheck()
    {
        $coin_release_checkItems = $this->getAutoCheckList();
        if(!$coin_release_checkItems){
            echo date('Y-m-d H:i:s').':has no need auto check items'.PHP_EOL;
            exit;
        }
        foreach($coin_release_checkItems as $coin_release_check){
            $from   = '';
            $member = CoinReleaseMember::findOne($coin_release_check->mid);
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
                echo date('Y-m-d H:i:s').':获取提币地址失败!'.PHP_EOL;
                $coin_release_check->memo = '获取提币地址失败';
                $coin_release_check->status = CoinReleaseCheck::STATUS_AUTO_FAILD;             //自动审核失败
                $coin_release_check->save();
                continue;
            }
            if(!$coin_publish->auto_check){
                echo date('Y-m-d H:i:s').':publish_id['.$coin_publish->id.']未设置自动审核功能'.PHP_EOL;
                continue;
            }
            $auto_check_limit = $coin_publish->auto_check_limit;
            if($auto_check_limit * 1e8 < $coin_release_check->amount){
                echo date('Y-m-d H:i:s').':check_id['.$coin_release_check->id.'],coin['.$coin_release_check->coin.'],amount['.$coin_release_check->amount/1e8.']提币额度大于自动审核额度,不能自动审核'.PHP_EOL;
                continue;
            }
            $res = CoinReleaseCheck::updateAll(['status' => CoinReleaseCheck::STATUS_AUTO_CHECKING], 'id=:id', [':id' => $coin_release_check->id]);
            if($res){
                //开启事务
                $trans = CoinReleaseCheck::getDb()->beginTransaction();
                $isBlockFalse = false;
                try {
                    $coin_release_check->check_first_uid     = 1;
                    $coin_release_check->check_first_status = 2;
                    $coin_release_check->check_second_uid    = 1;
                    $coin_release_check->check_second_status = 2;
                    $coin_release_check->status              = CoinReleaseCheck::STATUS_PADDING;
                    //区块链转币
                    if (strtoupper($coin_release_check->coin) == 'BTY') {
                        $isToken = false;
                    } else {
                        $isToken = true;
                    }
                    $walletpasswd = Yii::$app->redis->get('wallet_passwd');
                    $node_params = Yii::$app->params['chain_parallel']['secondary'];
                    Chain33Business::unLockWallet($walletpasswd, $node_params);
                    $result = Chain33Business::tradeToken(
                        (int)$coin_release_check->amount,
                        '',
                        1000,
                        $isToken,
                        trim($coin_release_check->to_address),
                        $coin_release_check->coin,
                        $node_params
                    );
                    if (is_array($result)) {
                        $isBlockFalse = true;          //区块链报错
                        throw new \Exception($result['msg']);
                    }
                    $coin_release_check->txhash = $result;
                    $coin_release_check->memo = '自动审核成功!';
                    $coin_release_check->save();
                    $trans->commit();
                    echo date('Y-m-d H:i:s').':check_id['.$coin_release_check->id.'],coin['.$coin_release_check->coin.'],amount['.$coin_release_check->amount/1e8.']提币成功'.PHP_EOL;
                } catch (\Exception $exception) {
                    $trans->rollBack();
                    echo date('Y-m-d H:i:s').':'.$exception->getMessage().PHP_EOL;
                    if($isBlockFalse){
                        CoinReleaseCheck::updateAll(['status' => CoinReleaseCheck::STATUS_AUTO_EXCEPTION,'memo' => $exception->getMessage()], 'id=:id', [':id' => $coin_release_check->id]);
                    }else{
                        CoinReleaseCheck::updateAll(['memo' => $exception->getMessage()], 'id=:id', [':id' => $coin_release_check->id]);
                    }

                }
            }else{
                echo  date('Y-m-d H:i:s').':数据库异常，无法自动审核!'.PHP_EOL;
            }

        }
    }

    private function getAutoCheckList()
    {
       return CoinReleaseCheck::find()->where(['status' => 0,'check_first_status' => 1])->all();
    }

    public function actionConfirmHash()
    {
        $coin_release_checkItems = $this->getConfirmList();
        if(!$coin_release_checkItems){
            echo date('Y-m-d H:i:s').':has no need confirm hash items'.PHP_EOL;
            exit;
        }
        foreach($coin_release_checkItems as $coin_release_check){
            $coin_release_list  = CoinReleaseList::findOne($coin_release_check->list_id);
            if ($coin_release_list) {
                $node_params = Yii::$app->params['chain_parallel']['secondary'];
                $result = Chain33Business::getTxByHashs([$coin_release_check->txhash], $node_params);
                if ($result['code'] == 0) {
                    $result = $result['result']['txs'][0]['receipt']['ty'] ?? 0;
                    if ($result == 1) {
                        //转账失败
                        echo date('Y-m-d H:i:s').':check_id['.$coin_release_check->id.'],coin['.$coin_release_check->coin.'],amount['.$coin_release_check->amount/1e8.']转账失败'.PHP_EOL;
                        $coin_release_list->status  = 2;
                        $coin_release_check->status = 3;
                        $trans                      = CoinReleaseCheck::getDb()->beginTransaction();
                        try {
                            if ($coin_release_check->save() && $coin_release_list->save()) {
                                //退换资产
                                $coin_release_member = CoinReleaseMember::findOne($coin_release_list->mid);
                                if ($coin_release_member) {
                                    $coin_release_member->release += $coin_release_list->amount;
                                    $coin_release_member->output  -= $coin_release_list->amount;
                                    if ($coin_release_member->save()) {
                                        $trans->commit();
                                    }
                                }
                            }
                            $trans->rollBack();
                        } catch (\Exception $exception) {
                            echo date('Y-m-d H:i:s').':'.$exception->getMessage().PHP_EOL;
                        }
                    } elseif ($result == 2) {
                        //转账成功
                        echo date('Y-m-d H:i:s').':check_id['.$coin_release_check->id.'],coin['.$coin_release_check->coin.'],amount['.$coin_release_check->amount/1e8.']转账成功'.PHP_EOL;
                        $coin_release_list->status  = 1;
                        $coin_release_check->status = 4;
                        $trans                      = CoinReleaseCheck::getDb()->beginTransaction();
                        try {
                            if ($coin_release_check->save() && $coin_release_list->save()) {
                                $trans->commit();
                            }
                            $trans->rollBack();
                        } catch (\Exception $exception) {
                            echo date('Y-m-d H:i:s').':'.$exception->getMessage().PHP_EOL;
                        }
                    }
                }
            }
        }
    }

    private function getConfirmList()
    {
        return CoinReleaseCheck::find()->where(['status' => 1,'check_first_status' => 2])->all();
    }



}