<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 下午5:47
 */

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\pwallet\News;

class ArticleController extends BaseController
{
    /**
     * 返回新闻列表
     */
    public function actionList()
    {
        $request   = Yii::$app->request;
        $page      = $request->post('page', 1);
        $limit     = $request->post('limit', 10);
        $post      = $request->post();
        $condition = [];

        $post = array_filter($post, function ($value, $key) {
            if ($key == 'status' && is_numeric($value)) {
                return true;
            }
            if ($key == 'id' && is_numeric($value)) {
                return true;
            }
            return $value;
        }, ARRAY_FILTER_USE_BOTH);

        if (isset($post['id'])) {
            $condition[] = ['id' => $post['id']];
        }
        if (isset($post['title'])) {
            $condition[] = ['like', 'title', $post['title']];
        }
        if (isset($post['status'])) {
            $condition[] = ['status' => $post['status']];
        }
        if (isset($post['create_at'])) {
            $condition[] = ['>=', 'create_at', $post['create_at']];
        }
        if (isset($post['update_at'])) {
            $condition[] = ['>=', 'update_at', $post['update_at']];
        }
        $data = News::getList($page, $limit, $condition);

        return $data;
    }
}