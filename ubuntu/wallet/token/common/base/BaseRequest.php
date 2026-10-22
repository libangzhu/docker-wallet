<?php
/**
 * Created by PhpStorm.
 * User: jiaming
 * Date: 2019/7/17
 * Time: 11:11
 */

namespace common\base;

use yii\web\Request;

class BaseRequest extends Request
{
    private $user_id = 0;
    private $platform_id = 0;
    private $group = '';

    public function getUserId()
    {
        return $this->user_id;
    }

    public function setUserId($value)
    {
        $this->user_id = $value;
    }

    public function getPlatformId()
    {
        return $this->platform_id;
    }

    public function setPlatformId($value)
    {
        $this->platform_id = $value;
    }

    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup($value)
    {
        $this->group = $value;
    }
}