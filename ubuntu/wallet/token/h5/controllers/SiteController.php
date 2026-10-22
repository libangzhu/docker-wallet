<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 上午10:08
 */

namespace h5\controllers;

use h5\base\BaseController;
use h5\base\ResponseBuild;

class SiteController extends BaseController
{
    public function actionIndex()
    {
        $response = new ResponseBuild();
        $response->build(ResponseBuild::STATUS_SUCCEED, 'welcome to h5 api!');
        return $response;
    }
}