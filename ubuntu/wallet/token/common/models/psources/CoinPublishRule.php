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
 * CoinPublishRule
 * 锁仓规则表
 *
 * @param integer $qid 任务id
 * @param integer $pid 监控币种id
 */
class CoinPublishRule extends BaseActiveRecord
{
    const SCENARIOS_ADD      = 'add';
    const SCENARIOS_UPDAET   = 'update';
    const DATE_FORMAT        = [
        0 => 'Y-m-d H:i:s',
        1 => 'm-d H:i:s',
        2 => 'd H:i:s',
        3 => 'H:i:s',
    ];
    const DATE_FORMAT_PREFIX = [
        0 => '',
        1 => 'Y-',
        2 => 'Y-m-',
        3 => 'Y-m-d ',
    ];

    const REPEAT_NORMAL = 0; //不重复
    const REPEAT_YEAR   = 1; //每年
    const REPEAT_MONTH  = 2; //每月

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_publish_rule}}';
    }

    public function getMemberCount()
    {
        return CoinReleaseMember::find()->where(['rule_id' => $this->id])->count();
    }

    public static function getRuleCountByPid($pid)
    {
        return self::find()->where(['pid' => $pid])->count();
    }


    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id'           => 'ID',
            'sid'          => '锁仓编号',
            'lock'         => '锁仓比例',
            'release'      => '释放比例',
            'type'         => '释放类型',
            'repeat'       => '重复',
            'release_time' => '释放时间',
            'notice'       => '备注',
            'pid'          => '规则id',
            'company_name' => '所属公司名称',
        ];
    }

    public function sercians()
    {
        return [
            self::SCENARIOS_ADD    => ['lock', 'release', 'type', 'repeat', 'release_time', 'notice'],
            self::SCENARIOS_UPDAET => ['id', 'sid', 'lock', 'release', 'type', 'repeat', 'release_time', 'notice'],
        ];
    }

    public function rules()
    {
        return [
            [['sid', 'release_time', 'notice', 'company_name'], 'string'],
            [['sid', 'release_time'], 'string', 'max' => 255],
            [['release_time'], 'validateTime'],
            [['id', 'lock', 'release', 'type', 'repeat', 'pid'], 'integer'],
            [['lock', 'release', 'type', 'repeat', 'release_time'], 'required', 'on' => self::SCENARIOS_ADD],
            [
                ['id', 'sid', 'lock', 'release', 'type', 'repeat', 'release_time'],
                'required',
                'on' => self::SCENARIOS_UPDAET,
            ],
        ];
    }

    /**
     * 获取距离下一次执行的时间(秒)
     *
     * @param  int $timestamp   开始时间戳
     * @param  int $repeat_type 重复类型
     * @param  int $repeat_time 重复时间戳
     * @return int|false
     */
    public static function getDelay($timestamp, $repeat_type, $repeat_time)
    {
        if (empty($timestamp)) {
            $timestamp = time();
        }
        $next_timestamp = self::getNextExecTimestamp($timestamp, $repeat_type, $repeat_time);
        if ($next_timestamp < $timestamp) {
            return false;
        }
        return $next_timestamp - $timestamp;
    }

    /**
     * 返回下一次执行的时间戳
     *
     * @param  integer $timestamp   开始时间戳
     * @param  integer $repeat_type 重复类型
     * @param  string  $repeat_time 数据库存储的时间戳
     * @return integer
     */
    public static function getNextExecTimestamp($timestamp, $repeat_type, $repeat_time)
    {
        if (empty($timestamp)) {
            $timestamp = time();
        }
        $format         = self::DATE_FORMAT[$repeat_type];
        //echo $format.PHP_EOL;
        $repeat         = date($format, $repeat_time);
        //echo $repeat.PHP_EOL;
        $prefix         = date(self::DATE_FORMAT_PREFIX[$repeat_type], $timestamp);
        //echo $prefix.PHP_EOL;
        $next_time      = $prefix . $repeat;
        //echo $next_time.PHP_EOL;
        $next_timestamp = strtotime($next_time);
        //如果小于$timestamp, 加上重复的频率
        if ($next_timestamp < $timestamp) {
            if ($repeat_type == self::REPEAT_YEAR) {
                $next_timestamp = strtotime('+1 year', $next_timestamp);
            } elseif ($repeat_type == self::REPEAT_MONTH) {
                $next_timestamp = strtotime('+1 month', $next_timestamp);
            }
        }
        return $next_timestamp;
    }

    /**
     * 验证时间
     *
     * @param  [type] $attribute      [description]
     * @param  [type] $params         [description]
     * @return [type] [description]
     */
    public function validateTime($attribute, $params)
    {
        if ($this->repeat == 0) {
            if (strtotime($this->$attribute) <= time()) {
                $this->addErrors(['无效的释放时间']);
            }
        }
    }

    /**
     * 获取当月解冻总量
     *
     * @param  integer $id
     * @return integer
     */
    public static function getReleaseAmountThisMonthById($id)
    {
        //获取时间范围
        $timestamp   = time();
        $month       = date('Y-m', $timestamp);
        $month_start = strtotime($month . '-01 00:00:00');
        $month_end   = strtotime('+1 month', $month_start);
        // 获取当月计划执行的规则 (每天+每月+每年+不重复的)
        $datas = CoinPublishRule::find()->where(['pid' => $id])->asArray()->all();
        foreach ($datas as $key => $value) {
            $variable = self::isVariableInRange($month_start, $month_end, $value['repeat'], strtotime($value['release_time']));
            if ($variable === false) {
                unset($datas[$key]);
            }
        }
        $datas = array_column($datas, null, 'id'); // 所有规则

        //获取规则下的所有用户
        $ids     = array_keys($datas);
        $members = CoinReleaseMember::getMemberByRuleIds($ids);
        // 已rule_id为键合并数组
        $new_members = [];
        foreach ($members as $key => &$value) {
            $new_members[$value['rule_id']][] = $value;
        }
        $members = &$new_members;

        $amount = 0;
        foreach ($members as $rule_id => $value) {
            $rule              = $datas[$rule_id];
            $lock_present      = $rule['lock'];
            $rel_present       = $rule['release'];
            $type              = $rule['type'];
            $repeat            = $rule['repeat'];
            $release_timestamp = strtotime($rule['release_time']);
            foreach ($value as $key => $item) {
                $amount += self::calReleaseAmountMonth($lock_present, $rel_present, $type, $repeat, $item['amount'], $item['freeze'], $timestamp, $month_end, $release_timestamp);
            }
        }
        return $amount;
    }

    /**
     * 获取时间范围内的有效任务(将要执行的)
     * zcy 判断release_time 根据$repeat 规则 在不在 $start和$end范围内
     * @param  string  $start        时间戳
     * @param  string  $end          时间戳
     * @param  integer $repeat       重复方法
     * @param  string  $release_time 时间戳
     * @return boolean $result
     */
    public static function isVariableInRange($start, $end, $repeat, $release_time)
    {
        $delay = self::getDelay($start, $repeat, $release_time);
        if (false === $delay) {
            return false;
        }
        if ($start + $delay > $end) {
            return false;
        }
        return true;
    }

    /**
     * 计算一次释放币种数量
     *
     * @param  integer $lock_present 锁仓百分比
     * @param  integer $rel_present  释放百分比
     * @param  integer $type         释放类型,1按总量,2按余量
     * @param  integer $amount       用户币的总量
     * @param  integer $freeze       用户释放的币
     * @param  integer $n            计算上n次释放数量
     * @return integer|boolean
     */
    public static function calReleaseAmount($lock_present, $rel_present, $type, $amount, $freeze, $n = 0)
    {
        $first_lock_amount = $amount * $lock_present / 100;
        if (1 == $type) {
            return $first_lock_amount * $rel_present / 100;
        } elseif (2 == $type) {
            return $freeze * pow((100 - $rel_present) / 100, $n) * $rel_present / 100;
        }
        return false;
    }

    /**
     * 计算一个月内释放的币种数量
     *
     * @param  integer $lock_present      锁仓百分比
     * @param  integer $rel_present       释放百分比
     * @param  integer $type              释放类型,1按总量,2按余量
     * @param  integer $amount            用户币的总量
     * @param  integer $freeze            用户已经释放的币
     * @param  integer $timestamp         当前时间戳
     * @param  integer $month_end         截止时间戳
     * @param  integer $repeat            重复类型
     * @param  integer $release_timestamp 重复时间戳
     * @return integer
     */
    public static function calReleaseAmountMonth($lock_present, $rel_present, $type, $repeat, $amount, $freeze, $timestamp, $month_end, $release_timestamp)
    {
        $result      = 0;
        $is_need_executed = self::isVariableInRange($timestamp, $month_end, $repeat, $release_timestamp);    // zcy 是否将要释放
        if ($is_need_executed) {
            $result += self::calReleaseAmount($lock_present, $rel_present, $type, $amount, $freeze, 0);    //zcy 这次将需要释放的量
        } else {
            //如果是第一次添加的不计算
            if ($amount * $lock_present / 100 != $freeze) {
                $result += self::calReleaseAmount($lock_present, $rel_present, $type, $amount, $freeze, -1);      //zcy 这次已经释放过的量(上次释放的量)
            }
        }
        return $result;
    }


    public static function getPublishRuleItemsByPid($pid)
    {
        return self::find()->where(['pid' => $pid])->asArray()->all();
    }
}
