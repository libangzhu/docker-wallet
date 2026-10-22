<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/15
 * Time: 10:26
 */

namespace backend\controllers;

use backend\models\coin\CoinApplicationForm;
use common\models\psources\CoinAppCate;
use common\models\psources\CoinApplication;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinImage;
use Yii;

class ApplicationController extends BaseController
{
    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 应用列表
     */
    public function actionList()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get('id');
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $data = CoinApplication::getListByCategory($page, $limit, $id);
            foreach ($data['data'] as &$val){
                $val['name'] = str_replace('en-US', 'en', $val['name']);
                $val['name'] = str_replace('zh-CN', 'zh', $val['name']);
            }
            return $data;
        } else {
            $id = Yii::$app->request->get('id');
            if ($id) {
                $parent_category = CoinApplicationCategory::getCategoryById($id);
                $platform_id = Yii::$app->user->identity->platform_id;
                $condition = ['platform_id' => $platform_id];
                $cate_items = CoinApplicationCategory::getCateItemsArray($condition);
                if ($parent_category) {
                    return $this->render('list', ['parent_category' => $parent_category, 'cate_items' => $cate_items]);
                } else {
                    $this->error('id参数不合法', Yii::$app->request->getReferrer());
                }

            } else {
                $this->error('id参数不能为空', Yii::$app->request->getReferrer());
            }
        }
    }

    /**
     * @return string
     * 添加应用
     */
    public function actionAdd()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $category_id = Yii::$app->request->get('category_id');
            $fields = ['category_id', 'h5_icon', 'official_url', 'introduce_image', 'show_width', 'show_height', 'open_type', 'open_type_app', 'platform_type', 'name', 'sort', 'icon', 'type', 'native_url', 'native_login_url', 'h5_url', 'android_url', 'ios_url', 'app_store_url', 'advertise', 'description', 'redirect_type', 'platform_id'];
            $data = Yii::$app->request->post();
            $name_arr = $data['name'];
            $name = [];
            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }
            unset($data['name']);
            $data['name'] = $name;
            $data['open_type_app'] = (0 == $data['open_type_app']) ? 1 : $data['open_type_app'];
            $params = array_merge($data, ['category_id' => $category_id, 'platform_id' => Yii::$app->user->identity->platform_id]);
            $params = $this->initParams($params, $fields);
            $coin_applicateion_form = new CoinApplicationForm();
            $coin_applicateion_form->setScenario(CoinApplicationForm::SCENARIO_ADD);
            $coin_applicateion_form->load($params, '');
            $result = $coin_applicateion_form->add();
            if ($result['code'] == 0) {
                $this->success('添加成功', '/admin/application/list?id=' . $category_id);
            } else {
                $this->error($result['msg'], Yii::$app->request->getReferrer());
            }
        }
        return $this->render('add');
    }

    /**
     * @return array|string
     * 应用编辑
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $fields = ['category_id', 'id', 'h5_icon', 'official_url', 'introduce_image', 'show_width', 'show_height', 'open_type', 'open_type_app', 'platform_type', 'name', 'sort', 'icon', 'type', 'native_url', 'native_login_url', 'h5_url', 'android_url', 'ios_url', 'app_store_url', 'advertise', 'description', 'redirect_type', 'platform_id'];
            $data = Yii::$app->request->post();

            $name_arr = $data['name'];
            $name = [];
            foreach (Yii::$app->params['lang'] as $key => $val) {
                $name[$val] = isset($name_arr[$val]) ? $name_arr[$val] : '';
            }

            unset($data['name']);
            $data['name'] = $name;
            $data['open_type_app'] = (0 == $data['open_type_app']) ? 1 : $data['open_type_app'];
            $params = array_merge($data, ['platform_id' => Yii::$app->user->identity->platform_id]);
            $params = $this->initParams($params, $fields);
            $coin_applicateion_form = new CoinApplicationForm();
            $coin_applicateion_form->setScenario(CoinApplicationForm::SCENARIO_EDIT);
            $coin_applicateion_form->load($params, '');
            return $coin_applicateion_form->edit();
        } elseif (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id', null);
            $category_id = Yii::$app->request->get('category_id', null);
            if ($id) {
                $applicate = CoinApplication::getApplicateById($id);
                $coin_ids = [];
                if ($applicate['icon']) {
                    $coin_ids[] = $applicate['icon'];
                }
                if ($applicate['h5_icon']) {
                    $coin_ids[] = $applicate['h5_icon'];
                }
                if ($applicate['introduce_image']) {
                    $coin_ids[] = $applicate['introduce_image'];
                }
                if ($coin_ids) {
                    $coin_items = CoinImage::getItemsByIds($coin_ids);
                }
                $app_cate = CoinAppCate::getAppCate($category_id, $id);
                if ($applicate['icon'] && isset($coin_items[$applicate['icon']])) {
                    $icon_info = $coin_items[$applicate['icon']];
                    $applicate['icon_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_info['file_url'];
                } else {
                    $applicate['icon_url'] = "";
                }
                if ($applicate['h5_icon']) {
                    $icon_info = $coin_items[$applicate['h5_icon']];
                    $applicate['h5_icon_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_info['file_url'];
                } else {
                    $applicate['h5_icon_url'] = "";
                }
                if ($applicate['introduce_image']) {
                    $icon_info = $coin_items[$applicate['introduce_image']];
                    $applicate['introduce_image_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_info['file_url'];
                } else {
                    $applicate['introduce_image_url'] = "";
                }
                #echo json_encode($applicate);exit;
                $applicate['sort'] = $app_cate->sort;
                $name_arr = json_decode($applicate['name'], true);
                $applicate['name_ja'] = isset($name_arr['ja']) ? $name_arr['ja'] : '';
                $applicate['name_en'] = isset($name_arr['en-US']) ? $name_arr['en-US'] : '';
                $applicate['name_zh'] = isset($name_arr['zh-CN']) ? $name_arr['zh-CN'] : '';
                $applicate['name_ko'] = isset($name_arr['ko']) ? $name_arr['ko'] : '';
                $this->layout = false;
                return $this->render('edit', ['item' => $applicate, 'category_id' => $category_id]);
            }
        }
    }

    /**
     * @return array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     * 应用删除
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';

        $id = Yii::$app->request->get('id');
        $category_id = Yii::$app->request->get('category_id');
        if ($id && $category_id) {
            $coin_applicateion_form = new CoinApplicationForm();
            return $coin_applicateion_form->del($id, $category_id);
        }
        return ['code' => 1, 'msg' => 'failed'];
    }

    /**
     * @return array
     * 设置应用所属分类
     */
    public function actionAddCategory()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $cate_id = Yii::$app->request->post('cate_id');
            $app_id = Yii::$app->request->post('app_id');
            $sort = Yii::$app->request->post('sort', 0);
            $coin_applicateion_form = new CoinApplicationForm();
            return $coin_applicateion_form->addCategory($app_id, $cate_id, $sort);
        }
    }

    /**
     * @return array|string
     * 应用图片页面
     */
    public function actionImageIndex()
    {
        if (Yii::$app->request->isAjax) {
            $id = Yii::$app->request->get('id');
            $image_category = Yii::$app->request->get('image_category', 1);
            Yii::$app->response->format = 'json';
            $applicate = CoinApplication::getApplicate($id);
            if ($applicate) {
                if ($image_category == 1) {
                    $data = CoinImage::getItemsByImageIds($applicate->image_ids);
                } else if ($image_category == 2) {
                    $data = CoinImage::getItemsByImageIds($applicate->h5_image_ids);
                } else {
                    $data = ['code' => 0, 'data' => []];
                }
            } else {
                $data = ['code' => 0, 'data' => []];
            }
            return $data;
        } else {
            $id = Yii::$app->request->get('id');
            if ($id) {
                $applicate = CoinApplication::getApplicate($id);
                if ($applicate) {
                    return $this->render('image-index', ['applicate' => $applicate]);
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
     * 添加应用详情图片
     */
    public function actionAddImage()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $id = Yii::$app->request->post('applicate_id', 0);
            $image_id = Yii::$app->request->post('image', 0);
            $image_category = Yii::$app->request->post('image_category', 1);
            if ($id && $image_id) {
                $coin_applicate_form = new CoinApplicationForm();
                return $coin_applicate_form->addImage($id, $image_id, $image_category);
            } else {
                return ['code' => 1, 'msg' => '图片添加失败'];
            }
        }
    }

    /**
     * @return array
     * 删除应用详情图片
     */
    public function actionDeleteImage()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->get('id');
        $applicate_id = Yii::$app->request->get('applicate_id');
        $image_category = Yii::$app->request->get('image_category', 1);
        if ($id && $applicate_id) {
            $coin_applicateion_form = new CoinApplicationForm();
            return $coin_applicateion_form->delImage($id, $applicate_id, $image_category);
        }
        return ['code' => 1, 'msg' => 'failed'];
    }

    /**
     * @return array
     * 设置应用排序
     */
    public function actionSetSort()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $category_id = $request->get('category_id', '');
            $sort = $request->get('sort', 1);
            if ($id && $category_id) {
                $app_cate = CoinAppCate::getAppCate($category_id, $id);
                if (!$app_cate) {
                    return ['code' => 1, 'msg' => '应用不存在,不能设置排序'];
                }
                $app_cate->sort = $sort;
                $app_cate->save();
                return ['code' => 0, 'msg' => '应用排序设置成功'];
            } else {
                return ['code' => 1, 'msg' => '应用排序设置失败'];
            }
        }
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
                $applicate = CoinApplication::getApplicate($id);
                if (!$applicate) {
                    return ['code' => 1, 'msg' => '应用不存在,不能设置启用状态'];
                }
                $applicate->enable = $enable;
                $applicate->save();
                return ['code' => 0, 'msg' => '应用使用状态设置成功'];
            } else {
                return ['code' => 1, 'msg' => '应用使用状态设置失败'];
            }
        }
    }

    /**
     * @return array
     * 设置h5显示尺寸
     */
    public function actionSetH5ImageShowtype()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('app_id', '');
            $h5_image_showtype = $request->get('h5_image_showtype', 1);
            if ($id) {
                $applicate = CoinApplication::getApplicate($id);
                if (!$applicate) {
                    return ['code' => 1, 'msg' => '应用不存在'];
                }
                $applicate->h5_image_showtype = $h5_image_showtype;
                $applicate->save();
                return ['code' => 0, 'msg' => '显示尺寸设置成功'];
            } else {
                return ['code' => 1, 'msg' => '显示尺寸设置失败'];
            }
        }
    }

}