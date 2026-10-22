<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

namespace backend\controllers;

use common\models\psources\CoinPublish;
use Yii;
use yii\web\UploadedFile;
use common\models\psources\CoinReleaseMember;
use common\models\psources\CoinPublishRule;

/**
 * CoinReleaseMember
 */
class CoinReleaseMemberController extends BaseController
{
    public $enableCsrfValidation = false;

    public function actionList()
    {
        $get   = Yii::$app->request->get();
        $page  = $get['page'] ?? 1;
        $limit = $get['limit'] ?? 10;

        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            // 获取rule_id
            $rule_id = $this->getRuleId();
            if (empty($rule_id)) {
                return ['code' => -1, 'msg' => 'failed'];
            }
            $condition = [['rule_id' => $rule_id]];
            $datas     = CoinReleaseMember::getList($page, $limit, $condition);
            return $datas;
        }
        return $this->render('list');
    }

    public function actionSet()
    {
        if (Yii::$app->request->isGet) {
            $this->layout = false;
            //获取token信息
            $rid   = $this->getRuleId();
            $model = CoinPublishRule::findOne($rid);
            $rule  = CoinPublish::findOne($model->pid);
            return $this->render('form', ['token' => $rule->coin_name_en]);
        } else {
            if (Yii::$app->request->isPost) {
                Yii::$app->response->format = 'json';
                $post                       = Yii::$app->request->post();
                // 获取rule_id
                $rule_id = $this->getRuleId();
                if ($rule_id) {
                    $post['rule_id']   = $rule_id;
                    $coin_publish_rule = CoinPublishRule::findOne($rule_id);
                    if ($coin_publish_rule) {
                        $post['release']      =  $post['amount'] * (100 - $coin_publish_rule->lock) / 100;
                        $post['freeze']       =  $post['amount'] * $coin_publish_rule->lock / 100;
                        $post['company_name'] = $coin_publish_rule->company_name;
                    } else {
                        return ['code' => -1, 'msg' => '规则不存在'];
                    }
                } else {
                    return ['code' => -1, 'msg' => '规则不存在'];
                }
                if (isset($post['id']) && !empty($post['id'])) {
                    $model           = CoinReleaseMember::findOne($post['id']);
                    $model->scenario = CoinReleaseMember::SCENARIOS_UPDATE;
                } else {
                    $model           = new CoinReleaseMember();
                    $model->scenario = CoinReleaseMember::SCENARIOS_ADD;
                }
                if ($model->load($post) && $model->validate()) {
                    $model->amount = $model->amount * 1e8;
                    $model->freeze = $model->freeze * 1e8;
                    $model->release = $model->release * 1e8;
                    if ($model->save(false)) {
                        return ['code' => 0, 'msg' => '添加成功'];
                    }
                } else {
                    return ['code' => -1, 'msg' => current($model->firstErrors)];
                }
                return ['code' => -1, 'msg' => '添加失败'];
            }
        }
    }

    /**
     * 名单导入(预览)
     */
    public function actionImplode()
    {
        Yii::$app->response->format = 'json';
        $file                       = UploadedFile::getInstanceByName('file');
        try {
            $fd      = fopen($file->tempName, 'r');
            $data    = [];
            $rule_id = $this->getRuleId();
            if ($rule_id) {
                $coin_publish_rule = CoinPublishRule::findOne($rule_id);
                $company_name      = $coin_publish_rule->company_name;
                $present           = $coin_publish_rule->lock;
                if (!$coin_publish_rule) {
                    return ['code' => -1, 'msg' => '规则不存在'];
                }
                while (($row = fgetcsv($fd)) !== false) {
                    array_unshift($row, $rule_id);
                    $data[] = $row;
                }
                if (empty($data)) {
                    return ['code' => -1, 'msg' => '导入失败'];
                }
                if (ctype_digit($data[0][0])) {
                    array_shift($data);
                }
                foreach ($data as $key => &$value) {
                    $value[0] = trim($value[0]);
                    $value[1] = trim($value[1]);
                    $value[2] = trim($value[2]);
                    $value[3] *= 1e8;
                    $value[]  = $value[3] * (100 - $present) / 100;
                    $value[]  = $value[3] * $present / 100;
                    $value[]  = $company_name;
                }
                $data_json = json_encode($data);
                Yii::$app->redis->set('coin_release_member_data',$data_json,'EX',3600);
                foreach ($data as $key => &$value){
                    $value[3] /= 1e8;
                    unset($value[4]);
                    unset($value[5]);
                    unset($value[6]);
                }
                //$result = CoinReleaseMember::implode($data);
                return ['code' => 0, 'msg' => '预览成功','data' => $data,'count' => count($data)];
            } else {
                return ['code' => -1, 'msg' => '规则不存在'];
            }
        } catch (\Exception $e) {
            return ['code' => $e->getCode(), 'msg' => $e->getMessage()];
        }
    }

    /**
     * @return array
     * 导入数据到数据库
     */
    public function actionDataUpdate()
    {
        Yii::$app->response->format = 'json';
        if(Yii::$app->request->isAjax){
            $coin_release_member_data = Yii::$app->redis->get('coin_release_member_data');
            if($coin_release_member_data){
                $coin_release_member_data = json_decode($coin_release_member_data);
                CoinReleaseMember::implode($coin_release_member_data);
                return ['code' => 0, 'msg' => '导入成功'];
            }
            return ['code' => 0, 'msg' => '没有需要导入的数据'];
        }
    }

    /**
     * 获取rule_id
     *
     * @return int
     */
    private function getRuleId()
    {
        $referer = Yii::$app->request->getReferrer();
        $referer = parse_url($referer, PHP_URL_QUERY);
        parse_str($referer, $query);
        if (!isset($query['rid']) || empty($query['rid'])) {
            return false;
        }
        return $query['rid'];
    }
}