<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/17
 * Time: 19:56
 */

namespace common\business;

use common\models\psources\CoinAppCate;
use common\models\psources\CoinApplicateRank;
use common\models\psources\CoinApplicateRecommend;
use common\models\psources\CoinApplication;
use common\models\psources\CoinApplicationCategory;
use common\models\psources\CoinBanner;
use common\models\psources\CoinBannerItem;
use common\models\psources\CoinImage;
use service\risk\constant\CoinCate;
use Yii;

class ApplicationBusiness
{

    /**
     * @return array|\yii\db\ActiveRecord[]
     * 获取首页推荐
     */
    public static function getRecommendList($condition = [])
    {
        $data = CoinApplicateRecommend::getRecommendList($condition);
        $icon_Items = array_column($data, 'icon');
        $icon_Infos = CoinImage::getItemsByIds($icon_Items);
        $disabe_applicate_Ids = CoinApplication::getDisableIds($condition);
        $disabe_applicate_category_Ids = CoinApplicationCategory::getDisableIds($condition);
        foreach ($data as $key => &$value) {
            if ($value['type'] == 1) {
                if (in_array($value['app_cate_id'], $disabe_applicate_category_Ids)) {
                    unset($data[$key]);
                    continue;
                }
            } else if ($value['type'] == 2) {
                if (in_array($value['app_cate_id'], $disabe_applicate_Ids)) {
                    unset($data[$key]);
                    continue;
                }
            }
            if ($value['icon']) {
                $value['icon'] = Yii::$app->params['service']['OssService']['url'] . $icon_Infos[$value['icon']]['file_url'];
            } else {
                $value['icon'] = '';
            }
        }
        return array_values($data);
    }

