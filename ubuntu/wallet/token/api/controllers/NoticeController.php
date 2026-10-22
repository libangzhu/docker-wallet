<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-5
 * Time: 上午10:27
 */

namespace api\controllers;

use common\service\exchange\ExchangeBuilderFactory;
use Yii;
use api\base\BaseController;
use common\models\pwallet\Notice;
use yii\data\Pagination;

/**
 * 公告控制器
 * Class NoticeController
 * @package api\controllers
 */
class NoticeController extends BaseController
{
    /**
     * 申请列表
     * @param integer page
     * @param integer size
     * @return array
     */
    public function actionList()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $type = Yii::$app->request->get('type', '');

        if (empty($platform_id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $page = \Yii::$app->request->get('page', 1);
        $size = \Yii::$app->request->get('size', 10);

        $query = Notice::find()
            ->select('id, title, content, author, type, is_top, create_time, update_time')
            ->where(['status' => Notice::STATUS_ON, 'platform_id' => (int)$platform_id])
            ->groupBy('title')
            ->orderBy('create_time desc');

        if (false != $type) {
            $query->andWhere(['type' => (int)$type]);
            if (Notice::TYPE_POP == (int)$type) {
                $size = 1;
            }
        }

        $countQuery = clone $query;
        $models = $query->offset(($page - 1) * $size)->limit($size)->all();
        $pages = new Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $size]);

        $data = [
            'list' => $models,
            'page' => [
                'pageCount' => $pages->pageCount,
                'pageSize' => (int)$size,
                'currentPage' => (int)$page,
            ]
        ];
        $msg = 'ok';
        $code = 0;

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    /**
     * 申请列表
     * @param integer page
     * @param integer size
     * @return array
     */
    public function actionDetail()
    {
        $data = null;
        $header = Yii::$app->request->headers;
        $platform_id = $header['FZM-PLATFORM-ID'] ?? null;
        $id = Yii::$app->request->get('id', 0);

        if (empty($platform_id) || empty($id)) {
            $msg = '缺少必要的参数';
            $code = -1;
            goto doEnd;
        }

        $data = Notice::find()->select('title,content,author,type,create_time')->where(['id' => $id, 'platform_id' => $platform_id])->asArray()->one();
        $msg = 'ok';
        $code = 0;

        doEnd :
        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }

    public function actionIndex()
    {
        $id = Yii::$app->request->get('id', '');
        $page = Yii::$app->request->get('page', 1);
        $size = Yii::$app->request->get('size', 10);
        $exchange = Yii::$app->request->get('exchange', 'zhaobi');
        $exchange_arr = ['huobi', 'binance', 'okex', 'zhaobi'];
        if (!in_array($exchange, $exchange_arr)) {
            $msg = '不存在的交易平台';
            $code = -1;
            $data = [];
            goto doEnd;
        }
        $params = [
            'id' => $id,
            'page' => $page,
            'size' => $size
        ];
        $builder = ExchangeBuilderFactory::create($exchange);
        $result = $builder->getNotice($params);
        $code = $result['code'];
        $data = $result['notice'];
        $msg = isset($result['msg']) ? $result['msg'] : 'success';
        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}