<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/1
 * Time: 17:14
 */

namespace backend\controllers;

use common\models\psources\CoinApplicateRank;
use common\models\psources\CoinApplicateRecommend;
use common\models\psources\CoinApplication;
use Yii;


class ApplicateRankController extends BaseController
{
    /**
     * @return array
     * 应用排行榜添加
     */
    public function actionAdd()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request->post();
            $applicate_id = $request['applicate_id'];
            $type = $request['type'];
            if($applicate_id && $type){
                $applicate = CoinApplication::getApplicateById($applicate_id);
                if(!$applicate){
                    return ['code' => 1, 'msg' => '应用不存在'];
                }
                $applicate_rank = CoinApplicateRank::getApplicate($applicate_id, $type);
                if($applicate_rank){
                    return ['code' => 1, 'msg' => '应用已添加，不能再添加'];
                }
                $rank = new CoinApplicateRank();
                $rank->relate_id = $applicate_id;
                $rank->type = $type;
                $rank->platform_id = Yii::$app->user->identity->platform_id;
                $rank->save();
                return ['code' => 0, 'msg' => '添加成功'];
            }else{
                return ['code' => 1, 'msg' => '添加失败'];
            }
        }
    }

    /**
     * @return array
     * 应用排行榜删除
     */
    public function actionDelete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $type = $request->get('type', '');
            if ($id && $type) {
                $rank = CoinApplicateRank::getApplicate($id, $type);
                if (!$rank) {
                    return ['code' => 1, 'msg' => '应用不存在,不能删除'];
                }
                $rank->delete();
                return ['code' => 0, 'msg' => '应用删除成功'];
            } else {
                return ['code' => 1, 'msg' => '应用删除失败'];
            }
        }
    }

    /**
     * 排行榜列表
     */
    public function actionIndex()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;
        $condition = [];
        if(1 !== $user_platform_id){
            $condition = ['platform_id' => $user_platform_id];
        }
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $type = $request->get('type', '');
            $data = CoinApplicateRank::getApplicateList($type, $user_platform_id);
            return ['data' => $data, 'code' => 0];
        }
        $items = CoinApplication::getAllApplicate($condition);
        return $this->render('index',['items' => $items]);
    }

    /**
     * @return array
     * 应用排行榜设置排序
     */
    public function actionSetSort()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $id = $request->get('id', '');
            $type = $request->get('type', '');
            $sort = $request->get('sort', 1);
            if ($id && $type) {
                $rank = CoinApplicateRank::getApplicate($id, $type);
                if (!$rank) {
                    return ['code' => 1, 'msg' => '应用不存在,不能设置排序'];
                }
                $rank->sort = $sort;
                $rank->save();
                return ['code' => 0, 'msg' => '排序设置成功'];
            } else {
                return ['code' => 1, 'msg' => '排序设置失败'];
            }
        }
    }

    /**
     * @return array
     * 添加热门搜索
     */
    public function actionAddSearch()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request->post();
            $serach_name = $request['name'];
            if($serach_name){
                Yii::$app->redis_app->zadd('application_hot_search',1,$serach_name);
                return ['code' => 0, 'msg' => '添加成功'];
            }else{
                return ['code' => 1, 'msg' => '添加失败'];
            }
        }
    }

    /**
     * @return array
     * 热门搜索列表
     */
    public function actionSearchIndex()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $redis = Yii::$app->redis_app;
            $data = $redis->zrange('application_hot_search',0,-1);
            $search_data = [];
            foreach($data as $value){
                $item['name'] = $value;
                $item['sort'] = $redis->zscore('application_hot_search',$value);
                $search_data[] = $item;
            }
            return ['data' => $search_data, 'code' => 0];
        }
    }

    /**
     * @return array
     * 热门搜索删除
     */
    public function actionSearchDelete()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $name = $request->get('name', '');
            if ($name) {
                Yii::$app->redis_app->zrem('application_hot_search',$name);
                return ['code' => 0, 'msg' => '热门搜索删除成功'];
            } else {
                return ['code' => 1, 'msg' => '热门搜索删除失败'];
            }
        }
    }

    /**
     * @return array
     * 热门搜索设置排序
     */
    public function actionSetSearchSort()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $name = $request->get('name', '');
            $sort = $request->get('sort', 1);
            if ($name) {
                Yii::$app->redis_app->zadd('application_hot_search',$sort,$name);
                return ['code' => 0, 'msg' => '排序设置成功'];
            } else {
                return ['code' => 1, 'msg' => '排序设置失败'];
            }
        }
    }



}