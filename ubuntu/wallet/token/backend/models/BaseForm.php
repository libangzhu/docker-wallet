<?php

/**
    @author ZCY
 */

namespace backend\models;

use Yii;
use yii\base\Model;

class BaseForm extends Model
{

    public static function getModelError($model)
    {
        $error = null;
        $errors = $model->getErrors();
        foreach ($errors as $key => $value) {
            $v = $value[0];
            $error = $v;
            break;
        }
        return $error;
    }

}