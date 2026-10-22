<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/6/20
 * Time: 11:11
 */

namespace common\behaviors;

use api\base\ResponseMsg;
use yii\base\ActionFilter;
use common\models\Admin;
use Yii;

class UserAuthInterceptor extends ActionFilter
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $request_class = get_class($action->controller);
        $request_action = $action->id;
        if ('login' == $request_action || 'user-sync' == $request_action) {
            return true;
        }
        $token_string = Yii::$app->request->headers->get('Token');
        $user = Admin::verfication($token_string, '');
        if (-1 == $user['code']) {
            $code = '40001';
            $msg = $user['msg'];
            goto doEnd;
        }

        if (strpos(strtolower($user["data"]->username), 'kefu') !== false || strpos(strtolower($user["data"]->username), 'caiwu') !== false) {
            $handerClass = strtolower(Yii::$app->controller->id);
            if (in_array(strtolower($request_action), ['manual-list', 'wallet-ballance', 'user-info', 'edit-pwd', 'verify'])) {
                return true;
            }
            if (in_array(strtoupper($handerClass), ['ASSET', 'FLASH'])) {
                return true;
            }
            $code = '403';
            $msg = 'Permission Denied';
            goto doEnd;
        }

        return true;

        $group = $user["data"]->group;
        $user_id = $user["data"]->uid;
        $platform_id = $user["data"]->platform_id;
        Yii::$app->request->setGroup($group);
        Yii::$app->request->setUserId($user_id);
        Yii::$app->request->setPlatformId($platform_id);
        $user_auth = Yii::$app->params['user_auth']['user_auth'];
        $user_auth_map = $user_auth[$platform_id] ?? null;
        if (empty($user_auth_map)) {
            $code = '40001';
            $msg = 'platform auth error';
            goto doEnd;
        }
        $user_auth_map = $user_auth_map[$user_id] ?? null;
        if (empty($user_auth_map)) {
            $code = '40001';
            $msg = 'user auth error';
            goto doEnd;
        }

        $auth_type_map = Yii::$app->params['user_auth'][$user_auth_map];
        $auth_type_map = array_unique($auth_type_map, SORT_REGULAR);

        $switch = false;
        foreach ($auth_type_map as $key => $auth_type) {
            if (empty($auth_type)) continue;
            if ($request_class == $auth_type['class']) {
                $action_map = $auth_type['actions'];
                $switch = true;
                break;
            }
        }
        if (false == $switch) {
            $code = '40003';
            $msg = 'controller auth error';
            goto doEnd;
        }
        if (empty($action_map)) {
            return true;
        }
        if (in_array($request_action, $action_map)) {
            return true;
        } else {
            $code = '40004';
            $msg = 'action auth error';
            goto doEnd;
        }

        doEnd :
        // 返回错误
        $response_message = new ResponseMsg();
        $content = $response_message->jsonError($msg, $code);
        $content = $response_message->withHeaders($response_message->getHeaders())->withContent($content);
        Yii::$app->response->data = $content;
        Yii::$app->response->send();
        return false;
    }
}