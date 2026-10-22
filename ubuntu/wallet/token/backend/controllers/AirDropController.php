<?php

namespace backend\controllers;

use common\service\chain33\Chain33Service;
use Yii;
use yii\web\UploadedFile;
use common\models\psources\CoinAirDropTransfer;

class AirDropController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionList()
    {
        $get = Yii::$app->request->get();
        $page = $get['page'] ?? 1;
        $limit = $get['limit'] ?? 10;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $condition = [];
            $data = CoinAirDropTransfer::getList($page, $limit, $condition);
            return $data;
        }
        return $this->render('list');
    }

    /**
     * 导入cvs
     */
    public function actionLoadFile()
    {
        Yii::$app->response->format = 'json';

        $file = UploadedFile::getInstanceByName('file');
        try {
            $fd = fopen($file->tempName, 'r');
            $data = [];
            while (($row = fgetcsv($fd)) !== false) {
                $data[] = $row;
            }
            if (ctype_digit($data[0][0])) {
                array_shift($data);
            }
            foreach ($data as $key => &$value) {
                $value[0] = trim($value[0]);
                $value[1] = trim($value[1]);
            }

            if ($data) {
                $additional = [1, '0', ' ', 0];
                foreach ($data as $key => &$val) {
                    $val = array_merge($val, $additional);
                }
                if (CoinAirDropTransfer::loadArray($data)) {
                    return ['code' => 0, 'msg' => 'succeed'];
                }
            }
            return ['code' => 1, 'msg' => '数据为空'];
        } catch (\Exception $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
    }

    public function actionAdd()
    {
        $this->layout = false;
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $post = Yii::$app->request->post();
            $model = new CoinAirDropTransfer();
            $model->setScenario(CoinAirDropTransfer::SCENARIOS_CREATE);
            $post['txhash'] = '0';
            $post['msg'] = '';
            $post['balance'] = 0;
            $post['origin'] = CoinAirDropTransfer::ORIGIN_ADD;
            if ($model->load($post, '') && $model->save()) {
                return ['code' => 0, 'msg' => 'succeed'];
            }
            $error = $model->errors;
            if ($error) {
                return ['code' => -1, 'msg' => current($model->firstErrors)];
            }
        } else {
            return $this->render('add');
        }
    }
}