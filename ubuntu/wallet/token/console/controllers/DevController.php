<?php
/**
 * Created By Sublime Text 3
 *
 * @authors rlgy <rlgyzhcn@qq.com>
 * @date    2018-09-05 16:54:03
 */

namespace console\controllers;

use h5\job\JugdeAwardJob;
use Yii;
use yii\base\BaseObject;
use backend\jobs\FreezeJob;
use yii\queue\JobInterface;
use yii\queue\Queue;
use yii\console\Controller;
use common\models\psources\CoinPublishRule;
use common\models\psources\CoinReleaseMember;
use backend\jobs\QueryTransJob;
use common\service\exchange\Bty;

/**
 * Class ExchangeController
 * 调用交易所api更新数据保存到redis
 *
 * @package console\controllers
 */
class DevController extends Controller
{
    /**
     * 解冻币任务
     *
     * @return [type] [description]
     */
    public function actionFreeze1()
    {
        $qid = Yii::$app->queue->push(new FreezeJob(['id' => 67]));
        var_dump($qid);
    }

    public function actionExtractToken()
    {
        $id = Yii::$app->queue->push(new QueryTransJob([
            'txhash' => '0x4ee0dd8615a70749a2f2bc38490c9483cf8399da523176f8c7b058afa26294ef',
            'lid'    => 25,
            'cid'    => 16
        ]));
        var_dump($id);
    }

    public function actionJugdeAward()
    {
        $id = Yii::$app->queue->push(new JugdeAwardJob());
        var_dump($id);
    }

    public function actionQuotation()
    {
        $bty = new Bty();
        $bty->setQuotation();
    }


}
