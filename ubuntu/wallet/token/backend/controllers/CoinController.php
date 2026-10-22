<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-5-31
 * Time: 上午9:56
 */

namespace backend\controllers;

use backend\models\coin\CoinForm;
use common\business\CoinBusiness;
use common\models\psources\Coin;
use common\models\psources\CoinPlatform;
use common\models\psources\CoinRecommend;
use Yii;
use yii\validators\ImageValidator;
use yii\web\Response;
use yii\web\UploadedFile;

/**
 * 币种管理控制器
 * Class CoinController
 * @package backend\controllers
 */
class CoinController extends BaseController
{
    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 币种列表
     */
    public function actionIndex()
    {
        if (Yii::$app->request->isAjax) {
            $request = Yii::$app->request;
            $page = $request->get('page', 1);
            $limit = $request->get('limit', 10);
            $name = $request->get('name', null);
            $platform = $request->get('platform', '');
            $chain = $request->get('chain', '');
            $recommend = $request->get('recommend', '');

            $condition = [];

            if ($name) {
                $condition[] = ['like', 'name', $name];
            }
            if ($platform != '') {
                $condition[] = ['platform' => $platform];
            }
            if ($chain != '') {
                $condition[] = ['chain' => $chain];
            }
            if (Yii::$app->user->id != Yii::$app->params['admin']) {
                $platform_id = Yii::$app->user->identity->platform_id;
                $condition[] = ("JSON_CONTAINS(platform_id,'$platform_id')");
            }
            if (!empty($recommend)) {
                //使用连表查询推荐币种
                $data = CoinBusiness::getRecommendList($page, $limit, $condition);
            } else {
                $data = CoinBusiness::getList($page, $limit, $condition);
            }
            $data['code'] = 0;
            //ajax return
            Yii::$app->response->format = 'json';
            return $data;

        }
        $platforms = Coin::getPlatformList();
        $chains = Coin::getChainList();
        return $this->render('index', ['platforms' => $platforms, 'chains' => $chains]);
    }

