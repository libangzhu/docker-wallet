<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/11/2
 * Time: 11:14
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

class CoinApplicateRank extends BaseActiveRecord
{
    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_applicate_rank}}';
    }

    public function getApplication()
    {
        return $this->hasOne(CoinApplication::class, ['id' => 'relate_id']);
    }

    public static function getApplicateList($type, $user_platform_id = null)
    {
        $applicate_rank_model = self::find();
        if(1 === $user_platform_id) {
            $data = $applicate_rank_model->JoinWith(['application'], false)
                ->select('relate_id as id,sort,name,'.self::tableName().".type")
                ->orderBy(self::tableName().'.sort asc')->where([self::tableName().'.type' => $type])->asArray()->all();
        } else {
            $data = $applicate_rank_model->JoinWith(['application'], false)
                ->select('relate_id as id,sort,name,'.self::tableName().".type")
                ->orderBy(self::tableName().'.sort asc')->where([self::tableName().'.type' => $type, self::tableName().'.platform_id' => $user_platform_id])->asArray()->all();
        }

        return $data;
    }

    public static function getApplicate($relate_id,$type)
    {
        return self::find()->where(['relate_id' => $relate_id,'type' => $type])->one();
    }

    public static function getAppList($type)
    {
        $applicate_rank_model = self::find();
        $data = $applicate_rank_model->JoinWith(['application'], false)
            ->select('relate_id as app_id,sort,name,icon,advertise,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url,redirect_type,'.CoinApplication::tableName().".type")
            ->orderBy(self::tableName().'.sort asc')->where([self::tableName().'.type' => $type,'enable' => 1])->asArray()->all();
        return $data;
    }

    public static function getH5AppList()
    {
        $applicate_rank_model = self::find();
        $data = $applicate_rank_model->JoinWith(['application'], false)
            ->select('relate_id as app_id,sort,name,h5_icon,introduce_image,show_width,show_height,advertise,native_url,native_login_url,h5_url,android_url,ios_url,app_store_url,redirect_type,open_type,'.CoinApplication::tableName().".type,".CoinApplicateRank::tableName().".type as rank_type")
            ->where(['enable' => 1])
            ->orderBy(self::tableName().'.sort asc')->asArray()->all();
        return $data;
    }


}