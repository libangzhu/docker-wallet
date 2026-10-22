<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-7
 * Time: 下午4:52
 */

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\pwallet\Feedback;

/**
 * 用户反馈接口
 * Class FeedbackController
 * @package api\controllers
 */
class FeedbackController extends BaseController
{
    /**
     * 添加反馈
     */
    public function actionAdd()
    {
        $reqest  = Yii::$app->request;
        $type    = $reqest->post('type', 0);
        $content = $reqest->post('content', null);
        $connect = $reqest->post('connect', null);
        try {
            if (empty($content)) {
                throw new \Exception('反馈内容不能为空', 1);
            }
            $feedback            = new Feedback();
            $feedback->type      = $type;
            $feedback->content   = $content;
            $feedback->connect   = $connect;
            $feedback->create_at = date('Y-m-d H:i:s');
            $feedback->save();
            return ['code' => 0, 'msg' => 'succeed'];
        } catch (\Exception $exception) {
            return ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
        }
    }
}