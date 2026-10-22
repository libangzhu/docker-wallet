<?php

namespace wallet\controllers;


use Firebase\JWT\JWT;
use Yii;
use common\models\Admin;
use common\models\LoginForm;
use wallet\base\BaseController;
use common\service\trusteeship\TrusteeShipService;

class UserController extends BaseController
{
    /**
     * landing
     * @return array
     * @throws \yii\base\Exception
     * @throws \yii\base\InvalidConfigException
     */
    public function actionLogin()
    {
        $msg = 'ok';
        $code = 0;
        $model = new LoginForm();
        $model->setScenario(LoginForm::SCENARIOS_LOGIN);
        $model->load(Yii::$app->request->post(), '');

        $key = Yii::$app->params['Jwt']['Key'];//key要和签发的时候一样
        $time = time(); //当前时间
        if (!$user = $model->login()) {
            $msg = implode(", ", \yii\helpers\ArrayHelper::getColumn($model->errors, 0, false)); // Model's Errors string
            $data = null;
            $code = -1;
            goto doEnd;
        }
        if (Admin::STATUS_DELETED == $user->status) {
            $msg = '用户已停用';
            $data = null;
            $code = -1;
            goto doEnd;
        }
        $token = [
            'iss' => Yii::$app->params['Jwt']['Iss'], //签发者 可选
            'aud' => Yii::$app->params['Jwt']['Aud'], //接收该JWT的一方，可选
            'iat' => time(), //签发时间
            'nbf' => $time, //(Not Before)：某个时间点后才能访问，比如设置time+30，表示当前时间30秒后才能使用
            'exp' => $time + 7200, //过期时间,这里设置2个小时
            'user' => [ //自定义信息，不要定义敏感信息
                'uid' => $user->uid,
                'username' => $user->username,
                'platform_id' => $user->platform_id,
                'group' => $user->group
            ]
        ];

        $access_token = JWT::encode($token, $key);

        $data = ['access_token' => $access_token];

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionUserInfo()
    {
        $msg = 'ok';
        $code = 0;
        $token_string = Yii::$app->request->headers->get('Token');
        $user = Admin::verfication($token_string);
        $data = [
            'username' => $user["data"]->username,
            'uid' => isset($user["data"]->bind_uid) ? $user["data"]->bind_uid : $user["data"]->uid,
            'type' => isset($user["data"]->bind_uid) ? 2 : 1,
            'platform_id' => $user["data"]->platform_id,
            'group' => $user["data"]->group
        ];

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 用户同步
     */
    public function actionUserSync()
    {
        $items = Yii::$app->request->post();
        if (count($items['items']) > 10) {
            return ['code' => -1, 'data' => [], 'msg' => '一次最多同步20条数据'];
        }
        $duplicate = 0;
        foreach ($items['items'] as $key => $item) {
            $model = Admin::find()->where(['username' => $item['username']])->andWhere(['platform_id' => (int)$item['platform']])->one();
            if ($model) {
                $duplicate++;
                continue;
            }
            $datas[] = [
                $item['bind_uid'],
                $item['username'],
                Yii::$app->security->generateRandomString(),
                Yii::$app->security->generatePasswordHash('123456'),
                time(),
                ip2long('127.0.0.1'),
                0,
                ip2long('127.0.0.1'),
                0,
                1,
                $item['platform']
            ];
        }
        if (!empty($datas)) {
            Admin::loadArray($datas);
        }
        return ['code' => 1, 'data' => [], 'msg' => '数据更新成功，共有 ' . $duplicate . ' 条重复'];


        $header = Yii::$app->request->headers;
        $platform_id = $header['platform_id'] ?? 17;

        $post = Yii::$app->request->post();
        $data = [
            'bind_uid' => $post['bind_uid'],
            'username' => $post['username'],
            'salt' => Yii::$app->security->generateRandomString(),
            'password' => Yii::$app->security->generatePasswordHash('123456'),
            'reg_time' => time(),
            'reg_ip' => ip2long('127.0.0.1'),
            'last_login_time' => 0,
            'last_login_ip' => ip2long('127.0.0.1'),
            'update_time' => 0,
            'status' => 1,
            'platform_id' => $platform_id
        ];
        $role = Yii::$app->request->post('role', 'GHPwallet');

        $model = new Admin();
        if ($model->load($data, '') && $model->save()) {
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($role);
            $auth->assign($role, $model->uid);
            exit;
        } else {
            var_dump($model->errors);
            exit;
        }
    }


    /**
     * 用户列表
     */
    public function actionUserList()
    {
        $current_platform_id = Yii::$app->request->getPlatformId();
        if (1 === $current_platform_id) {
            $platform_id = Yii::$app->request->get('platform_id', 1);
            $platform_id = empty($platform_id) ? 1 : $platform_id;
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }

        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 15);
        $real_type = Yii::$app->request->get('real_type', '');
        $search_type = Yii::$app->request->get('search_type', 'user');
        $search = Yii::$app->request->get('search', '');
        $excel = Yii::$app->request->get('excel', '');
        $start_time = Yii::$app->request->get('start_time', '');
        $end_time = Yii::$app->request->get('end_time', '');
        $params = [
            'page' => $page,
            'size' => $size,
            'real_type' => $real_type,
            'search_type' => $search_type,
            'search' => $search,
            'excel' => $excel,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];

        $time = time();
        $appKey = isset($node_params['appKey']) ? $node_params['appKey'] : null;
        $appSecret = isset($node_params['appSecret']) ? $node_params['appSecret'] : null;
        $signature = self::getSign($params, $appKey, $appSecret, $time);

        $headers = [
            'FZM-Wallet-Signature' => $signature,
            'FZM-Wallet-Timestamp' => $time,
            'FZM-Wallet-AppKey' => $appKey,
            'FZM-Wallet-AppIp' => Yii::$app->request->userIP
        ];

        $service = new TrusteeShipService($node_params, $headers);
        $result = $service->getUserList($params);
        if (200 !== $result['code']) {
            return ['code' => $result['code'], 'data' => [], 'msg' => $result['msg']];
        }
        return ['code' => 1, 'data' => $result['msg'], 'msg' => 'success'];
    }

    /**
     *    人工实名全部列表
     */
    public function actionManualList()
    {
        $current_platform_id = Yii::$app->request->getPlatformId();
        if (1 === $current_platform_id) {
            $platform_id = Yii::$app->request->get('platform_id', 1);
            $platform_id = empty($platform_id) ? 1 : $platform_id;
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }

        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 15);
        $status = Yii::$app->request->get('status', '');
        $search = Yii::$app->request->get('search', '');
        $start_time = Yii::$app->request->get('start_time', '');
        $end_time = Yii::$app->request->get('end_time', '');
        $params = [
            'page' => $page,
            'size' => $size,
            'status' => $status,
            'search' => $search,
            'start_time' => $start_time,
            'end_time' => $end_time
        ];

        $time = time();
        $appKey = isset($node_params['appKey']) ? $node_params['appKey'] : null;
        $appSecret = isset($node_params['appSecret']) ? $node_params['appSecret'] : null;
        $signature = self::getSign($params, $appKey, $appSecret, $time);

        $headers = [
            'FZM-Wallet-Signature' => $signature,
            'FZM-Wallet-Timestamp' => $time,
            'FZM-Wallet-AppKey' => $appKey,
            'FZM-Wallet-AppIp' => Yii::$app->request->userIP
        ];

        $service = new TrusteeShipService($node_params, $headers);
        $result = $service->getManualList($params);
        if (200 !== $result['code']) {
            return ['code' => $result['code'], 'data' => [], 'msg' => $result['msg']];
        }
        return ['code' => 1, 'data' => $result['msg'], 'msg' => 'success'];
    }

