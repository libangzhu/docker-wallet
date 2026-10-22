<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/10/13
 * Time: 10:53
 */
namespace backend\controllers;


use backend\models\UploadForm;
use Yii;
use yii\web\UploadedFile;


class ImageController extends BaseController
{
    /**
     * @return array
     * 图片上传功能
     */
    public function actionUpload()
    {
        Yii::$app->response->format = 'json';
        $fields = ['isSave','image_type'];
        $params = $this->initParams(Yii::$app->request->post(), $fields);
        $uploadForm=new UploadForm();
        $uploadForm->setScenario(UploadForm::SCENARIO_UPLOAD_IMAGE);
        $uploadForm->load($params,'');
        $fielsInfo=UploadedFile::getInstanceByName('file');
        $uploadForm->file=$fielsInfo;
        return $uploadForm->uploadImage();
    }




}