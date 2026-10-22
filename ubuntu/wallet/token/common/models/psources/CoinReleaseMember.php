<?php
/**
 * Created By Sublime Test 3
 *
 * @author rlgyzhcn <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;

/**
 * CoinReleaseMember
 *
 * @property int    $id
 * @property int    $rule_id
 * @property int    $user_id
 * @property int    $amount              币种总数
 * @property int    $release             币种释放总数
 * @property int    $freeze              币种冻结总数
 * @property int    $output              币种提取总数
 * @property int    $platform_id         平台id
 * @property string $mobile              手机号码
 * @property string $coin                币种
 * @property string $company_name        所属公司
 */
class CoinReleaseMember extends BaseActiveRecord
{
    const SCENARIOS_ADD    = 'add';
    const SCENARIOS_UPDATE = 'update';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_release_member}}';
    }

    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'rule_id'      => '规则ID',
            'user_id'      => '用户ID',
            'amount'       => '总量',
            'release'      => '已解冻',
            'freeze'       => '冻结',
            'platform_id'  => '平台id',
            'mobile'       => '手机',
            'coin'         => '币种',
            'company_name' => '所属公司名称',
        ];
    }

    public function rules()
    {
        return [
            [['id', 'rule_id', 'user_id', 'amount', 'release', 'freeze', 'platform_id'], 'integer'],
            [['mobile', 'coin', 'company_name'], 'string'],
            [['rule_id', 'amount', 'mobile', 'coin'], 'required', 'on' => self::SCENARIOS_ADD],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function sercians()
    {
        return [
            self::SCENARIOS_ADD    => ['rule_id', 'mobile', 'coin', 'amount'],
            self::SCENARIOS_UPDATE => ['id', 'rule_id', 'mobile', 'coin', 'amount'],
        ];
    }

    /**
     * 导入数据
     *
     * @param  int    $datas [0] mobile
     * @param  string $datas [1] coin
     * @param  int    $datas [2] amount
     * @return boolean
     */
    public static function implode(array $datas)
    {
        if (!is_array($datas)) {
            return false;
        }
        try {
            $result = self::getDb()->createCommand()->batchInsert(self::tableName(), ['rule_id', 'mobile', 'coin', 'amount', 'release', 'freeze', 'company_name'], $datas)->execute();
            return $result;
        } catch (\Exception $e) {
        }
        return false;
    }

    /**
     * 批量获取锁仓规则下的人数
     *
     * @param  array $ids 规则id
     * @return array|boolean 返回id为键,数量为值的数组
     */
    public static function getCountByRuleIds($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $query = CoinReleaseMember::find()->select(['rule_id', 'COUNT(id) as count'])
                                  ->groupBy('rule_id')->where(['in', 'rule_id', $ids]);
        // return $query->createCommand()->getRawSql();
        $datas = $query->asArray()->all();
        return array_column($datas, 'count', 'rule_id');
    }

    /**
     * 批量返回用户
     *
     * @param  integer|array $ids
     * @return array
     */
    public static function getMemberByRuleIds($ids)
    {
        return CoinReleaseMember::find()->where(['rule_id' => $ids])->andWhere(['>', 'freeze', 0])->asArray()->all();
    }

    public static function getMemberListByMobile($ruleIds,$mobile)
    {
        return self::find()->where(['in','rule_id',$ruleIds])->andWhere(['mobile' => $mobile])->select('id,rule_id,mobile,coin,amount,release,freeze,output')->asArray()->all();
    }
}
