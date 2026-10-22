<?php

namespace backend\controllers;

use common\core\rbac\Rule;
use common\helpers\ArrayHelper;
use common\models\Menu;
use common\models\search\MenuSearch;
use Yii;
use yii\web\NotFoundHttpException;

/**
 * 后台菜单控制器
 * @author libingle
 */
class MenuController extends BaseController
{
    /**
     * ---------------------------------------
     * 列表页
     * ---------------------------------------
     */
    public function actionIndex()
    {
        /* 添加当前位置到cookie供后续跳转调用*/
        $this->setForward();
        $menu         = new Menu();
        $searchModel  = new MenuSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
        return $this->render('index', [
            'menu'         => $menu,
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
        $pid   = Yii::$app->request->get('pid', 0);
        $model = $this->findModel(0);

        if (Yii::$app->request->isPost) {
            /* 表单验证 */
            $data           = Yii::$app->request->post('Menu');
            $data['status'] = 1;

            if ($this->saveRow($model, $data)) {
                //添权限
                $auth       = Yii::$app->authManager;
                $rule       = new Rule();
                $rule->name = $data['url'];
                $auth->add($rule);
                $permission           = $auth->createPermission($data['url']);
                $permission->ruleName = $rule->name;
                $auth->add($permission);
                $this->success('操作成功', $this->getForward());
            } else {
                $this->error('操作错误');
            }
        }

        /* 设置默认值 */
        $model->loadDefaultValues();
        $model->pid = $pid;
        /* 渲染模板 */
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * ---------------------------------------
     * 编辑
     * ---------------------------------------
     */
    public function actionEdit()
    {
        $id    = Yii::$app->request->get('id', 0);
        $model = $this->findModel($id);

        if (Yii::$app->request->isPost) {
            /* 表单验证 */
            $data = Yii::$app->request->post('Menu');
            //删除原来的权限
            $auth = Yii::$app->authManager;

            $rule       = $auth->getRule($model->url);
            $permission = $auth->getPermission($model->url);
            if ($this->saveRow($model, $data)) {

                $auth->remove($permission);
                $auth->remove($rule);
                //添权限
                $rule       = new Rule();
                $rule->name = $data['url'];
                $auth->add($rule);
                $permission           = $auth->createPermission($data['url']);
                $permission->ruleName = $rule->name;
                $auth->add($permission);
                $this->success('操作成功', $this->getForward());
            } else {
                $this->error('操作错误');
            }
        }
        /* 渲染模板 */
        return $this->render('edit', [
            'model' => $model,
        ]);
    }

    /**
     * ---------------------------------------
     * 删除或批量删除
     * ---------------------------------------
     */
    public function actionDelete()
    {
        $model = $this->findModel(0);
        if ($this->delRow($model, 'id')) {
            $this->success('删除成功', $this->getForward());
        } else {
            $this->error('删除失败！');
        }
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param  integer               $id
     * @throws NotFoundHttpException if the model cannot be found
     * @return Menu                  the loaded model
     */
    protected function findModel($id)
    {
        if ($id == 0) {
            return new Menu();
        }
        if (($model = Menu::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    /**
     * ---------------------------------------
     * 菜单树
     * ---------------------------------------
     */
    public function actionTree()
    {
        $lists                      = Menu::find()->orderBy('sort asc')->where(['hide' => 0])->asArray()->all();
        $lists                      = ArrayHelper::list_to_tree($lists, 'id', 'pid');
        $lists                      = ArrayHelper::jstree($lists);
        Yii::$app->response->format = 'json';
        return $lists;
    }

    /**
     * [刷新菜单]
     * @author: libingke
     */
    public function actionFlush()
    {
        Menu::flushMenu();
        return $this->goBack(Yii::$app->request->getReferrer());
    }
}
