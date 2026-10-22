<?php

namespace backend\controllers;

use common\models\Admin;
use common\models\search\AdminSearch;
use Yii;

/**
 * 后台用户控制器
 *
 * @author libingle
 */
class AdminController extends BaseController
{
    /**
     * ---------------------------------------
     * 用户列表
     * ---------------------------------------
     */
    public function actionIndex()
    {
        /* 添加当前位置到cookie供后续操作调用 */
        $this->setForward();

        $searchModel  = new AdminSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'searchModel'  => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * ---------------------------------------
     * 添加
     * ---------------------------------------
     */
    public function actionAdd()
    {

        $model = new Admin();

        if (Yii::$app->request->isPost) {
            /* 表单验证 */
            $data                    = Yii::$app->request->post('Admin');
            $data                    = array_map('trim', $data);
            $data['reg_time']        = time();
            $data['reg_ip']          = ip2long(Yii::$app->request->getUserIP());
            $data['last_login_time'] = 0;
            $data['last_login_ip']   = ip2long('127.0.0.1');
            $data['update_time']     = 0;
            $role                    = Yii::$app->request->post('role', 'administrator');
            /* 表单数据加载和验证，具体验证规则在模型rule中配置 */
            /* 密码单独验证，否则setPassword后密码肯定符合rule */
            if (empty($data['password']) || strlen($data['password']) < 6) {
                $this->error('密码为空或小于6字符');
            }
            if (empty($data['status'])) {
                $data['status'] = 0;
            }
            $model->setAttributes($data);

            //判断用户名重复
            $count = Admin::find()->where(['username' => $model->username])->count();
            if ($count > 0) {
                $this->error('用户名已被占用');
            }

            $model->generateAuthKey();
            $model->setPassword($data['password']);
            $auth = Yii::$app->authManager;
            $roleTemp = $auth->getRole($role);
            $model->platform_id = empty($roleTemp->data) ? Yii::$app->user->identity->platform_id : $roleTemp->data;
            /* 保存用户数据到数据库 */
            if ($model->save()) {
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($role);
                $auth->assign($role, $model->uid);
                $this->success('操作成功', $this->getForward());
            } else {
                $this->error('操作错误');
            }
        }

        return $this->render('add', [
            'model' => $model,
            'role'  => Yii::$app->request->get('role', 'administrator'),
        ]);
    }

    /**
     * ---------------------------------------
     * 用户授权
     * ---------------------------------------
     */
    public function actionAuth()
    {
        /* 获取用户信息 */
        $uid = Yii::$app->request->get('uid');
        if (!is_numeric($uid) || !($model = Admin::findOne($uid))) {
            $this->error('操作对象不合法');
        }

        $auth = Yii::$app->authManager;
        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post();

            /* 用户权限组 */
            $item_name = $data['param'];

            /* 先删除 用户组-用户 记录 */
            $auth->revokeAll($uid);
            /* 再添加记录 */
            $role = $auth->getRole($item_name);
            $auth->assign($role, $uid);

            $this->success('授权成功！', $this->getForward());

        }

        $roles = $auth->getRoles();
        $group = array_keys($auth->getAssignments($uid));

        return $this->render('auth', [
            'model' => $model,
            'roles' => $roles,
            'group' => $group,
        ]);
    }

    public function actionEdit()
    {

        $model = Admin::findOne(Yii::$app->request->get('uid'));

        if (Yii::$app->request->isPost) {
            /* 表单验证 */
            $data = Yii::$app->request->post('Admin');

            $model->setAttributes($data);

            if (!empty($data['password'])) {
                $model->generateAuthKey();
                $model->setPassword($data['password']);
            }
            /* 保存用户数据到数据库 */
            if ($model->save()) {
                $this->success('操作成功', $this->getForward());
            } else {
                $this->error('操作错误');
            }
        }
        $model->password = '';
        return $this->render('add', [
            'model' => $model,
            'role'  => Yii::$app->request->get('role', 'administrator'),
        ]);
    }
}
