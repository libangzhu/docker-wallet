<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/29
 * Time: 16:26
 */

namespace api\controllers;

use api\base\BaseController;

use common\business\ExchangeBusiness;
use common\models\psources\Coin;
use common\models\psources\CoinRecommend;
use common\models\psources\MinerFee;
use common\service\coin\CoinFactory;
use common\service\exchange\ExchangeFactory;
use Yii;

/**
 * 对外(托管钱包)服务控制器
 * Class CoinController
 *
 * @package api\controllers
 */
class ServiceController extends BaseController
{
    /**
     * 获取币种行情
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public function actionCoinTickers()
    {
        $request = Yii::$app->request;
        $coinItems = $request->post('names');
        if (!$coinItems) {
            return ['code' => 1, 'data' => [], 'msg' => '币种不能为空'];
        }
        if (!is_array($coinItems)) {
            $coinItems = [$coinItems];
        }
        $tol_coins = ['ETC'];
        $tickerData = [];
        if ($coinItems) {
            foreach ($coinItems as $item) {
                $item = strtoupper($item);
                if (in_array($item, $tol_coins)) {
                    $exchange = ExchangeFactory::createExchange('HuoBi');
                    if ($exchange->symbolExists($item)) {
                        $quotation = $exchange->getTicker($item);
                        if ($quotation) {
                            //格式化行情数据
                            foreach ($quotation as $key => $value) {
                                $quotation[$key] = (float)sprintf("%0.4f", (double)$value);
                            }
                            $rate = $this->getRate();
                            $quotation['rmb'] = (float)sprintf("%0.4f", $rate * $quotation['last']);
                        }
                    }
                } else {
                    $quotation = ExchangeBusiness::getquatation($item);
                }
                if (!$quotation) {
                    //使用Coin服务
                    try {
                        $coinServer = CoinFactory::createCoin($item, '', '');
                        $tickerData[$item]['rmb'] = $coinServer->getPrice();
                        $tickerData[$item]['last'] = $coinServer->getDollar();
                        $tickerData[$item]['low'] = $coinServer->getLow();
                        $tickerData[$item]['high'] = $coinServer->getHigh();
                        $tickerData[$item]['open'] = $coinServer->getDollar();
                        $tickerData[$item]['usd'] = $coinServer->getDollar();
                        $coinServer->__destruct();
                    } catch (\Exception $exception) {
                        $tickerData[$item]['rmb'] = 0;
                        $tickerData[$item]['last'] = 0;
                        $tickerData[$item]['low'] = 0;
                        $tickerData[$item]['high'] = 0;
                        $tickerData[$item]['open'] = 0;
                        $tickerData[$item]['usd'] = 0;
                        \Yii::error($exception->getMessage());
                    }
                } else {
                    $tickerData[$item] = $quotation;
                }
            }
            return ['code' => 0, 'data' => $tickerData, 'msg' => '行情获取成功'];
        }
    }

    /**
     * @return array
     * 托管钱包首页
     */
    public function actionCoinIndex()
    {
        $platform_id = Yii::$app->request->get('platform_id', 6);
        $type = Yii::$app->request->get('type', 1);
        $coin_recommendItems = $this->coinRecommendList($platform_id, $type);
        $fields = ['id', 'sid', 'icon', 'name', 'nickname', 'chain', 'platform'];
        $rows = Coin::getSelectList(1, 999, $fields, [['in', 'id', $coin_recommendItems]], $coin_recommendItems);
        foreach ($rows['data'] as $key => &$value) {
            $nickname = json_decode($value['nickname'], true);
            $value['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
        }
        return ['code' => 0, 'data' => $rows, 'msg' => '币种列表获取成功'];
    }

    /**
     * @return array
     * 托管钱包推荐币种
     */
    private function coinRecommendList($platform_id, $type = 1)
    {
        $recommend_list = CoinRecommend::find()->select('cid')->where(['platform_id' => $platform_id, 'type' => $type])->orderBy('sort')->all();
        if ($recommend_list) {
            $coin_ids = array_column($recommend_list, 'cid');
            return $coin_ids;
        }
        return [];
    }

    /**
     * @return array
     * 获取旷工费
     */
    public function actionFee()
    {
        $request = Yii::$app->request;
        $coin = $request->post('name');
        if (!$coin) {
            return ['code' => 1, 'data' => [], 'msg' => '币种不能为空'];
        }
        $fee = MinerFee::find()->where(['platform' => $coin, 'type' => 2])->select('id,platform,type,fee,create_at,update_at')->asArray()->one();
        if (!$fee) {
            return ['code' => 1, 'data' => [], 'msg' => '旷工费未设置'];
        }
        return ['code' => 0, 'data' => $fee, 'msg' => '旷工费获取成功'];
    }

    /**
     * @return float|int
     * 获取汇率
     */
    private function getRate()
    {
        $exchange = ExchangeFactory::createExchange("Bty");
        $rate = $exchange->getTicker("BTY", "USDT");
        return (float)$rate['rmb'] / $rate['last'];
    }

    /**
     * @return array
     * 根据币种获取类型
     */
    public function actionChain()
    {
        $request = Yii::$app->request;
        $currency = $request->post('currency', '');
        $coin = Coin::find()->where(['name' => $currency])->select('name,nickname,chain')->asArray()->one();
        if ($coin) {
            return ['code' => 0, 'data' => $coin];
        }
        return ['code' => -1, 'msg' => '币种不存在'];
    }

}
