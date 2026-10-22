<?php

namespace api\controllers;

use common\business\ExchangeBusiness;
use common\models\psources\Coin;
use common\models\psources\CoinSupportedChain;
use Yii;
use api\base\BaseController;
use common\models\psources\WalletRecommendCoin;
use common\models\psources\WalletRecommendCategory;

class RecommendCoinController extends BaseController
{
    public function actionTop()
    {
        $platform_id = $this->platform_id;
        if (false == $platform_id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }
        $type = Yii::$app->request->get('type', 1);

        $recommend_category = WalletRecommendCategory::find()->select('id, name')
            ->where(['wallet_type' => (int)$type, 'platform_id' => (int)$platform_id])
            ->orderBy('sort')->limit(1)->asArray()->all();

        if (empty($recommend_category)) {
            goto doEnd;
        }
        foreach ($recommend_category as &$category) {
            $name = json_decode($category['name'], true);
            $category['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';

            $recommend_coin = WalletRecommendCoin::find()->where(['category_id' => $category['id']])->orderBy('sort')->all();
            $items = [];
            if (!empty($recommend_coin)) {
                foreach ($recommend_coin as $coin) {
                    $temp['id'] = $coin->coin['id'];
                    $temp['icon'] = $coin->coin['icon'];
                    $temp['name'] = $coin->coin['name'];
                    $temp['platform'] = $coin->coin['platform'];
                    $temp['chain'] = $coin->coin['chain'];
                    $temp['treaty'] = $coin->coin['treaty'];
                    $nickname = json_decode($coin->coin['nickname'], true);
                    $temp['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                    $introduce = json_decode($coin->coin['introduce'], true);
                    $temp['introduce'] = isset($introduce[$this->lang]) ? $introduce[$this->lang] : '';
                    array_push($items, $temp);
                }
            }
            $category['items'] = $items;
        }

        $this->data = $recommend_category[0]['items'];

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    public function actionIndex()
    {
        $platform_id = $this->platform_id;
        if (false == $platform_id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }
        $type = Yii::$app->request->get('type', 1);

        $recommend_category = WalletRecommendCategory::find()->select('id, name')
            ->where(['wallet_type' => (int)$type, 'platform_id' => (int)$platform_id])
            ->andWhere(['>', 'sort', 1])
            ->orderBy('sort')->asArray()->all();

        if (!empty($recommend_category)) {
            foreach ($recommend_category as &$category) {
                $name = json_decode($category['name'], true);
                $category['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';

                $recommend_coin = WalletRecommendCoin::find()->where(['category_id' => $category['id']])->all();
                unset($category['id']);
                $items = [];
                foreach ($recommend_coin as $coin) {
                    if (null == $coin->coin) continue;
                    $temp['id'] = $coin->coin['id'];
                    $temp['icon'] = $coin->coin['icon'];
                    $temp['name'] = $coin->coin['name'];
                    $temp['platform'] = $coin->coin['platform'];
                    $temp['chain'] = $coin->coin['chain'];
                    $temp['treaty'] = $coin->coin['treaty'];
                    $nickname = json_decode($coin->coin['nickname'], true);
                    $temp['nickname'] = isset($nickname[$this->lang]) ? $nickname[$this->lang] : '';
                    $introduce = json_decode($coin->coin['introduce'], true);
                    $temp['introduce'] = isset($introduce[$this->lang]) ? $introduce[$this->lang] : '';
                    array_push($items, $temp);
                }
                $category['items'] = $items;
            }
        }

        $this->data = $recommend_category;
        $supported_chain_model = CoinSupportedChain::find()->where(['platform_id' => $platform_id])->all();
        $data = [];
        if (false != $supported_chain_model) {
            foreach ($supported_chain_model as $key => &$val) {
                $data[$key]['id'] = isset($val->coin->id) ? $val->coin->id : '';
                $data[$key]['icon'] = isset($val->coin->icon) ? $val->coin->icon : '';
                $data[$key]['name'] = isset($val->coin->name) ? $val->coin->name : '';
                $data[$key]['platform'] = isset($val->coin->platform) ? $val->coin->platform : '';
                $data[$key]['chain'] = isset($val->coin->chain) ? $val->coin->chain : '';
                $data[$key]['treaty'] = isset($val->coin->treaty) ? $val->coin->treaty : '';
                $data[$key]['nickname'] = isset($val->coin->nickname[$this->lang]) ? $val->coin->nickname[$this->lang] : '';
                $data[$key]['introduce'] = isset($val->coin->introduce[$this->lang]) ? $val->coin->introduce[$this->lang] : '';
            }
            $supported_chain = [
                'name' => '主链',
                'items' => $data
            ];
            array_unshift($this->data, $supported_chain);
        }

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    public function actionTrusteeship()
    {
        $platform_id = $this->platform_id;
        if (false == $platform_id) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }
        $recommend = Yii::$app->request->get('recommend', 1);
        if ($recommend != 1) {
            $this->code = 1;
            $this->msg = '数据为空';
            goto doEnd;
        }
        $coin_trusteeship = WalletRecommendCoin::find()->select('cid')->where(['type' => WalletRecommendCoin::TYPE_TRUSTEESHIP, 'platform_id' => $platform_id])->asArray()->all();
        $select = ['id', 'sid', 'icon', 'name', 'introduce', 'optional_name', 'nickname', 'platform', 'chain', 'treaty'];

        $coin_ids = array_column($coin_trusteeship, 'cid');
        if (false == $coin_ids) {
            $this->code = 1;
            $this->msg = '数据为空';
            goto doEnd;
        }
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
            foreach ($coin_trusteeship as $key => &$value) {
                $temp_key = $coin_infos[$value['cid']]['name'];
                foreach ($select as $item) {
                    $value[$item] = $coin_infos[$value['cid']][$item];
                    if ($platform_id != 2) {
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
                $value['type'] = WalletRecommendCoin::TYPE_TRUSTEESHIP;
                $value['platform_id'] = $platform_id;
                $value['sort'] = $value['recommend'] = 1;
                $value['coin'] = $value['name'];
            }
            unset($key, $value);
        }
        $this->data = $coin_trusteeship;
        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg, 'count' => empty($this->data) ? 0 : count($this->data)];
    }
}