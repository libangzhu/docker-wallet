<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-13
 * Time: 下午3:22
 */

namespace backend\controllers;

use common\base\Exception;
use Yii;
use common\models\pwallet\InvestmentType;
use common\models\pwallet\Investment;
use yii\web\UploadedFile;

class InvestmentController extends BaseController
{
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $page    = $request->get('page', 1);
            $limit   = $request->get('limit', 10);
            $status  = $request->get('status', 0);
            $tid     = $request->get('tid', 0);
            $uid     = $request->get('uid', 0);
            $coin    = $request->get('coin', '');
            $where   = [];
            if ($uid) {
                $where[] = ['a.uid' => $uid];
            }
            if ($status) {
                $where[] = ['a.status' => $status];
            }
            if ($tid) {
                $where[] = ['a.tid' => $tid];
            }
            if ($coin) {
                $where[] = ['a.coin' => $coin];
            }
            Yii::$app->response->format = 'json';
            return Investment::getList($page, $limit, $where);
        }
        return $this->render('index');
    }

    public function actionAddOne()
    {
        $this->layout = false;
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';

            $post  = Yii::$app->request->post();
            $model = new Investment();
            if ($model->load($post) && $model->validate()) {
                $model->create_at = date('Y-m-d H:i:s');
                $model->update_at = date('Y-m-d H:i:s');
                try {
                    $model->save();
                } catch (\Exception $e) {
                    return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
                }
                return ['code' => 0, 'msg' => 'succeed'];
            }
            $error = $model->errors;
            if ($error) {
                foreach ($error as $item) {
                    $error = $item[0];
                    break;
                }
                return $error;
            }
            return ['code' => 1, 'msg' => '未知错误'];
        } else {
            return $this->render('form_add_one');
        }
    }

    public function actionType()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';

            return InvestmentType::getList(1, 999, []);
        }
        return $this->render('type');
    }

    public function actionTypeAdd()
    {
        $this->layout = false;
        if (Yii::$app->request->isGet) {
            return $this->render('type-add');
        } elseif (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';

            $name = Yii::$app->request->post('name', '');
            if ($name) {
                if (!InvestmentType::isExists($name)) {
                    $model            = new InvestmentType();
                    $model->name      = $name;
                    $model->create_at = date('Y-m-d H:i:s');
                    $model->save();
                } else {
                    return ['code' => 2, 'msg' => '类型已经存在'];
                }
            } else {
                return ['code' => 1, 'msg' => '名称不能为空'];
            }
            return ['code' => 0, 'msg' => 'succeed'];
        }
    }

    public function actionTypeDelete($id)
    {
        Yii::$app->response->format = 'json';

        $model = InvestmentType::findOne(['id' => $id]);
        if ($model) {
            try {
                $model->delete();
            } catch (\Throwable $e) {
                return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
            } catch (\Exception $e) {
                return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
            }
        } else {
            return ['code' => 1, 'msg' => '类型不存在'];
        }
        return ['code' => 0, 'msg' => 'succeed'];
    }

    public function actionTypeUpdate()
    {
        if (Yii::$app->request->isGet) {
            $id    = Yii::$app->request->get('id', 0);
            $model = InvestmentType::findOne(['id' => $id]);
            if ($model) {
                $this->layout = false;
                return $this->render('type-update', ['model' => $model]);
            } else {
                Yii::$app->response->format = 'json';
                return ['code' => 1, 'msg' => '类型不存在'];
            }
        } elseif (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';

            $id    = Yii::$app->request->post('id', 0);
            $name  = Yii::$app->request->post('name', 0);
            $model = InvestmentType::findOne(['id' => $id]);
            if ($model) {
                $model->name = $name;
                $model->save();
            } else {
                return ['code' => 1, 'msg' => '类型不存在'];
            }
            return ['code' => 0, 'msg' => 'succeed'];
        }
    }

    /**
     * 导入cvs
     */
    public function actionLoadFile()
    {
        Yii::$app->response->format = 'json';

        $file = UploadedFile::getInstanceByName('file');
        try {
            $fd   = fopen($file->tempName, 'r');
            $data = [];
            while (($row = fgetcsv($fd)) !== false) {
                array_push($row, date('Y-m-d H:i:s'));
                $data[] = $row;
            }
            if ($data) {
                if (Investment::loadArray($data)) {
                    return ['code' => 0, 'msg' => 'succeed'];
                }
            }
            return ['code' => 1, 'msg' => '数据为空'];
        } catch (\Exception $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
    }

}