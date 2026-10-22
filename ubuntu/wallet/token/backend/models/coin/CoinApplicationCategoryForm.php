<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/16
 * Time: 11:43
 */

namespace backend\models\coin;

use backend\models\BaseForm;
use common\models\psources\CoinAppCate;
use common\models\psources\CoinApplicateRecommend;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinBanner;

class CoinApplicationCategoryForm extends BaseForm
{
    const SCENARIO_ADD = 'add';
    const SCENARIO_EDIT = 'edit';

    public $id;
    public $name;
    public $sort;
    public $icon;
    public $pid;
    public $banner;
    public $banner_url;
    public $platform_id;

    public function scenarios()
    {
        return [
            self::SCENARIO_ADD => ['name', 'sort', 'icon', 'banner', 'banner_url', 'platform_id'],
            self::SCENARIO_EDIT => ['id', 'name', 'sort', 'icon', 'banner', 'banner_url', 'platform_id'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => '名称',
            'icon' => '图标',
            'sort' => '排序',
            'banner' => 'banner图片',
            'banner_url' => 'banner链接',
            'platform_id' => '平台Id',
        ];
    }

    public function rules()
    {
        return [
            [['name'], 'required', 'on' => self::SCENARIO_ADD],
            [['id', 'name', 'sort'], 'required', 'on' => self::SCENARIO_EDIT],
            ['sort', 'default', 'value' => 1],
            ['sort', 'integer'],
            ['icon', 'default', 'value' => 0],
            ['banner', 'default', 'value' => 0],
//            ['name', 'checkName'],
        ];
    }

    public function checkName($attribute, $params)
    {
        $res = CoinApplicationCategory::find()->where(['name' => $this->$attribute])->one();
        $scenario = $this->getScenario();
        if ($scenario == self::SCENARIO_ADD) {
            if ($res) {
                $this->addError($attribute, '应用分类名称已存在');
            }
        } else if ($scenario == self::SCENARIO_EDIT) {
            if ($res && $res->id != $this->id) {
                $this->addError($attribute, '应用分类名称已存在');
            }
        }
    }


    /**
     * 添加应用分类
     */
    public function add()
    {
        if ($this->validate()) {
            $coin_applicate_category = new CoinApplicationCategory();
            $coin_applicate_category->setAttributes($this->attributes, false);
            $coin_applicate_category->save();
            return ['code' => 0, 'msg' => '应用分类添加成功!'];
        } else {
            $error = self::getModelError($this);
            return ['code' => 1, 'msg' => $error];
        }
    }

    /**
     * 编辑应用分类
     */
    public function edit()
    {
        if($this->validate()){
            $coin_applicate_category = CoinApplicationCategory::getCategoryById($this->id);
            if($coin_applicate_category){
                $coin_applicate_category->setAttributes($this->attributes,false);
                $coin_applicate_category->save();
                CoinApplicateRecommend::updateName($this->id,1,$this->name);
                return ['code' => 0,'msg' => '应用分类修改成功!'];
            }else{
                return ['code' => 1,'msg' => '应用分类不存在'];
            }
        }else{
            $error = self::getModelError($this);
            return ['code' => 1,'msg' => $error];
        }
    }

    /**
     * @param $id
     * 删除应用分类
     */
    public function del($id)
    {
        $coin_applicate_category = CoinApplicationCategory::getCategoryById($id);
        if($coin_applicate_category){
            $hasApp = CoinAppCate::getAppCountByCateId($id);
            if($hasApp){
                return ['code' => 1,'msg' => '分类下有应用,无法删除'];
            }
            $coin_applicate_category->delete();
            CoinApplicateRecommend::del($id,1);          //删除首页推荐
            return ['code' => 0,'msg' => '应用分类删除成功'];
        }else{
            return ['code' => 1,'msg' => '应用分类不存在'];
        }
    }

    public function addBanner($id,$image_id,$banner_url)
    {
        $coin_applicate_category = CoinApplicationCategory::getCategoryById($id);
        if($coin_applicate_category){
            $tr = CoinApplicationCategory::getDb()->beginTransaction();
            $coin_banner  = new CoinBanner();
            $coin_banner->image_id = $image_id;
            $coin_banner->banner_url = $banner_url;
            if($coin_banner->save()){
                $banner_id = $coin_banner->id;
                $banner_ids = $coin_applicate_category->h5_banner;
                if($banner_ids){
                    $banner_items = explode(',',$banner_ids);
                    array_push($banner_items,$banner_id);
                    $banner_ids = implode(",",$banner_items);
                }else{
                    $banner_ids = $banner_id;
                }
                $coin_applicate_category->h5_banner = $banner_ids;
                $coin_applicate_category->save();
                $tr->commit();
                return ['code' => 0,'msg' => 'banner添加成功'];
            }else{
                return ['code' => 0,'msg' => 'banner添加失败'];
            }
        }else{
            return ['code' => 1,'msg' => '应用分类不存在'];
        }
    }

    public function delBanner($banner_id,$id)
    {
        $coin_applicate_category = CoinApplicationCategory::getCategoryById($id);
        if($coin_applicate_category){
            $tr = CoinApplicationCategory::getDb()->beginTransaction();
            $banner = CoinBanner::getBannerById($banner_id);
            if($banner->delete()){
                $banner_ids = $coin_applicate_category->h5_banner;
                $banner_items = explode(',',$banner_ids);
                $banner_items = array_diff($banner_items,[$banner_id]);
                if($banner_items){
                    $banner_ids = implode(",",$banner_items);
                }else{
                    $banner_ids = '';
                }
                $coin_applicate_category->h5_banner = $banner_ids;
                $coin_applicate_category->save();
                $tr->commit();
                return ['code' => 0,'msg' => 'banner删除成功'];
            }else{
                return ['code' => 0,'msg' => 'banner删除失败'];
            }
        }else{
            return ['code' => 1,'msg' => '应用分类不存在'];
        }
    }

}