    public static function getCategoryAppList($condition = [])
    {
        $condition = array_merge($condition, ['enable' => 1]);
        $data = CoinApplicationCategory::getAllList($condition);
        $cate_app_data = [];
        $icon_Items = array_column($data, 'icon');
        $banner_Items = array_column($data, 'banner');
        $image_Items = array_merge($icon_Items, $banner_Items);
        $icon_Infos = CoinImage::getItemsByIds($image_Items);
        unset($condition['enable']);
        $limit = 5;
        if (isset($condition['platform_id']) && 26 == $condition['platform_id']) {
            $limit = 15;
        }
        $cate_app_Infos = self::getCateAppInfo($limit, $condition);
        foreach ($data as $value) {
            $cate_app_item['id'] = $value['id'];
            $cate_app_item['name'] = $value['name'];
            if ($value['icon']) {
                $cate_app_item['icon_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_Infos[$value['icon']]['file_url'];
            } else {
                $cate_app_item['icon_url'] = '';
            }
            if ($value['banner']) {
                $cate_app_item['banner_image_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_Infos[$value['banner']]['file_url'];
            } else {
                $cate_app_item['banner_image_url'] = '';
            }
            $cate_app_item['banner_url'] = $value['banner_url'];

            if (isset($cate_app_Infos[$value['id']])) {   //分类下面有应用
                $cate_app_item['apps'] = $cate_app_Infos[$value['id']];
            } else {
                $cate_app_item['apps'] = [];
            }
            $cate_app_data[] = $cate_app_item;
            $cate_app_item = [];
        }
        return $cate_app_data;
    }

    /**
     * @return mixed
     * 获取分类以及应用
     */
    public static function getCateAppInfo($limit = 0, $condition = [])
    {
        $app_cate_Model = CoinAppCate::find();
        if (array_key_exists('platform_id', $condition)) {
            $query = $app_cate_Model->JoinWith(['application'], false)
                ->select('cate_id,app_id,name,icon,type,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url,advertise,redirect_type,open_type_app,platform_type,' . CoinAppCate::tableName() . '.sort')
                ->where($condition)
                ->orderBy(CoinAppCate::tableName() . '.sort asc');
        } else {
            $query = $app_cate_Model->JoinWith(['application'], false)
                ->select('cate_id,app_id,name,icon,type,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url,advertise,redirect_type,open_type_app,platform_type,' . CoinAppCate::tableName() . '.sort')
                ->orderBy(CoinAppCate::tableName() . '.sort asc');
        }

        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $query->andWhere(['enable' => 1]);
        $data = $query->asArray()->all();
        $icon_Items = array_column($data, 'icon');
        $icon_Items = array_unique($icon_Items);
        $icon_Infos = CoinImage::getItemsByIds($icon_Items);
        $cate_app_item = [];
        foreach ($data as &$value) {
            if ($value['icon']) {
                $value['icon'] = isset($icon_Infos[$value['icon']]) ? Yii::$app->params['service']['OssService']['url'] . $icon_Infos[$value['icon']]['file_url'] : '';
            } else {
                $value['icon'] = '';
            }
            $value['app_user_num'] = self::getAppUserNum($value['app_id']);
        }
        foreach ($data as $item) {
            if ($limit && isset($cate_app_item[$item['cate_id']]) && count($cate_app_item[$item['cate_id']]) >= $limit) {
                continue;
            } else {
                $cate_app_item[$item['cate_id']] [] = $item;
            }
        }
        return $cate_app_item;
    }

    public static function appInfo($id)
    {
        $app_item = CoinApplication::getApplicateById($id);
        if ($app_item) {
            if ($app_item['icon']) {
                $icon_item = CoinImage::getItemById($app_item['icon']);
                $app_item['icon'] = $icon_item['base_url'] . $icon_item['file_url'];
            } else {
                $app_item['icon'] = "";
            }
            if ($app_item['image_ids']) {
                $image_items = CoinImage::getItemsByImageIds($app_item['image_ids']);
                $app_item['images'] = array_column($image_items['data'], 'image_url');
            } else {
                $app_item['images'] = [];
            }
            unset($app_item['image_ids']);
            $app_user_num = self::getAppUserNum($id);
            $app_item['app_user_num'] = $app_user_num;
            $unsetfields = ['h5_icon', 'official_url', 'introduce_image', 'h5_image_ids', 'open_type', 'show_width', 'show_height'];
            foreach ($unsetfields as $field) {
                unset($app_item[$field]);
            }
            return ['code' => 0, 'data' => $app_item];
        }
        return ['code' => 1, 'data' => [], 'msg' => "id不合法"];
    }

    public static function getAppUserNum($id)
    {
        $key = "app_id_" . $id;
        $app_user_num = Yii::$app->redis_app->hget('application_user_num', $key);
        if (!$app_user_num) {
            $app_user_num = rand(1000, 2000);
            Yii::$app->redis_app->hset('application_user_num', $key, $app_user_num);
        }
        return $app_user_num;
    }

    public static function getCateInfoByAppIds($appIds)
    {
        $app_cate_Model = CoinAppCate::find();
        $data = $app_cate_Model->JoinWith(['category'], false)
            ->select('cate_id,app_id,name')
            ->where(['in', 'app_id', $appIds])
            ->andWhere(['enable' => 1])
            ->orderBy(CoinApplicationCategory::tableName() . '.sort asc')->asArray()->all();
        return $data;
    }


    public static function getH5CategoryAppList($limit = 0)
    {
        $data = CoinApplicationCategory::getAllList(['enable' => 1]);
        $cate_app_data = [];
        $icon_Items = array_column($data, 'icon');
        $icon_Infos = CoinImage::getItemsByIds($icon_Items);
        $banner_data = array_column($data, 'h5_banner');
        $banner_items = [];
        foreach ($banner_data as $item) {
            if ($item) {
                $banner_items = array_merge($banner_items, explode(',', $item));
            }
        }
        $banner_infos = CoinBanner::getBannerDataByIds($banner_items);
        $cate_app_Infos = self::getH5CateAppInfo($limit);
        foreach ($data as $value) {
            $cate_app_item['id'] = $value['id'];
            $cate_app_item['name'] = $value['name'];
            if ($value['icon']) {
                $cate_app_item['icon_url'] = Yii::$app->params['service']['OssService']['url'] . $icon_Infos[$value['icon']]['file_url'];
            } else {
                $cate_app_item['icon_url'] = '';
            }
            if ($value['h5_banner']) {
                $banner_ids = explode(',', $value['h5_banner']);
                foreach ($banner_ids as $banner_id) {
                    $cate_app_item['h5_banner'][] = $banner_infos[$banner_id];
                }
            } else {
                $cate_app_item['h5_banner'] = [];
            }
            if (isset($cate_app_Infos[$value['id']])) {   //分类下面有应用
                $cate_app_item['apps'] = $cate_app_Infos[$value['id']];
            } else {
                $cate_app_item['apps'] = [];
            }
            $cate_app_data[] = $cate_app_item;
            $cate_app_item = [];
        }
        return $cate_app_data;
    }

    /**
     * @return mixed
     * 获取分类以及应用(H5)
     */
    public static function getH5CateAppInfo($limit = 0, $condition = [])
    {
        $app_cate_Model = CoinAppCate::find();
        $query = $app_cate_Model->JoinWith(['application'], false)
            ->select('cate_id,app_id,name,h5_icon,show_width,show_height,introduce_image,official_url,advertise,type,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url,redirect_type,open_type,' . CoinAppCate::tableName() . '.sort')
            ->orderBy(CoinAppCate::tableName() . '.sort asc');
        foreach ($condition as $item) {
            $query = $query->andWhere($item);
        }
        $query = $query->andWhere(['enable' => 1]);
        $data = $query->asArray()->all();
        $icon_Items = array_column($data, 'h5_icon');
        $introduce_image_Items = array_column($data, 'introduce_image');
        $icon_Items = array_unique($icon_Items);
        $image_Items = array_merge($icon_Items, $introduce_image_Items);
        $image_Infos = CoinImage::getItemsByIds($image_Items);
        $cate_app_item = [];
        foreach ($data as &$value) {
            if ($value['h5_icon']) {
                $value['h5_icon'] = $image_Infos[$value['h5_icon']]['base_url'] . $image_Infos[$value['h5_icon']]['file_url'];
            } else {
                $value['h5_icon'] = '';
            }
            if ($value['introduce_image']) {
                $value['introduce_image'] = $image_Infos[$value['introduce_image']]['base_url'] . $image_Infos[$value['introduce_image']]['file_url'];
            } else {
                $value['introduce_image'] = '';
            }
        }
        foreach ($data as $item) {
            if ($limit && isset($cate_app_item[$item['cate_id']]) && count($cate_app_item[$item['cate_id']]) >= $limit) {
                continue;
            } else {
                $cate_app_item[$item['cate_id']] [] = $item;
            }
        }
        return $cate_app_item;
    }

    public static function rankList()
    {
        $data = CoinApplicateRank::getH5AppList();
        $icon_Items = array_column($data, 'h5_icon');
        $introduce_image_Items = array_column($data, 'introduce_image');
        $image_items = array_merge($icon_Items, $introduce_image_Items);
        $image_Infos = CoinImage::getItemsByIds($image_items);
        foreach ($data as $key => &$value) {
            if ($value['h5_icon']) {
                $value['h5_icon'] = $image_Infos[$value['h5_icon']]['base_url'] . $image_Infos[$value['h5_icon']]['file_url'];
            } else {
                $value['h5_icon'] = "";
            }
            if ($value['introduce_image']) {
                $value['introduce_image'] = $image_Infos[$value['introduce_image']]['base_url'] . $image_Infos[$value['introduce_image']]['file_url'];
            } else {
                $value['introduce_image'] = "";
            }
            $value['app_user_num'] = self::getAppUserNum($value['app_id']);
            if ($value['rank_type'] == 1) {  //热度榜
                $rank['hot'][] = $value;
            } else if ($value['rank_type'] == 2) { //新品榜
                $rank['new'][] = $value;
            }
        }
        return $rank;
    }

    public static function h5AppInfo($id)
    {
        $app_item = CoinApplication::getH5ApplicateById($id);
        if ($app_item) {
            if ($app_item['h5_icon']) {
                $icon_item = CoinImage::getItemById($app_item['h5_icon']);
                $app_item['h5_icon'] = $icon_item['base_url'] . $icon_item['file_url'];
            } else {
                $app_item['h5_icon'] = "";
            }
            if ($app_item['h5_image_ids']) {
                $image_items = CoinImage::getItemsByImageIds($app_item['h5_image_ids']);
                $app_item['images'] = array_column($image_items['data'], 'image_url');
            } else {
                $app_item['images'] = [];
            }
            unset($app_item['h5_image_ids']);
            $app_user_num = self::getAppUserNum($id);
            $app_item['app_user_num'] = $app_user_num;
            $category_ids = CoinAppCate::getCateItemsByAppId($id);
            $category_infos = CoinApplicationCategory::getCateItemsByIds($category_ids);
            $category_names = '';
            foreach ($category_infos as $item) {
                if ($category_names) {
                    $category_names .= ',' . $item['name'];
                } else {
                    $category_names = $item['name'];
                }
            }
            $app_item['category'] = $category_names;

        }
        return $app_item;
    }

    public static function getBannerList($condition = [])
    {
        return CoinBannerItem::getItems($condition);
    }
}