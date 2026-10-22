<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-1
 * Time: 下午2:32
 */

namespace common\components\uploader;

use common\base\Exception;
use yii\base\Component;
use yii\web\UploadedFile;

class Uploader extends Component
{

    //文件上传前事件
    const EVENT_BEFORE_UPLOAD = 'before_upload';
    //文件上传后事件
    const EVENT_AFTER_UPLOAD = 'after_upload';

    //文件上传对象
    public $upload_class = '';

    //文件上传方法
    public $upload_method = '';

    //文件上传参数
    public $upload_params = [];

    //文件保存目录
    public $absolute = '';

    //文件base uri
    public $baseuri = '';

    /**
     * 上传文件
     * @param  $uploaded_file UploadedFile
     * @param  $savename      string         文件保存名
     * @throws Exception
     * @return bool|string    文件rui or false
     */
    public function upload($uploaded_file, $savename = '')
    {
        if (empty($savename)) {
            $savename = md5(date('Y-m-d H:i:s') . $uploaded_file->tempName);
            if ($uploaded_file->extension) {
                $savename = $savename . '.' . $uploaded_file->extension;
            }
        }
        $this->absolute = rtrim(str_replace('\\', '/', $this->absolute), '/') . '/';

        if (empty($this->upload_class)) {
            //默认保存到本地
            if ($uploaded_file->saveAs($this->absolute . $savename)) {
                return rtrim($this->baseuri, '/') . '/' . $savename;
            }
            throw new Exception(3, '保存文件失败');
        } else {
            if (class_exists($this->upload_class)) {
                if (method_exists($this->upload_class, $this->upload_method)) {
                    $this->upload_params = array_push($this->upload_params, $savename, $uploaded_file->tempName);
                    call_user_func_array([$this->upload_class, $this->upload_method], $this->upload_params);
                    return $this->baseuri . $savename;
                }
                throw new Exception(2, 'upload method not exists.');
            }
            throw new Exception(1, 'upload class not exists.');
        }
    }



}
