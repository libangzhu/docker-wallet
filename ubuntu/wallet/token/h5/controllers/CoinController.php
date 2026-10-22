<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-28
 * Time: 下午2:52
 */

namespace h5\controllers;

use common\business\ExchangeBusiness;
use h5\base\ResponseBuild;
use Yii;
use common\models\psources\Coin;
use h5\base\BaseController;

/**
 * Class CoinController
 * 币种信息控制器
 *
 * @package h5\controllers
 */
class CoinController extends BaseController
{
    /**
     * @return ResponseBuild
     * 根据币种名称获取图标
     */
    public function actionGetIconByNames()
    {
        $response_ = new ResponseBuild();
        $names = Yii::$app->request->POST('names');
        if (empty($names)) {
            $response_->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE);
            return $response_;
        }
        if (!is_array($names)) {
            $names = [$names];
        }
        $infos = Coin::getInfoByNames($names);
        $data = [];
        if ($infos) {
            $data = array_column($infos, 'icon', 'name');
        }
        $response_->build(ResponseBuild::STATUS_SUCCEED, '', $data);
        return $response_;
    }

    /**
     * @return ResponseBuild
     * 根据币种名称获取币种详情
     */
    public function actionCoinInfoByNames()
    {
        $response_ = new ResponseBuild();
        $names = Yii::$app->request->POST('names');
        if (empty($names)) {
            $response_->build(ResponseBuild::STATUS_PARAMS_NOT_VALIDATE);
            return $response_;
        }
        if (!is_array($names)) {
            $names = [$names];
        }
        $condition = $names;
        $platform = Yii::$app->request->POST('platform', '');
        if (!empty($platform)) {
            $condition = [];
            foreach ($names as $val) {
                $condition[] = [$val, $platform];
            }
        }
        $fields = ['id', 'sid', 'icon', 'name', 'nickname', 'platform', 'chain', 'address as contract_address', 'introduce'];
        $result = ExchangeBusiness::getApiListForIndex(1, 999, $condition, $fields);
        if ($result) {
            $response_->build(ResponseBuild::STATUS_SUCCEED, '', $result['data']);
        } else {
            $response_->build(ResponseBuild::STATUS_SUCCEED, '', []);
        }
        return $response_;
    }
}