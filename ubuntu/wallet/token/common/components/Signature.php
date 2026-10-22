<?php
namespace common\components;

use yii\base\Component;
use yii\base\InvalidParamException;
use yii\base\InvalidValueException;

/**
 * Signature
 * 签名
 * ---------
 * @author Verdient。
 * @version 1.0.0
 */
class Signature extends Component
{
	/**
	 * @var public String $key
	 * 签名密钥
	 * -----------------------
	 * @author Verdient。
	 */
	public $key;

	/**
	 * @var protected String $_verb
	 * 访问方式
	 * ----------------------------
	 * @author Verdient。
	 */
	protected $_verb;

	/**
	 * @var protected String $_contentType
	 * 消息体类型
	 * -----------------------------------
	 * @author Verdient。
	 */
	protected $_contentType = 'application/json';

	/**
	 * @var protected String $_content
	 * 消息体
	 * -------------------------------
	 * @author Verdient。
	 */
	protected $_content = [];

	/**
	 * @var protected String $_contentMd5
	 * 消息体MD5值
	 * ----------------------------------
	 * @author Verdient。
	 */
	protected $_contentMd5 = null;

	/**
	 * @var protected String $_date
	 * 日期
	 * ----------------------------
	 * @author Verdient。
	 */
	protected $_date;

	/**
	 * @var protected String $_signature
	 * 签名
	 * ---------------------------------
	 * @author Verdient。
	 */
	protected $_signature;

	/**
	 * @var protected String $_signatureMethod
	 * 签名方法
	 * ---------------------------------------
	 * @author Verdient。
	 */
	protected $_signatureMethod;

	/**
	 * @var protected String $_signatureVersion
	 * 签名版本
	 * ----------------------------------------
	 * @author Verdient。
	 */
	protected $_signatureVersion = '1.0';

	/**
	 * @var protected String $_enabledVerb
	 * 允许的访问方式
	 * -----------------------------------
	 * @author Verdient。
	 */
	protected $_enabledVerb = ['get', 'post', 'put', 'patch', 'delete', 'options'];

	/**
	 * @var protected String $_enabledContentType
	 * 允许的消息体类型
	 * ------------------------------------------
	 * @author Verdient。
	 */
	protected $_enabledContentType = ['application/json'];

	/**
	 * @var protected String $_enabledSignatureMethod
	 * 允许的签名方法
	 * ----------------------------------------------
	 * @author Verdient。
	 */
	protected $_enabledSignatureMethod = ['sha1', 'sha256'];

	/**
	 * @var protected String $_enabledSignatureMethod
	 * 允许的签名版本
	 * ----------------------------------------------
	 * @author Verdient。
	 */
	protected $_enabledSignatureVersion = ['1.0'];

	/**
	 * setVerb(String $verb)
	 * 设置访问方式
	 * ---------------------
	 * @author Verdient。
	 */
	public function setVerb($verb){
		if(!$verb){
			throw new InvalidValueException('Verb can not be empty');
		}
		if(!is_string($verb)){
			throw new InvalidValueException('Verb must be a string');
		}
		$verb = strtolower($verb);
		if(!in_array($verb, $this->_enabledVerb)){
			throw new InvalidValueException('Verb must in: [' . implode(', ', $this->_enabledVerb) . ']');
		}
		$this->_verb = $verb;
	}

	/**
	 * getVerb(String $verb)
	 * 获取访问方式
	 * ---------------------
	 * @return String
	 * @author Verdient。
	 */
	public function getVerb(){
		return $this->_verb;
	}

	/**
	 * setContentType(String $contentType)
	 * 设置消息体类型
	 * -----------------------------------
	 * @author Verdient。
	 */
	public function setContentType($contentType){
		if(!$contentType){
			throw new InvalidValueException('ContentType can not be empty');
		}
		if(!is_string($contentType)){
			throw new InvalidValueException('ContentType must be a string');
		}
		$contentType = strtolower($contentType);
		if(!in_array($contentType, $this->_enabledContentType)){
			throw new InvalidValueException('ContentType must in: [' . implode(', ', $this->_enabledContentType) . ']');
		}
		$this->_contentType = $contentType;
	}

	/**
	 * getContentType()
	 * 获取消息体类型
	 * ----------------
	 * @return String
	 * @author Verdient。
	 */
	public function getContentType(){
		return $this->_contentType;
	}

	/**
	 * setContent(Array $content)
	 * 设置消息体
	 * --------------------------
	 * @param Array $content 消息体
	 * ----------------------------
	 * @author Verdient。
	 */
	public function setContent(Array $content){
		static::_prepareContent($content);
		$this->_content = $content;
	}

	/**
	 * getContent()
	 * 获取消息体
	 * ------------
	 * @return Array
	 * @author Verdient。
	 */
	public function getContent(){
		return $this->_content;
	}

