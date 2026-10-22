<?php

namespace api\controllers;

use api\base\BaseController;
use common\models\psources\CoinAirDropTrade;
use common\models\psources\CoinSupportedSymbol;
use common\service\chain33\Chain33Service;
use linslin\yii2\curl\Curl;
use Yii;
use yii\helpers\ArrayHelper;


class SupportedSymbolController extends BaseController
{
    public function actionSearch()
    {
        $platform_id = Yii::$app->request->get('id', '');
        if (empty($platform_id)) {
            $msg = '参数不能为空';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $parentSupportedSymbol = CoinSupportedSymbol::find()->where(['platform_id' => $platform_id])->groupBy('currency')->all();
        if (empty($parentSupportedSymbol)) {
            $msg = 'success';
            $code = 0;
            $market = null;
            goto doEnd;
        }
        $market = [];
        foreach ($parentSupportedSymbol as $val) {
            $coin_name[] = $val->coinInfo->name;
        }
        $curl = new Curl();
        $data = [
            "names" => $coin_name
        ];
        $params = json_encode($data);
        $curl->setHeader('Content-Type', 'application/json');
        $curl->setRawPostData($params);
        $res = $curl->post(\Yii::$app->params['biqianbao'] . '/interface/coin/coin-index', true);
        $res = json_decode($res, true);
        $res = array_column($res['data'], NULL, 'id');;

        foreach ($parentSupportedSymbol as &$val) {
            $temp = [];
            $temp['id'] = (int)$val->currency;
            $temp['name'] = $val->coinInfo->name;
            $temp['icon'] = $val->coinInfo->icon;
            $temp['isToken'] = (1 == $val->coinInfo->treaty) ? true : false;
            $temp['isParacross'] = ('BTY' == strtoupper($val->coinInfo->platform)) ? false : true;
            $temp['url'] = 'http://www.huifen.com';
            $temp['platform'] = $val->coinInfo->platform;
            $temp['rmb'] = isset($res[$val->currency]['rmb']) ? $res[$val->currency]['rmb'] : 0.00;
            $temp['usd'] = isset($res[$val->currency]['usd']) ? $res[$val->currency]['usd'] : 0.00;
            array_push($market, $temp);
        }
        $coin_name = [];
        foreach ($market as &$val) {
            $childSupportedSymbol = CoinSupportedSymbol::find()->where(['platform_id' => $platform_id, 'currency' => $val['id']])->all();
            $tokens = [];

            foreach ($childSupportedSymbol as $child) {
                $coin_name[] = $child->baseCurrencyInfo->name;
            }
            $data_child = [
                "names" => $coin_name
            ];
            $params = json_encode($data_child);
            $curl = new Curl();
            $curl->setHeader('Content-Type', 'application/json');
            $curl->setRawPostData($params);
            $res = $curl->post(\Yii::$app->params['biqianbao'] . '/interface/coin/coin-index', true);
            $res = json_decode($res, true);
            $res = array_column($res['data'], NULL, 'id');;

            foreach ($childSupportedSymbol as $child) {
                $temp = [];
                $temp['id'] = (int)$child->base_currency;
                $temp['name'] = $child->baseCurrencyInfo->name;
                $temp['icon'] = $child->baseCurrencyInfo->icon;
                $temp['isToken'] = (1 == $child->baseCurrencyInfo->treaty) ? true : false;
                $temp['isParacross'] = ('BTY' == strtoupper($child->baseCurrencyInfo->platform)) ? false : true;
                $temp['url'] = 'http://www.huifen.com';
                $temp['platform'] = $child->baseCurrencyInfo->platform;
                $temp['rmb'] = isset($res[$child->base_currency]['rmb']) ? $res[$child->base_currency]['rmb'] : 0.00;
                $temp['usd'] = isset($res[$child->base_currency]['usd']) ? $res[$child->base_currency]['usd'] : 0.00;
                array_push($tokens, $temp);
            }
            $val['tokens'] = $tokens;
        }
        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $market];
    }
}