<?php
/**
 * Created By SUblime Text 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

/**
 * @property int $id id
 * @property string $company_name 公司名称
 * @property string $coin_name_zh 币种中文名
 * @property string $coin_name_en 币种英文名
 * @property string $address 热钱包地址
 * @property int $platform_id 平台id
 * @property string $owner 平台id
 */
class CoinPublish extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_publish}}';
    }

    public static function getPublish($id)
    {
        return self::find()->where(['id' => $id])->one();
    }

}
