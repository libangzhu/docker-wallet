<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-04 10:43:18
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace backend\jobs;

use common\base\Exception;
use common\models\psources\CoinPublish;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseList;
use common\models\psources\CoinReleaseMember;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class FreezeJob
 * 定时释放币任务
 *
 * @package backend\jobs
 */
class FreezeJob extends BaseObject implements JobInterface
{
    /**
     * @var integer $id 锁仓规则id
     */
    public $id;

    /**
     * @param  Queue $queue which pushed and is handling the job
     * @throws \Exception
     * @return integer
     */
    public function execute($queue)
    {
        $coin_publish_rule = CoinPublishRule::findOne($this->id);
        if (!$coin_publish_rule) {
            // 计划任务已经被删除
            echo $this->id.'计划任务已删除'.PHP_EOL;
            return 0;
        }
        $coin_publish = CoinPublish::findOne($coin_publish_rule->pid);
        $address      = $coin_publish->address;

        //修改用户的币种冻结信息
        $members = CoinReleaseMember::getMemberByRuleIds($this->id);

        foreach ($members as $key => &$value) {
            //获取改的币种数量
            $change = CoinPublishRule::calReleaseAmount($coin_publish_rule->lock, $coin_publish_rule->release, $coin_publish_rule->type, $value['amount'], $value['freeze'], 0);
            if ($change > $value['freeze']) {
                $change = $value['freeze'];
            }
            $value['release'] += $change;
            $value['freeze']  -= $change;
            $value['change']  = $change;
        }
        unset($key, $value);
        foreach ($members as $key => $value) {
            try{
                $trans           = CoinPublish::getDb()->beginTransaction();
                $models          = CoinReleaseMember::findOne($value['id']);
                $models->release = $value['release'];
                $models->freeze  = $value['freeze'];
                //记录保存到数据库
                $list         = new CoinReleaseList();
                $list->amount = $value['change'];
                $list->mobile = $value['mobile'];
                $list->coin   = $value['coin'];
                $list->type   = 2;
                $list->status = 1;
                $list->from   = $address;
                $list->mid    = $value['id'];
                if ($models->save(false) && $list->save(false)) {
                    $trans->commit();
                }
                echo date('Y-m-d H:i:s').': 释放币成功 [Coin_release_member_id]: ' . $value['id'].PHP_EOL;
            }catch (\Exception $e){
                $trans->rollBack();
                Yii::info('释放币失败, [Coin_release_member_id]: ' . $value['id'], __CLASS__);
                echo date('Y-m-d H:i:s').': 释放币失败:'.$e->getMessage().PHP_EOL;
            }

        }

        // 执行下一次任务
        if ($coin_publish_rule->repeat) {
            $delay_time             = CoinPublishRule::getDelay('', $coin_publish_rule->repeat, strtotime($coin_publish_rule->release_time));
            $qid                    = Yii::$app->queue->delay($delay_time)->push(new FreezeJob(['id' => $this->id]));
            $coin_publish_rule->qid = $qid;
            $coin_publish_rule->save();
        }
        // 记录解冻操作
        Yii::info(
            sprintf(
                'Coin Freeze Job Finished: sid[%s] lock[%s] release[%s]',
                $coin_publish_rule->sid,
                $coin_publish_rule->lock,
                $coin_publish_rule->release
            ),
            __CLASS__
        );
        return 0;
    }
}
