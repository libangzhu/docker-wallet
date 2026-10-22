<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/13
 * Time: 15:52
 */

namespace h5\controllers;

use common\business\ApplicationBusiness;
use common\models\psources\CoinApplicationCategory;
use h5\base\ResponseBuild;
use h5\base\BaseController;
use Yii;

/**
 * Class CoinController
 * 应用控制器
 *
 * @package h5\controllers
 */
class ApplicationController extends BaseController
{
    /**
     * @return ResponseBuild
     * 探索模块首页
     */
    public function actionIndex()
    {
        $response_ = new ResponseBuild();
        $recommendData = ApplicationBusiness::getRecommendList();
        $data['recommend'] =$recommendData;
        $cate_app_data = ApplicationBusiness::getH5CategoryAppList(8);
        $data['cate_app_data'] = $cate_app_data;
        $data['rank_list'] = ApplicationBusiness::rankList();
        $response_->build(ResponseBuild::STATUS_SUCCEED, '', $data);
        return $response_;
    }

    /**
     * 分类下的应用列表
     */
    public function actionGetAppsByCate()
    {
        $response_ = new ResponseBuild();
        $request   = Yii::$app->request;
        $cate_id      = $request->get('cate_id','');
        $cate_info_data = [];
        if($cate_id){
            $cate_info = CoinApplicationCategory::getCategoryById($cate_id);
            if($cate_info){
                $cate_info_data['id'] = $cate_info->id;
                $cate_info_data['name'] = $cate_info->name;
                $appItems = ApplicationBusiness::getH5CateAppInfo(0,[['cate_id' => $cate_id]]);
                if($appItems){
                    $appItems = array_shift($appItems);
                    foreach($appItems as &$value){
                        $value['app_user_num'] = ApplicationBusiness::getAppUserNum($value['app_id']);
                    }
                    $cate_info_data['apps'] = $appItems;
                }
            }
        }
        $response_->build(ResponseBuild::STATUS_SUCCEED, '', $cate_info_data);
        return $response_;
    }

    /**
     * 获取应用详情
     */
    public function actionAppInfo()
    {
        $response_ = new ResponseBuild();
        $request   = Yii::$app->request;
        $id      = $request->post('id',0);
        $data = [];
        if($id){
           $data =  ApplicationBusiness::h5AppInfo($id);
        }
        $response_->build(ResponseBuild::STATUS_SUCCEED, '', $data);
        return $response_;
    }

}