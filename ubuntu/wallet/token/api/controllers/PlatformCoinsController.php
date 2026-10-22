<?php

namespace api\controllers;

use common\components\Tools;
use Yii;
use api\base\BaseController;
use common\models\psources\CoinAddress;
use common\models\psources\CoinPlatformCoins;
use common\models\psources\CoinAirDropTransfer;

class PlatformCoinsController extends BaseController
{
    public function actionAirDrop()
    {
        $platform_id = Yii::$app->request->post('platform_id', '');
        $coins_address = Yii::$app->request->post('platform_coins', '');
        $type = Yii::$app->request->post('type', '');

        $header = Yii::$app->request->headers;
        $deviceCode = $header['FZM-REQUEST-UUID'] ?? null;

        if (empty($platform_id) || empty($coins_address) || empty($type)) {
            return ['code' => -1, 'data' => null, 'msg' => '缺少必要的参数'];
        }

        if (empty($deviceCode)) {
            return ['code' => 0, 'data' => null, 'msg' => '数据导入成功'];
        }

        $is_exist = true;

        if (41 !== $platform_id) {
            return ['code' => -1, 'data' => null, 'msg' => '缺少必要的参数'];
        }

        $model = CoinPlatformCoins::find()->where(['platform_id' => $platform_id, 'deviceCode' => $deviceCode])->one();
        if (!$model) {
            $is_exist = false;
        }

        $item_array = [];
        $to_address = '';
        foreach ($coins_address as $item) {
            $platform_coins_arr = explode(',', $item);
            $coin_name = $platform_coins_arr[0];
            $coins_address = $platform_coins_arr[1];
            $to_address = $coins_address;
            if (empty($coin_name) || empty($coins_address)) continue;
            $isExistModel = CoinPlatformCoins::find()->where(['coins_name' => $coin_name, 'coins_address' => $coins_address, 'platform_id' => $platform_id])->one();
            if (!empty($isExistModel) && empty($isExistModel->deviceCode)) {
                $isExistModel->deviceCode = $deviceCode;
                $isExistModel->save();
                continue;
            }

            if (!empty($isExistModel) && $isExistModel->deviceCode == $deviceCode) {
                continue;
            };
            $item_array[] = [
                $coin_name,
                $coins_address,
                $type,
                $deviceCode,
                $platform_id,
            ];
        }
        if (empty($item_array)) {
            return ['code' => 0, 'data' => null, 'msg' => '数据导入成功'];
        }

        $result = CoinPlatformCoins::batchImport($item_array);
        if (!$result) {
            return ['code' => -1, 'data' => null, 'msg' => '数据导入失败'];
        }

        if (false == $is_exist) {
            $is_air_drop = CoinAirDropTransfer::find()->where(['to_address' => $to_address, 'origin' => CoinAirDropTransfer::ORIGIN_API, 'type' => CoinAirDropTransfer::TYPE_COINS])->one();
            if (false == $is_air_drop) {
                $model = new CoinAirDropTransfer();
                $model->txhash = '0';
                $model->msg = '';
                $model->to_address = $to_address;
                $model->coin_name = 'EPC';
                $model->amount = 0.05;
                $model->origin = CoinAirDropTransfer::ORIGIN_API;
                $model->type = CoinAirDropTransfer::TYPE_COINS;
                $model->save();
            }
        }

        return ['code' => 0, 'data' => null, 'msg' => '数据导入成功'];
    }


    /**
     * 新增空投钱包
     *
     * @param  string $platform_id
     * @param  string $platform_coins
     * @return array
     */

    public function actionIndex()
    {


        $platform_id = Yii::$app->request->post('platform_id', '');
        $coins_address = Yii::$app->request->post('platform_coins', '');
        $type = Yii::$app->request->post('type', 0);
        $contact = Yii::$app->request->post('contact', '');
        if ($contact) {
            $des = Yii::$app->des;
            $contact = $des->decrypt($contact);
        }
        $header = Yii::$app->request->headers;
        $deviceCode = $header['FZM-REQUEST-UUID'] ?? "";
        $version = $header['version'] ?? "";
        $device = $header['device'] ?? "";

        if (empty($platform_id) || empty($coins_address)) {
            return ['code' => -1, 'data' => null, 'msg' => '缺少必要的参数'];
        }

        if (41 == $platform_id) {
            return ['code' => -1, 'data' => null, 'msg' => '缺少必要的参数'];
        }

        if (empty($deviceCode)) {
            return ['code' => 0, 'data' => null, 'msg' => '数据导入成功'];
        }

        foreach ($coins_address as $item) {
            $platform_coins_arr = explode(',', $item);
            $coin_name = $platform_coins_arr[0];
            $coins_address = $platform_coins_arr[1];
            if (empty($coin_name) || empty($coins_address)) continue;
            $isExistModel = CoinPlatformCoins::find()->where(['coins_name' => $coin_name, 'coins_address' => $coins_address, 'contact' => $contact])->one();
            if ($isExistModel) {
                $isExistModel->version = $version;
                $isExistModel->update_time = date('Y-m-d H:i:s');
                $isExistModel->save();
            } else {
                $model = new CoinPlatformCoins();
                $model->setScenario(CoinPlatformCoins::SCENARIOS_CREATE);
                $model->coins_name = $coin_name;
                $model->coins_address = $coins_address;
                $model->type = $type;
                $model->deviceCode = $deviceCode;
                $model->platform_id = $platform_id;
                $model->device = $device;
                $model->version = $version;
                $model->contact = $contact;
                $model->save();
            }
        }

        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

    public function actionRelation()
    {
        $data = Yii::$app->request->post();
        if (empty($data)) {
            $this->code = -1;
            $this->msg = '参数错误';
            goto doEnd;
        }

        $item_array = [];
        foreach ($data as $val) {
            $address = $val['address'] ?? null;
            $mobile = $val['mobile'] ?? null;
            $mail = $val['mail'] ?? null;
            if (empty($address) || empty($mobile) || empty($mail)) continue;

            $count = CoinAddress::find()->where(['address' => $address, 'mobile' => $mobile, 'mail' => $mail])->count();
            if ($count > 0) continue;
            $item_array[] = [
                $address,
                $mobile,
                $mail
            ];
        }

        if (empty($item_array)) {
            goto doEnd;
        }

        $result = CoinAddress::batchImport(Tools::super_unique($item_array));
        if (!$result) {
            $this->code = -1;
            $this->msg = '数据导入失败';
            goto doEnd;
        }

        doEnd :
        return ['code' => $this->code, 'data' => $this->data, 'msg' => $this->msg];
    }

}
