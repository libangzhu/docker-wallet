<?php

namespace api\controllers;

use common\models\psources\WalletCoinTicker;
use common\service\exchange\ExchangeFactory;
use Yii;
use api\base\BaseController;
use common\service\exchange\ExchangeBuilderFactory;

class MarketController extends BaseController
{
    public function actionDetail()
    {
        $currency = Yii::$app->request->post('currency', '');
        $base_currency = Yii::$app->request->post('base_currency', '');
        $exchange = Yii::$app->request->post("exchange", 'Zhaobi');
        if (false == $currency || false == $base_currency) {
            $msg = 'invalid symbol';
            $code = -1;
            goto doEnd;
        }
        $builder = ExchangeBuilderFactory::create('Zhaobi');
        $result = $builder->getDetail(strtoupper($currency), strtoupper($base_currency));
        if (-1 == $result['code']) {
            $msg = 'invalid symbol';
            $code = -1;
            goto doEnd;
        }
        $code = 0;
        $msg = 'ok';
        $ticker = $result['ticker'];
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'tick' => $ticker ?? []];
    }

    public function actionExchange()
    {
        $exchanges = array_unique(Yii::$app->params['exchange']);

        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $exchanges];
    }

    public function actionIndex()
    {
        $currency = Yii::$app->request->post('currency', '');
        $base_currency = Yii::$app->request->post('base_currency', '');
        $exchange = Yii::$app->request->post("exchange", 'Zhaobi');
        if (false == $currency || false == $base_currency) {
            $this->msg = 'invalid symbol';
            $this->code = -1;
            goto doEnd;
        }

        if ('USDT' == strtoupper($base_currency) && 'USDT' == strtoupper($currency)) {
            $this->msg = 'invalid symbol';
            $this->code = -1;
            goto doEnd;
        }

        if (false == $exchange || !in_array($exchange, Yii::$app->params['exchange'])) {
            $this->msg = 'invalid exchange';
            $this->code = -1;
            goto doEnd;
        }

        $currency_ticker = WalletCoinTicker::find()->where(['coin_name' => $currency])->asArray()->one();
        $base_currency_ticker = WalletCoinTicker::find()->where(['coin_name' => $base_currency])->asArray()->one();
        if (!empty($currency_ticker) && !empty($currency_ticker)) {
            $currency_quotation = [
                'low' => $currency_ticker['ticker'],
                'high' => $currency_ticker['ticker'],
                'last' => $currency_ticker['ticker'],
                'rmb' => $currency_ticker['ticker'],
            ];

            $base_currency_quotation = [
                'low' => $base_currency_ticker['ticker'],
                'high' => $base_currency_ticker['ticker'],
                'last' => $base_currency_ticker['ticker'],
                'rmb' => $base_currency_ticker['ticker'],
            ];
            goto doEnd;
        }

        $exchange = ExchangeFactory::createExchange($exchange);

        if ('USDT' == strtoupper($base_currency)) {
            $currency_ticker = $exchange->getTicker($currency, 'USDT');
            if (empty($currency_ticker)) {
                $this->code = -1;
                $this->msg = '此交易所暂不支持所选币种!';
                goto doEnd;
            }
            $this->data = rtrim(sprintf('%.6f', $currency_ticker['last']), '0');
            goto doEnd;
        }

        if ('USDT' == strtoupper($currency)) {
            $currency_ticker = $exchange->getTicker($base_currency, 'USDT');
            if (empty($currency_ticker)) {
                $this->code = -1;
                $this->msg = '此交易所暂不支持所选币种!';
                goto doEnd;
            }
            $this->data = rtrim(sprintf('%.6f', 1 / $currency_ticker['last']), '0');
            if ('.' == substr($this->data, -1)) {
                $this->data = rtrim($this->data, '.');
            }
            goto doEnd;
        }

        $currency_ticker = $exchange->getTicker($currency, 'USDT');
        $base_currency_ticker = $exchange->getTicker($base_currency, 'USDT');
        if (empty($currency_ticker) || empty($base_currency_ticker)) {
            $this->code = -1;
            $this->msg = '此交易所暂不支持所选币种!';
            goto doEnd;
        }
        $currency_quotation = [
            'low' => (float)sprintf("%0.4f", $currency_ticker['low']),
            'high' => (float)sprintf("%0.4f", $currency_ticker['high']),
            'last' => (float)sprintf("%0.4f", $currency_ticker['last']),
            'rmb' => (float)sprintf("%0.2f", $currency_ticker['last']),
        ];

        $base_currency_quotation = [
            'low' => (float)sprintf("%0.4f", $base_currency_ticker['low']),
            'high' => (float)sprintf("%0.4f", $base_currency_ticker['high']),
            'last' => (float)sprintf("%0.4f", $base_currency_ticker['last']),
            'rmb' => (float)sprintf("%0.2f", $base_currency_ticker['last']),
        ];

        doEnd :
        $this->data = rtrim(sprintf('%.6f', $currency_quotation['last'] / $base_currency_quotation['last']), '0');

        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

}