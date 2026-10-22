<?php

namespace backend\models;


use common\models\psources\CoinImage;
use Yii;
class UploadForm extends BaseForm
{
    const SCENARIO_UPLOAD_IMAGE = 'upload_image';


    public $file;             //用来保存文件
    public $isSave;           //是否保存到本地
    public $image_type;       //图片类型


    const IMAGE_SAVE_DIR = [
        1 => "/coin/",             //存放logo
        2 => "/banner/",           //存放banner
        3 => "/application/",      //存放应用详情图片,
        4 => "/h5_icon/",          //存放h5_logo ,
        5 => "/h5_banner/",        //存放h5_banner,
        6 => "/h5_application/",   //存放h5_application
        7 => "/platform_icon/" ,   //平台logo
    ];


    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return [
            self::SCENARIO_UPLOAD_IMAGE => ['file','isSave','image_type'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['file'],'file', 'skipOnEmpty' => false, 'on'=>[self::SCENARIO_UPLOAD_IMAGE]],
            ['isSave', 'default', 'value' => 1],
            ['image_type', 'default', 'value' => 1],

        ];
    }

    public function uploadImage()
    {
        if($this->validate()){
            $uploader = Yii::$app->uploader;
            $save_dir = self::IMAGE_SAVE_DIR[$this->image_type];
            $uploadPath = $uploader->absolute.$save_dir;  // 取得上传路径
            if (!file_exists($uploadPath)) {
                @mkdir($uploadPath, 0777, true);
            }
            $ext = $this->file->getExtension();                 // 获取文件的扩展名
            $randnums = $this->getrandnums();                   // 生成一个随机数，为了重命名文件
            $imageName = date("YmdHis").$randnums.'.'.$ext;     // 重命名文件
            $filePath = $uploadPath.$imageName;                 // 生成文件的绝对路径
            $this->file->saveAs($filePath);
            $base_url =  rtrim($uploader->baseuri, '/');
            $file_url = $save_dir .$imageName;
            $image_id = CoinImage::addOneRecord($base_url,$file_url);
            $image_src = rtrim($uploader->baseuri, '/') .$save_dir .$imageName;
            $data = ['image_id' => $image_id, 'image_src' => $image_src];
            return ['code' => 0,'data' => $data];
        }else{
            $error=self::getModelError($this);
            return ['code' => 1,'msg' => $error];
        }

    }


    /**
     * 生成随机数
     * @return string 随机数
     */
    protected function getrandnums()
    {
        $arr = array();
        while (count($arr) < 10) {
            $arr[] = rand(1, 10);
            $arr = array_unique($arr);
        }
        return implode("", $arr);
    }



}

?>