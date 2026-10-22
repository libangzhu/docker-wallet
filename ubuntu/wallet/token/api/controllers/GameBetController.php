<?php

namespace api\controllers;

use Yii;
use yii\data\Pagination;
use api\base\BaseController;
use common\models\psources\CoinGameBet;

class GameBetController extends BaseController
{
    public function actionBetStatus()
    {
        $platform = Yii::$app->request->get('platform', 'ts_wallet');
        $player = Yii::$app->request->get('player', '');
        $page = Yii::$app->request->get('page', 1);
        if(empty($player) || empty($platform)){
            $msg = '请求参数错误';
            $code = -1;
            $data = null;
            goto doEnd;
        }

        $query = CoinGameBet::find()
            ->select('round, player, amount, height, guess_num, valid, rand_num, player_win, platform')
            ->where('player= :player',[':player' => $player])
            ->andWhere(['platform' => $platform])
            //->andWhere(['valid' => CoinGameBet::VAILD_TRUE])
            ->orderBy('update_time desc');

        $count = $query->count();
        if( 0 == $count) {
            $msg = '数据不存在';
            $code = -1;
            $data = null;
            goto doEnd;
        }
        $data = $query->offset(($page - 1) * 20)->limit(20)->asArray()->all();
        $countQuery = clone $query;
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => '20']);
        $data = [
            'list' => $data,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize'  => 20,
                'currentPage' => $page,
            ]
        ];
        $code = 1;
        $msg = 'success';
        doEnd :

        return ['code' => $code, 'data' => $data,  'msg' => $msg];
    }
}