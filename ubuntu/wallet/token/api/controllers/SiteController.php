<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午10:29
 */

namespace api\controllers;

use api\base\BaseController;

class SiteController extends BaseController
{
    public function actionIndex()
    {
        return ['code' => '0', 'msg' => 'welcome to api!'];
    }

    /**
     * [通用Error错误处理]
     *
     * @author: libingke
     * @return string
     */
    public function actionError()
    {

    }
}