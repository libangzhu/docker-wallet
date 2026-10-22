<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/6/20
 * Time: 11:11
 */

namespace common\behaviors;

use Yii;
use common\models\Admin;
use api\base\ResponseMsg;
use common\components\Response;
use yii\base\ActionFilter;

class LoginStatusAuthInterceptor extends ActionFilter
{
    public function beforeAction($action)
    {
        $request_class = get_class($action->controller);
        $request_action = $action->id;
        if ('login' == $request_action || 'user-sync' == $request_action) {
            return true;
        }
        $token_string = Yii::$app->request->headers->get('Token');
        if (false == $token_string) {
            $msg = 'platform auth error';
            $code = '40001';
            goto doEnd;
        }
        $user = Admin::verfication($token_string, '');
        if (-1 == $user['code']) {
            $code = '40001';
            $msg = $user['data'];
            goto doEnd;
        }
        $group = $user["data"]->group;
        $user_id = $user["data"]->uid;
        $platform_id = $user["data"]->platform_id;
        Yii::$app->request->setGroup($group);
        Yii::$app->request->setUserId($user_id);
        Yii::$app->request->setPlatformId($platform_id);
        return true;

        doEnd :
        // 返回错误
        $response_message = new ResponseMsg();
        $content = $response_message->jsonError($msg, $code);
        $content = $response_message->withHeaders($response_message->getHeaders())->withContent($content);
        Yii::$app->response->data = $content;
        Yii::$app->response->send();
        return false;
    }

    public function frontAuth()
    {
        //验证用户token正确性
    }
}