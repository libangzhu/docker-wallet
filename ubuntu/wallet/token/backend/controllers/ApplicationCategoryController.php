<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/11
 * Time: 16:55
 */

namespace backend\controllers;

use backend\models\coin\CoinApplicationCategoryForm;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinBanner;
use common\models\psources\CoinImage;
use common\models\psources\CoinPlatform;
use Yii;


class ApplicationCategoryController extends BaseController
{
    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 应用分类列表
     */
    public function actionIndex()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        $platforms = CoinPlatform::find()->select('id, name')->asArray()->all();
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $name = $request->get('category_name', '');
            $where = [];
            if (1 !== $user_platform_id) {
                $where[] = ['platform_id' => $user_platform_id];
            } else {
                $platform_id = Yii::$app->request->get('platform', '');
                if(!empty($platform_id)) {
                    $where[] = ['platform_id' => $platform_id];
                }
            }

            if ($name) {
                $where[] = ['name' => $name];
            }
            $data = CoinApplicationCategory::getList($page, $limit, $where);
            foreach ($data['data'] as &$val) {
                $val['name'] = str_replace('en-US', 'en', $val['name']);
                $val['name'] = str_replace('zh-CN', 'zh', $val['name']);
                $val['coin_name'] = isset($val['platform']['name']) ? $val['platform']['name'] : '';
            }

