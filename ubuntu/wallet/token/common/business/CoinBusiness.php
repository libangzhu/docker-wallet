<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 上午11:15
 */

namespace common\business;

use common\models\psources\Coin;
use common\service\coin\CoinFactory;

/**
 * 币种逻辑层
 *
 * Class CoinBusiness
 * @package common\business
 */
class CoinBusiness
{
    /**
     * 管理员后台获取币种列表
     * @param  int                            $page
     * @param  int                            $limit
     * @param  array                          $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getList($page = 1, $limit = 10, $condition = [])
    {
        $rows = Coin::getList($page, $limit, $condition);
   /*     if ($rows['count'] > 0) {
            $datas = $rows['data'];
            foreach ($datas as $key => $value) {
                //获取交易所信息
                try {
                    $coin = CoinFactory::createCoin($value['name'], $value['id'], $value['sid']);

                    $rows['data'][$key]['exchange'] = $coin->exchange_count();
                } catch (\Exception $exception) {
                    $rows['data'][$key]['exchange'] = 0;
                }
            }
        }*/
        return $rows;
    }

    /**
     * 管理员后台获取币种列表
     * @param  int                            $page
     * @param  int                            $limit
     * @param  array                          $condition
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getRecommendList($page = 1, $limit = 10, $condition = [])
    {
        $rows = Coin::getRecommendList($page, $limit, $condition);
   /*     if ($rows['count'] > 0) {
            $datas = $rows['data'];
            foreach ($datas as $key => $value) {
                //获取交易所信息
                try {
                    $coin = CoinFactory::createCoin($value['name'], $value['id'], $value['sid']);

                    $rows['data'][$key]['exchange'] = $coin->exchange_count();
                } catch (\Exception $exception) {
                    $rows['data'][$key]['exchange'] = 0;
                }
            }
        }*/
        return $rows;
    }

    /**
     * 返回交易所列表
     *
     * @param  $id
     * @return array
     */
    public static function getExchangeListById($id = 0)
    {
        if ($id) {
            $coin = Coin::findOne(['id' => $id]);
            if ($coin) {
                $model = CoinFactory::createCoin($coin->name, $coin->id, $coin->sid);
                $datas = $model->exchange();
                $model->__destruct();
                if ($datas['count'] > 0) {
                    return $datas['data'];
                }
            }
        }
        return [];
    }

    /**
     * api获取行情列表
     * @param  int     $page
     * @param  int     $limit
     * @param  array   $condition
     * @return array
     */
    public static function getApiList($page = 1, $limit = 10, $condition = [])
    {
        $rows = Coin::getList($page, $limit, $condition);
        if ($rows['count'] > 0) {
            $rows = $rows['data'];
            foreach ($rows as $key => $value) {
                if (empty($value['publish_count'])) {
                    $rows[$key]['publish_count'] = 0;
                }
                if (empty($value['circulate_count'])) {
                    $rows[$key]['circulate_count'] = 0;
                }
                if (empty($value['release'])) {
                    $rows[$key]['release'] = '';
                }
                if (empty($value['sid'])) {
                    $rows[$key]['sid'] = '';
                }
                if (empty($value['introduce'])) {
                    $rows[$key]['introduce'] = '';
                }
                $coin                    = CoinFactory::createCoin($value['name'], $value['id'], $value['sid']);
                $rows[$key]['quotation'] = $coin->quotation();
                $coin->__destruct();
            }
        }
        return $rows;
    }

    /**
     * 根据name返回币种信息
     * @param  array   $condition 需要的币种sid列表
     * @return array
     */
    public static function getApiListForIndex($condition = [])
    {
        $rows = Coin::getSelectList(1, 999, ['id', 'sid', 'icon', 'name', 'nickname', 'platform', 'chain'], $condition);
        if ($rows['count'] > 0) {
            $rows = $rows['data'];
            foreach ($rows as $key => $row) {
                $coinServer        = CoinFactory::createCoin($row['name'], $row['id'], $row['sid']);
                $rows[$key]['sid'] = ucfirst($rows[$key]['sid']);
                $rows[$key]['rmb'] = $coinServer->getPrice();
                $rows[$key]['usd'] = $coinServer->getDollar();
                $coinServer->__destruct();
            }
            return $rows;
        }
        return [];
    }

    /**
     * 根据名称搜索
     * @param  int                                                 $page
     * @param  int                                                 $limit
     * @param  array                                               $condition
     * @return array|\yii\db\ActiveRecord|\yii\db\ActiveRecord[]
     */
    public static function SearchByName($page = 1, $limit = 10, $condition = [])
    {
        $rows = Coin::getSelectList($page, $limit, ['id', 'sid', 'icon', 'name', 'nickname', 'platform', 'chain'], $condition);
        if ($rows['count'] > 0) {
            $rows = $rows['data'];
            foreach ($rows as $key => $row) {
                $coinServer        = CoinFactory::createCoin($row['name'], $row['id'], $row['sid']);
                $rows[$key]['sid'] = ucfirst($rows[$key]['sid']);
                $rows[$key]['rmb'] = $coinServer->getPrice();
                $rows[$key]['usd'] = $coinServer->getDollar();
                $coinServer->__destruct();
            }
            return $rows;
        }
        return [];
    }

    /**
     * 获取币种的所有信息
     * @param  integer $id
     * @return array
     */
    public static function getCoinAllById($id)
    {
        $row = Coin::find()->where(['id' => $id])->asArray()->One();
        if ($row) {
            if (empty($row['publish_count'])) {
                $row['publish_count'] = 0;
            }
            if (empty($row['circulate_count'])) {
                $row['circulate_count'] = 0;
            }
            if (empty($row['release'])) {
                $row['release'] = '';
            }
            if (empty($row['sid'])) {
                $row['sid'] = '';
            }else{
                $row['sid'] = ucwords($row['sid']);
            }
            if (empty($row['introduce'])) {
                $row['introduce'] = '';
            }
            $row['price']          = sprintf("%s", (double) $row['price']);
            $quotation = ExchangeBusiness::getquatation($row['name']);
            if($quotation){
                $tikerdata['price'] = $quotation['rmb'];
                $tikerdata['dollar'] = $quotation['usd'];
            }else{
                $tikerdata['price'] = 0;
                $tikerdata['dollar'] = 0;
            }
            $row['quotation'] = $tikerdata;
            $row['quotation']['publish_count'] = $row['publish_count'];
            $row['quotation']['circulate_count'] = $row['circulate_count'];
        }
        return $row ? [$row] : [];
    }
}
