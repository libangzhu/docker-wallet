<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午1:02
 */

namespace api\controllers;

use api\base\BaseController;
use common\base\Exception;
use common\business\BrowerBusiness;
use common\business\Chain33Business;
use common\business\CoinBusiness;
use common\business\ExchangeBusiness;
use common\models\psources\Coin;
use common\models\psources\CoinCurrency;
use common\models\psources\CoinPlatformWithHold;
use common\models\psources\CoinRecommend;
use common\models\psources\MinerFee;
use Yii;
use yii\data\Pagination;

/**
 * 币种信息管理控制器
 * Class CoinController
 *
 * @package api\controllers
 */
class CoinController extends BaseController
{
    /**
     * 单币种按照id查询
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public function actionGetCoinById()
    {
        $request = Yii::$app->request;
        $id = $request->post('id', 0);
        if ($id) {
            $ret = CoinBusiness::getCoinAllById($id);
            if ($ret) {
                $data = $ret[0];
                $nickname = json_decode($data['nickname'], true);
                $introduce = json_decode($data['introduce'], true);
                $data['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                $data['introduce'] = isset($introduce[$this->lang]) ? $introduce[$this->lang] : '';
                return $data;
            }
        }
        return [];
    }

    /**
     * 获取推介币种列表
     */
    public function actionGetRecList()
    {
        $request = Yii::$app->request;
        $page = $request->post('page', 1);
        $limit = $request->post('limit', 999);
        $platform_id = $request->post('platform_id', 2);//默认币钱包
        if ($platform_id == 1) {
            $platform_id = 3;
        } elseif ($platform_id == 2) {
            $platform_id = 1;
        } elseif ($platform_id == 3) {
            $platform_id = 2;
        }
        $recommend = $request->post('recommend', '');

        $condition = ['platform_id' => $platform_id, 'type' => 1];
        if ($recommend) {
            $condition['recommend'] = $recommend;
        }
        $select = ['id', 'sid', 'icon', 'name', 'nickname', 'platform', 'chain'];
        $datas = CoinRecommend::getList($page, $limit, $condition, [], $select);
        //获取详细信息
        $coin_recommends = &$datas['data'];
        if (!empty($coin_recommends)) {
            $coin_ids = array_column($coin_recommends, 'cid');
            //获取币种信息
            $coin_infos = Coin::getCoinInfoByIds($coin_ids, $select, 'id');
            //获取行情信息
            $coin_names = array_column($coin_infos, 'name');
            $coin_names = array_merge($coin_names, array_column($coin_infos, 'chain'));
            $coin_quotations = ExchangeBusiness::getQuatationByNames($coin_names);

            if ($coin_infos) {
                array_shift($select);
                foreach ($coin_recommends as $key => &$value) {
                    $temp_key = $coin_infos[$value['cid']]['name'];
                    foreach ($select as $item) {
                        $value[$item] = $coin_infos[$value['cid']][$item];
                        if ($value['platform_id'] != 2) {
                            //国盾币不需要行情
                            $value['low'] = $coin_quotations[$temp_key]['low'];
                            $value['high'] = $coin_quotations[$temp_key]['high'];
                            $value['last'] = $coin_quotations[$temp_key]['last'];
                            $value['rmb'] = $coin_quotations[$temp_key]['rmb'];
                        } else {
                            $value['low'] = 0;
                            $value['high'] = 0;
                            $value['last'] = 0;
                            $value['rmb'] = 0;
                        }
                    }
                    $value['id'] = $value['cid'];
                    $value['sid'] = ucfirst($value['sid']);
                    $value['chain_quotation'] = $coin_quotations[$coin_infos[$value['cid']]['chain']];
                    unset($value['create_time'], $value['update_time'], $value['cid']);
                }
                unset($key, $value);
            }
        }
        return $datas;
    }

