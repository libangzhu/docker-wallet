<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 下午2:38
 */

namespace backend\controllers;

use common\service\trusteeship\Trusteeship;
use Yii;
use common\models\psources\MinerFee;
use backend\models\coin\MinerFeeForm;
use common\models\psources\Coin;
use \Exception;

class MinerFeeController extends BaseController
{
    /**
     * 矿工费列表
     */
    public function actionCost()
    {
        if (Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $type = $request->get('type', 1);
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $limit = 100;
            $data = MinerFee::getList($page, $limit, [['type' => $type]]);//数据不多
            if ($data['count'] > 0) {
                $data['code'] = 0;
            } else {
                $data['code'] = 1;
                $data['msg'] = '数据为空';
            }
            Yii::$app->response->format = 'json';
            Yii::$app->response->data = $data;
            Yii::$app->response->send();
        }
        return $this->render('index');
    }

    /**
     * 添加矿工费
     */
    public function actionAdd()
    {
        $model = new MinerFeeForm();
        $type = Yii::$app->request->get('type', 1);
        if (MinerFeeForm::TYPE_WALLET == $type) {
            $model->setScenario(MinerFeeForm::SCENARIOS_WALLET_CREATE);
        } else {
            $model->setScenario(MinerFeeForm::SCENARIOS_TRUSTEESHIP_CREATE);
        }
        $platforms = Coin::getChainList();
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            if ($model->load($request->post()) && $model->validate()) {
                $minerFee = new MinerFee();
                $minerFee->platform = $model->platform;
                if (MinerFeeForm::TYPE_WALLET == $type) {
                    $minerFee->min = $model->min;
                    $minerFee->max = $model->max;
                    $minerFee->level = $model->level;
                } else {
                    $minerFee->fee = $model->fee;
                }

                $minerFee->type = $type;
                $minerFee->create_at = date('Y-m-d H:i:s');
                $minerFee->update_at = date('Y-m-d H:i:s');
                $minerFee->save();
                $this->success('添加成功', '/admin/miner-fee/cost');
            }
            //表单验证失败
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            }
            $this->error($errors, Yii::$app->request->getReferrer());
        }
        return $this->render('add', ['model' => $model, 'platforms' => $platforms, 'type' => $type]);
    }

    /**
     * 编辑
     * @return string
     */
    public function actionEdit()
    {
        $model = new MinerFeeForm();
        $id = Yii::$app->request->get('id', null);
        $type = Yii::$app->request->get('type', 1);
        if (MinerFeeForm::TYPE_WALLET == $type) {
            $model->setScenario(MinerFeeForm::SCENARIOS_WALLET_UPDATE);
        } else {
            $model->setScenario(MinerFeeForm::SCENARIOS_TRUSTEESHIP_UPDATE);
        }
        if ($id) {
            $minerFee = MinerFee::findOne(['id' => $id]);
            $platforms = Coin::getChainList();
            if ($minerFee) {
                if (Yii::$app->request->isPost) {
                    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                        if (MinerFeeForm::TYPE_WALLET == $type) {
                            $minerFee->min = $model->min;
                            $minerFee->max = $model->max;
                            $minerFee->level = $model->level;
                        } else {
                            $minerFee->fee = $model->fee;
                        }
                        $minerFee->update_at = date('Y-m-d H:i:s');
                        $minerFee->save();
                        $this->success('更新成功', '/admin/miner-fee/cost');
                    }
                    $errors = $model->errors;
                    if ($errors) {
                        foreach ($errors as $k => $v) {
                            $errors = $v[0];
                            break;
                        }
                    }
                    $this->error($errors, Yii::$app->request->getReferrer());
                }
                return $this->render('edit', ['model' => $minerFee, 'platforms' => $platforms, 'type' => $type]);
            }
        }
        $this->error('公告不存在', Yii::$app->request->getReferrer());
    }

    /**
     * 删除
     */
    public function actionDel()
    {
        $id = Yii::$app->request->get('id', null);
        if ($id) {
            $minerFee = MinerFee::findOne(['id' => $id]);
            if ($minerFee) {
                try {
                    $minerFee->delete();
                    $this->success('删除成功', Yii::$app->request->getReferrer());
                } catch (Exception $exception) {

                }
            }
        }
        $this->error('删除失败', Yii::$app->request->getReferrer());
    }

    /**
     * @return array
     * 更新托管钱包币种库
     */
    public function actionUpdateCoin()
    {
        Yii::$app->response->format = 'json';
        $trusteeship = new Trusteeship();
        $data = $trusteeship->getSupportCoin();
        if ($data['code'] != 0) {
            return ['code' => -1, 'msg' => $data['msg']];
        } else {
            $trusteeship_coins = array_column($data['data'], 'currency');
            $list = MinerFee::getList(1, 999, [['type' => 2]]);
            if ($list) {
                $local_coins = array_column($list['data'], 'platform');
            } else {
                $local_coins = [];
            }
            $need_add_coins = array_diff($trusteeship_coins, $local_coins);
            if (!$need_add_coins) {
                return ['code' => 0, 'msg' => '币种库已经最新'];
            }
            foreach ($need_add_coins as $item) {
                $minerFee = new MinerFee();
                $minerFee->platform = $item;
                $minerFee->type = 2;
                $minerFee->create_at = date('Y-m-d H:i:s');
                $minerFee->update_at = date('Y-m-d H:i:s');
                $minerFee->save();
            }
            return ['code' => 0, 'msg' => '币种库更新成功'];
        }
    }

    /**
     * @return array
     * 设置托管钱包旷工费
     */
    public function actionSetFee()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $fee = $request->get('fee', 0);
            if ($id) {
                $minerFee = MinerFee::find()->where(['id' => $id])->one();
                if (!$minerFee) {
                    return ['code' => 1, 'msg' => '币种旷工费异常'];
                }
                $minerFee->fee = $fee;
                $minerFee->update_at = date('Y-m-d H:i:s');
                $minerFee->save();
                return ['code' => 0, 'msg' => '旷工费设置成功'];
            } else {
                return ['code' => 1, 'msg' => '旷工费设置失败'];
            }
        }
    }
}