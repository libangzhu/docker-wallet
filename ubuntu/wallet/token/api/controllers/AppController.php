<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-22
 * Time: 下午2:48
 */

namespace api\controllers;

use api\base\BaseController;
use common\models\psources\CoinAppVersion;
use common\models\pwallet\AppVersion;

class AppController extends BaseController
{
    public function actionUpdate()
    {
        $type = \Yii::$app->request->post('type', 1);
        $data = AppVersion::getLastStable($type);
        if ($data) {
            $data['log'] = json_decode($data['log']);
        }
        $data['id']=(integer)$data['id'];
        $data['status']=(integer)$data['status'];
        $data['version_code']=(integer)$data['version_code'];
        return $data;
    }

    public function actionNewUpdate()
    {
        $type = \Yii::$app->request->post('type', 1);
        $data = AppVersion::getLastStable($type);
        if ($data) {
            $logItems = json_decode($data['log']);
            $logstr = '';
            foreach($logItems as $item){
                if($logstr){
                    $logstr .="\n".$item;
                }else{
                    $logstr = $item;
                }

            }
            $data['log'] = $logstr;
        }
        $data['id']=(integer)$data['id'];
        $data['status']=(integer)$data['status'];
        $data['version_code']=(integer)$data['version_code'];
        return $data;
    }

    /**
     * @return array
     * 获取最新版本
     */
    public function actionUpdateVersion()
    {
        $type = \Yii::$app->request->post('type', 1);
        $platform_id = \Yii::$app->request->post('platform_id', 1);
        $data = CoinAppVersion::getLastStable($type,$platform_id);
        if ($data) {
            $logstr = '';
            if(!empty($data['log'])){
                $logItems = json_decode($data['log']);
                $logstr = '';
                foreach($logItems as $item){
                    if($logstr){
                        $logstr .="\n".$item;
                    }else{
                        $logstr = $item;
                    }
                }
            }
            $data['log'] = $logstr;
            $data['id']=(integer)$data['id'];
            $data['status']=(integer)$data['status'];
            $data['version_code']=(integer)$data['version_code'];
        }
        return $data;
    }
}