<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-13
 * Time: 上午10:41
 */

namespace api\controllers;

use Yii;
use api\base\BaseController;
use common\models\pwallet\Member;

class MemberController extends BaseController
{
    /**
     * @return array
     * @throws \Exception
     */
    public function actionAddOne()
    {
        $post                = Yii::$app->request->post();
        $member              = new Member();
        $member->scenario    = Member::SCENARIO_ADD;
        $post['register_at'] = date('Y-m-d H:i:s');
        if ($member->load($post) && $member->validate()) {
            //加密password
            //加盐
            try {
                $member->addOne();
                return ['code' => 0, 'msg' => 'succeed'];
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
        if ($member->hasErrors()) {
            $errors = $member->errors;
            foreach ($errors as $error) {
                throw  new \Exception($error[0], 1);
            }
        }
    }
}