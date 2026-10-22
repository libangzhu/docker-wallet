<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 下午4:21
 */

namespace console\controllers;

use common\business\Chain33Business;
use common\business\CoinBusiness;
use h5\job\JugdeAwardJob;
use Yii;
use yii\console\Controller;
use common\models\psources\CoinGuessResult;

/**
 * Class AwardController
 * 竞猜开奖
 *
 * @package console\controllers
 */
class AwardController extends Controller
{
    /**
     * 区块链开奖
     */
    public function actionIndex()
    {
        $config = Yii::$app->params['h5_activity']['ZWG_GUESS_NEWBEE'];
        //获取最新的区块高度
        $last_header = Chain33Business::getLastHeader();
        if ($last_header['code'] != 0) {
            Yii::info('获取区块高度失败: ' . $last_header['msg'], __CLASS__);
            return 0;
        }
        $last_header = $last_header['result'];
        //获取最新竞猜期数以及区块高度
        $last = CoinGuessResult::getMaxStage();
        if ($last) {
            $stage        = $last->stage + 1;
            $start_height = $last->end_height;
        } else {
            //第一次计算开奖
            $stage        = intval($last_header['height'] / $config['do_award_step']);
            $start_height = ($stage - 1) * $config['do_award_step'];
        }
        $end_height = $start_height + $config['do_award_step'];
        //判断区块是否超前了
        if ($end_height > $last_header['height']) {
            return 0;
        }
        //获取中奖号码
        $hash = Chain33Business::getBlockHashByHeight($end_height);
        if ($hash['code'] != 0) {
            Yii::info('获取区块hash失败: ' . $hash['msg'], __CLASS__);
            return 0;
        }
        $hash   = $hash['result']['hash'];
        preg_match_all('/\d+/', $hash, $matchs);
        $hash   = implode('', $matchs[0]);
        $hash   = substr($hash, -2);
        if (!ctype_digit($hash)) {
            $hash = hexdec($hash) % 100;
            $hash = sprintf("%02d", $hash);
        }
        $hash = strval($hash);
        //保存开奖结果到数据库
        $guess_result               = new CoinGuessResult();
        $guess_result->stage        = $stage;
        $guess_result->start_height = $start_height;
        $guess_result->end_height   = $end_height;
        $guess_result->result       = $hash;
        $guess_result->is_award     = 0;
        if ($guess_result->save()) {
            //添加派奖任务到队列
            Yii::$app->queue->push(new JugdeAwardJob());
            Yii::info("竞猜开奖成功! [期数]: $stage", __CLASS__);
        } else {
            Yii::info("保存开奖记录失败!  [期数]: $stage", __CLASS__);
        }
        return 0;
    }
}