    public function actionVerify()
    {
        $current_platform_id = Yii::$app->request->getPlatformId();
        if (1 === $current_platform_id) {
            $platform_id = Yii::$app->request->get('platform_id', 1);
            $platform_id = empty($platform_id) ? 1 : $platform_id;
        } else {
            $platform_id = Yii::$app->request->getPlatformId();
        }
        if (!isset(Yii::$app->params['trusteeship']['node_' . $platform_id])) {
            return ['code' => -1, 'data' => [], 'msg' => '此钱包节点尚未开通'];
        }

        $node_params = Yii::$app->params['trusteeship']['node_' . $platform_id];

        $post = Yii::$app->request->post();
        $uid = isset($post['uid']) ? $post['uid'] : null;
        $action = isset($post['action']) ? $post['action'] : null;
        if (false == $uid || false == $action) {
            return ['code' => -1, 'data' => [], 'msg' => '参数错误'];
        }

        $params = [
            'uid' => $uid,
        ];

        $time = time();
        $appKey = isset($node_params['appKey']) ? $node_params['appKey'] : null;
        $appSecret = isset($node_params['appSecret']) ? $node_params['appSecret'] : null;
        $signature = self::getSign($params, $appKey, $appSecret, $time);

        $headers = [
            'FZM-Wallet-Signature' => $signature,
            'FZM-Wallet-Timestamp' => $time,
            'FZM-Wallet-AppKey' => $appKey,
            'FZM-Wallet-AppIp' => Yii::$app->request->userIP
        ];

        $service = new TrusteeShipService($node_params, $headers);
        if ('verify' == strtolower($action)) {
            $result = $service->verifyReal($params);
        }
        if ('refuse' == strtolower($action)) {
            $result = $service->refuseReal($params);
        }
        if ('revoke' == strtolower($action)) {
            $result = $service->revokeReal($params);
        }

        if (200 !== $result['code']) {
            return ['code' => $result['code'], 'data' => [], 'msg' => $result['msg']];
        }
        return ['code' => 1, 'data' => $result['msg'], 'msg' => 'success'];
    }

