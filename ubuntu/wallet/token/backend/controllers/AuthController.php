<?php

namespace backend\controllers;

use common\components\Tools;
use common\models\psources\CoinPlatform;
use Yii;
use common\models\Admin;
use common\models\Menu;

/**
 * 身份授权控制器
 * @author libingle
 */
class AuthController extends BaseController
{
    /**
     * @var \common\core\rbac\DbManager
     */
    public $authManager;

    /**
     * @var bool 这里很多自定义的表单，就没有添加验证
     */
    public $enableCsrfValidation = false;

    /**
     * ---------------------------------------
     * 构造方法
     * ---------------------------------------
     */
    public function init()
    {
        parent::init();
        $this->authManager = Yii::$app->authManager;
    }

    /**
     * ---------------------------------------
     * “角色”列表
     * ---------------------------------------
     */
    public function actionIndex()
    {
        /* 添加当前位置到cookie供后续跳转调用 */
        $this->setForward();
        $auth = Yii::$app->authManager;
        /* 获取角色列表 */
        $self_roles = current($auth->getRolesByUser(Yii::$app->user->id));
        $roles = $auth->getChildRoles($self_roles->name);
        $roles_temp = [];
        foreach ($roles as $key => $role) {
            $roles_temp[$role->name]['type'] = $role->type;
            $roles_temp[$role->name]['name'] = $role->name;
            $roles_temp[$role->name]['description'] = $role->description;
            $roles_temp[$role->name]['ruleName'] = $role->ruleName;
            $roles_temp[$role->name]['data'] = $role->data;
            $roles_temp[$role->name]['createdAt'] = $role->createdAt;
            $roles_temp[$role->name]['updatedAt'] = $role->updatedAt;
        }
        $roles_temp = Tools::arraySort($roles_temp, 'createdAt');
        return $this->render('index', [
            'roles' => $roles_temp,
        ]);
    }

    /**
     * ---------------------------------------
     * 添加“角色”
     * 注意：角色表的“rule_name”字段必须为“NULL”，不然会出错。
     *      详情见“yii\rbac\BaseManager”的203行if($item->ruleName === null){return true;}
     * @throws \Exception|\Throwable
     * ---------------------------------------
     */
    public function actionAdd()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        if ($user_platform_id == 1) {
            $platforms = CoinPlatform::find()->asArray()->all();
        } else {
            $platforms = CoinPlatform::find()->where(['id' => $user_platform_id])->asArray()->all();
        }

