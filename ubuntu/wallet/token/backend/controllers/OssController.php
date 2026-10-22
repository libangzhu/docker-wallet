<?php

namespace backend\controllers;

use common\models\psources\CoinImage;
use Yii;
use OSS\OssClient;
use yii\web\UploadedFile;
use yii\validators\ImageValidator;

class OssController extends BaseController
{

    public function actionUpload()
    {
        Yii::$app->response->format = 'json';
        $type = Yii::$app->request->post('image_type', '');
        $uploaded_file = UploadedFile::getInstanceByName('file');
        $object = md5(date('Y-m-d H:i:s') . $uploaded_file->tempName) . '.' . $uploaded_file->extension;
        $filePath = $uploaded_file->tempName;
        $ossClient = new OssClient(Yii::$app->params['Oss']['AccessKeyId'], Yii::$app->params['Oss']['AccessKeySecret'], Yii::$app->params['Oss']['Endpoint']);
        $result = $ossClient->uploadFile(Yii::$app->params['Oss']['Bucket'], 'upload/' . (empty($type) ? '' : $type . '/') . $object, $filePath);
        if (!isset($result["info"]['url'])) {
            return ['code' => 1, 'msg' => '上传失败'];
        }
        if (in_array($type, ['coin', 'banner', 'h5_banner', 'h5_icon', 'application', 'h5_application'])) {
            $file_url = explode('http://bqbwallet.oss-cn-shanghai.aliyuncs.com/upload', $result["info"]['url']);
            $image_id = CoinImage::addOneRecord($file_url[1]);
        }

        return ['code' => 0, 'msg' => '上传成功', 'data' => ['src' => $result["info"]['url'], 'image_id' => !empty($image_id) ? $image_id : 0]];
    }
}