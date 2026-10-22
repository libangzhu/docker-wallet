<?php
/**
 * Created By Sublime text 3
 * User: rlgyzhcn
 * Date: 18-08-14
 * Time: 上午10:09
 */

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\pwallet\GameUser;
use common\models\pwallet\GameUserAddress;

class GameController extends BaseController
{
    /**
     * 保存地址与昵称
     */
    public function actionSaveAddressAndNickname()
    {
        $post = Yii::$app->request->post();
        $address = $post['address'] ?? '';
        $nickname = $post['nickname'] ?? '';
        if (empty($address) || empty($nickname)) {
            return ['code' => -1, 'msg' => '地址和昵称不能为空', 'data' => null];
        }
        //判断重复
        $count = GameUser::find()->where(['nickname' => $nickname])->count();
        if ($count > 0) {
            return ['code' => -1, 'msg' => '昵称重复'];
        }
        // todo 保存
        $models = new GameUser();
        $models->nickname = $nickname;
        $models->address = $address;
        if ($models->save()) {
            return ['code' => 0, 'msg' => 'Succeed', 'data' => $models->id];
        }
        return ['code' => -1, 'msg' => 'Failed'];
    }

    /**
     * 根据地址获取昵称
     */
    public function actionGetNicknameByAddress()
    {
        $address = Yii::$app->request->post('address', '');
        if (empty($address)) {
            return ['code' => -1, 'msg' => '地址不能为空', 'data' => null];
        }
        //todo 获取地址
        $address = GameUser::find()->select(['nickname'])->where(['address' => $address])->one();
        if (!empty($address)) {
            return ['code' => 0, 'msg' => 'Succeed', 'data' => $address];
        }
        return ['code' => -1, 'msg' => 'Failed'];
    }

    public function actionSaveGameInfo()
    {
        $post = Yii::$app->request->post();
        $address = $post['address'] ?? '';
        $nickname = $post['nickname'] ?? '';
        if (empty($address) || empty($nickname)) {
            return ['code' => -1, 'msg' => '地址和昵称不能为空', 'data' => null];
        }
        //判断重复
        $count = GameUserAddress::find()->where(['address' => $address])->count();
        if ($count > 0) {
            return ['code' => -1, 'msg' => '地址已存在', 'data' => null];
        }
        // todo 保存
        $models = new GameUserAddress();
        $models->nickname = $nickname;
        $models->address = $address;
        if ($models->save()) {
            return ['code' => 0, 'msg' => 'Succeed', 'data' => $models->id];
        }
        return ['code' => -1, 'msg' => 'Failed', 'data' => null];
    }

    public function actionGetNicknameByAddressArr()
    {
        $address = Yii::$app->request->post('address', '');
        if (empty($address)) {
            return ['code' => -1, 'msg' => '地址不能为空', 'data' => null];
        }
        //todo 获取地址
        $address = GameUserAddress::find()
            ->select(['nickname', 'address'])
            ->where(['in', 'address', $address])
            ->asArray()->all();
        if (!empty($address)) {
            return ['code' => 0, 'msg' => 'Succeed', 'data' => $address];
        }
        return ['code' => -1, 'msg' => '数据为空', 'data' => null];
    }
}