        if (Yii::$app->request->isPost) {
            $auth = Yii::$app->authManager;
            $self_role = current($auth->getRolesByUser(Yii::$app->user->id));
            $data = Yii::$app->request->post('param');
            $data['name'] = trim($data['name']);
            if (!$data['name']) {
                $this->error('请输入要添加的角色名！');
            } else {
                $role_name = $data['name'];
                if (Yii::$app->authManager->getRole($role_name) != null) {
                    $this->error('该角色名已存在！');
                } else {
                    /* 创建角色 */
                    $role = Yii::$app->authManager->createRole($role_name);
                    $role->type = 1;
                    $role->description = $data['description'];
                    $role->data = $data['platform_id'];
                    if (Yii::$app->authManager->add($role)) {
                        $auth = Yii::$app->authManager;
                        $auth->addChild($self_role, $role);
                        $this->success('添加成功！', $this->getForward());
                    }
                    $this->error('添加失败！');
                }
            }
        }
        return $this->render('add', ['platforms' => $platforms]);
    }

    /**
     * ---------------------------------------
     * 编辑“角色”
     * 注意：角色表的“rule_name”字段必须为“NULL”，不然会出错。
     *      详情见“yii\rbac\BaseManager”的203行if($item->ruleName === null){return true;}
     * ---------------------------------------
     */
    public function actionEdit()
    {
        /* 获取角色信息 */
        $item_name = trim(Yii::$app->request->get('role'));

        $role = Yii::$app->authManager->getRole($item_name);

        $user_platform_id = Yii::$app->user->identity->platform_id;
        if ($user_platform_id == 1) {
            $platforms = CoinPlatform::find()->asArray()->all();
        } else {
            $platforms = CoinPlatform::find()->where(['id' => $user_platform_id])->asArray()->all();
        }

        if (Yii::$app->request->isPost) {
            $data = Yii::$app->request->post('param');

            $data['name'] = trim($data['name']);

            if (!$data['name']) {
                $this->success('请输入要更新的角色名！');
            } else {
                if (Yii::$app->authManager->getRole($data['name']) != null) {
                    $this->error('该角色名已存在！');
                } else {
                    $role->name = $data['name'];
                    $role->description = $data['description'];
                    $role->data = $data['platform_id'];
                    if (Yii::$app->authManager->update($item_name, $role)) {
                        $this->success('更新成功！', $this->getForward());
                    }
                    $this->error('更新失败！');
                }
            }
        }

        return $this->render('edit', [
            'role' => $role,
            'platforms' => $platforms
        ]);
    }

    /**
     * ---------------------------------------
     * 删除“角色”
     * 同时会删除auth_assignment、auth_item_child、auth_item中关于$role的内容
     * @param string $role 角色名称
     * ---------------------------------------
     */
    public function actionDelete($role)
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole($role);
        $role_self = current($auth->getRolesByUser(Yii::$app->user->id));
        if ($auth->hasChild($role_self, $role)) {
            if ($auth->remove($role)) {
                $this->success('删除成功', $this->getForward());
            }
        }
        $this->error('删除失败');
    }

    /**
     * ---------------------------------------
     * 角色授权
     * ---------------------------------------
     */
    public function actionAuth($role)
    {
        /* 提交后 */
        if (Yii::$app->request->isPost) {
            $rules = Yii::$app->request->post('rules');
            /* 判断角色是否存在 */
            $auth = Yii::$app->authManager;
            $role_update = $auth->getRole($role);
            if (!$role) {
                $this->error('角色不存在');
            }
            // if ($role_update->name == 'administrator') {
            //     $this->error('超级管理员权限不允许修改');
            // }
            // 获取操作者角色的所有权限
            $role_self = current($auth->getRolesByUser(Yii::$app->user->id));
            $all_permissions = array_keys($auth->getPermissionsByRole($role_self->name));

            // 获取角色原来的所有权限
            $can_permissions = array_keys($auth->getPermissionsByRole($role));

            // 需要移除的权限
            $remove = array_intersect(array_diff($all_permissions, $rules), $can_permissions);
            //需要添加的权限
            $add = array_diff($rules, $can_permissions);

            if (is_array($add)) {
                foreach ($add as $rule) {
                    /* 更新auth_item_child表 */
                    $rule = $auth->getRule($rule);
                    $auth->addChild($role_update, $rule);
                }
            }
            if (is_array($remove)) {
                foreach ($remove as $value) {
                    $rule = $auth->getRule($value);
                    $auth->removeChild($role_update, $rule);
                }
            }
            $this->success('更新权限成功', $this->getForward());
        }

        /* 获取栏目节点 */
        $node_list = Menu::returnNodes();
        $auth_rules = Yii::$app->authManager->getChildren($role);
        $auth_rules = array_keys($auth_rules);

        return $this->render('auth', [
            'node_list' => $node_list,
            'auth_rules' => $auth_rules,
            'role' => $role,
        ]);
    }

    /**
     * ---------------------------------------
     * 授权用户列表
     * ---------------------------------------
     */
    public function actionUser($role)
    {
        /* 添加当前位置到cookie供后续跳转调用 */
        $this->setForward();

        $uids = Yii::$app->authManager->getUserIdsByRole($role);
        $uids = implode(',', array_unique($uids));

        /*更新uids 为空的情况*/
        if ($uids) {
            $_where = 'uid in(' . $uids . ')';
        } else {
            $_where = '1 != 1';
        }

        return $this->render('user', [
            'dataProvider' => $this->lists1(new Admin(), $_where),
            'role' => Yii::$app->request->get('role', 'administrator'),
        ]);
    }

}