    /**
     * 获取推介币种列表(新)
     */
    public function actionGetNewRecList()
    {
        $request = Yii::$app->request;
        $page = $request->post('page', 1);
        $limit = $request->post('limit', 999);
        $platform_id = $request->post('platform_id', 1);//默认币钱包
        $recommend = $request->post('recommend', '');
        $type = $request->post('type', 1);
        $chain = $request->post('chain', '');
        $condition = ['platform_id' => $platform_id, 'type' => $type];
        if ($recommend) {
            $condition['recommend'] = $recommend;
        }

        if ($chain) {
            $condition['chain'] = $chain;
        }
        $select = ['id', 'sid', 'icon', 'name', 'introduce', 'optional_name', 'nickname', 'platform', 'chain', 'treaty'];
        $order_by = ['sort' => SORT_ASC];
        $datas = CoinRecommend::getList($page, $limit, $condition, $order_by, $select);
        //获取详细信息
        $coin_recommends = &$datas['data'];
        if (!empty($coin_recommends)) {
            $coin_ids = array_column($coin_recommends, 'cid');
            //获取币种信息
            $coin_infos = Coin::getCoinInfoByIds($coin_ids, $select, 'id');
            foreach ($coin_infos as $key => &$val) {
                $nickname = json_decode($val['nickname'], true);
                $val['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                $introduce = json_decode($val['introduce'], true);
                $val['introduce'] = isset($introduce[$this->lang]) ? $introduce[$this->lang] : '';
            }
            //获取行情信息
            $coin_names = array_column($coin_infos, 'name');
            $coin_names = array_merge($coin_names, array_column($coin_infos, 'chain'));
            $coin_quotations = ExchangeBusiness::getQuatationByNames($coin_names);

            if ($coin_infos) {
                array_shift($select);
                foreach ($coin_recommends as $key => &$value) {
                    $temp_key = $coin_infos[$value['cid']]['name'];
                    foreach ($select as $item) {
                        $value[$item] = $coin_infos[$value['cid']][$item];
                        if ($value['platform_id'] != 2) {
                            //国盾币不需要行情
                            $value['low'] = $coin_quotations[$temp_key]['low'];
                            $value['high'] = $coin_quotations[$temp_key]['high'];
                            $value['last'] = $coin_quotations[$temp_key]['last'];
                            $value['rmb'] = $coin_quotations[$temp_key]['rmb'];
                            $value['usd'] = $coin_quotations[$temp_key]['usd'] ?? 0;
                        } else {
                            $value['low'] = 0;
                            $value['high'] = 0;
                            $value['last'] = 0;
                            $value['rmb'] = 0;
                            $value['usd'] = 0;
                        }
                    }
                    $value['id'] = $value['cid'];
                    $value['sid'] = ucfirst($value['sid']);
                    $value['chain_quotation'] = $coin_quotations[$coin_infos[$value['cid']]['chain']];
                    $value['chain_rmb'] = isset($coin_quotations[$coin_infos[$value['cid']]['chain']]['rmb']) ? $coin_quotations[$coin_infos[$value['cid']]['chain']]['rmb'] : 0;
                    $value['chain_usd'] = isset($coin_quotations[$coin_infos[$value['cid']]['chain']]['usd']) ? $coin_quotations[$coin_infos[$value['cid']]['chain']]['usd'] : 0;
                    unset($value['create_time'], $value['update_time'], $value['cid']);
                }
                unset($key, $value);
            }
        }
        if (!$datas['data']) {
            $datas['data'] = null;
        }
        return $datas;
    }

    /**
     * 矿工费获取
     *
     * 根据name获取
     *
     * @throws Exception
     */
    public function actionGetMinerFeeByName()
    {
        $names = Yii::$app->request->post('name');
        $coin = Coin::findOne(['name' => $names]);
        if ($coin) {
            $chain = $coin->chain;
            $miner_fee = MinerFee::find()->where(['platform' => $chain, 'type' => 1])->one();
            if (!$miner_fee) {
                throw new Exception('8', '旷工费未设置');
            }
        } else {
            //如果coin为null，$coin->minerFee会抛出Trying to get property 'minerFee' of non-object"，code=>8
            throw new Exception('8', '币种不存在');
        }
        $result = (array)$miner_fee->getAttributes();
        $result['min'] = number_format($result['min'], 6);
        $result['max'] = number_format($result['max'], 6);
        return $result;
    }

    /**
     * app首页接口V2
     */
    public function actionCoinIndex()
    {
        $names = Yii::$app->request->post('names');
        if (!$names) {
            return ['code' => 0, 'data' => []];
        }
        foreach ($names as $key => $val) {
            if (strpos($val, ',') !== false) {
                $val_array = explode(',', $val);
                if ('USDT' == strtoupper($val_array[0]) && 'BTC' == strtoupper($val_array[1])) {
                    $condition[] = [$val_array[0], 'omni'];
                    continue;
                }
                $condition[] = [$val_array[0], $val_array[1]];
            } else {
                $condition[] = $val;
            }
        }
        $result = ExchangeBusiness::getApiListForIndex(1, 999, $condition);
        if ($result) {
            $chains = array_unique(array_column($result['data'], 'chain'));
            $chain_quotation = [];
            foreach ($chains as $key => $value) {
                $chain_quotation[$value] = ExchangeBusiness::getquatation($value);
            }
            $last[] = 1;
            $currency = CoinCurrency::find()->where(['pj_id' => $this->currency_id])->one();
            if (false != $currency) {
                $last = Yii::$app->redis_currency->hmget('quotation_currency_' . 'CNY_' . $currency->symbol, 'last');
            }

            foreach ($result['data'] as $key => &$value) {
                if (!isset($value['nickname']) || empty($value['nickname'])) continue;
                $nickname = json_decode($value['nickname'], true);
                $introduce = json_decode($value['introduce'], true);
                $value['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                $value['introduce'] = isset($introduce[$this->lang]) ? $introduce[$this->lang] : '';
                $value['chain_quotation'] = $chain_quotation[$value['chain']] ?: null;
                $value['chain_rmb'] = isset($value['chain_quotation']['rmb']) ? $value['chain_quotation']['rmb'] : 0;
                $value['chain_usd'] = isset($value['chain_quotation']['usd']) ? $value['chain_quotation']['usd'] : 0;
                $value['chain_country_rate'] = (float)sprintf("%0.4f", $last[0] * $value['chain_rmb']);
                $value['country_rate'] = (false == $currency) ? $value['rmb'] : (float)sprintf("%0.4f", $last[0] * $value['rmb']);
            }
            return $result;
        }
    }

    public function actionSearch()
    {
        $count = 0;
        $params = Yii::$app->request->post();
        $chain = isset($params['chain']) ? $params['chain'] : '';
        $keyword = isset($params['keyword']) ? $params['keyword'] : '';
        $platform_id = isset($params['platform_id']) ? $params['platform_id'] : '';

        $page = empty($params['page']) ? 1 : $params['page'];
        $limit = empty($params['limit']) ? 10 : $params['limit'];

        if (false == $platform_id) {
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $query = Coin::find()
            ->select('id, sid, icon, name, optional_name, nickname, platform, chain, address as contract_address, treaty')
            ->where("JSON_CONTAINS(platform_id,'$platform_id')")
            ->orderBy('id');

        if (false != $chain) {
            $query->andWhere(['chain' => $chain]);
        }

        if (false != $keyword) {
            $query->andWhere(['or', ['like', 'address', $keyword], ['like', 'name', $keyword]]);
        }

        $data = $query->offset(($page - 1) * $limit)->limit($limit)->asArray()->all();
        $count = $query->count();
        if (false != $data) {
            $chains = array_unique(array_column($data, 'chain'));
            $chain_quotation = [];
            foreach ($chains as $key => $value) {
                $chain_quotation[$value] = ExchangeBusiness::getquatation($value);
            }
            foreach ($data as $key => $value) {
                $data[$key]['chain_quotation'] = $chain_quotation[$value['chain']] ?: null;
            }
            foreach ($data as $key => &$value) {
                $nickname = json_decode($value['nickname'], true);
                $value['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                $value['chain_rmb'] = isset($value['chain_quotation']['rmb']) ? $value['chain_quotation']['rmb'] : 0;
                $value['chain_usd'] = isset($value['chain_quotation']['usd']) ? $value['chain_quotation']['usd'] : 0;
            }
        }

        $code = 0;
        doEnd :

        return ['code' => $code, 'data' => $data, 'count' => $count];
    }

    /**
     * 按照名称搜索币种
     *
     * @return array
     */
    public function actionSearchCoinByName()
    {
        $request = Yii::$app->request;
        $name = $request->post('name', '');
        $page = $request->post('page', 1);
        $limit = $request->post('limit', 10);
        $platform_ids = $request->post('platform_id', null);

        $condition = [['in', 'chain', ['ETH', 'DCR', 'BTC', 'BTY']]];
        if (!empty($name)) {
            $condition[] = ['or', ['address' => $name], ['or', ['like', 'name', $name], ['like', 'nickname', $name]]];
        }
        if ($platform_ids) {
            $condition[] = ("JSON_CONTAINS(platform_id,'$platform_ids')");

        }
        $result = ExchangeBusiness::SearchByName($page, $limit, $condition);
        if (empty($result)) {
            return ['code' => 0, 'count' => 0, 'data' => []];
        }
        $total = $result['total'];
        $result = $result['data'];
        if ($result) {
            $chains = array_unique(array_column($result, 'chain'));
            $chain_quotation = [];
            foreach ($chains as $key => $value) {
                $chain_quotation[$value] = ExchangeBusiness::getquatation($value);
            }
            foreach ($result as $key => $value) {
                $result[$key]['chain_quotation'] = $chain_quotation[$value['chain']] ?: null;
            }
            foreach ($result as $key => &$value) {
                $nickname = json_decode($value['nickname'], true);
                $value['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                $value['chain_rmb'] = isset($value['chain_quotation']['rmb']) ? $value['chain_quotation']['rmb'] : 0;
                $value['chain_usd'] = isset($value['chain_quotation']['usd']) ? $value['chain_quotation']['usd'] : 0;
            }
            return ['code' => 0, 'count' => $total, 'data' => $result];
        }
    }

    /**
     * 返回交易状态
     *
     * @return mixed
     */
    public function actionTransaction()
    {
        $request = Yii::$app->request;
        $name = $request->post('name', '');
        $txhash = $request->post('txhash', '');
        if ($name && $txhash) {
            return BrowerBusiness::getTransStatus($name, $txhash);
        }
        return false;
    }

    /**
     * @return array
     * 获取平台币图标、区块链地址
     */
    public function actionGetBrowerByPlatform()
    {
        $request = Yii::$app->request;
        $platform = $request->post('platform', '');
        if ($platform) {
            $brower_url = Yii::$app->redis->hget('platform_brower_info', strtolower($platform));
            return ['code' => 0, 'data' => $brower_url];
        } else {
            return ['code' => 1, 'data' => [], 'msg' => '平台参数不能为空'];
        }
    }

    /**
     *获取代扣信息
     */
    public function actionGetWithHold()
    {
        $request = Yii::$app->request;
        $platform = $request->get('platform', '');
        $coin_name = $request->get('coinname', '');
        $platform_with_hold = CoinPlatformWithHold::getRecord($platform);

        $coin_info = Coin::find()->select('treaty')->where(['name' => strtoupper($coin_name), 'platform' => $platform])->asArray()->one();
        $des = Yii::$app->des;
        $platform_with_hold['private_key'] = isset($platform_with_hold['private_key']) ? $des->encrypt($platform_with_hold['private_key']) : '';
        if (1 == $coin_info['treaty']) {
            $platform_with_hold['exer'] = 'user.p.' . $platform . '.token';
            if ('BTY' !== strtoupper($platform)) {
                $platform_with_hold['tokensymbol'] = $platform . '.' . $coin_name;
            } else {
                $platform_with_hold['tokensymbol'] = $coin_name;
            }
        } else {
            $platform_with_hold['exer'] = 'user.p.' . $platform . '.coins';
            $platform_with_hold['tokensymbol'] = $platform . '.coins';
        }
        $platform_with_hold['fee'] = isset($platform_with_hold['fee']) ? (float)sprintf("%0.4f", (double)$platform_with_hold['fee']) : 0;
        $coins_model = Coin::find()->select('name')->where(['platform' => $platform, 'treaty' => 2])->asArray()->one();
        $platform_with_hold['coins_name'] = empty($coins_model) ? '' : $coins_model['name'];
        $service = new Chain33Business();
        $result = $service->getProperFee();
        if (0 === $result['code']) {
            $platform_with_hold['bty_fee'] = (float)sprintf("%0.4f", $result['result']['properFee'] / 1e8) > 0.01 ? 0.01 : (float)sprintf("%0.4f", $result['result']['properFee'] / 1e8);
        } else {
            $platform_with_hold['bty_fee'] = (float)sprintf("%0.4f", Yii::$app->params['bty_fee']);
        }
        return ['code' => 0, 'data' => $platform_with_hold];
    }
}