            return $data;
        }
        return $this->render('index', ['platforms' => $platforms, 'user_platform_id' => $user_platform_id]);
    }

    /**
     * 添加一个application category
     */
    public function actionAddAndEdit()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $fields = ['id', 'name', 'sort', 'icon', 'banner', 'banner_url'];
            $params = $this->initParams(Yii::$app->request->post(), $fields);

            $application_category = new CoinApplicationCategoryForm();
            if ($params['id']) {   //edit
                $category = CoinApplicationCategory::getCategoryById($params['id']);
                $params['platform_id'] = $category->platform_id;
                $application_category->setScenario(CoinApplicationCategoryForm::SCENARIO_EDIT);
                $application_category->load($params, '');
                return $application_category->edit();
            } else {
                $params['platform_id'] = Yii::$app->user->identity->platform_id;
                $application_category->setScenario(CoinApplicationCategoryForm::SCENARIO_ADD);
                $application_category->load($params, '');
                return $application_category->add();

            }
        }
    }

    public function actionAdd()
    {
        $model = new CoinApplicationCategoryForm();
        $model->setScenario(CoinApplicationCategoryForm::SCENARIO_ADD);
        if (Yii::$app->request->isPost) {
            $fields = ['id', 'name', 'sort', 'icon', 'banner', 'banner_url'];
            $params = $this->initParams(Yii::$app->request->post(), $fields);
            $params['platform_id'] = Yii::$app->user->identity->platform_id;
            $name_arr = $params['name'];
            $name = [];

            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }

            unset($params['name']);
            $params['name'] = $name;
            $model->load($params, '');
            $result = $model->add();
            if (0 === $result['code']) {
                $this->success('添加成功', '/admin/application-category/index');
            }
        }
        return $this->render('add', ['model' => $model]);
    }

    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $fields = ['id', 'name', 'sort', 'icon', 'banner', 'banner_url'];
            $params = $this->initParams(Yii::$app->request->post(), $fields);
            $application_category = new CoinApplicationCategoryForm();
            $category = CoinApplicationCategory::getCategoryById($params['id']);
            $params['platform_id'] = $category->platform_id;
            $application_category->setScenario(CoinApplicationCategoryForm::SCENARIO_EDIT);
            $name_arr = $params['name'];
            $name = [];
            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }

            unset($params['name']);
            $params['name'] = $name;
            $application_category->load($params, '');
            return $application_category->edit();
        }
        $id = Yii::$app->request->get('id', null);
        $coin = CoinApplicationCategory::getAppCategory($id);
        $icon_model = CoinImage::findOne($coin['icon']);
        $banner_model = CoinImage::findOne($coin['banner']);
        $coin['icon_url'] = empty($icon_model) ? '' : Yii::$app->params['service']['OssService']['url'] . $icon_model->file_url;
        $coin['banner_image_url'] = empty($banner_model) ? '' : Yii::$app->params['service']['OssService']['url'] . $banner_model->file_url;
        $name_arr = json_decode($coin['name'], true);
        $coin['name_ja'] = isset($name_arr['ja']) ? $name_arr['ja'] : '';
        $coin['name_zh'] = isset($name_arr['zh-CN']) ? $name_arr['zh-CN'] : '';
        $coin['name_en'] = isset($name_arr['en-US']) ? $name_arr['en-US'] : '';
        $coin['name_ko'] = isset($name_arr['ko']) ? $name_arr['ko'] : '';
        $this->layout = false;
        return $this->render('edit', ['model' => $coin]);
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * 应用分类删除
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';

        $id = Yii::$app->request->get('id');
        if ($id) {
            $application_category = new CoinApplicationCategoryForm();
            return $application_category->del($id);
        }
        return ['code' => 1, 'msg' => 'failed'];
    }

    /**
     * @return array
     * 应用分类设置排序
     */
    public function actionSetSort()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $sort = $request->get('sort', 1);
            if ($id) {
                $category = CoinApplicationCategory::getCategoryById($id);
                if (!$category) {
                    return ['code' => 1, 'msg' => '分类不存在,不能设置排序'];
                }
                $category->sort = $sort;
                $category->save();
                return ['code' => 0, 'msg' => '分类排序设置成功'];
            } else {
                return ['code' => 1, 'msg' => '分类排序设置失败'];
            }
        }
    }

    /**
     * @return array|string
     * banner列表
     */
    public function actionBannerIndex()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get('id');
            Yii::$app->response->format = 'json';
            $applicate_category = CoinApplicationCategory::getCategoryById($id);
            if ($applicate_category) {
                $data = CoinBanner::getBannerInfoByIds($applicate_category->h5_banner);
            } else {
                $data = ['code' => 0, 'data' => []];
            }
            return $data;
        } else {
            $id = Yii::$app->request->get('id');
            if ($id) {
                $applicate_category = CoinApplicationCategory::getCategoryById($id);
                if ($applicate_category) {
                    return $this->render('banner-index', ['applicate_category' => $applicate_category]);
                } else {
                    $this->error('id参数不合法', Yii::$app->request->getReferrer());
                }
            } else {
                $this->error('id参数不能为空', Yii::$app->request->getReferrer());
            }
        }
    }

    /**
     * @return array
     * 添加banner
     */
    public function actionAddBanner()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $id = Yii::$app->request->post('applicate_category_id', 0);
            $image_id = Yii::$app->request->post('image', 0);
            $banner_url = Yii::$app->request->post('banner_url', 0);
            if ($id && $image_id) {
                $coin_applicate_category_form = new CoinApplicationCategoryForm();
                return $coin_applicate_category_form->addBanner($id, $image_id, $banner_url);
            } else {
                return ['code' => 1, 'msg' => 'banner添加失败'];
            }
        }
    }

    /**
     * @return array
     * 删除banner
     */
    public function actionDeleteBanner()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->get('id');
        $applicate_category_id = Yii::$app->request->get('applicate_category_id');
        if ($id && $applicate_category_id) {
            $coin_applicateion_category_form = new CoinApplicationCategoryForm();
            return $coin_applicateion_category_form->delBanner($id, $applicate_category_id);
        }
        return ['code' => 1, 'msg' => 'failed'];
    }

    /**
     * @return array
     * 设置是否显示
     */
    public function actionSetEnable()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $enable = $request->get('enable', 1);
            if ($id) {
                $category = CoinApplicationCategory::getCategoryById($id);
                if (!$category) {
                    return ['code' => 1, 'msg' => '分类不存在,不能设置启用状态'];
                }
                $category->enable = $enable;
                $category->save();
                return ['code' => 0, 'msg' => '分类使用状态设置成功'];
            } else {
                return ['code' => 1, 'msg' => '分类使用状态设置失败'];
            }
        }
    }


}