	/**
	 * setDate(Mixed $date)
	 * 设置日期
	 * --------------------
	 * @param Mixed $date 日期
	 * -----------------------
	 * @author Verdient。
	 */
	public function setDate($date){
		if(is_numeric($date) && ctype_digit($date) && $date <= 2147483647){
			$this->_date = gmdate('D, d M Y H:i:s \G\M\T', $date);
		}else if(is_string($date)){
			$this->_date = gmdate('D, d M Y H:i:s \G\M\T', strtotime($date));
		}else{
			throw new InvalidParamException('Data must be a timestamp or date string');
		}
	}

	/**
	 * getDate()
	 * 获取日期
	 * ---------
	 * @return String
	 * @author Verdient。
	 */
	public function getDate(){
		if(!$this->_date){
			$this->_date = gmdate('D, d M Y H:i:s \G\M\T');
		}
		return $this->_date;
	}

	/**
	 * setSignatureMethod()
	 * 获取签名方法
	 * --------------------
	 * @return String
	 * @author Verdient。
	 */
	public function setSignatureMethod($signatureMethod){
		if(!$signatureMethod){
			throw new InvalidValueException('SignatureMethod can not be empty');
		}
		if(!is_string($signatureMethod)){
			throw new InvalidValueException('SignatureMethod must be a string');
		}
		$signatureMethod = strtolower($signatureMethod);
		if(!in_array($signatureMethod, $this->_enabledSignatureMethod)){
			throw new InvalidValueException('SignatureMethod must in: [' . implode(', ', $this->_enabledSignatureMethod) . ']');
		}
		$this->_signatureMethod = $signatureMethod;
	}

	/**
	 * getSignatureMethod()
	 * 获取签名方法
	 * --------------------
	 * @return String
	 * @author Verdient。
	 */
	public function getSignatureMethod(){
		return $this->_signatureMethod;
	}

	/**
	 * setSignatureVersion()
	 * 获取签名版本
	 * --------------------
	 * @return String
	 * @author Verdient。
	 */
	public function setSignatureVersion($signatureVersion){
		if(!$signatureVersion){
			throw new InvalidValueException('SignatureVersion can not be empty');
		}
		if(!is_string($signatureVersion)){
			throw new InvalidValueException('SignatureVersion must be a string');
		}
		$signatureVersion = strtolower($signatureVersion);
		if(!in_array($signatureVersion, $this->_enabledSignatureVersion)){
			throw new InvalidValueException('SignatureVersion must in: [' . implode(', ', $this->_enabledSignatureMethod) . ']');
		}
		$this->_signatureVersion = $signatureVersion;
	}

	/**
	 * getSignatureVersion()
	 * 获取签名版本
	 * ---------------------
	 * @return String
	 * @author Verdient。
	 */
	public function getSignatureVersion(){
		return $this->_signatureVersion;
	}

	/**
	 * getContentMd5()
	 * 获取消息体MD5值
	 * ---------------
	 * @return String
	 * @author Verdient。
	 */
	public function getContentMd5(){
		if(!$this->_contentMd5 !== null){
			if(!is_array($this->content)){
				throw new InvalidParamException('Content must be an Array');
			}
			$this->_contentMd5 = empty($this->content) ? '' : md5(json_encode($this->content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
		}
		return $this->_contentMd5;
	}

	/**
	 * getSignature()
	 * 获取签名
	 * --------------
	 * @return String
	 * @author Verdient。
	 */
	public function getSignature(){
		if(!$this->_signature){
			if(!$this->key){
				throw new InvalidParamException('Signature key must be set');
			}
			if(!$this->signatureMethod){
				throw new InvalidConfigException('SignatureMethod must be set');
			}
			$this->_signature = md5(hash_hmac($this->signatureMethod, $this->_buildSignatureString(), $this->key, false));
		}
		return $this->_signature;
	}

	/**
	 * _buildSignatureString()
	 * 构建待签名的字符串
	 * -----------------------
	 * @return String
	 * @author Verdient。
	 */
	protected function _buildSignatureString(){
		return strtoupper($this->verb) .
			"\n\n" .
			$this->contentMd5 .
			"\n" .
			$this->contentType .
			"\n" .
			$this->date .
			"\n" .
			$this->signatureMethod .
			"\n" .
			$this->signatureVersion .
			"\n\n" .
			$this->key .
			"\n";
	}

	/**
	 * _prepareContent(Array &$content)
	 * 准备消息体
	 * --------------------------------
	 * @param Array &$content 消息体
	 * ----------------------------
	 * @author Verdient。
	 */
	protected static function _prepareContent(&$content){
		ksort($content);
		foreach($content as $key => $value){
			if(empty($value) && $value !== 0 && $value !== '0'){
				unset($content[$key]);
			}else if(is_array($value)){
				static::_prepareContent($content[$key]);
			}
		}
	}
}