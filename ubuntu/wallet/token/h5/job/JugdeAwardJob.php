<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 下午6:11
 */

namespace h5\job;

use common\models\psources\CoinGuessResult;
use Yii;
use yii\base\BaseObject;
use yii\queue\Queue;
use yii\queue\JobInterface;
use common\business\Chain33Business;

/**
 * Class JugdeAwardJob
 * 查询区块交易信息, 分发奖励
 *
 * @package h5\job
 */
class JugdeAwardJob extends BaseObject implements JobInterface
{
    /**
     * @param Queue $queue which pushed and is handling the job
     * @return integer
     */
    public function execute($queue)
    {
        do {
            $loop = true;
            //获取最新的未开奖的竞猜
            $guess_result = CoinGuessResult::getWaitingAward();
            if ($guess_result) {
                $config = Yii::$app->params['h5_activity']['ZWG_GUESS_NEWBEE'];
                //获取区块间的所有交易(正常竞猜)
                $trans_normal = Chain33Business::getTxByAddrInRange($config['to_normal'], 2, 0, $guess_result->start_height, $guess_result->end_height - 1);
                $trans_new    = Chain33Business::getTxByAddrInRange($config['to'], 2, 0, $guess_result->start_height, $guess_result->end_height - 1);
                $trans        = array_merge($trans_normal, $trans_new);
                $txhashs      = array_column($trans, 'hash');
                $trans_info   = Chain33Business::getTxByHashs($txhashs);
                if ($trans_info['code'] == 0) {
                    $trans_info = $trans_info['result']['txs'];
                    if (is_array($trans_info)) {
                        $txs = [];
                        foreach ($trans_info as $k => $v) {
                            //收集数据
                            $temp           = [];
                            $temp['amount'] = $v['amount'];
                            $temp['from']   = $v['fromAddr'];
                            $temp['to']     = $v['tx']['to'];
                            $temp['note']   = $v['tx']['payload']['Value']['Transfer']['note'] ?? '';
                            if ($temp['amount'] == intval($config['amount'] * 1e8) || $temp['amount'] == 2 * 1e8) {
                                $matchs = [];
                                if (preg_match('/^\d\d$/', $temp['note'], $matchs)) {
                                    $temp['guess'] = $matchs[0];
                                    $txs[]         = $temp;
                                }
                                if (preg_match('/^(\d\d) (\w+)$/', $temp['note'], $matchs)) {
                                    $temp['guess'] = $matchs[1];
                                    $temp['from']  = $matchs[2];
                                    $txs[]         = $temp;
                                }
                            }
                        }
                        //为所有参与用户判断中奖情况
                        $this->doAward($txs, $guess_result->result);
                    }
                }
                $guess_result->is_award = 1;
                if ($guess_result->save()) {
                    Yii::info("第{$guess_result->stage}期开奖成功", __CLASS__);
                } else {
                    Yii::warning("第{$guess_result->stage}期开奖成功, 修改竞猜开奖情况失败", __CLASS__);
                }
            } else {
                $loop = false;
            }
        } while ($loop);
        return 0;
    }

    private function doAward($txs, $result)
    {
        $config = Yii::$app->params['h5_activity']['ZWG_GUESS_NEWBEE'];
        foreach ($txs as $item) {
            $flag = false;
            if (preg_match('/^' . $result . '$/', $item['guess'])) {
                $flag   = true;
                $amount = $config['award_beta'];
            } else {
                $guess   = [];
                $guess[] = intval($item['guess'] % 10);
                $guess[] = intval($item['guess'] / 10);
                $ans     = [];
                $ans[]   = intval($result % 10);
                $ans[]   = intval($result / 10);
                if ($ans[0] == $guess[0] || $ans[1] == $guess[1]) {
                    $flag   = true;
                    $amount = $config['award_normal'];
                }
            }
            if (!$flag) {
                continue;
            }
            Yii::$app->queue->push(new AwardJob([
                'from'     => $config['from'],
                'to'       => $item['from'],
                'is_token' => $config['award_isToken'],
                'token'    => $config['award_token'],
                'amount'   => $amount * 1e8,
                'note'     => '竞猜奖励',
            ]));
        }
    }
}