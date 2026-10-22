<?php
/**
 * Created by PhpStorm.
 * User: rlgyzhcn
 * Date: 18-9-19
 * Time: 下午4:37
 */

namespace common\models\psources;

/**
 * Class CoinGuessResult
 *
 * @property integer $id
 * @property integer $stage
 * @property integer $start_height
 * @property integer $end_height
 * @property integer $result
 * @property integer $is_award
 * @package common\models\psources
 */
class CoinGuessResult extends BaseActiveRecord
{
    public static function tableName()
    {
        return 'coin_guess_result';
    }

    /**
     * 获取最后的一条记录(stage max)
     */
    public static function getMaxStage()
    {
        return self::find()->orderBy(['stage' => SORT_DESC])->one();
    }

    /**
     * 获取等待开奖的竞猜
     *
     * @return CoinGuessResult|null|array
     */
    public static function getWaitingAward()
    {
        return self::find()->where(['is_award' => 0])->orderBy(['stage' => SORT_DESC])->one();
    }
}