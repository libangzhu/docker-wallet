<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午10:29
 */

namespace h5\base;

use yii\web\Controller;

class BaseController extends Controller
{
    public $start;
    public $end;

    public $header_list = [];
    public $lang;

    private static $default_header_list = [];

    public function init()
    {
        if ('cli' !== php_sapi_name()) {
            $this->header_list = self::$default_header_list;
            $this->fzmCrossHeader();
        }
    }

    public function fzmCrossHeader()
    {
        $this->lang = \Yii::$app->request->headers->get('lang') ?? 'zh-CN';
        if ('en' == strtolower($this->lang)) {
            $this->lang = 'en-US';
        }
    }
    /**
     * @param $action
     * @return bool
     * @throws \yii\web\BadRequestHttpException
     */
    public function beforeAction($action)
    {
        $this->start = microtime(true);
        return parent::beforeAction($action);
    }

    /**
     * @param \yii\base\Action $action
     * @param mixed            $result
     * @return mixed
     */
    public function afterAction($action, $result)
    {
        $this->end = microtime(true);
        return parent::afterAction($action, $result);
    }
}