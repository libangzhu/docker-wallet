<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-18
 * Time: 下午2:50
 */

namespace backend\jobs;

use common\models\psources\CoinReleaseList;
use Yii;
use yii\queue\JobInterface;
use yii\queue\Queue;
use yii\base\BaseObject;
use common\models\psources\CoinReleaseCheck;
use common\models\psources\CoinReleaseMember;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinPublish;
use common\business\Chain33Business;


/**
 * Class ExtractTokenJob
 * 查询提币状态任务
 *
 * @package backend\jobs
 */
class QueryTransJob extends BaseObject implements JobInterface
{
    /**
     * 提币交易hash
     *
     * @var string $cid
     */
    public $txhash = '';

    /**
     * coin_release_list id
     *
     * @var int
     */
    public $lid = 0;

    /**
     * coin_release_check id
     *
     * @var int
     */
    public $cid = 0;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return integer
     */
    public function execute($queue)
    {
        $coin_release_list  = CoinReleaseList::findOne($this->lid);
        $coin_release_check = CoinReleaseCheck::findOne($this->cid);

        if ($coin_release_list && $coin_release_check) {
            $result = Chain33Business::getTxByHashs([$this->txhash]);
            if ($result['code'] == 0) {
                $result = $result['result']['txs'][0]['receipt']['ty'] ?? 0;
                if ($result == 1) {
                    //转账失败
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
                                    return 0;
                                }
                            }
                        }
                        $trans->rollBack();
                    } catch (\Exception $exception) {
                        Yii::$app->queue->delay(15)->push($this);
                        return 0;
                    }
                } elseif ($result == 2) {
                    //转账成功
                    $coin_release_list->status  = 1;
                    $coin_release_check->status = 4;
                    $trans                      = CoinReleaseCheck::getDb()->beginTransaction();
                    try {
                        if ($coin_release_check->save() && $coin_release_list->save()) {
                            $trans->commit();
                            return 0;
                        }
                        $trans->rollBack();
                    } catch (\Exception $exception) {
                        Yii::$app->queue->delay(15)->push($this);
                        return 0;
                    }
                }
            }
        }
        Yii::$app->queue->delay(15)->push($this);
        return 0;
    }
}