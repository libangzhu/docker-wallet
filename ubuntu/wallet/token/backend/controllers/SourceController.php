<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-4
 * Time: 上午9:17
 */

namespace backend\controllers;

use backend\models\coin\ArticleForm;
use backend\models\coin\NoticeForm;
use Yii;
use common\models\pwallet\News;
use common\models\pwallet\Notice;
use common\models\pwallet\Feedback;
use yii\db\Exception;

class SourceController extends BaseController
{
    /**
     * 显示文章列表
     * @return string
     */
    public function actionArticle()
    {
        if (Yii::$app->request->isAjax) {
            $request   = Yii::$app->request;
            $title     = $request->get('title', '');
            $status    = $request->get('status', 'all');
            $page      = $request->get('page', 1);
            $limit     = $request->get('limit', 10);
            $condition = [];
            if (!empty($title)) {
                $condition[] = ['like', 'title', $title];
            }
            if ($status != 'all') {
                $condition[] = ['status' => $status];
            }
            $results                    = News::getList($page, $limit, $condition);
            Yii::$app->response->format = 'json';
            Yii::$app->response->data   = $results;
            Yii::$app->response->send();
        }
        return $this->render('article');
    }

    /**
     * 添加文章
     * @return string
     */
    public function actionArticleAdd()
    {
        $model           = new ArticleForm();
        $model->scenario = 'add';
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            if ($model->load($request->post()) && $model->validate()) {
                $article = new News();
                $res     = $article->addOne($request->post());
                if ($res === true) {
                    $this->success('添加成功', '/admin/source/article');
                }
            }
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $k => $v) {
                    $errors = $v[0];
                    break;
                }
            } elseif (isset($res) && $res['code'] != 0) {
                $errors = $res['msg'];
            }
            $this->error($errors, Yii::$app->request->getReferrer());
        }
        return $this->render('article-add', ['model' => $model]);
    }

    /**
     * 编辑文章
     */
    public function actionArticleEdit()
    {
        $model           = new ArticleForm();
        $model->scenario = 'edit';
        $id              = Yii::$app->request->get('id', null);
        if ($id) {
            $article = News::findOne(['id' => $id]);
            if ($article) {
                if (Yii::$app->request->isPost) {
                    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                        $res = $article->updateOne(array_merge(Yii::$app->request->post(), ['id' => $id]));
                        if ($res === true) {
                            $this->success('修改成功', '/admin/source/article');
                        }
                    }
                    $errors = $model->errors;
                    if ($errors) {
                        foreach ($errors as $k => $v) {
                            $errors = $v[0];
                            break;
                        }
                    } elseif (isset($res) && $res['code'] != 0) {
                        $errors = $res['msg'];
                    }
                    $this->error($errors, Yii::$app->request->getReferrer());
                }
                return $this->render('article-edit', ['model' => $article]);
            }
        }
        $this->error('文章不存在', Yii::$app->request->getReferrer());
    }

    /**
     * 删除文章
     */
    public function actionArticleDel()
    {
        $id = Yii::$app->request->get('id', null);
        if ($id) {
            $article = News::findOne(['id' => $id]);
            if ($article) {
                try {
                    $article->delete();
                    $this->success('删除成功', Yii::$app->request->getReferrer());
                } catch (Exception $exception) {

                }
            }
        }
        $this->error('删除失败', Yii::$app->request->getReferrer());
    }

    /**
     * 公告列表
     * @return string
     */
    public function actionNotice()
    {
        if (Yii::$app->request->isAjax) {
            $request   = Yii::$app->request;
            $title     = $request->get('title', '');
            $status    = $request->get('status', 'all');
            $page      = $request->get('page', 1);
            $limit     = $request->get('limit', 10);
            $condition = [];
            if (!empty($title)) {
                $condition[] = ['like', 'title', $title];
            }
            if ($status != 'all') {
                $condition[] = ['status' => $status];
            }
            $results                    = Notice::getList($page, $limit, $condition);
            Yii::$app->response->format = 'json';
            Yii::$app->response->data   = $results;
            Yii::$app->response->send();
        }
        return $this->render('notice');
    }

    /**
     * 添加公告
     * @return string
     */
    public function actionNoticeAdd()
    {
        $model           = new NoticeForm();
        $model->scenario = 'add';
        if (Yii::$app->request->isPost) {
            $request = Yii::$app->request;
            if ($model->load($request->post()) && $model->validate()) {
                $notice            = new Notice();
                $notice->title     = $model->title;
                $notice->author    = $model->author;
                $notice->content   = $model->content;
                $notice->status    = $model->status;
                $notice->create_at = date('Y-m-d H:i:s');
                $notice->update_at = date('Y-m-d H:i:s');
                try {
                    $notice->save();
                    $this->success('添加成功', '/admin/source/notice');
                } catch (Exception $exception) {
                    $this->error($exception->getMessage(), '/admin/source/notice');
                }
            }
            $errors = $model->errors;
            if ($errors) {
                foreach ($errors as $k => $v) {
                    $errors = $v[0];
                    break;
                }
            }
            $this->error($errors, Yii::$app->request->getReferrer());
        }
        return $this->render('notice-add', ['model' => $model]);
    }

    /**
     * 编辑公告
     * @return string
     */
    public function actionNoticeEdit()
    {
        $model           = new NoticeForm();
        $model->scenario = 'edit';
        $id              = Yii::$app->request->get('id', null);
        if ($id) {
            $notice = Notice::findOne(['id' => $id]);
            if ($notice) {
                if (Yii::$app->request->isPost) {
                    if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                        $notice->title     = $model->title;
                        $notice->author    = $model->author;
                        $notice->content   = $model->content;
                        $notice->status    = $model->status;
                        $notice->update_at = date('Y-m-d H:i:s');
                        try {
                            $notice->save();
                            $this->success('修改成功', '/admin/source/notice');
                        } catch (Exception $exception) {
                            $this->error($exception->getMessage(), '/admin/source/notice');
                        }
                    }
                    $errors = $model->errors;
                    if ($errors) {
                        foreach ($errors as $k => $v) {
                            $errors = $v[0];
                            break;
                        }
                    }
                    $this->error($errors, Yii::$app->request->getReferrer());
                }
                return $this->render('notice-edit', ['model' => $notice]);
            }
        }
        $this->error('公告不存在', Yii::$app->request->getReferrer());
    }

    /**
     * 删除公告
     */
    public function actionNoticeDel()
    {
        $id = Yii::$app->request->get('id', null);
        if ($id) {
            $notice = Notice::findOne(['id' => $id]);
            if ($notice) {
                try {
                    $notice->delete();
                    $this->success('删除成功', Yii::$app->request->getReferrer());
                } catch (Exception $exception) {

                }
            }
        }
        $this->error('删除失败', Yii::$app->request->getReferrer());
    }

    public function actionFeedback()
    {
        if (Yii::$app->request->isAjax) {
            $request   = Yii::$app->request;
            $page      = $request->get('page', 1);
            $limit     = $request->get('limit', 10);
            $type      = $request->get('type', 0);
            $connect   = $request->get('connect', null);
            $condition = [];
            if (isset($type)) {
                $condition[] = ['type' => $type];
            }
            if (isset($connect)) {
                $condition[] = ['like', 'connect', $connect];
            }
            $data = Feedback::getList($page, $limit, $condition);

            Yii::$app->response->format = 'json';
            Yii::$app->response->data   = $data;
            Yii::$app->response->send();
        }
        if (Yii::$app->request->isGet) {
            $operation = Yii::$app->request->get('operation', 0);
            $id        = Yii::$app->request->get('id', 0);
            if ($operation == 1) {
                //删除反馈
                $feedback = Feedback::findOne(['id' => $id]);
                if ($feedback) {
                    try {
                        $feedback->delete();
                        $this->success('删除成功', '/admin/source/feedback');
                    } catch (\Exception $exception) {

                    } catch (\Throwable $throwable) {

                    }
                }
                $this->error('删除成功', '/admin/source/feedback');
            } elseif ($operation == 2) {
                //导出csv
                $data   = Feedback::find()->select(['connect', 'content', 'type', 'create_at'])->asArray()->all();
                $handle = fopen('php://temp', 'rw');
                fputcsv($handle, ['联系方式', '反馈内容', '反馈类型', '反馈时间']);
                foreach ($data as $item) {
                    //todo 类型转换
                    if ($item['type'] == 0) {
                        $item['type'] = '默认';
                    }
                    fputcsv($handle, $item);
                }
                Yii::$app->response->sendStreamAsFile($handle, 'feedback.csv');
                Yii::$app->response->send();
            }
        }
        return $this->render('feedback');
    }
}