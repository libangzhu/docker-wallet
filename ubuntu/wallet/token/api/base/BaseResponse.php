<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午1:28
 */

namespace api\base;

use yii\web\Response;

class BaseResponse extends Response
{
    public function send()
    {
        //错误处理
        $excpetion = \Yii::$app->errorHandler->exception;
        if ($excpetion !== null) {
            $this->data = [
                'code' => $excpetion->getCode(),
                'msg'  => $excpetion->getMessage(),
                'line' => $excpetion->getLine(),
                'file' => $excpetion->getFile(),
            ];
        }
        //TODO 在这里对数据进行format，这样控制器中可以直接return一个array，保存到数据域data中即可，eg:['code'=>0,'data'=>$data]
        $data = \Yii::$app->response->data;
        if (empty($data)) {
            $return['code'] = 1;
            $return['msg']  = '数据为空';
        } elseif (is_array($data) && !isset($data['code'])) {
            $return['code']  = 0;
            $return['count'] = count($data);
            $return['data']  = $data;
        } else {
            $return = $data;
        }
        if (YII_ENV_DEV) {
            #$return['time'] = \Yii::$app->controller->end - \Yii::$app->controller->start;
        }
        \Yii::$app->response->data = $return;
        parent::send();
    }
}