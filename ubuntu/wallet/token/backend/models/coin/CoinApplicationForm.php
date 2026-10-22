<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/12
 * Time: 19:10
 */

namespace backend\models\coin;

use backend\models\BaseForm;
use common\models\psources\CoinAppCate;
use common\models\psources\CoinApplicateRecommend;
use common\models\psources\CoinApplication;
use Yii;

class CoinApplicationForm extends BaseForm
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';

    public $id;
    public $category_id;
    public $name;
    public $icon;
    public $h5_icon;
    public $official_url;
    public $introduce_image;
    public $sort;
    public $type;
    public $native_url;
    public $native_login_url;
    public $h5_url;
    public $android_url;
    public $ios_url;
    public $app_store_url;
    public $advertise;
    public $description;
    public $image_ids;
    public $redirect_type;
    public $show_width;
    public $show_height;
    public $open_type;
    public $open_type_app;
    public $platform_type;
    public $platform_id;

    public function scenarios()
    {
        return [
            self::SCENARIO_ADD => ['category_id', 'name', 'h5_icon', 'official_url', 'introduce_image', 'show_width', 'show_height', 'open_type', 'open_type_app', 'platform_type', 'sort', 'icon', 'type', 'native_url', 'native_login_url', 'h5_url', 'android_url', 'ios_url', 'app_store_url', 'advertise', 'description', 'redirect_type', 'platform_id'],
            self::SCENARIO_EDIT => ['category_id', 'id', 'h5_icon', 'official_url', 'introduce_image', 'show_width', 'show_height', 'open_type', 'open_type_app', 'platform_type', 'name', 'sort', 'icon', 'type', 'native_url', 'native_login_url', 'h5_url', 'android_url', 'ios_url', 'app_store_url', 'advertise', 'description', 'redirect_type', 'platform_id'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'category_id' => '分类ID',
            'name' => '名称',
            'icon' => '图标',
            'h5_icon' => 'h5图标',
            'official_url' => '官方链接',
            'introduce_image' => '介绍图',
            'sort' => '排序',
            'type' => '类型',
            'native_url' => '原生链接',
            'native_login_url' => '原生登录链接',
            'h5_url' => 'h5链接',
            'android_url' => '安卓链接',
            'ios_url' => 'ios企业版链接',
            'app_store_url' => 'ios商店版链接',
            'advertise' => '宣传语',
            'description' => '介绍',
            'redirect_type' => '跳转方式',
            'show_width' => '显示宽度',
            'show_height' => '显示高度',
            'open_type' => 'H5打开方式  PC版',
            'open_type_app' => 'H5打开方式 APP版',
            'platform_type' => '所属平台',
            'platform_id' => '平台Id'
        ];
    }

    public function rules()
    {
        return [
            [['category_id'], 'required', 'on' => self::SCENARIO_ADD],
            ['sort', 'default', 'value' => 1],
            ['sort', 'integer'],
            ['icon', 'default', 'value' => 0],
            ['advertise', 'string', 'length' => [0, 20]],
            ['description', 'string', 'length' => [0, 500]],
            ['type', 'default', 'value' => 1],
            ['redirect_type', 'default', 'value' => 1],
            ['show_height', 'default', 'value' => 0],
            ['show_width', 'default', 'value' => 0],

        ];
    }

    public function checkName($attribute, $params)
    {
        $res = CoinApplication::find()->where(['name' => $this->$attribute])->one();
        $scenario = $this->getScenario();
        if ($scenario == self::SCENARIO_ADD) {
            if ($res) {
                $this->addError($attribute, '应用名称已存在');
            }
        } else if ($scenario == self::SCENARIO_EDIT) {
            if ($res && $res->id != $this->id) {
                $this->addError($attribute, '应用名称已存在');
            }
        }
    }

    /**
     * 添加应用
     */
    public function add()
    {
        if ($this->validate()) {
            $tr = CoinApplication::getDb()->beginTransaction();
            try {
                $coin_applicate = new CoinApplication();
                $coin_applicate->setAttributes($this->attributes, false);
                $res = $coin_applicate->save();
                if ($res) {
                    $coin_app_cate = new CoinAppCate();
                    $coin_app_cate->app_id = $coin_applicate->id;
                    $coin_app_cate->cate_id = $this->category_id;
                    $coin_app_cate->sort = $this->sort;
                    $coin_app_cate->save();
                    $tr->commit();
                }
                return ['code' => 0, 'msg' => '应用添加成功!'];
            } catch (\Exception $e) {
                $tr->rollBack();
                return ['code' => 1, 'msg' => $e->getMessage()];
            }

        } else {
            $error = self::getModelError($this);
            return ['code' => 1, 'msg' => $error];
        }
    }

    /**
     * 编辑应用
     */
    public function edit()
    {
        if ($this->validate()) {
            $tr = CoinApplication::getDb()->beginTransaction();
            try {
                $coin_applicate = CoinApplication::getApplicate($this->id);
                if ($coin_applicate) {
                    $image_ids = $coin_applicate->image_ids;
                    $coin_applicate->setAttributes($this->attributes, false);
                    $coin_applicate->image_ids = $image_ids;
                    $coin_applicate->save();
                    CoinApplicateRecommend::updateName($this->id, 2, $this->name);     //更新首页推荐name
                    CoinAppCate::updateSort($this->category_id, $this->id, $this->sort);
                    $tr->commit();
                    return ['code' => 0, 'msg' => '应用修改成功!'];
                } else {
                    return ['code' => 1, 'msg' => '应用不存在'];
                }
            } catch (\Exception $e) {
                $tr->rollBack();
                return ['code' => 1, 'msg' => $e->getMessage()];
            }
        } else {
            $error = self::getModelError($this);
            return ['code' => 1, 'msg' => $error];
        }
    }

    /**
     * @param $id
     * 删除应用
     */
    public function del($id, $category_id)
    {
        $coin_app_cate = CoinAppCate::getAppCate($category_id, $id);
        if ($coin_app_cate) {
            $tr = CoinApplication::getDb()->beginTransaction();
            try {
                $cate_count = CoinAppCate::getCateCountByAppId($id);
                if ($cate_count == 1) {
                    $applicate = CoinApplication::getApplicate($id);
                    $applicate->delete();
                    CoinApplicateRecommend::del($id, 2);     //删除首页推荐
                }
                $coin_app_cate->delete();
                $tr->commit();
                return ['code' => 0, 'msg' => '应用分类删除成功'];
            } catch (\Exception $e) {
                return ['code' => 1, 'msg' => $e->getMessage()];
            }

        } else {
            return ['code' => 1, 'msg' => '应用分类不存在'];
        }
    }


    /**
     * @param $id
     * 添加所属分类
     */
    public function addCategory($id, $category_id, $sort)
    {
        $coin_app_cate = CoinAppCate::getAppCate($category_id, $id);
        if ($coin_app_cate) {
            return ['code' => 1, 'msg' => '应用已属于该分类,不能再添加'];
        } else {
            $coin_app_cate = new CoinAppCate();
            $coin_app_cate->app_id = $id;
            $coin_app_cate->cate_id = $category_id;
            $coin_app_cate->sort = $sort;
            $coin_app_cate->save();
            return ['code' => 0, 'msg' => '应用所属分类添加成功'];
        }
    }

    public function addImage($id, $image_id, $image_category)
    {
        $coin_applicate = CoinApplication::getApplicate($id);
        if ($coin_applicate) {
            if ($image_category == 1) {    //app图片
                $image_ids_field = 'image_ids';
            } else if ($image_category == 2) {  //h5图片
                $image_ids_field = 'h5_image_ids';
            }
            $image_ids = $coin_applicate->$image_ids_field;
            if ($image_ids) {
                $image_items = explode(',', $image_ids);
                array_push($image_items, $image_id);
                $image_ids = implode(",", $image_items);
            } else {
                $image_ids = $image_id;
            }
            $coin_applicate->$image_ids_field = $image_ids;
            $coin_applicate->save();
            return ['code' => 0, 'msg' => '图片添加成功'];
        } else {
            return ['code' => 1, 'msg' => '应用不存在'];
        }
    }

    public function delImage($image_id, $id, $image_category)
    {
        $coin_applicate = CoinApplication::getApplicate($id);
        if ($coin_applicate) {
            if ($image_category == 1) {    //app图片
                $image_ids_field = 'image_ids';
            } else if ($image_category == 2) {  //h5图片
                $image_ids_field = 'h5_image_ids';
            }
            $image_ids = $coin_applicate->$image_ids_field;
            $image_items = explode(',', $image_ids);
            $image_items = array_diff($image_items, [$image_id]);
            if ($image_items) {
                $image_ids = implode(",", $image_items);
            } else {
                $image_ids = '';
            }
            $coin_applicate->$image_ids_field = $image_ids;
            $coin_applicate->save();
            return ['code' => 0, 'msg' => '图片删除成功'];
        } else {
            return ['code' => 1, 'msg' => '应用不存在'];
        }
    }


}