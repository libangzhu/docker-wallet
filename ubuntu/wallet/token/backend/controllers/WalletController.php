<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/17
 * Time: 10:13
 */

namespace backend\controllers;

use backend\models\coin\CoinPlatformForm;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinPlatformWithHold;
use Yii;


class WalletController extends BaseController
{
    public function actionList()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $name = $request->get('name', '');
            $where = [];
            if ($name) {
                $where[] = ['name' => $name];
            }
            $data = CoinPlatform::getList($page, $limit, $where);
            $data['code'] = 0;
            Yii::$app->response->format = 'json';
            return $data;
        }
        return $this->render('index');
    }

    public function actionAdd()
    {
        $model = new CoinPlatformForm();
        $model->scenario = 'add';
        $platform_withhold = CoinPlatformWithHold::find()->select('id, platform')->orderBy('platform')->asArray()->all();
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();
            if ($model->load($data, '') && $model->validate()) {
                $coin = Yii::createObject(CoinPlatform::className());
                $result = $coin->addOne($data);
                if ($result === true) {
                    $this->success('添加成功', '/admin/wallet/list');
                }
            }
            //表单验证失败
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            } elseif (isset($result) && $result['code'] != 0) {
                $errors = $result['message'];
            }
            $this->error($errors, Yii::$app->request->getReferrer());
        }

        return $this->render('add', ['model' => $model, 'platform_withhold' => $platform_withhold]);
    }

    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            $model = new CoinPlatformForm();
            $model->scenario = 'update';
            $data = Yii::$app->request->post();
            Yii::$app->response->format = 'json';
            if ($model->load($data, '') && $model->validate()) {
                $coin = Yii::createObject(CoinPlatform::className());
                $result = $coin->updateOne($data);
                if ($result === true) {
                    return ['code' => 0, 'msg' => 'succeed'];
                }
            }
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            } elseif (isset($result) && $result['code'] != 0) {
                $errors = $result['message'];
            }
            return ['code' => 1, 'msg' => $errors];
        } elseif (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id', null);
            if ($id) {
                $platform_withhold = CoinPlatformWithHold::find()->select('id, platform')->orderBy('platform')->asArray()->all();
                $coin = CoinPlatform::findOne(['id' => $id]);
                $this->layout = false;
                return $this->render('edit', ['model' => $coin, 'platform_withhold' => $platform_withhold]);
            }
        }
    }

    public function actionDelete()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->get('id', 0);
        if ($id) {
            $model = CoinPlatform::findOne(['id' => $id]);
            if ($model) {
                try {
                    $model->delete();
                    return ['code' => 0, 'msg' => 'succeed'];
                } catch (\Throwable $t) {
                    return ['code' => $t->getCode(), 'msg' => $t->getMessage()];
                }
            }
        }
        return ['code' => -1, 'msg' => '删除失败'];
    }

    /**
     * @return string
     * 设置钱包密码
     */
    public function actionSetPasswd()
    {
        if (Yii::$app->request->isPost) {
            $old_passwd = Yii::$app->request->post('old_passwd');
            $new_passwd = Yii::$app->request->post('new_passwd');
            $confirm_passwd = Yii::$app->request->post('confirm_passwd');
            $passwd = Yii::$app->redis->get('wallet_passwd');
            if (!$passwd) {
                Yii::$app->redis->set('wallet_passwd', $new_passwd);
                $this->success('钱包密码设置成功', Yii::$app->request->getReferrer());
            }
            if ($passwd != $old_passwd) {
                $this->error('原始密码错误', Yii::$app->request->getReferrer());
            }
            if ($new_passwd != $confirm_passwd) {
                $this->error('确认密码和设置的密码不一致', Yii::$app->request->getReferrer());
            }
            Yii::$app->redis->set('wallet_passwd', $new_passwd);
            $this->success('钱包密码设置成功', Yii::$app->request->getReferrer());
        }
        return $this->render('set-passwd');
    }


}