<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 下午4:02
 */

namespace h5\job;

use Yii;
use yii\queue\Queue;
use yii\queue\JobInterface;
use yii\base\BaseObject;
use common\business\Chain33Business;

/**
 * Class AwardJob
 * 发放奖励任务
 *
 * @package h5\job
 */
class AwardJob extends BaseObject implements JobInterface
{
    public $from; //发币地址
    public $to; //受币地址
    public $is_token;//是否代币
    public $token;//代币名称
    public $amount;//数量 * 1e8
    public $note; //备注

    /**
     * @param Queue $queue which pushed and is handling the job
     */
    public function execute($queue)
    {
        $result = Chain33Business::transToken($this->from, $this->to, $this->amount, $this->note, $this->is_token, $this->token);
        if (is_string($result)) {
            //派奖记录到数据库中
            Yii::info("派奖成功! 奖励[{$this->to}][$this->token][$this->amount]个", __CLASS__);
        } else {
            Yii::info("失败的派奖: [to]: {$this->to}, [amount]: {$this->amount}, [is_token] : {$this->is_token}, [token]: {$this->token}",
                __CLASS__);
        }
    }
}