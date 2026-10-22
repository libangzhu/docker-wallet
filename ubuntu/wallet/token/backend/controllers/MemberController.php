<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-12
 * Time: 下午5:50
 */

namespace backend\controllers;

use Yii;
use common\models\pwallet\Member;
use common\models\pwallet\MemberAsset;

class MemberController extends BaseController
{
    public function actionIndex()
    {
        $req      = Yii::$app->request;
        $response = Yii::$app->response;
        if ($req->isAjax) {
            $page        = $req->get('page', 0);
            $limit       = $req->get('limit', 10);
            $register_at = $req->get('register_at', null);
            $type        = $req->get('type', 0);
            $condition   = $req->get('condition', '');
            $where       = [];
            if (!empty($condition)) {
                $where[] = [
                    'OR',
                    ['=', 'uid', $condition],
                    ['=', 'phone', $condition],
                    ['=', 'email', $condition],
                    ['=', 'realname', $condition],
                    ['=', 'nickname', $condition],
                    ['=', 'username', $condition],
                ];

                if ($type) {
                    //todo 推客查找
                    //先获取推客uid
                    $recommenders = Member::getList(1, 999, $where);
                    if ($recommenders['code'] == 0) {
                        $recommenders = $recommenders['data'];
                        $ids          = [];
                        foreach ($recommenders as $recommender) {
                            $ids[] = $recommender['uid'];
                        }
                        unset($where);
                        $where   = [];
                        $where[] = ['in', 'recommend_id', $ids];
                    }
                }
            }
            if ($register_at != null && !empty($register_at)) {
                list($s, $e) = explode('~', str_replace(' ', '', $register_at));
                $where[] = ['between', 'register_at', $s, $e];
            }
            $data             = Member::getList($page, $limit, $where);
            $response->format = 'json';
            $response->data   = $data;
            $response->send();
        }
        return $this->render('index');
    }

    public function actionEdit()
    {
        $req = Yii::$app->request;
        if ($req->isPost) {
            //TODO 保存信息
            $id = $req->post('uid', 0);
            if ($id) {
                $memeber = Member::findOne(['uid' => $id]);
                if ($memeber) {
                    $memeber->scenario = Member::SCENARIO_UPDATE;
                    if ($memeber->load($req->post()) && $memeber->validate()) {
                        try {
                            $memeber->updateOne();
                            $this->success('修改成功', '/admin/member/index');
                        } catch (\Exception $exception) {

                        }
                    }
                    if ($memeber->hasErrors()) {
                        $errors = $memeber->errors;
                        foreach ($errors as $error) {
                            $this->error($error[0], $req->getReferrer());
                        }
                    }
                }
            }
        }
        $id = $req->get('id', 0);
        if ($id) {
            $memeber = Member::findOne(['uid' => $id]);
            if ($memeber) {
                return $this->render('edit', ['model' => $memeber]);
            }
        }
        $this->error('用户不存在', '/admin/member/index');
    }

    public function actionWallet()
    {
        if (Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $page    = $request->get('page', 1);
            $limit   = $request->get('limit', 10);
            $uid     = $request->get('uid', '');
            $where   = [];
            if ($uid) {
                $where[] = ['uid' => $uid];
            }
            $data = MemberAsset::getList($page, $limit, $where);

            Yii::$app->response->format = 'json';
            Yii::$app->response->data   = $data;
            Yii::$app->response->send();
        }
        return $this->render('wallet');
    }
}