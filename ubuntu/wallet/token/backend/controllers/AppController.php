<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-22
 * Time: 下午1:32
 */

namespace backend\controllers;

use common\models\psources\CoinPlatform;
use common\models\psources\CoinAppVersion;
use Yii;
use common\models\pwallet\AppVersion;
use yii\web\UploadedFile;


class AppController extends BaseController
{
    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 应用列表
     */
    public function actionList()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';

            $request = Yii::$app->request;
            $page    = $request->get('page', 1);
            $limit   = $request->get('limit', 10);
            $type    = $request->get('type', [1, 2, 3]);
            $platform_id = $request->get('platform_id', 1);
            $where   = [];
            $where[] = ['in', 'type', $type];
            $where[] = ['platform_id' => $platform_id];
            $data    = CoinAppVersion::getList($page, $limit, $where);
            return $data;
        }
        $platforms = CoinPlatform::find()->asArray()->all();
        return $this->render('list',['platforms' => $platforms]);
    }

    /**
     * 添加一个app版本
     */
    public function actionAdd()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            if ($id = Yii::$app->request->post('id')) {
                $model           = CoinAppVersion::findOne($id);
                $model->scenario = CoinAppVersion::APP_UPDATE;
            } else {
                $model           = new CoinAppVersion();
                $model->scenario = CoinAppVersion::APP_ADD;
            }
            $post = array_filter(Yii::$app->request->post(), function ($item) {
                return $item;
            });
            if ($model->load($post) && $model->validate()) {
                if (!isset($post['id'])) {
                    $model->created_at = date('Y-m-d H:i:s');
                }
                $model->updated_at = date('Y-m-d H:i:s');
                //更新日志使用json array
                if (isset($post['log'])) {
                    $log_str    = $post['log'];
                    $log_arr    = explode("\n", $log_str);
                    $model->log = $log_arr;
                }
                $model->save(false);
                return ['code' => 0, 'msg' => 'succeed'];
            }
            $errors = $model->errors;
            foreach ($errors as $error) {
                return ['code' => 1, 'msg' => $error[0]];
            }
        }
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * 删除app
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';

        $id = Yii::$app->request->get('id');
        if ($id) {
            $model = CoinAppVersion::findOne($id);
            if ($model) {
                $model->delete();
                return ['code' => 0, 'msg' => 'succeed'];
            }
        }
        return ['code' => 1, 'msg' => 'failed'];
    }

    /**
     * @return array
     * 上传
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';

        try {
            $apk      = UploadedFile::getInstanceByName('apk');
            $filepath = Yii::$app->uploader->upload($apk);
            return ['code' => 0, 'msg' => 'succeed', 'path' => $filepath];
        } catch (\Exception $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
    }
}