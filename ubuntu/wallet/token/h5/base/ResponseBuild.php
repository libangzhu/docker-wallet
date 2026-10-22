<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 上午10:29
 */

namespace h5\base;

use Yii;
use yii\web\Response;
use h5\constant\ResponseConstant;

class ResponseBuild
{
    const STATUS_SUCCEED             = 0;
    const STATUS_PARAMS_NOT_VALIDATE = 1;
    const STATUS_INTERNAL_ERROR      = 2;

    public $code = self::STATUS_PARAMS_NOT_VALIDATE;
    public $msg  = '';
    public $data = [];

    public $format;

    public function __construct()
    {
        $this->format = Response::FORMAT_JSON;
    }

    public function build($code = 0, $msg = '', $data = [])
    {
        $this->code = $code;
        $msgs       = ResponseConstant::RESPONSE_MSG;
        if (empty($msg)) {
            $this->msg = $msgs[$code] ?? '';
        } else {
            $this->msg = $msg;
        }
        $this->data = $data;
    }

    public function format()
    {
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }
}