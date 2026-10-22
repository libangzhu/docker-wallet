<?php

namespace api\controllers;

use common\models\psources\Coin;
use Yii;
use api\base\BaseController;
use common\models\psources\CoinPlatform;

class IssueChainController extends BaseController
{
    /**
     * 可发行链列表
     * @return array
     */
    public function actionIndex()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $chain_model = CoinPlatform::find()->where(['id' => $platform_id])->all();

        if (false == $chain_model) {
            $msg = '不存在的链';
            $code = -1;
            goto doEnd;
        }

        foreach ($chain_model as &$val) {
            $val->chain_name = isset($val->chain->platform) ? $val->chain->platform : '';
            $val->issue_charge = (float)sprintf("%0.3f", $val->issue_charge);
            $coin_model = Coin::find()->select('name, treaty')->where(['name' => strtoupper($val->charge_unit), 'platform' => $val->chain_name, 'treaty' => 2])->one();
            $val->tokens = empty($coin_model) ? false : true;
            if (empty($coin_model)) {
                $val->exer = isset($val->chain->exer) ? $val->chain->exer : '';
            } else {
                if (1 == $coin_model->treaty) {
                    $val->exer = 'user.p.' . $val->chain->platform . '.token';
                } else {
                    $val->exer = 'user.p.' . $val->chain->platform . '.coins';
                }
            }
            unset($val->download_url);
            unset($val->introduce);
            unset($val->create_time);
            unset($val->update_time);
        }

        $msg = 'ok';
        $code = 0;
        $data = is_array($chain_model) ? $chain_model : [$chain_model];

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 人工审核状态
     * @return array
     */
    public function actionManualReviewStatus()
    {
        $status = 0;
        $manual_review = Yii::$app->redis->get('issue_chain_manual_review');
        if (false == $manual_review || 'open' == $manual_review) {
            $status = 1;
        }

        return ['code' => 0, 'data' => $status];
    }
}