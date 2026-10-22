<?php

namespace api\controllers;

use Yii;
use OSS\OssClient;
use yii\web\UploadedFile;
use api\base\BaseController;

class OssController extends BaseController
{

    public function actionUpload()
    {
        $type = Yii::$app->request->post('type', '');
        $uploaded_file = UploadedFile::getInstanceByName('file');
        if (false == $uploaded_file || false == $type) {
            $this->msg = '参数错误';
            $this->code = -1;
            goto doEnd;
        }
        $object = md5(date('Y-m-d H:i:s') . $uploaded_file->tempName) . '.' . $uploaded_file->extension;
        $filePath = $uploaded_file->tempName;
        $ossClient = new OssClient(Yii::$app->params['Oss']['AccessKeyId'], Yii::$app->params['Oss']['AccessKeySecret'], Yii::$app->params['Oss']['Endpoint']);
        $result = $ossClient->uploadFile(Yii::$app->params['Oss']['Bucket'], 'upload/' . (empty($type) ? '' : $type . '/') . $object, $filePath);
        if (!isset($result["info"]['url'])) {
            $this->msg = '上传失败';
            $this->code = -1;
            goto doEnd;
        }

        $this->data = $result["info"]['url'];

        doEnd :
        return ['code' => $this->code, 'msg' => $this->msg, 'data' => $this->data];
    }
}