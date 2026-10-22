<?php
namespace common\components;

use Yii;
use yii\base\Model;

/**
 * Base
 * 基本类
 * -----
 * @author Verdient。
 * @version 1.0.0
 */
class Base extends Model
{
	/**
	 * @var protected String $_key
	 * 签名密钥
	 * ---------------------------
	 * @method Config
	 * @author Verdient。
	 */
	protected $_key;

	/**
	 * @var public $_url
	 * URL 路径
	 * -----------------
	 * @author Verdient。
	 */
	protected $_url;

	/**
	 * @var protected $_code
	 * 状态码
	 * ---------------------
	 * @author Verdient。
	 */
	protected $_code;

	/**
	 * @var protected $_data
	 * 数据
	 * ---------------------
	 * @author Verdient。
	 */
	protected $_data;

	/**
	 * @var protected $_header
	 * 响应头部信息
	 * -----------------------
	 * @author Verdient。
	 */
	protected $_header;

	/**
	 * @var protected $_response
	 * 响应原文
	 * -------------------------
	 * @author Verdient。
	 */
	protected $_response;

	/**
	 * @var protected $_error
	 * 错误信息
	 * ----------------------
	 * @author Verdient。
	 */
	protected $_error;

	/**
	 * __construct(String $key, String $url[, Mixed $id = null])
	 * 初始化
	 * ---------------------------------------------------------
	 * @param String $key 签名密钥
	 * @param String $url url
	 * @param Mixed $id 实例编号
	 * --------------------------
	 * @author Verdient。
	 */
	public function __construct($key, $url){
		$this->_key = $key;
		$this->_url = $url;
		parent::__construct();
	}

	/**
	 * getCode()
	 * 获取响应码
	 * ---------
	 * @return Integer
	 * @author Verdient。
	 */
	public function getCode(){
		return $this->_code;
	}

	/**
	 * getData()
	 * 获取响应数据
	 * -----------
	 * @return Array
	 * @author Verdient。
	 */
	public function getData(){
		return $this->_data['data'];
	}

	/**
	 * getHeader()
	 * 获取响应头数据
	 * -------------
	 * @return Array
	 * @author Verdient。
	 */
	public function getHeader(){
		return $this->_header;
	}

	/**
	 * getError()
	 * 获取响应错误
	 * -----------
	 * @return Array
	 * @author Verdient。
	 */
	public function getError(){
		return $this->_error;
	}

	/**
	 * getResponse()
	 * 获取响应原文
	 * -------------
	 * @return Array
	 * @author Verdient。
	 */
	public function getResponse(){
		return $this->_response;
	}

	/**
	 * formName()
	 * 获取表单名称
	 * -----------
	 * @inheritdoc
	 * -----------
	 * @return String
	 * @author Verdient。
	 */
	public function formName(){
		return '';
	}

	/**
	 * setAttributes(Array $values[, Boolean $safeOnly = false])
	 * 设置属性
	 * ---------------------------------------------------------
	 * @param Array $values 要设置的属性值
	 * @param Boolean $safeOnly 是否仅设置安全的属性
	 * -------------------------------------------
	 * @inheritdoc
	 * -----------
	 * @author Verdient。
	 */
	public function setAttributes($values, $safeOnly = false){
		return parent::setAttributes($values, $safeOnly);
	}

	/**
	 * _formatError(Array $errors)
	 * 格式化错误
	 * ---------------------------
	 * @return Array
	 * @author Verdient。
	 */
	protected function _formatError(Array $errors){
		$errorMap = [];
		foreach($errors as $error){
			$errorMap[$error['field']][] = $error['message'];
		}
		return $errorMap;
	}

	/**
	 * _send(String $method, String $url[, Array $data = [], Array $query = []])
	 * 发送数据
	 * -------------------------------------------------------------------------
	 * @param String $method 访问方法
	 * @param String $url URL路径
	 * @param Array $data 发送的数据
	 * @param Array $query 查询条件
	 * ------------------------------
	 * @return Array
	 * @author Verdient。
	 */
	protected function _send($method, $url, Array $data = [], Array $query = []){
		$method = strtolower($method);
		if(!empty($query)){
			Yii::$app->cUrl->setQuery($query);
		}
		$signature = new Signature();
		$signature->key = $this->_key;
		$signature->verb = $method;
		$signature->contentType = 'application/json';
		$signature->signatureMethod = 'sha256';
		if(!empty($data)){
			Yii::$app->cUrl->setData($data, 'JSON');
		}
		$signature->content = $data;
		Yii::$app->cUrl->setHeader([
			'Signature' => $signature->signature,
			'Date' => $signature->date,
			'Verb' => $signature->verb,
			'Signature-Method' => $signature->signatureMethod,
			'Signature-Version' => $signature->signatureVersion
		]);
		$options = Yii::$app->cUrl->getOptions();
		$response = Yii::$app->cUrl->$method($url, 'JSON');
		Yii::trace(['url' => $url, 'cUrl' => $options, 'response' => $response], __METHOD__);
		$this->_response = $response;
		$this->_code = isset($response['content']['code']) ? $response['content']['code'] : null;
		if(isset($response['content']['id'])){
			$this->_id = $response['content']['id'];
		}
		$this->_data = isset($response['content']) ? $response['content'] : null;
		$this->_header = isset($response['header']) ? $response['header'] : null;
	}
}