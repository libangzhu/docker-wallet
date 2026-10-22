<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-27
 * Time: 上午10:15
 */

namespace console\controllers;

use yii\console\Controller;
use common\business\ExchangeBusiness;

/**
 * Class ExchangeController
 * 调用交易所api更新数据保存到redis
 * @package console\controllers
 */
class ExchangeController extends Controller
{

    /**
     * 更新各个交易所支持的交易对
     */
    public function actionSupportedSymbol()
    {
        try {
            ExchangeBusiness::setSupportedSymbol();
        } catch (\Exception $e) {
            print $e->getMessage() . "\n";
        }
    }

    /**
     * 更新交易对行情
     */
    public function actionQuotation()
    {
        ExchangeBusiness::setQuotation();
        echo date('Y-m-d H:i:s') . '数据更新成功'.PHP_EOL;
        return 0;
    }

    public function actionTest()
    {
        $a = new \common\service\exchange\Zb();
        $a->setSupportedSymbol();
        $a->setQuotation();
    }
}