    /**
     * @return string
     * 币种添加
     */
    public function actionAdd()
    {
        $model = new CoinForm();
        $model->scenario = 'add';
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            if ($model->load($request->post()) && $model->validate()) {
                /**
                 * @var $coin \common\models\psources\Coin
                 */
                $coin = Yii::createObject(Coin::className());
                $data = array_merge($request->post(), ['platform_id' => Yii::$app->user->identity->platform_id]);
                unset($data['id']);
                if (isset($data['optional_name'])) {
                    $data['optional_name'] = strtoupper($data['optional_name']);
                }
                $data['name'] = strtoupper(trim($data['name']));
                $data['platform'] = $data['platform'];
                $data['chain'] = strtoupper($data['chain']);

                $nickname_arr = $data['nickname'];
                $introduce_arr = $data['introduce'];
                $nickname = [];
                $introduce = [];
                foreach (Yii::$app->params['lang'] as $key => $val) {
                    $nickname[$val] = isset($nickname_arr[$val]) ? $nickname_arr[$val] : '';
                }
                foreach (Yii::$app->params['lang'] as $key => $val) {
                    $introduce[$val] = isset($introduce_arr[$val]) ? $introduce_arr[$val] : '';
                }

                unset($data['nickname']);
                unset($data['introduce']);
                $data['nickname'] = $nickname;
                $data['introduce'] = $introduce;
                $result = $coin->addOne($data);
                if ($result === true) {
                    $this->success('添加成功', '/admin/coin/index');
                }
            }
            //表单验证失败
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            } elseif (isset($result) && $result['code'] != 0) {
                $errors = $result['message'];
            }
            $this->error($errors, Yii::$app->request->getReferrer());
        }
        return $this->render('add', ['model' => $model]);
    }

    /**
     * @return array|string
     * 币种编辑
     */
    public function actionEdit()
    {
        if (Yii::$app->request->isPost) {

            $model = new CoinForm();
            $model->scenario = 'update';
            $req = Yii::$app->request;
            $data = $req->post();
            $data['name'] = strtoupper($data['name']);
            $data['platform'] = $data['platform'];
            $data['chain'] = strtoupper($data['chain']);
            if (isset($data['optional_name'])) {
                $data['optional_name'] = strtoupper($data['optional_name']);
            }
            Yii::$app->response->format = 'json';
            if ($model->load($data) && $model->validate()) {
                $platform_id = Yii::$app->user->identity->platform_id;

                $can = false;

                if (Yii::$app->params['admin'] != Yii::$app->user->id) {
                    $coin = Coin::find()->where(['id' => $req->post('id')])->andWhere("JSON_CONTAINS(platform_id,'$platform_id')");
                    if ($coin) {
                        $can = true;
                    }
                } else {
                    $can = true;
                }
                if ($can) {
                    $nickname_arr = $data['nickname'];
                    $introduce_arr = $data['introduce'];
                    $nickname = [];
                    $introduce = [];
                    foreach (Yii::$app->params['lang'] as $key => $val) {
                        $nickname[$val] = isset($nickname_arr[$val]) ? $nickname_arr[$val] : '';
                    }
                    foreach (Yii::$app->params['lang'] as $key => $val) {
                        $introduce[$val] = isset($introduce_arr[$val]) ? $introduce_arr[$val] : '';
                    }
                    unset($data['nickname']);
                    $data['nickname'] = $nickname;
                    $data['introduce'] = $introduce;
                    $coin = Yii::createObject(Coin::className());
                    $result = $coin->updateOne($data);
                    if ($result === true) {
                        return ['code' => 0, 'msg' => 'succeed'];
                    }
                } else {
                    return ['code' => -1, 'msg' => '没有权限修改'];
                }
            }
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $key => $item) {
                    $errors = $item[0];
                    break;
                }
            } elseif (isset($result) && $result['code'] != 0) {
                $errors = $result['message'];
            }
            return ['code' => 1, 'msg' => $errors];
        } elseif (Yii::$app->request->isGet) {
            $id = Yii::$app->request->get('id', null);
            if ($id) {
                $coin = Coin::findOne(['id' => $id]);
                $this->layout = false;
                return $this->render('edit', ['model' => $coin]);
            }
        }
    }

    /**
     * 上传币种图标
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';

        $uploaded_file = UploadedFile::getInstanceByName('file');
        try {
            /**
             * @var $validator ImageValidator
             */
            $validator = Yii::createObject(ImageValidator::className(), ['extensions' => 'png,jpg,gif']);
            if ($validator->validate($uploaded_file)) {
                $src = Yii::$app->uploader->upload($uploaded_file);
                if ($src === false) {
                    return ['code' => 1, 'msg' => '上传失败'];
                } else {
                    return ['code' => 0, 'data' => ['src' => $src]];
                }
            }
        } catch (\Exception $exception) {
            return ['code' => $exception->getCode(), 'msg' => $exception->getMessage()];
        }
    }

    /**
     * @return array
     * 币种删除
     */
    public function actionDelete()
    {
        Yii::$app->response->format = 'json';
        $id = Yii::$app->request->get('id', 0);
        if ($id) {

            $coin_recommend = CoinRecommend::find()->where(['cid' => $id])->one();
            if ($coin_recommend) {
                return ['code' => -1, 'msg' => '推荐币种里有改币种，无法删除'];
            }
            $model = Coin::findOne(['id' => $id]);
            if ($model) {
                $platform_id = Yii::$app->user->identity->platform_id;
                $can = false;

                if (Yii::$app->params['admin'] != Yii::$app->user->id) {
                    $coin = Coin::find()->where(['id' => $id, 'platform_id' => $platform_id])->one();
                    if ($coin) {
                        $can = true;
                    }
                } else {
                    $can = true;
                }
                if ($can) {
                    try {
                        $model->delete();
                        return ['code' => 0, 'msg' => 'succeed'];
                    } catch (\Throwable $t) {
                        return ['code' => $t->getCode(), 'msg' => $t->getMessage()];
                    }
                } else {
                    return ['code' => -1, 'msg' => '没有权限修改'];
                }
            }
        }
        return ['code' => -1, 'msg' => '删除失败'];
    }

    /**
     * @return string
     * 获取币种行情
     */
    public function actionGetExchangeListById()
    {
        $id = Yii::$app->request->get('id', 0);
        $exchanges = [];
        if ($id) {
            $exchanges = CoinBusiness::getExchangeListById($id);
        }
        $this->layout = false;
        return $this->render('exchange', ['exchanges' => $exchanges]);
    }

    /**
     * @return array|string|\yii\db\ActiveRecord[]
     * 币种库配置列表
     */
    public function actionPackage()
    {
        $user_platform_id = Yii::$app->user->identity->platform_id;

        if (Yii::$app->request->isAjax) {
            $get = Yii::$app->request->get();

            $page = $get['page'] ?? 1;
            $limit = $get['limit'] ?? 10;
            $name = $get['name'] ?? '';
            $platform = $get['platform'] ?? '';
            $chain = $get['chain'] ?? '';

            $condition = [];

            if ($name) {
                $condition[] = ['like', 'name', $name];
            }
            if ($platform) {
                $condition[] = ['platform' => $platform];
            }
            if ($chain) {
                $condition[] = ['chain' => $chain];
            }

            if (Yii::$app->user->id == Yii::$app->params['admin']) {
                $platform_id = $get['platform_id'] ?? '';
            } else {
                $platform_id = $user_platform_id;
            }

            if ($platform_id) {
                $condition[] = ("JSON_CONTAINS(platform_id,'$platform_id')");
            }
            $data = Coin::getSelectList($page, $limit, ['id', 'name', 'nickname', 'platform', 'chain'], $condition);

            Yii::$app->response->format = Response::FORMAT_JSON;
            if (!empty($data)) {
                $data['code'] = 0;
            }
            return $data;
        }
        //获取币种平台
        $coin_platforms = Coin::getPlatformList();
        //获取主链
        $chains = Coin::getChainList();

        if (Yii::$app->user->id == Yii::$app->params['admin']) {
            $platforms = CoinPlatform::find()->asArray()->all();
        } else {
            $platforms = CoinPlatform::find()->where(['id' => $user_platform_id])->asArray()->all();
        }
        return $this->render('package', [
            'platforms' => $platforms,
            'chains' => $chains,
            'coin_platforms' => $coin_platforms,
        ]);
    }

    /**
     * @return array
     * 删除指定钱包币种
     */
    public function actionPackageDel()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $id = Yii::$app->request->get('id', '');
            $platform_id = Yii::$app->request->get('platform_id', '');
            if (empty($platform_id)) {
                return ['code' => -1, 'msg' => '请选择钱包'];
            }
            $user_platform_id = Yii::$app->user->identity->platform_id;

            $coin = Coin::findOne($id);
            if ($coin) {
                $can = false;

                $platform_ids = explode(',', $coin->platform_id);

                if (Yii::$app->user->id == Yii::$app->params['admin']) {
                    $can = true;
                } else {
                    if (in_array($user_platform_id, $platform_ids) && $platform_id == $user_platform_id) {
                        $can = true;
                    }
                }
                if ($can) {
                    //删除
                    $platform_ids = array_diff($platform_ids, [$platform_id]);
                    $platform_ids = implode(',', $platform_ids);
                    $coin->platform_id = $platform_ids;
                    if ($coin->save()) {
                        return ['code' => 0, 'msg' => '删除成功'];
                    }
                    return ['code' => -1, 'msg' => '删除失败'];
                } else {
                    return ['code' => -1, 'msg' => '没有权限修改'];
                }

            } else {
                return ['code' => -1, 'msg' => '币种不存在'];
            }
        }
    }

    /**
     * @return array
     * 添加指定钱包币种
     */
    public function actionPackageAdd()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get = Yii::$app->request->get();
            $platform_id = $get['platform_id'] ?? '';
            $name = $get['name'] ?? '';
            $chain = $get['chain'] ?? '';
            foreach (['platform_id' => '平台', 'name' => '币种名称', 'chain' => '币种主链'] as $key => $value) {
                if (empty($$key)) {
                    return ['code' => -1, 'msg' => $value . '不能为空'];
                }
            }

            $coin = Coin::find()->where(['name' => $name, 'chain' => $chain])->One();
            if ($coin) {
                $can = false;
                $user_platform_id = Yii::$app->user->identity->platform_id;
                $platform_ids = explode(',', $coin->platform_id);

                if ($user_platform_id == Yii::$app->params['admin']) {
                    $can = true;
                } else {
                    if (in_array($user_platform_id, $platform_ids) && $platform_id == $user_platform_id) {
                        $can = true;
                    }
                }
                if ($can) {
                    $platform_ids = implode(',', array_unique(array_merge($platform_ids, [$platform_id])));
                    $coin->platform_id = $platform_ids;
                    if ($coin->save()) {
                        return ['code' => 0, 'msg' => '添加成功'];
                    }
                    return ['code' => -1, 'msg' => '添加失败'];
                }
                return ['code' => -1, 'msg' => '没有权限修改'];
            }
            return ['coin' => -1, 'msg' => '币种不存在'];
        }
    }

    /**
     * @return array
     * 复制币种到指定钱包
     */
    public function actionPackageCopy()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;

            $get = Yii::$app->request->get();
            $platform_id = $get['platform_id'] ?? '';
            $coin_ids = $get['coin_ids'] ?? '';
            foreach (['platform_id' => '钱包', 'coin_ids' => '币种'] as $key => $value) {
                if (empty($$key)) {
                    return ['code' => -1, 'msg' => $value . '不能为空'];
                }
            }
            $user_platform_id = Yii::$app->user->identity->platform_id;
            if ($user_platform_id == Yii::$app->params['admin']) {
                $coin_id_items = explode(',', $coin_ids);
                foreach ($coin_id_items as $id) {
                    $coin = Coin::getOneById($id);
                    if ($coin) {
                        $platform_ids = explode(',', $coin->platform_id);
                        $platform_ids = implode(',', array_unique(array_merge($platform_ids, [$platform_id])));
                        $coin->platform_id = $platform_ids;
                        $coin->save();
                    }
                }
                return ['code' => 0, 'msg' => '复制成功'];
            }
            return ['code' => -1, 'msg' => '没有权限复制'];
        }
    }

    /**
     * @return array
     * 批量删除指定钱包币种
     */
    public function actionPackageBatchDel()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            $get = Yii::$app->request->get();
            $platform_id = $get['platform_id'] ?? '';
            $coin_ids = $get['coin_ids'] ?? '';
            foreach (['platform_id' => '钱包', 'coin_ids' => '币种'] as $key => $value) {
                if (empty($$key)) {
                    return ['code' => -1, 'msg' => $value . '不能为空'];
                }
            }
            $user_platform_id = Yii::$app->user->identity->platform_id;
            if ($user_platform_id == Yii::$app->params['admin']) {
                $coin_id_items = explode(',', $coin_ids);
                foreach ($coin_id_items as $id) {
                    $coin = Coin::getOneById($id);
                    if ($coin) {
                        $platform_ids = explode(',', $coin->platform_id);
                        $platform_ids = implode(',', array_diff($platform_ids, [$platform_id]));
                        $coin->platform_id = $platform_ids;
                        $coin->save();
                    }
                }
                return ['code' => 0, 'msg' => '批量删除成功'];
            }
            return ['code' => -1, 'msg' => '没有权限删除'];
        }
    }

    /**
     * @return array|string
     * 平台币图标列表
     */
    public function actionPlatformCoin()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $data = Coin::getPlatformList();
            foreach ($data as $item) {
                if ($item) {
                    $platformInfo['platform'] = $item;
                    $icon = Yii::$app->redis->hget('platform_image_info', $item);
                    $brower_url = Yii::$app->redis->hget('platform_brower_info', $item);
                    $platformInfo['icon'] = $icon ?? '';
                    $platformInfo['brower_url'] = $brower_url ?? '';
                    $platformItems[] = $platformInfo;
                }
            }
            return ['data' => $platformItems, 'code' => 0];
        }
        return $this->render('platform_coin');
    }

    /**
     * @return array
     * 添加平台币图标
     */
    public function actionAddPlatformCoin()
    {
        if (Yii::$app->request->isPost) {
            Yii::$app->response->format = 'json';
            $platform = Yii::$app->request->post('platform');
            $image = Yii::$app->request->post('image');
            if ($platform && $image) {
                Yii::$app->redis->hset('platform_image_info', $platform, $image);
                return ['code' => 0, 'msg' => '图片添加成功'];
            }
            return ['code' => 0, 'msg' => '图片添加失败'];
        }
    }

    /**
     * @return array
     * 设置区块链浏览器地址
     */
    public function actionSetBrower()
    {
        if (Yii::$app->request->isAjax) {
            Yii::$app->response->format = 'json';
            $request = Yii::$app->request;
            $platform = $request->get('platform', '');
            $brower_url = $request->get('brower_url', '');
            if ($platform) {
                Yii::$app->redis->hset('platform_brower_info', $platform, $brower_url);
                return ['code' => 0, 'msg' => '区块链浏览器地址设置成功'];
            } else {
                return ['code' => 1, 'msg' => '区块链浏览器地址设置失败'];
            }
        }
    }

}
