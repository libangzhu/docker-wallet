<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/17
 * Time: 19:53
 */

namespace api\controllers;

use api\base\BaseController;
use common\business\ApplicationBusiness;
use common\models\psources\CoinApplicateRank;
use common\models\psources\CoinApplication;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinImage;
use Yii;


class ApplicationController extends BaseController
{
    /**
     * 首页接口
     */
    public function actionIndex()
    {
        $condition = [];
        $platform_id = Yii::$app->request->post('platform_id', 1);
        if(!empty($platform_id)){
            $condition = ['platform_id' => $platform_id];
        }

        $recommendData = ApplicationBusiness::getRecommendList($condition);
        foreach($recommendData as $j => &$val){
            $name_arr = json_decode($val['name'], true);
            $val['name'] = isset($name_arr[$this->lang]) ? $name_arr[$this->lang] : '';
        }
        $data['recommend'] =$recommendData;
        $cate_app_data = ApplicationBusiness::getCategoryAppList($condition);
        foreach ($cate_app_data as $key => &$val){
            $name = json_decode($val['name'], true);
            $val['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';
            if(!empty($val['apps'])){
                foreach($val['apps'] as $j => &$value){
                    $name = json_decode($value['name'], true);
                    $value['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';
                }
            }
        }
        $data['cate_app_data'] = $cate_app_data;
        $data['banner'] = ApplicationBusiness::getBannerList($condition);
        $result['code'] = 0;
        $result['data'] = $data;
        return $result;

    }

    /**
     * 分类/应用接口
     */
    public function actionCateAppInfo()
    {
        $result['code'] = 0;
        $result['data'] = ApplicationBusiness::getCategoryAppList();
        foreach ($result['data'] as $key => &$val){
            $name = json_decode($val['name'], true);
            $val['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';
            if(!empty($val['apps'])){
                foreach($val['apps'] as $j => &$value){
                    $name = json_decode($value['name'], true);
                    $value['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';
                }
            }
        }
        return $result;
    }

    /**
     * 获取应用详情
     */
    public function actionAppInfo()
    {
        $request   = Yii::$app->request;
        $id      = $request->post('id',0);
        if($id){
            $result = ApplicationBusiness::appInfo($id);
            $data = $result['data'];
            $name = json_decode($data['name'], true);
            $data['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';
            return ['code' => 0,'data' => $data];
        }else{
            return ['code' => 1,'data' => [],'msg' => 'id不能为空'];
        }
    }

    /**
     * @return array
     * 添加应用用户数量
     */
    public function actionAddUserNum()
    {
        $request   = Yii::$app->request;
        $id      = $request->post('id',0);
        if($id){
            $key = "app_id_".$id;
            $isExist = Yii::$app->redis_app->hexists('application_user_num',$key);
            if($isExist){
                Yii::$app->redis_app->hincrby('application_user_num',$key,1);
            }else{
                $app_user_num = rand(1000,2000);
                Yii::$app->redis_app->hset('application_user_num',$key,$app_user_num);
            }
            return ['code' => 0,'data' => [], 'msg' => '应用用户数量更新成功'];
        }else{
            return ['code' => 1,'data' => [],'msg' => 'id不能为空'];
        }
    }

    /**
     * 应用排行榜
     */
    public function actionRankList()
    {
        $request   = Yii::$app->request;
        $type      = $request->post('type',1);
        $result['code'] = 0;
        $data = CoinApplicateRank::getAppList($type);
        $icon_Items = array_column($data,'icon');
        $icon_Infos = CoinImage::getItemsByIds($icon_Items);
        foreach($data as $key => &$value){
            if($value['icon']){
                $value['icon'] =  Yii::$app->params['service']['OssService']['url'].$icon_Infos[$value['icon']]['file_url'];
            }else{
                $value['icon'] = "";
            }
            $value['app_user_num'] = ApplicationBusiness::getAppUserNum($value['app_id']);
        }
        $result['data'] =  $data;
        return $result;
    }

    /**
     * 应用搜索
     */
    public function actionAppSearch()
    {
        $request   = Yii::$app->request;
        $name      = $request->get('name','');
        $cate_app_items = [];
        if($name){
            $appItems = CoinApplication::getAppItemsByName($name);
            if($appItems){
                $appIds = array_column($appItems,'app_id');
                $icon_Items = array_column($appItems,'icon');
                $icon_Infos = CoinImage::getItemsByIds($icon_Items);
                foreach($appItems as &$value){
                    $name = json_decode($value['name'], true);
                    $value['name'] = isset($name[$this->lang]) ? $name[$this->lang] : '';
                    if($value['icon']){
                        $value['icon'] = Yii::$app->params['service']['OssService']['url'].$icon_Infos[$value['icon']]['file_url'];
                    }else{
                        $value['icon'] = '';
                    }
                    $value['app_user_num'] = ApplicationBusiness::getAppUserNum($value['app_id']);
                }
                $cateItems = ApplicationBusiness::getCateInfoByAppIds($appIds);
                foreach($cateItems as $item){
                    $cate_id = $item['cate_id'];
                    $app_id = $item['app_id'];
                    $cate_app_items[$cate_id]['cate_id'] = $cate_id;
                    $name_arr = json_decode($item['name'], true);
                    $name = isset($name_arr[$this->lang]) ? $name_arr[$this->lang] : '';
                    $cate_app_items[$cate_id]['name'] = $name;
                    $cate_app_items[$cate_id]['apps'][] = $appItems[$app_id];
                }
                foreach ($cate_app_items as $item){
                    $categoryItems[] = $item;
                }
                return ['code' => 0,'data' => $categoryItems];
            }
        }
        return ['code' => 0,'data' => []];
    }

    /**
     * 分类下的应用列表
     */
    public function actionGetAppsByCate()
    {
        $request   = Yii::$app->request;
        $cate_id      = $request->get('cate_id','');
        if($cate_id){
            $cate_info = CoinApplicationCategory::getCategoryById($cate_id);
            $name = isset($cate_info->name[$this->lang]) ? $cate_info->name[$this->lang] : '';
            $cate_info_data['id'] = $cate_info->id;
            $cate_info_data['name'] = $name;
            $appItems = ApplicationBusiness::getCateAppInfo(0,[['cate_id' => $cate_id]]);
            if($appItems){
                $appItems = array_shift($appItems);
                foreach($appItems as &$value){
                    $value['app_user_num'] = ApplicationBusiness::getAppUserNum($value['app_id']);
                    $name_arr = json_decode($value['name'], true);
                    $value['name'] = isset($name_arr[$this->lang]) ? $name_arr[$this->lang] : '';
                }
                $cate_info_data['apps'] = $appItems;
                return ['code' => 0,'data' => $cate_info_data];
            }
        }
        return ['code' => 0,'data' => []];
    }

    /**
     * @return array
     * 获取热门搜索
     */
    public function actionGetHotSearch()
    {
        $redis = Yii::$app->redis_app;
        $data = $redis->zrange('application_hot_search',0,-1);
        $search_data = [];
        foreach($data as $value){
            $item['name'] = $value;
            $item['sort'] = $redis->zscore('application_hot_search',$value);
            $search_data[] = $item;
        }
        return [ 'code' => 0,'data' => $search_data];
    }

}