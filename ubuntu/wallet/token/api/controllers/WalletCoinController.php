<?php

namespace api\controllers;

use common\models\psources\CoinPlatformWithHold;
use Yii;
use api\base\BaseController;
use common\business\ExchangeBusiness;
use common\models\psources\WalletCoin;
use common\models\psources\CoinCurrency;
use common\models\psources\WalletCoinRelation;

class WalletCoinController extends BaseController
{
    /**
     * 币种列表
     * @param array names
     * @return array
     */
    public function actionIndex()
    {
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $names = Yii::$app->request->post('names', '');
        if (false == $names || false == $platform_id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }

        foreach ($names as $key => $val) {
            if (strpos($val, ',') !== false) {
                $val_array = explode(',', $val);
                if ('USDT' == strtoupper($val_array[0]) && 'BTC' == strtoupper($val_array[1])) {
                    $condition[] = [$val_array[0], 'omni'];
                    continue;
                }
                //张雄需求
                if ('HYS' == strtoupper($val_array[0]) && 'HYSCHAIN' == strtoupper($val_array[1])) {
                    $condition[] = [$val_array[0], 'COINUN'];
                    continue;
                }
                $condition[] = [$val_array[0], $val_array[1]];
            } else {
                $condition[] = $val;
            }
        }
        $this->data = [];

        foreach ($condition as $val) {
            if (is_array($val)) {
                $model = WalletCoin::find()->select('id,icon,name,nickname,optional_name,introduce,platform,chain,address as contract_address,treaty')
                    ->where(['name' => $val[0], 'platform' => $val[1]])
                    ->andWhere("JSON_CONTAINS(platform_id,'$platform_id')")
                    ->asArray()
                    ->one();
            }

            if (is_string($val)) {
                $model = WalletCoin::find()->select('id,icon,name,nickname,optional_name,introduce,platform,chain,address as contract_address,treaty')
                    ->where(['name' => $val])
                    ->andWhere("JSON_CONTAINS(platform_id,'$platform_id')")
                    ->asArray()
                    ->one();
            }
            if (!empty($model)) {
                $lock = CoinPlatformWithHold::find()->select('lock')->where(['platform' => $model['platform']])->asArray()->one();
                $model['lock'] = (int)$lock['lock'];
                $this->data = array_merge($this->data, [$model]);
            }
        }

        if (empty($this->data)) {
            goto doEnd;
        }

        $chains = array_column($this->data, 'chain');
        $symbol = array_column($this->data, 'name');
        $chains_symbols = array_keys(array_flip($chains) + array_flip($symbol));
        $coin_quotations = ExchangeBusiness::getQuatationByNames($chains_symbols);

        $last[] = 1;
        $currency = CoinCurrency::find()->where(['pj_id' => $this->currency_id])->one();
        if (false != $currency) {
            $last = Yii::$app->redis_currency->hmget('quotation_currency_' . 'CNY_' . $currency->symbol, 'last');
        }

        $ticker = ['low' => 0, 'high' => 0, 'last' => 0, 'open' => 0, 'vol' => 0, 'rmb' => 0, 'usd' => 0];
        foreach ($this->data as $key => &$val) {
            $val['chain_quotation'] = isset($coin_quotations[strtoupper($val['chain'])]) ? $coin_quotations[strtoupper($val['chain'])] : [];
            $nickname = json_decode($val['nickname'], true);
            $val['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
            $introduce = json_decode($val['introduce'], true);
            $val['introduce'] = isset($introduce[$this->lang]) ? $introduce[$this->lang] : '';
            $this->data[$key] = array_merge($val, isset($coin_quotations[strtoupper($val['name'])]) ? $coin_quotations[strtoupper($val['name'])] : $ticker);

            $val['chain_rmb'] = isset($val['chain_quotation']['rmb']) ? $val['chain_quotation']['rmb'] : 0;
            $val['chain_usd'] = isset($val['chain_quotation']['usd']) ? $val['chain_quotation']['usd'] : 0;
            $val['chain_country_rate'] = (float)sprintf("%0.4f", $last[0] * $val['chain_rmb']);
            $val['country_rate'] = (false == $currency) ? $val['rmb'] : (float)sprintf("%0.4f", $last[0] * $val['rmb']);
            unset($val['chain_quotation']);
        }

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    /**
     * 币种搜索
     * @param string chain
     * @param string keyword
     * @return array
     */
    public function actionSearch()
    {
        $params = Yii::$app->request->post();
        $chain = isset($params['chain']) ? $params['chain'] : '';
        $keyword = isset($params['keyword']) ? $params['keyword'] : '';

        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        if (false == $platform_id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }

        $page = empty($params['page']) ? 1 : $params['page'];
        $limit = empty($params['limit']) ? 10 : $params['limit'];

        $query = WalletCoin::find()
            ->select('id, icon, name, nickname, platform, chain, address as contract_address, treaty')
            ->where("JSON_CONTAINS(platform_id,'$platform_id')")
            ->orderBy('id');

        if (false != $chain) {
            $query->andWhere(['chain' => $chain]);
        }

        if (false != $keyword) {
            $query->andWhere(['or', ['like', 'address', $keyword], ['like', 'name', $keyword]]);
        }

        $this->data = $query->offset(($page - 1) * $limit)->limit($limit)->asArray()->all();
        if (false != $this->data) {
//            $chains = array_unique(array_column($this->data, 'chain'));
//            $chain_quotation = [];
//            foreach ($chains as $key => $value) {
//                $chain_quotation[$value] = ExchangeBusiness::getquatation($value);
//            }
//            foreach ($this->data as $key => $value) {
//                $this->data[$key]['chain_quotation'] = $chain_quotation[$value['chain']] ?: null;
//            }
            foreach ($this->data as $key => &$value) {
                $nickname = json_decode($value['nickname'], true);
                $value['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                $value['chain_rmb'] = isset($value['chain_quotation']['rmb']) ? $value['chain_quotation']['rmb'] : 0;
                $value['chain_usd'] = isset($value['chain_quotation']['usd']) ? $value['chain_quotation']['usd'] : 0;
            }
        }

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    /**
     * 获取币种简介
     * @param integer id
     * @return array
     */
    public function actionGetCoinById()
    {
        $params = Yii::$app->request->post();
        $id = isset($params['id']) ? $params['id'] : 0;

        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        if (false == $platform_id || false == $id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }
        $this->data = WalletCoin::find()
            ->where(['id' => $id])
            ->andWhere("JSON_CONTAINS(platform_id,'$platform_id')")
            ->one();
        if (empty($this->data)) {
            goto doEnd;
        }

        $quotation = ExchangeBusiness::getquatation($this->data->name);
        $tikerdata = [
            'publish_count' => $this->data->publish_count,
            'circulate_count' => $this->data->circulate_count
        ];
        if ($quotation) {
            $tikerdata['price'] = $quotation['rmb'];
            $tikerdata['dollar'] = $quotation['usd'];
        } else {
            $tikerdata['price'] = 0;
            $tikerdata['dollar'] = 0;
        }

        $this->data->quotation = $tikerdata;
        $this->data->nickname = isset($this->data->nickname[$this->lang]) ? $this->data->nickname[$this->lang] : '';
        $this->data->introduce = isset($this->data->introduce[$this->lang]) ? $this->data->introduce[$this->lang] : '';

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }
}