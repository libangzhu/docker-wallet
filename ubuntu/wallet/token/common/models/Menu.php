<?php

namespace common\models;

use Yii;
use common\helpers\ArrayHelper;

class Menu extends \common\modelsgii\Menu
{
    const BASE_KEY = 'cache_menu_';

    /**
     * 配置model规则
     */
    public function rules()
    {
        return [
            [['title','url'],'required'],
            [['pid', 'sort', 'hide', 'status'], 'integer'],
            [['title', 'group', 'icon'], 'string', 'max' => 50],
            [['url'], 'string', 'max' => 255]
        ];
    }

    /**
     * [栏目权限检测]
     * @author  libingke
     * @param   string  $rule   检测的规则
     * @return  bool
     */
    public static function checkRule($rule)
    {
        /* 超级管理员允许访问任何页面 */
        if(Yii::$app->params['admin'] == Yii::$app->user->id){
            return true;
        }

        /* rbac */
        if (!\Yii::$app->user->can($rule))
            return false;
        return true;

    }

    public static function getPermissions()
    {
        $authManager = Yii::$app->authManager;
        $user = Yii::$app->user;
        if (Yii::$app->params['admin'] == $user->id) {
            $can_permissions = $authManager->getPermissions();
        }else{
            $can_permissions = $authManager->getPermissionsByUser($user->id);
        }
        $can_permission_keys = array_keys($can_permissions);
        return $can_permission_keys;
    }

    /**
     * [获取导航缓存]
     * @author: libingke
     */
    public static function getBreadcrumbs()
    {
        $uid = 'base';
        $key = static::BASE_KEY . $uid;
        $cache = Yii::$app->cache;
        $data = $cache->get($key);
        $data = false;
        if ($data === false) {
            $all = static::find()
                ->where(['hide' => static::HIDE_NO, 'status' => static::STATUS_SHOW])
                ->orderBy('pid ASC,sort ASC')->asArray()->all();
            $can_permission_keys = self::getPermissions();
            $data = static::menuTree($all, 0, $can_permission_keys);
            $cache->set($key, $data);
        }

        return $data;
    }

    /**
     * [刷新数据]
     * @author: libingke
     * @param null $uid
     */
    public static function flushMenu($uid = null)
    {
        $key = $uid == null ? $key = static::BASE_KEY . 'base' : static::BASE_KEY . $uid;
        Yii::$app->cache->delete($key);
    }


    /**
     * [导航树]
     * @author: libingke
     * @param $all
     * @param $pid
     * @return array
     */
    public static function menuTree($all, $pid, &$can_permission_keys)
    {
        $tree = [];
        foreach ($all as $v) {
            if (!in_array($v['url'], $can_permission_keys)) {
                continue;
            }
            if ($v['pid'] == $pid) {
                $notes = static::menuTree($all, $v['id'], $can_permission_keys);
                $temp = [];
                $temp['id']     = $v['id'];
                $temp['label']  = $v['title'];
                if ($pid == 0) {
                    $temp['url']    = $notes ? 'javascript:;' : \yii\helpers\Url::toRoute($v['url']);
                    $temp['icon']   = '';
                } else {
                    $temp['url']    = \yii\helpers\Url::toRoute($v['url']);
                    $temp['icon']   = $v['icon'];
                }
                //子节点
                $notes ? $temp['notes'] = $notes : null;

                $tree[] = $temp;
            }
        }
        return $tree;
    }


    /**
     * ---------------------------------------
     * 根据menu库，返回权限节点，或后台菜单
     * @param boolean $tree 是否返回多维数组结构(生成菜单时用到),为false返回一维数组(生成权限节点时用到)
     * @return array
     * ---------------------------------------
     */
    public static function returnNodes($tree = true){
        /* 如果已经生成，直接调用 */
        static $tree_nodes = array();
        if ( $tree && !empty($tree_nodes[(int)$tree]) ) {
            return $tree_nodes[$tree];
        }
        /* 生成节点 */
        if((int)$tree){
            $list = (new \yii\db\Query())
                ->select(['id','pid','title','url','hide'])
                ->from(Menu::tableName())
                ->orderBy(['sort'=>SORT_ASC])->all();
            $can_permission_keys = self::getPermissions();
            foreach ($list as $key => $value) {
                if (!in_array($value['url'], $can_permission_keys)) {
                    unset($list[$key]);
                }
            }
            $nodes = ArrayHelper::list_to_tree($list, $pk='id', $pid='pid', $child='child', $root=0);
        }else{
            $nodes = (new \yii\db\Query())
                ->select(['title','url','tip','pid'])
                ->from(Menu::tableName())
                ->orderBy(['sort'=>SORT_ASC])->all();
            foreach ($nodes as $key => $value) {
                if (!in_array($value['url'], $can_permission_keys)) {
                    unset($nodes[$key]);
                }
            }
        }
        /* 节点赋值到静态变量中，以供下次调用 */
        $tree_nodes[(int)$tree]   = $nodes;

        return $nodes;
    }


    /**
     * ---------------------------------------
     * 递归获取其所有父栏目
     * @param $id
     * @return array
     * ---------------------------------------
     */
    public static function getParentMenus($id){
        $path = [];
        $nav = static::find()->select(['id','pid','title', 'url'])->where(['id'=>$id])->asArray()->one();
        $path[] = $nav;
        if($nav['pid'] > 0){
            $path = array_merge(static::getParentMenus($nav['pid']),$path);
        }
        return $path;
    }
}
