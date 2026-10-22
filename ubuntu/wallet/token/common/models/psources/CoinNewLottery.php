<?php
/**
 * Created by PhpStorm.
 * User: ZCY
 * Date: 2018/12/14
 * Time: 15:49
 */

namespace common\models\psources;
use Yii;

class CoinNewLottery extends BaseActiveRecord
{
    public static function tableName()
    {
        return 'coin_new_lottery';
    }

    public function beforeSave($instert)
    {
        if (parent::beforeSave($instert)) {
            if ($this->isNewRecord) {
                $this->create_time = Yii::$app->formatter->asTimestamp('now');
            } else {
                $this->update_time = Yii::$app->formatter->asTimestamp('now');
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取指定日期最后的一条记录(stage max)
     */
    public static function getMaxStage($date = '')
    {
        if(!$date){
            $date = date('Y-m-d');
        }
        return self::find()->where(['date' => $date])->orderBy(['stage' => SORT_DESC])->one();
    }

    public static function addOneRecord($start_height,$last_lottery_height,$stage,$round,$start_date)
    {
        $coin_lottery = new self();
        $coin_lottery->stage = $stage;
        $coin_lottery->start_height = $start_height;
        $coin_lottery->last_lottery_height = $last_lottery_height;
        $coin_lottery->status = 2;
        $coin_lottery->round = $round;
        $coin_lottery->date = $start_date;
        $coin_lottery->save();
    }

    /**
     * @return array|null|\yii\db\ActiveRecord
     * 获取最新记录
     */
    public static function getLastRecord()
    {
        return self::find()->orderBy(['id' => SORT_DESC])->asArray()->one();
    }

}