<?php
/**
 * Created By Sublime Text 3
 *
 * @date    2018-09-06 14:07:10
 * @authors rlgy <rlgyzhcn@qq.com>
 */

namespace common\models\psources;

use common\core\BaseActiveRecord;
use Yii;
use common\business\Chain33Business;
use backend\jobs\QueryTransJob;

/**
 * @property integer $id
 * @property integer $uid
 * @property integer $mid
 * @property integer $list_id
 * @property string  $txhash
 * @property string  $mobile
 * @property string  $coin
 * @property double  $amount
 * @property string  $to_address
 * @property integer $check_first_status
 * @property integer $check_first_uid
 * @property integer $check_second_status
 * @property integer $check_second_uid
 * @property integer $status
 * @property string  $company_name
 * @property string  $create_time
 * @property string  $update_time
 */
class CoinReleaseCheck extends BaseActiveRecord
{
    const SCENARIOS_CHECK  = 'check'; //审核列表筛选
    const SCENARIOS_SEARCH = 'search'; //查询列表筛选

    const CHECK_FIRST_STATUS_NORMAL  = 1; // 初审未审核
    const CHECK_FIRST_STATUS_SUCCEED = 2; // 初审审核通过
    const CHECK_FIRST_STATUS_FAILD   = 3; // 初审审核未通过

    const CHECK_SECOND_STATUS_NORMAL  = 1; // 复审未审核
    const CHECK_SECOND_STATUS_SUCCEED = 2; // 复审审核通过
    const CHECK_SECOND_STATUS_FAILD   = 3; // 复审审核未通过

    const STATUS_NORMAL  = 0; // 提币申请审核未通过
    const STATUS_PADDING = 1; // 提币申请审核完成,提币未完成
    const STATUS_CANCEL  = 2; // 提币申请被撤提
    const STATUS_FAILD   = 3; // 提币失败
    const STATUS_SUCCEED = 4; // 提币成功
    const STATUS_AUTO_CHECKING = 5; //自动审核中
    const STATUS_AUTO_FAILD = 6; //自动审核失败
    const STATUS_AUTO_EXCEPTION = 7; //自动审核异常(区块链异常)

    public $start_time = '';
    public $end_time   = '';

    public static function getDb()
    {
        return Yii::$app->get('p_sources');
    }

    public static function tableName()
    {
        return '{{%coin_release_check}}';
    }

    public function formName()
    {
        return '';
    }

    public function attributeLabels()
    {
        return [
            'id'                  => 'ID',
            'uid'                 => 'UID',
            'list_id'             => '记录ID',
            'txhash'              => '交易txhash',
            'mobile'              => '手机',
            'coin'                => '币种',
            'to_address'          => '对方地址',
            'check_first_status'  => '初审状态',
            'check_first_uid'     => '初审员',
            'check_second_status' => '复审状态',
            'check_second_uid'    => '复审员',
            'create_time'         => '创建时间',
            'update_time'         => '更新时间',
            'start_time'          => '提交开始时间',
            'end_time'            => '提交结束时间',
            'status'              => '审核状态',
        ];
    }

    public function sercians()
    {
        return [
            self::SCENARIOS_CHECK  => ['coin', 'start_time', 'end_time', 'mobile'],
            self::SCENARIOS_SEARCH => ['status', 'coin', 'check_first_uid', 'check_second_uid', 'start_time', 'end_time', 'mobile'],
        ];
    }

    public function rules()
    {
        return [
            [['id', 'uid', 'check_first_status', 'check_first_uid', 'check_second_status', 'check_second_uid', 'status', 'list_id'], 'integer'],
            [['mobile', 'coin', 'to_address', 'create_time', 'update_time', 'start_time', 'end_time'], 'string', 'max' => 255],
            [['end_time'], 'compare', 'compareAttribute' => 'start_time', 'operator' => '>=', 'skipOnEmpty' => true],
            [['coin', 'start_time', 'end_time', 'mobile'], 'default', 'value' => '', 'on' => self::SCENARIOS_CHECK],
            [['status', 'coin', 'check_first_uid', 'check_second_uid', 'start_time', 'end_time', 'mobile'], 'default', 'value' => '', 'on' => self::SCENARIOS_SEARCH],
        ];
    }

