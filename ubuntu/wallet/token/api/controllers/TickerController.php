<?php

namespace api\controllers;

use common\components\Tools;
use common\models\psources\CoinCurrency;
use common\models\psources\CoinSupportedCurrency;
use Yii;
use yii\data\Pagination;
use linslin\yii2\curl\Curl;
use yii\helpers\ArrayHelper;
use api\base\BaseController;
use common\models\psources\CoinOptional;
use common\service\exchange\ExchangeFactory;
use common\service\exchange\ExchangeBuilderFactory;

class TickerController extends BaseController
{
    protected $basic_coin = ['ETH', 'BTC', 'USDT', 'BTY', 'CNYT'];
    protected $basic_price = [];

    public function actionIndex()
    {
        $page = Yii::$app->request->get('page', 1);
        $device_code = Yii::$app->request->get('device_code', '');
        $platform_id = Yii::$app->request->get('platform_id', 0);
        $exchange = Yii::$app->request->get('exchange', 'zhaobi');
        $data_value = Yii::$app->request->get('data_value', '');
        $sort_value = Yii::$app->request->get('sort_value', '');

        $exchange_arr = ['huobi', 'binance', 'zhaobi', 'bitnasdaq', 'jingwang'];

        if (!in_array($exchange, $exchange_arr)) {
            $this->msg = '不存在的交易平台';
            $this->code = -1;
            goto doEnd;
        }
        $condition = [];
        if (false != $data_value && false != $sort_value) {
            $condition = [
                'data_value' => $data_value,
                'sort_value' => $sort_value
            ];
        }
        if ('jingwang' == $exchange) {
            $exchange = 'jinwang';
        }
        $builder = ExchangeBuilderFactory::create($exchange);
        $result = $builder->getTickerFromCache($page, $condition);
        $this->code = $result['code'];
        $this->data = $result['data'];

        if (false != $device_code) {
            if (!$platform_id) {
                $coin_optional = CoinOptional::find()->select('symbol')->where(['platform' => $exchange, 'device_code' => $device_code])->asArray()->all();
            } else {
                $coin_optional = CoinOptional::find()->select('symbol')->where(['platform' => $exchange, 'device_code' => $device_code, 'platform_id' => $platform_id])->asArray()->all();
            }

            $coin_optional = ArrayHelper::getColumn($coin_optional, 'symbol');
            foreach ($this->data['ticker'] as &$val) {
                if (in_array($val['symbol'], $coin_optional)) {
                    $val['optional'] = true;
                }
            }
        }

        $this->msg = 'success';
        doEnd :

        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionOptional()
    {
        $curl = new Curl();
        $data = [
            "names" => [
                "eth,ethereum",
                "btc,btc",
                "usdt,ethereum",
                "bty,bty",
                "cnyt,bitnasdaqchain"
            ]
        ];
        $params = json_encode($data);
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setRawPostData($params);
        $res = $curl->post(Yii::$app->params['biqianbao'] . '/interface/coin/coin-index', true);
        $res = json_decode($res, true);
        foreach ($res['data'] as $val) {
            $this->basic_price[$val['name']]['rmb'] = $val['rmb'];
            $this->basic_price[$val['name']]['usd'] = $val['usd'];
        }

        $code = -1;
        $msg = 'fail';
        $data = null;

        $request = Yii::$app->request;
        if ($request->isPost) {
            $symbol = $request->post('symbol', '');
            $platform = $request->post('platform', '');
            $device_code = $request->post('device_code', '');
            $platform_id = $request->post('platform_id', 0);

            if (empty($symbol) || empty($platform) || empty($device_code) || empty($platform_id)) {
                $msg = '参数错误';
                goto doEnd;
            }
            $model = CoinOptional::find()->where(['device_code' => $device_code, 'symbol' => $symbol, 'platform' => $platform, 'platform_id' => (int)$platform_id])->one();
            if ($model) {
                $msg = '数据已存在!';
                goto doEnd;
            }

            $model = new CoinOptional();
            $model->setScenario(CoinOptional::SCENARIOS_CREATE);
            if ($model->load(Yii::$app->request->post(), '') && $model->save()) {
                $msg = 'success';
                $code = 0;
            } else {
                $msg = current($model->firstErrors);
            }
            goto doEnd;
        }

        if ($request->isGet) {
            $device_code = $request->get('device_code', '');
            $platform_id = $request->get('platform_id', 0);
            $page = Yii::$app->request->get('page', 1);
            if (false == $device_code || false == $platform_id) {
                $msg = '参数错误';
                goto doEnd;
            }
            $data_value = Yii::$app->request->get('data_value', '');
            $sort_value = Yii::$app->request->get('sort_value', '');
            $condition = [];
            if (false != $data_value && false != $sort_value) {
                $condition = [
                    'data_value' => $data_value,
                    'sort_value' => $sort_value
                ];
            }

            $temp = [];
            $data = CoinOptional::find()->select('symbol, platform')
                ->where(['device_code' => $device_code, 'platform_id' => (int)$platform_id])
                ->orderBy('update_time desc')
                ->asArray()
                ->all();
            if (false == $data) {
                $msg = 'success';
                $code = 0;
                $data = [
                    'ticker' => [],
                    'page' => [
                        'pageSize' => 50,
                        'currentPage' => (int)$page,
                    ]
                ];
                goto doEnd;
            }
            $ticker = [];
            foreach ($data as $val) {
                if ('huobi' == $val['platform']) {
                    $exchange = 'HuoBi';
                } else if ('binance' == $val['platform']) {
                    $exchange = 'Binance';
                } else if ('zhaobi' == $val['platform']) {
                    $exchange = 'Zhaobi';
                } else if ('bitnasdaq' == strtolower($val['platform'])) {
                    $exchange = 'Bitnasdaq';
                } else {

                }

                $symbol = explode('/', $val['symbol']);
                $tag_first = $currency = $symbol[0];
                $tag_second = $symbol[1];
                if ('BCH' == strtoupper($tag_first)) {
                    $tag_first = 'BCC';
                }

                if ('USAT' == strtoupper($tag_second)) {
                    $tag_second = 'USDT';
                }
                $exchange = ExchangeFactory::createExchange($exchange);
                $quotation = $exchange->getTicker(strtolower($tag_first), strtolower($tag_second));
                if (empty($quotation)) continue;
                $temp['symbol'] = $val['symbol'];
                $temp['currency'] = strtoupper($currency);
                $temp['base_currency'] = strtoupper($tag_second);
                $temp['close'] = rtrim(sprintf('%.8f', floatval($quotation['last'])), '0');
                $temp['close_usd'] = rtrim(sprintf('%.6f', floatval($quotation['last'] * $this->basic_price[$tag_second]['usd'])), '0');
                $temp['close_rmb'] = (float)sprintf("%0.4f", $quotation['last'] * $this->basic_price[$tag_second]['rmb']);
                $temp['change'] = (0 == $quotation['open']) ? 0 : (float)sprintf("%0.2f", ($quotation['last'] - $quotation['open']) / $quotation['open'] * 100);
                $temp['high_usd'] = (float)sprintf("%0.4f", $quotation['high'] * $this->basic_price[$tag_second]['usd']);
                $temp['low_usd'] = (float)sprintf("%0.4f", $quotation['low'] * $this->basic_price[$tag_second]['usd']);
                $temp['high_rmb'] = (float)sprintf("%0.4f", $quotation['high'] * $this->basic_price[$tag_second]['rmb']);
                $temp['low_rmb'] = (float)sprintf("%0.4f", $quotation['low'] * $this->basic_price[$tag_second]['rmb']);
                $temp['vol'] = (float)sprintf("%0.4f", $quotation['vol']);
                $temp['platform_us'] = $val['platform'];
                if ('ZHAOBI' == strtoupper($val['platform'])) {
                    $temp['platform_zh'] = '找币';
                    $temp['platform_us'] = 'zhaobi';
                }
                if ('HUOBI' == strtoupper($val['platform'])) {
                    $temp['platform_zh'] = '火币';
                    $temp['platform_us'] = 'huobi';
                }
                if ('BINANCE' == strtoupper($val['platform'])) {
                    $temp['platform_zh'] = '币安';
                    $temp['platform_us'] = 'binance';
                }
                if ('BITNASDAQ' == strtoupper($val['platform'])) {
                    $temp['platform_zh'] = '比特纳斯达克';
                    $temp['platform_us'] = 'bitnasdaq';
                }
                array_push($ticker, $temp);
            }
            if (!empty($condition)) {
                if ('price' == $condition['data_value']) {
                    if ('price_asc' == $condition['sort_value']) {
                        $ticker = Tools::arraySort($ticker, 'close', SORT_ASC);
                    }
                    if ('price_desc' == $condition['sort_value']) {
                        $ticker = Tools::arraySort($ticker, 'close', SORT_DESC);
                    }
                }
                if ('change' == $condition['data_value']) {
                    if ('change_asc' == $condition['sort_value']) {
                        $ticker = Tools::arraySort($ticker, 'change', SORT_ASC);
                    }
                    if ('change_desc' == $condition['sort_value']) {
                        $ticker = Tools::arraySort($ticker, 'change', SORT_DESC);
                    }
                }
            }
        }

        $data = [
            'ticker' => $ticker,
            'page' => [
                'pageSize' => 50,
                'currentPage' => (int)$page,
            ]
        ];
        $msg = 'success';
        $code = 0;

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionRemoveOptional()
    {
        $code = -1;
        $msg = 'fail';
        $data = null;

        $request = Yii::$app->request;
        if (!$request->isPost) {
            $msg = '请求错误!';
            goto doEnd;
        }

        $symbol = $request->post('symbol', '');
        $platform = $request->post('platform', '');
        $device_code = $request->post('device_code', '');
        $platform_id = $request->post('platform_id', 0);
        if (empty($symbol) || empty($device_code) || empty($platform) || empty($platform_id)) {
            $msg = '请求参数错误!';
            goto doEnd;
        }
        $model = CoinOptional::find()->where(['symbol' => $symbol, 'platform' => $platform, 'device_code' => $device_code, 'platform_id' => (int)$platform_id])->one();
        if (empty($model)) {
            $msg = '数据不存在!';
            goto doEnd;
        }

        if (!$model->delete()) {
            $msg = '删除失败!';
            goto doEnd;
        }
        $code = 0;
        $msg = 'success';
        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionHotTicker()
    {
        $platform_id = $this->platform_id ?? 0;
        $datas = [
            [
                'exchange' => 'huobi',
                'symbol' => [
                    'btcusdt',
                    'ethusdt'
                ]
            ], [
                'exchange' => 'zhaobi',
                'symbol' => [
                    'btyusdt'
                ]
            ]
        ];
        if (9 == $platform_id) {
            $datas = [
                [
                    'exchange' => 'huobi',
                    'symbol' => [
                        'btcusdt',
                        'ethusdt'
                    ],
                ], [
                    'exchange' => 'dag',
                    'symbol' => [
                        'dagusdt'
                    ],
                ]
            ];
        }
        if (17 == $platform_id) {
            $datas = [
                [
                    'exchange' => 'huobi',
                    'symbol' => [
                        'btcusdt',
                        'ethusdt'
                    ],
                ], [
                    'exchange' => 'bitnasdaq',
                    'symbol' => [
                        'bncusdt'
                    ],
                ]
            ];
        }
        if (52 == $platform_id) {
            $datas = [
                [
                    'exchange' => 'huobi',
                    'symbol' => [
                        'btcusdt',
                        'ethusdt'
                    ],
                ], [
                    'exchange' => 'ztb',
                    'symbol' => [
                        'zycusdt'
                    ],
                ]
            ];
        }
        if (53 == $platform_id) {
            $datas = [
                [
                    'exchange' => 'huobi',
                    'symbol' => [
                        'btcusdt',
                        'ethusdt'
                    ],
                ], [
                    'exchange' => 'wbf',
                    'symbol' => [
                        'leleusdt'
                    ],
                ]
            ];
        }
        if (54 == $platform_id) {
            $datas = [
                [
                    'exchange' => 'huobi',
                    'symbol' => [
                        'btcusdt',
                        'ethusdt'
                    ],
                ], [
                    'exchange' => 'hd',
                    'symbol' => [
                        'hdcusdt'
                    ],
                ]
            ];
        }
        if (64 == $platform_id) {
            $datas = [
                [
                    'exchange' => 'huobi',
                    'symbol' => [
                        'btcusdt',
                        'ethusdt'
                    ],
                ], [
                    'exchange' => 'gwa',
                    'symbol' => [
                        'gwausdt'
                    ],
                ]
            ];
        }
        $ticker = [];
        foreach ($datas as $data) {
            $builder = ExchangeBuilderFactory::create($data['exchange']);
            $result = $builder->getHotTicker($data['symbol']);
            if (0 != $result['code']) continue;
            $ticker = array_merge($ticker, $result['ticker']);
        }
        $code = 0;
        $data = $ticker;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionCurrency()
    {
        $code = 0;
        $data = null;
        $platform_id = $this->platform_id;
        if (false == $platform_id) {
            $code = -1;
            $msg = '请求参数错误!';
            goto doEnd;
        }
        $currency = CoinSupportedCurrency::find()->where(['platform_id' => $platform_id])->orderBy('sort')->all();
        foreach ($currency as $val) {
            if (1111 == $val->currency->pj_id) {
                $rate = (string)1;
            } else {
                list($rate) = Yii::$app->redis_currency->hmget('quotation_currency_' . 'CNY_' . $val->currency->symbol, 'last');
            }
            $val->pj_id = $val->currency->pj_id;
            $val->pj_name = $val->currency->pj_name;
            $val->pj_symbol = $val->currency->pj_symbol;
            $val->rate = $rate;
            unset($val->id);
            unset($val->platform_id);
            unset($val->currency_id);
        }
        $data = $currency;
        $code = 0;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}