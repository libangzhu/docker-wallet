<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-7-30
 * Time: 下午3:12
 */

namespace api\job;

use common\business\BrowerBusiness;
use common\models\pwallet\Bill;
use common\service\brower\Brower;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Class BillJob
 *
 * 处理账单队列数据
 * @package api\job
 */
class BillJob extends BaseObject implements JobInterface
{
    public $id;
    public $chain;
    public $txid;
    public $from;

    /**
     * @param Queue $queue which pushed and is handling the job
     * @return integer
     */
    public function execute($queue)
    {
        $result = BrowerBusiness::getTransStatus($this->chain, $this->txid);
        $f      = false;
        if ($result['code'] == Brower::TRANS_OK) {
            $model         = Bill::findOne($this->id);
            $model->status = 1;
            $data          = $result['data'] ?? null;
            if ($data) {
                $model->height = $data['height'] ?? 0;
                if (isset($data['blocktime']) && !empty($data['blocktime'])) {
                    date_default_timezone_set('PRC');
                    $model->blocktime = date('Y-m-d H:i:s', $data['blocktime']);
                }
            }
            if ($model->save()) {
                $f = true;
            }
        } elseif ($result['code'] == Brower::TRANS_FAIL) {
            $model         = Bill::findOne($this->id);
            $model->status = -1;
            if ($model->save()) {
                $f = true;
            }
        }
        if (!$f) {
            \Yii::$app->queue->delay(5)->push(new BillJob(['id' => $this->id, 'txid' => $this->txid, 'chain' => $this->chain, 'from' => $this->from]));
            print "Fail: {$this->id}.\n";
        }
        return 0;
    }

}