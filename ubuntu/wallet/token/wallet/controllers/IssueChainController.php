<?php

namespace wallet\controllers;

use common\models\psources\Coin;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinPlatformWithHold;
use common\models\psources\CoinSupportedCoin;
use common\service\chain33\Chain33Service;
use Yii;
use wallet\base\BaseController;

class IssueChainController extends BaseController
{
    /**
     * 可发行链列表
     * @return array
     */
    public function actionIndex()
    {
        $data = null;
        $group = Yii::$app->request->getGroup();
        if ('administrator' == $group) {
            $chain_model = CoinPlatform::find()->where(['<>', 'chain_id', 0])->all();
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
            $chain_model = CoinPlatform::find()->where(['id' => $platform_id])->andWhere(['<>', 'chain_id', 0])->all();
        }

        if (false == $chain_model) {
            $msg = '不存在的链';
            $code = -1;
            goto doEnd;
        }

        foreach ($chain_model as $key => $val) {
            if (!isset(Yii::$app->params['chain_nodes'][strtoupper($val->chain->platform)])) {
                unset($chain_model[$key]);
            }
            $val->chain_name = isset($val->chain->platform) ? $val->chain->platform : '';
            unset($val->download_url);
            unset($val->introduce);
            unset($val->create_time);
            unset($val->update_time);
        }
        $data = [];
        foreach ($chain_model as $val) {
            $data[] = $val;
        }

        $msg = 'ok';
        $code = 0;
        #$data = $chain_model;

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 发行链信息
     * @return array
     */
    public function actionChainInfo()
    {
        $data = null;
        $platform_id = Yii::$app->request->getPlatformId();
        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        if (1 == $platform_id) {
            $chain_model = CoinPlatform::find()->select('id, chain_id')->all();
        } else {
            $chain_model = CoinPlatform::find()->select('id, chain_id')->where(['id' => $platform_id])->all();
        }

        if (false == $chain_model) {
            $msg = '不存在的链';
            $code = -1;
            goto doEnd;
        }

        foreach ($chain_model as &$val) {
            $val->chain_name = isset($val->chain->platform) ? $val->chain->platform : '';
            $coin_supported_coin = CoinSupportedCoin::find()->select('id, coin_name')->where(['platform_id' => $val->id])->asArray()->all();
            $val->charge_unit = $coin_supported_coin;
        }

        $msg = 'ok';
        $code = 0;
        $data = is_array($chain_model) ? $chain_model : [$chain_model];

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 设置手续费
     * @param integer issue_charge
     * @param integer charge_unit_id
     * @param integer platform_id
     * @return array
     */
    public function actionSetCharge()
    {
        $data = null;
        $result = Yii::$app->request->post();
        $group = Yii::$app->request->getGroup();

        if ('administrator' == $group) {
            $platform_id = isset($result['platform_id']) ? (int)$result['platform_id'] : 0;
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
        }

        $issue_charge = isset($result['issue_charge']) ? $result['issue_charge'] : '';
        $charge_unit = isset($result['charge_unit']) ? strtoupper($result['charge_unit']) : '';

        if (false == $charge_unit || false == $platform_id) {
            $msg = '提交数据有误';
            $code = -1;
            goto doEnd;
        }

        if (floatval($issue_charge) == 0) {
            $msg = '数量错误';
            $code = -1;
            goto doEnd;
        }

        $chain_model = CoinPlatform::find()->where(['id' => $platform_id])->andWhere(['<>', 'chain_id', 0])->one();
        if (false == $chain_model) {
            $msg = '钱包尚未开通一键发币功能，不能设置手续费';
            $code = -1;
            goto doEnd;
        }

        $chain_model->issue_charge = $issue_charge;
        $chain_model->charge_unit = $charge_unit;
        if (false == $chain_model->save()) {
            $msg = '手续费设置失败';
            $code = -1;
            goto doEnd;
        }
        $msg = 'ok';
        $code = 0;

        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    /**
     * 人工审核开启/关闭
     * @param string manual_review
     * @return array
     */
    public function actionManageReview()
    {
        $data = null;
        $group = Yii::$app->request->getGroup();
        if ('administrator' != $group) {
            $msg = '当前账户无权限操作';
            $code = -1;
            goto doEnd;
        }

        $manual_review = \Yii::$app->request->post('manual_review', '');
        if (!in_array($manual_review, ['open', 'close'])) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }
        Yii::$app->redis->set('issue_chain_manual_review', $manual_review);
        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg];
    }

    /**
     * 查询所有预创建的token
     * @param string execer
     * @return array
     */
    public function actionGetTokens()
    {
        $data = null;
        $group = Yii::$app->request->getGroup();
        if ('administrator' == $group) {
            $chains = array_keys(Yii::$app->params['chain_nodes']);
            $platform = CoinPlatformWithHold::find()->select('id, platform, exer')->where(['in', 'platform', $chains])->all();
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
            $chain_model = CoinPlatform::find()->select('chain_id')->where(['id' => $platform_id])->andWhere(['<>', 'chain_id', 0])->one();
            if (empty($chain_model)) {
                $msg = '尚未开通此功能';
                $code = -1;
                goto doEnd;
            }
            $platform = CoinPlatformWithHold::find()->select('id, platform, exer')->where(['id' => $chain_model->chain_id])->one();
            if (empty($platform)) {
                $msg = '尚未开通此功能';
                $code = -1;
                goto doEnd;
            }
            if (!isset(Yii::$app->params['chain_nodes'][strtoupper($platform->platform)])) {
                $msg = '尚未开通此功能';
                $code = -1;
                goto doEnd;
            }
            $platform = array($platform);
        }

        foreach ($platform as $val) {
            $val->tokens = [];
            $symbol = [];
            if (!empty($val->exer)) {
                $node = Yii::$app->params['chain_nodes'][strtoupper($val->platform)];
                $service = new Chain33Service($node);
                $funcName = 'GetTokens';
                $result = $service->getTokens($val->exer, $funcName);
                if (isset($result['code']) && 0 == $result['code']) {
                    foreach ($result['result']['tokens'] as $temp) {
                        $symbol[] = $temp['symbol'];
                    }
                }
                $coin_model = Coin::find()->select('name')->where(['platform' => $val->platform, 'treaty' => 2])->one();
                if (!empty($coin_model)) {
                    array_unshift($symbol, $coin_model->name);
                }
                array_unshift($symbol, 'BTY');
                $val->tokens = $symbol;
                $val->platform_id = Yii::$app->params['chain_nodes'][strtoupper($val->platform)]['platform_id'];
            }
            unset($val->exer);
            $coin_platform = CoinPlatform::find()->select('issue_charge, charge_unit')->where(['id' => Yii::$app->params['chain_nodes'][strtoupper($val->platform)]['platform_id']])->one();
            $val->issue_charge = empty($coin_platform->issue_charge) ? '' : $coin_platform->issue_charge;
            $val->charge_unit = empty($coin_platform->charge_unit) ? '' : $coin_platform->charge_unit;
        }
        $data = $platform;

        $code = 0;
        $msg = 'success';

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}