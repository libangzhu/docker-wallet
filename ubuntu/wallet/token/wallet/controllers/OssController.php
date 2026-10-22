<?php

namespace wallet\controllers;

use Yii;
use OSS\OssClient;
use yii\web\UploadedFile;
use wallet\base\BaseController;

class OssController extends BaseController
{

    public function actionUpload()
    {
        $data = [];
        $type = Yii::$app->request->post('type', '');
        $uploaded_file = UploadedFile::getInstanceByName('file');
        if (false == $uploaded_file || false == $type) {
            $msg = '参数错误';
            $code = -1;
            goto doEnd;
        }
        $object = md5(date('Y-m-d H:i:s') . $uploaded_file->tempName) . '.' . $uploaded_file->extension;
        $filePath = $uploaded_file->tempName;
        $ossClient = new OssClient(Yii::$app->params['Oss']['AccessKeyId'], Yii::$app->params['Oss']['AccessKeySecret'], Yii::$app->params['Oss']['Endpoint']);
        $result = $ossClient->uploadFile(Yii::$app->params['Oss']['Bucket'], 'upload/' . (empty($type) ? '' : $type . '/') . $object, $filePath);
        if (!isset($result["info"]['url'])) {
            $msg = '上传失败';
            $code = -1;
            goto doEnd;
        }
        $code = 0;
        $msg = 'ok';
        $data = $result["info"]['url'];

        doEnd :

        return ['code' => $code, 'msg' => $msg, 'data' => $data];
    }
}