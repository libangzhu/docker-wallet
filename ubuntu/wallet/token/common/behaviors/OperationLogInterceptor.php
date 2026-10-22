<?php

namespace common\behaviors;

use Yii;
use linslin\yii2\curl\Curl;
use yii\base\ActionFilter;
use wallet\base\ResponseMsg;

class OperationLogInterceptor extends ActionFilter
{
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }

        $method = strtolower(Yii::$app->request->method);
        $handerClass = strtolower(Yii::$app->controller->id);
        $handlerAction = strtolower(Yii::$app->controller->action->id);

        if ('login' == $handlerAction) {
            return true;
        }
//        if (!isset(Yii::$app->params['handleTableAndDesc']['handleTableAndDesc'][$handerClass][$handlerAction][$method])) {
//            return false;
//        }
        if ('asset' == $handerClass) {
            $uri = Yii::$app->request->post('uri', '');
            $params = [
                'ip' => Yii::$app->request->userIP,
                'operation' => '操作',
                'business' => isset(Yii::$app->params['handleTableAndDesc']['handleTableAndDesc'][$handerClass][$handlerAction][$uri]) ? Yii::$app->params['handleTableAndDesc']['handleTableAndDesc'][$handerClass][$handlerAction][$uri] : $uri,
                'table' => 'asset'
            ];
        } else {
            switch ($method) {
                case "get":
                    $operation = '查询';
                    break;
                case 'post':
                    $operation = '增加';
                    break;
                case 'put':
                    $operation = '更新';
                    break;
                case 'delete':
                    $operation = '删除';
                    break;
                default:
                    $operation = '未知';
            }

            $params = [
                'ip' => Yii::$app->request->userIP,
                'operation' => $operation,
                'business' => Yii::$app->params['handleTableAndDesc']['handleTableAndDesc'][$handerClass][$handlerAction][$method] ?? $handerClass . '-' . $handlerAction,
                'table' => Yii::$app->params['handleTableAndDesc']['handleTableAndDesc'][$handerClass][$handlerAction]['table'] ?? '未知'
            ];
        }

        $token_string = Yii::$app->request->headers->get('Token');

        $curl = new Curl();
        $response = $curl->setRequestBody(json_encode($params))
            ->setHeaders([
                'Content-Type' => 'application/json',
                'Token' => $token_string
            ])
            ->post(Yii::$app->params['go_biqianbao'] . '/api/log');
        $response = json_decode($response, true);
        if (!is_array($response) || 0 != $response['code']) {
            $msg = '同步日志错误';
            $code = -1;
            goto doEnd;
        }

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
}