<?php

namespace backend\controllers;

use common\models\AdminSession;
use common\models\NewLoginForm;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\helpers\Url;
use Yii;

/**
 * 后台登录控制器
 */
class LoginController extends Controller
{

    public $layout = false;

    public $enableCsrfValidation = false;

    public $defaultAction = 'login';

    /**
     * ---------------------------------------
     * 行为控制
     * ---------------------------------------
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => ['login', 'error', 'logout'],
                        'allow'   => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
        ];
    }

    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $error = null;
        $name  = isset(Yii::$app->request->post('info')['username']) ? Yii::$app->request->post('info')['username'] : null;
        if (Yii::$app->request->isPost) {
            $model = new NewLoginForm();
            if ($model->load(Yii::$app->request->post(), 'info')) {
                if ($model->login()) {
                    //更新时间
                    \common\modelsgii\Admin::updateAll(['last_login_time' => time()],
                        ['username' => $model->username]);

                    //重复登陆判断
                    if (isset(Yii::$app->getBehavior('check')->filter) && Yii::$app->getBehavior('check')->filter === true) {
                        //使用session和表gli_admin_session记录登录账号的token:time&id&ip,并进行MD5加密
                        $id       = Yii::$app->user->id;            //登录用户的ID
                        $username = $model->username;        //登录账号
                        $ip       = Yii::$app->request->userIP;    //登录用户主机IP
                        $token    = md5(sprintf("%s&%s&%s", time(), $id, $ip));
                        $session  = Yii::$app->session;
                        $session->set(md5(sprintf("%s&%s", $id, $username)), $token);  //将token存到session变量中
                        AdminSession::insertSession($id, $token);//将token存到tbl_admin_session
                    }

                    return $this->goHome();

                } else {
                    $code = isset($info) && is_string($info) ? $info : 'LOGIN_002';
//                    $del  = Admin::findOne(['username' => $model->username]);
//                    if ($del) {
//                        $del->delete();
//                    }
                }
            }
            $error = Yii::t('error', $code);
        }

        return $this->render('login', ['error' => $error, 'name' => $name]);
    }

    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect(Url::toRoute('/login/login'));
    }
}
