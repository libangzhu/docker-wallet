<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 下午1:28
 */

namespace h5\base;

use Yii;
use yii\web\Response;

class BaseResponse extends Response
{
    public function send()
    {
        $exception = Yii::$app->errorHandler->exception;
        if ($exception) {
            $this->format = Response::FORMAT_JSON;
            $this->data   = ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
        } else {
            /**
             * @var ResponseBuild $response
             */
            $response                   = Yii::$app->response->data;
            Yii::$app->response->format = $response->format;
            Yii::$app->response->data   = $response->format();
        }
        parent::send();
    }
}