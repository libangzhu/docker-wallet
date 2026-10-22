<?php

namespace api\controllers;

use common\business\ExchangeBusiness;
use Yii;
use api\base\BaseController;
use common\models\psources\CoinSupportedChain;

class SupportedChainController extends BaseController
{
    public function actionIndex()
    {
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $data = null;

        if (false == $platform_id) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }

        $supported_chain_model = CoinSupportedChain::find()->where(['platform_id' => $platform_id])->all();
        if (false == $supported_chain_model) {
            $msg = 'success';
            $code = 0;
            goto doEnd;
        }
        $data = [];
        foreach ($supported_chain_model as $key => &$val) {
            $data[$key]['id'] = isset($val->coin->id) ? $val->coin->id : '';
            $data[$key]['name'] = isset($val->coin->name) ? $val->coin->name : '';
            $data[$key]['icon'] = isset($val->coin->icon) ? $val->coin->icon : '';
            $data[$key]['name'] = isset($val->coin->name) ? $val->coin->name : '';
            $data[$key]['nickname'] = isset($val->coin->nickname[$this->lang]) ? $val->coin->nickname[$this->lang] : '';
            $data[$key]['chain'] = isset($val->coin->chain) ? $val->coin->chain : '';
            $data[$key]['platform'] = isset($val->coin->platform) ? $val->coin->platform : '';
            $data[$key]['treaty'] = isset($val->coin->treaty) ? $val->coin->treaty : '';

            $data[$key]['rmb'] = 0;
            $data[$key]['usd'] = 0;

            $ticker = ExchangeBusiness::getquatation($val->coin->name);
            if (false != $ticker) {
                $data[$key]['rmb'] = $ticker['rmb'];
                $data[$key]['usd'] = $ticker['usd'];
            }

        }
        $msg = 'success';
        $code = 0;
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}