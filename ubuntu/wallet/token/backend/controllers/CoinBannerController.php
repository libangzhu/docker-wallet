<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/18
 * Time: 16:24
 */

namespace backend\controllers;

use common\models\psources\CoinApplicateRecommend;
use common\models\psources\CoinApplication;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinBannerItem;
use common\models\psources\CoinImage;
use Yii;


class CoinBannerController extends BaseController
{
    /**
     * @return array
     * banner添加
     */
    public function actionAdd()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $image_url    = $request->post('image_url', '');
            $banner_url  = $request->post('banner_url','');
            if($image_url){
                $banner_item = new CoinBannerItem();
                $banner_item->banner_url = $banner_url;
                $banner_item->image_url = $image_url;
                $banner_item->platform_id = $user_platform_id;
                $banner_item->save();
                return ['code' => 0,'msg' => 'banner添加成功'];
            }else{
                return ['code' => 1,'msg' => 'banner添加加失败'];
            }
        }
    }

    /**
     * @return array
     * banner删除
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id    = $request->get('id', '');
            if($id){
                $banner = CoinBannerItem::getBanner($id);
                if(!$banner){
                    return ['code' => 1,'msg' =>'banner不存在,不能删除'];
                }
                $banner->delete();
                return ['code' => 0,'msg' => 'banner删除成功'];
            }else{
                return ['code' => 1 ,'msg' => 'banner删除失败'];
            }
        }
    }

    /**
     * banner列表
     */
    public function actionIndex()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        if(Yii::$app->request->isAjax){
            Yii::$app->response->format = 'json';
            $condition = [];
            if(1 !== $user_platform_id){
                $condition = ['platform_id' => $user_platform_id];
            }
            $data = CoinBannerItem::getItems($condition);
            foreach ($data as $key => &$val){
                $val['coin_name'] = isset($val['platform']['name']) ? $val['platform']['name'] : '';
            }
            return [ 'data' =>$data, 'code' => 0];
        }
        return $this->render('index');
    }
    
}