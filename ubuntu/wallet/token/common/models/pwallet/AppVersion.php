<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-6-22
 * Time: 下午1:59
 */

namespace common\models\pwallet;

use common\core\BaseActiveRecord;
use Yii;

/**
 * Class AppVersion
 * @property integer $id
 * @property integer $status
 * @property integer $type
 * @property integer $version_code
 * @property string $version
 * @property string $download_url
 * @property string $log
 * @property string $created_at
 * @property string $updated_at
 * @package common\models\pwallet
 */
class AppVersion extends BaseActiveRecord
{
    const APP_ADD    = 'add';
    const APP_UPDATE = 'update';

    /**
     * @throws \yii\base\InvalidConfigException
     * @return null|object|\yii\db\Connection
     */
    public static function getDb()
    {
        return Yii::$app->get('db_pwallet');
    }

    /**
     * 获取最新的稳定版app
     *
     * @param  int     $type android(1) IOS(3)
     * @return array
     */
    public static function getLastStable($type = 1)
    {
        $query = <<<EOF
select
  id,
  status,
  version,
  version_code,
  download_url,
  `log`
from app_version
where type = :type AND version = (SELECT MAX(version)
                              from app_version
                              where type = :type);
EOF;
        try {
            return self::getDb()->createCommand($query)->bindValue(':type', $type)->queryOne();
        } catch (\Exception $exception) {
            return [];
        }
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'status'       => '状态',
            'type'         => '类型',
            'version_code' => '版本代码',
            'version'      => '版本号',
            'download_url' => '下载地址',
            'log'          => '升级日志',
            'created_at'   => '创建时间',
            'updated_at'   => '更新时间',
        ];
    }

    public function rules()
    {
        return [
            [['id', 'status', 'type', 'version_code'], 'integer'],
            [['log', 'created_at', 'updated_at'], 'string'],
            [['version'], 'string', 'max' => 17],
            [['download_url'], 'string', 'max' => 255],

            [['status', 'type', 'version_code', 'version', 'download_url'], 'required', 'on' => 'add'],
            [['id', 'status', 'type', 'version_code', 'version', 'download_url'], 'required', 'on' => 'update'],
        ];
    }

    public function scenarios()
    {
        return [
            'add'    => ['status', 'type', 'version_code', 'version', 'download_url', 'log'],
            'update' => ['id', 'status', 'type', 'version_code', 'version', 'download_url', 'log'],
        ];
    }
}