    /**
     * 初审
     *
     * @param int $status
     * @throws \Exception
     * @return bool
     */
    public function check_first(int $status)
    {
        if ($this->check_second_status != CoinReleaseCheck::CHECK_SECOND_STATUS_NORMAL) {
            throw new \Exception('已经复核的申请不允许初审', -1);
        }
        if (self::CHECK_FIRST_STATUS_FAILD == $status) {
            $this->check_first_status = $status;
            return $this->check_cancel();
        } else {
            $this->check_first_uid    = Yii::$app->user->id;
            $this->check_first_status = $status;
            return $this->save();
        }
    }

    /**
     * @param int    $status
     * @param string $address 提币地址
     * @throws \Exception
     * @return bool
     */
    public function check_second($status, $address = '')
    {
        if (self::CHECK_FIRST_STATUS_SUCCEED != $this->check_first_status) {
            throw new \Exception('初审未通过!', -1);
        }
        if (CoinReleaseCheck::CHECK_SECOND_STATUS_FAILD == $status) {
            $this->check_second_status = $status;
            return $this->check_cancel();
        } else {
            //开启事务
            $trans = self::getDb()->beginTransaction();
            try {
                $this->check_second_uid    = Yii::$app->user->id;
                $this->check_second_status = $status;
                $this->status              = self::STATUS_PADDING;
                //区块链转币
                if (strtoupper($this->coin) == 'BTY') {
                    $isToken = false;
                } else {
                    $isToken = true;
                }
                $walletpasswd = Yii::$app->redis->get('wallet_passwd');
                $node_params = Yii::$app->params['chain_parallel']['secondary'];
                $unChain = Chain33Business::unLockWallet($walletpasswd, $node_params);

                if(false == $unChain['result']['isOK']){
                    throw new \Exception($unChain['result']['msg'], -1);
                }
                $result = Chain33Business::tradeToken(
                    (int)$this->amount,
                    '',
                    1000,
                    $isToken,
                    trim($this->to_address),
                    $this->coin,
                    $node_params
                );
//                $result = Chain33Business::transToken(
//                    trim($address),
//                    trim($this->to_address),
//                    (int)$this->amount,
//                    "锁仓释放",
//                    $isToken,
//                    $this->coin
//                );
                if (is_array($result)) {
                    $trans->rollBack();
                    throw new \Exception(Yii::t('backend', $result['msg']), $result['code']);
                }
                $this->txhash = $result;
                if ($this->save(false)) {
                    Yii::$app->queue->delay(15)->push(new QueryTransJob([
                        'txhash' => $result,
                        'lid'    => $this->list_id,
                        'cid'    => $this->id
                    ]));
                    $trans->commit();
                    return true;
                } else {
                    throw new \Exception('修改审核状态失败', -1);
                }
            } catch (\Exception $exception) {
                throw $exception;
            }
        }
    }

    /**
     * 提币审核撤提
     *
     * @return bool
     * @throws \Exception
     */
    public function check_cancel()
    {
        //开启事务
        $trans = self::getDb()->beginTransaction();
        try {
            $this->status = self::STATUS_CANCEL;
            if (empty($this->check_first_uid)) {
                $this->check_first_uid = Yii::$app->user->id;
            }
            if (empty($this->check_second_uid)) {
                $this->check_second_uid = Yii::$app->user->id;
            }
            if ($this->save(false)) {
                //修改提币记录
                $coin_release_list = CoinReleaseList::findOne($this->list_id);
                if ($coin_release_list) {
                    $coin_release_list->status = 2;//提币失败
                    if ($coin_release_list->save(false)) {
                        //返还用户资产
                        $coin_release_member          = CoinReleaseMember::findOne($this->mid);
                        $coin_release_member->release += $this->amount;
                        $coin_release_member->output  -= $this->amount;
                        if ($coin_release_member->save(false)) {
                            $trans->commit();
                            return true;
                        } else {
                            throw new \Exception('返还资产失败', -1);
                        }
                    } else {
                        throw new \Exception('修改提币记录失败', -1);
                    }
                } else {
                    throw new \Exception('获取提币记录失败', -1);
                }
            } else {
                throw new \Exception('修改审核状态失败', -1);
            }
        } catch (\Exception $exception) {
            $trans->rollBack();
            throw $exception;
        }
    }
}
