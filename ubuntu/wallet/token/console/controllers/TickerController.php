<?php

namespace console\controllers;

use common\service\exchange\ExchangeFactory;
use Yii;
use linslin\yii2\curl\Curl;
use yii\console\Controller;
use voku\helper\HtmlDomParser;
use common\models\psources\CoinSupportedCurrency;
use common\service\exchange\ExchangeBuilderFactory;

class TickerController extends Controller
{
    public function actionSort()
    {
        $class = ['Binance', 'Huobi', 'Zhaobi', 'Bitnasdaq', 'Jinwang'];
        foreach ($class as $val) {
            go(function () use ($val) {
                \Co::sleep(0.5);
                $ticker_builder = ExchangeBuilderFactory::create($val);
                $ticker_builder->TickerSort();
            });
        }
        echo date('Y-m-d H:i:s') . '排序更新成功' . PHP_EOL;
        return 0;
    }

    public function actionHot()
    {
        $ticker_builder = ExchangeBuilderFactory::create('Huobi');
        $ticker_builder->hotTickerUpdate();
        echo date('Y-m-d H:i:s') . '更新成功' . PHP_EOL;
        return 0;
    }

    public function actionExchange($exchange)
    {
        $ticker_builder = ExchangeFactory::createExchange($exchange);
        $ticker_builder->setQuotation();
        echo date('Y-m-d H:i:s') . $exchange . '更新成功' . PHP_EOL;
        return 0;
    }

    public function actionCurrency()
    {
        $curl = new Curl();
        $curl->setHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:98.0) Gecko/20100101 Firefox/98.0',
            'Host' => 'cc-api.oanda.com',
            'Origin' => 'https://www.oanda.com',
            'Sec-Fetch-Dest' => 'empty',
            'Sec-Fetch-Mode' => 'cors',
            'Sec-Fetch-Site' => 'same-site',
            'TE' => 'trailers'
        ]);
        $currency_model = CoinSupportedCurrency::find()->groupBy('currency_id')->all();
        $start_date = date("Y-m-d", strtotime("-1 day"));
        $end_data = date("Y-m-d");
        foreach ($currency_model as $val) {
            $url = 'https://cc-api.oanda.com/cc-api/v1/currencies?base=USD&quote=' . $val->currency->symbol . '&data_type=general_currency_pair&start_date=' . $start_date . '&end_date=' . $end_data;
            $content = $curl->get($url, false);
            if (is_array($content) && isset($content['response'])) {
                $key = 'quotation_currency_' . 'USD_' . $val->currency->symbol;
                Yii::$app->redis_currency->hmset($key, 'low', $content['response'][0]['low_ask'], 'high', $content['response'][0]['high_ask'], 'last', $content['response'][0]['average_ask'], 'open', $content['response'][0]['average_ask']);
                Yii::$app->redis_currency->sadd('supported_symbol_currency', $content['response'][0]['base_currency'] . '_' . $content['response'][0]['quote_currency']);
            }

            $url = 'https://cc-api.oanda.com/cc-api/v1/currencies?base=CNY&quote=' . $val->currency->symbol . '&data_type=general_currency_pair&start_date=' . $start_date . '&end_date=' . $end_data;
            $content = $curl->get($url, false);
            if (is_array($content) && isset($content['response'])) {
                $key = 'quotation_currency_' . 'CNY_' . $val->currency->symbol;
                Yii::$app->redis_currency->hmset($key, 'low', $content['response'][0]['low_ask'], 'high', $content['response'][0]['high_ask'], 'last', $content['response'][0]['average_ask'], 'open', $content['response'][0]['average_ask']);
                Yii::$app->redis_currency->sadd('supported_symbol_currency', $content['response'][0]['base_currency'] . '_' . $content['response'][0]['quote_currency']);
            }
        }

        echo date('Y-m-d H:i:s') . '计价货币更新成功' . PHP_EOL;
        return 0;
    }
}