    public function actionEditPwd()
    {
        if (!Yii::$app->request->isPut) {
            $this->msg = '请求方式错误';
            $this->code = -1;
            goto doEnd;
        }

        $token = Yii::$app->request->headers->get('Token');
        $user_info = Admin::verfication($token);
        if (0 != $user_info['code']) {
            $this->msg = $user_info['data'];
            $this->code = -1;
            goto doEnd;
        }
        $user_info = (array)$user_info['data'];
        $user = Admin::findOne($user_info['uid']);
        $old_password = Yii::$app->request->post('old_password', null);
        $new_password = Yii::$app->request->post('new_password', null);
        $new_password1 = Yii::$app->request->post('new_password1', null);
        if (!$old_password) {
            $this->msg = "请输入原始密码";
            $this->code = -1;
            goto doEnd;
        }
        if (!$new_password) {
            $this->msg = "请输入新密码";
            $this->code = -1;
            goto doEnd;
        }
        if (!$new_password1) {
            $this->msg = "请再次输入新密码";
            $this->code = -1;
            goto doEnd;
        }
        if ($new_password != $new_password1) {
            $this->msg = "新密码输入不一致，请重新输入";
            $this->code = -1;
            goto doEnd;
        }

        if (!Yii::$app->security->validatePassword($old_password, $user->password)) {
            $this->msg = "原始密码错误，请重新输入";
            $this->code = -1;
            goto doEnd;
        }

        $user->setPassword($new_password);
        $user->salt = Yii::$app->security->generateRandomString();
        $user->save();
        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }

    public function actionAdd()
    {
        if (!Yii::$app->request->isPost) {
            $this->msg = '请求方式错误';
            $this->code = -1;
            goto doEnd;
        }
        $group = Yii::$app->request->getGroup();
        if (!in_array($group, ['administrator', 'admin'])) {
            $this->code = -1;
            $this->msg = '当前用户无权操作';
            goto doEnd;
        }
        $platform_id = Yii::$app->request->getPlatformId();
        if ('administrator' === $group) {
            $platform_id = Yii::$app->request->post('platform_id', 1);
        }
        $token = Yii::$app->request->headers->get('Token');
        $user_info = Admin::verfication($token);
        if (0 != $user_info['code']) {
            $this->msg = $user_info['data'];
            $this->code = -1;
            goto doEnd;
        }

        $data = Yii::$app->request->post();
        $username = Yii::$app->request->post('username', null);
        $password = Yii::$app->request->post('password', null);

        if (empty($username)) {
            $this->code = -1;
            $this->msg = '用户名不能为空';
            goto doEnd;
        }

        if (empty($password)) {
            $this->code = -1;
            $this->msg = '密码不能为空';
            goto doEnd;
        }

        if (strlen($data['password']) < 6) {
            $this->code = -1;
            $this->msg = '密码不能小于6字符';
            goto doEnd;
        }

        $user = Admin::findByUsername($username);
        if ($user) {
            $this->code = -1;
            $this->msg = '用户名已存在';
            goto doEnd;
        }

        $params = [
            'username' => $username,
            'reg_time' => time(),
            'status' => $data['status'] ?? 0,
            'update_time' => 0,
            'last_login_time' => 0,
            'group' => 'general',
            'platform_id' => $platform_id,
            'reg_ip' => ip2long(Yii::$app->request->getUserIP()),
            'last_login_ip' => ip2long('127.0.0.1'),
        ];
        $model = new Admin();
        #var_dump($model->getAttributes());exit;
        $model->setAttributes($params);
        $model->generateAuthKey();
        $model->setPassword($password);
        $model->save();

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }
}