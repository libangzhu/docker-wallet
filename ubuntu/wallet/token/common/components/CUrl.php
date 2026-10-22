<?php
namespace common\components;

use Yii;
use yii\base\Component;
use yii\helpers\Json;
use yii\web\HttpException;
use common\helpers\XmlHelper;

/**
 * CUrl Model
 * cURL 模型
 * ----------------
 * @version 1.0.0
 * @author Verdient。
 */
class CUrl extends Component
{
	/**
	 * const CURLOPT_QUERY
	 * 查询参数
	 * -------------------
	 * @author Verdient。
	 */
	const CURLOPT_QUERY = 'query';

	/**
	 * @var public $onlyContent
	 * 只返回消息体
	 * ------------------------
	 * @author Verdient。
	 */
	public $onlyContent = true;

	/**
	 * @var private $_response
	 * 响应内容
	 * -----------------------
	 * @author Verident。
	 */
	private $_response = null;

	/**
	 * @var private $_responseCode
	 * 状态码
	 * ---------------------------
	 * @author Verdient。
	 */
	private $_responseCode = null;

	/**
	 * @var private $_responseHeader
	 * 响应头
	 * -----------------------------
	 * @author Verdient。
	 */
	private $_responseHeader = null;

	/**
	 * @var private $_options
	 * 参数
	 * ----------------------
	 * @author Verdient。
	 */
	private $_options = [];

	/**
	 * @var private $_curl
	 * cUrl实例
	 * -------------------
	 * @author Verdient。
	 */
	private $_curl = null;

	/**
	 * @var private $_defaultOptions
	 * 默认参数
	 * -----------------------------
	 * @author Verdient。
	 */
	private $_defaultOptions = [
		CURLOPT_USERAGENT => 'Yii2-CUrl-Agent',
		CURLOPT_TIMEOUT => 30,
		CURLOPT_CONNECTTIMEOUT => 30,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => false,
		CURLOPT_HTTPHEADER => [],
		self::CURLOPT_QUERY => [],
	];

	/**
	 * get(String $url, String $dataType)
	 * 通过get方式获取数据
	 * ------------------------------
	 * @param String $url url地址
	 * @param String $dataType 返回数据格式
	 * ------------------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function get($url, $dataType = null){
		return $this->_httpRequest('GET', $url, $dataType);
	}

	/**
	 * head(String $url)
	 * 通过head方式获取数据
	 * --------------------
	 * @param String $url url地址
	 * --------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function head($url){
		return $this->_httpRequest('HEAD', $url);
	}

	/**
	 * post(String $url, String $dataType)
	 * 通过post方式获取数据
	 * -----------------------------------
	 * @param String $url url地址
	 * @param String $dataType 返回数据格式
	 * ------------------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function post($url, $dataType = null){
		return $this->_httpRequest('POST', $url, $dataType);
	}

	/**
	 * put(String $url, String $dataType)
	 * 通过put方式获取数据
	 * -------------------
	 * @param String $url url地址
	 * @param String $dataType 返回数据格式
	 * ------------------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function put($url, $dataType = null){
		return $this->_httpRequest('PUT', $url, $dataType);
	}

	/**
	 * delete(String $url, String $dataType)
	 * 通过delete方式获取数据
	 * -------------------------------------
	 * @param String $url url地址
	 * @param String $dataType 返回数据格式
	 * ------------------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function delete($url, $dataType = null){
		return $this->_httpRequest('DELETE', $url, $dataType);
	}

	/**
	 * setHeader(Array $headers)
	 * 设置发送的头部信息
	 * --------------------------
	 * @param Array $headers 头部信息
	 * ------------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function setHeader(Array $headers){
		$header = [];
		foreach($headers as $key => $value){
			$header[] = $key . ':' . $value;
		}
		$this->setOption(CURLOPT_HTTPHEADER, $header);
	}

	/**
	 * setData(Array $data, Callable / String $callback = null)
	 * 设置发送的数据
	 * --------------------------------------------------------
	 * @param Array $data 发送的数据
	 * @param Callable / String $callback 回调函数
	 * -------------------------------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function setData(Array $data, $callback = null){
		if(is_string($callback)){
			if(strtoupper($callback) == 'JSON'){
				$data = Json::encode($data);
				$this->setHeader(['Content-Type' => 'application/json', 'Content-Length' => strlen($data)]);
				return $this->setOption(CURLOPT_POSTFIELDS, $data);
			}
			if(strtoupper($callback) == 'XML'){
				$data = XmlHelper::encode($data);
				$this->setHeader(['Content-Type' => 'application/xml', 'Content-Length' => strlen($data)]);
				return $this->setOption(CURLOPT_POSTFIELDS, $data);
			}
		}
		if(is_callable($callback)){
			$data = call_user_func($callback, $data);
		}
		return $this->setOption(CURLOPT_POSTFIELDS, $callback == null ? http_build_query($data) : $data);
	}

	/**
	 * setQuery(Array $query)
	 * 设置查询信息
	 * ----------------------
	 * @param Array $query 查询信息
	 * ---------------------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function setQuery(Array $query){
		$this->setOption(self::CURLOPT_QUERY, $query);
	}

	/**
	 * setOption(String $key, Mixed $value)
	 * 设置选项
	 * ------------------------------------
	 * @param String $key 选项名称
	 * @param Mixed $value 选项内容
	 * ----------------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function setOption($key, $value){
		if(isset($this->_options[$key]) && is_array($this->_options[$key])){
			$this->_options[$key] = array_merge($this->_options[$key], $value);
		}else{
			$this->_options[$key] = $value;
		}
		return $this;
	}

	/**
	 * setOptions(Array $options)
	 * 批量设置选项
	 * --------------------------
	 * @param String $options 选项集合
	 * -------------------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function setOptions($options){
		foreach($options as $key => $value){
			$this->setOption($key, $value);
		}
		return $this;
	}

	/**
	 * unsetOption(String $key)
	 * 删除选项
	 * ------------------------
	 * @param String $key 选项名称
	 * --------------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function unsetOption($key){
		if(isset($this->_options[$key])){
			unset($this->_options[$key]);
		}
		return $this;
	}

	/**
	 * resetOptions()
	 * 重置选项
	 * --------------
	 * @return Object
	 * @author Verdient。
	 */
	public function resetOptions(){
		if (isset($this->_options)) {
			$this->_options = [];
		}
		return $this;
	}

	/**
	 * reset()
	 * 重置
	 * -------
	 * @return Object
	 * @author Verdient。
	 */
	public function reset(){
		if($this->_curl !== null){
			@curl_close($this->_curl);
		}
		$this->_curl = null;
		$this->_options = [];
		$this->_response = null;
		$this->_responseCode = null;
		$this->_responseHeader = null;
		return $this;
	}

	/**
	 * getOption(String $key)
	 * 获取选项内容
	 * ----------------------
	 * @param String $key 选项名称
	 * ---------------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function getOption($key){
		$mergesOptions = $this->getOptions();
		return isset($mergesOptions[$key]) ? $mergesOptions[$key] : false;
	}

	/**
	 * getOptions()
	 * 获取所有的选项内容
	 * ------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function getOptions(){
		return $this->_options + $this->_defaultOptions;
	}

	/**
	 * getInfo(String $opt)
	 * 获取连接资源句柄的信息
	 * ----------------------
	 * @param String $opt 选项名称
	 * ---------------------------
	 * @return Object
	 * @author Verdient。
	 */
	public function getInfo($opt = null){
		if($this->_curl !== null && $opt === null){
			return curl_getinfo($this->_curl);
		}else if($this->_curl !== null && $opt !== null){
			return curl_getinfo($this->_curl, $opt);
		}else{
			return [];
		}
	}

	/**
	 * getResponse()
	 * 获取响应内容
	 * -------------
	 * @return Mixed
	 * @author Verdient。
	 */
	public function getResponse(){
		return $this->_response;
	}

	/**
	 * getResponseCode()
	 * 获取状态码
	 * -----------------
	 * @return Integer
	 * @author Verdient。
	 */
	public function getResponseCode(){
		return $this->_responseCode;
	}

	/**
	 * getResponseHeader()
	 * 获取响应头
	 * -------------------
	 * @return Integer
	 * @author Verdient。
	 */
	public function getResponseHeader(){
		if(empty($this->_responseHeader)){
			$this->_responseHeader = [];
		}else if(is_string($this->_responseHeader)){
			$headers = [];
			foreach(explode("\r\n", $this->_responseHeader) as $i => $line){
				if($i !== 0 && !empty($line)){
					list($key, $value) = explode(': ', $line);
					$headers[$key] = $value;
				}
			}
			$this->_responseHeader = $headers;
		}
		return $this->_responseHeader;
	}

	/**
	 * _httpRequest(String $method, String $url, String $dataType)
	 * http请求
	 * -----------------------------------------------------------
	 * @param String $method 请求方式
	 * @param String $url 请求地址
	 * @param String $dataType 返回数据格式
	 * ------------------------------------
	 * @return Object
	 * @author Verdient。
	 */
	private function _httpRequest($method, $url, $dataType = null){
		if($this->onlyContent === true){
			$this->setOption(CURLOPT_HEADER, false);
		}else{
			$this->setOption(CURLOPT_HEADER, true);
		}
		$this->setOption(CURLOPT_CUSTOMREQUEST, strtoupper($method));
		if($method === 'HEAD'){
			$this->setOption(CURLOPT_NOBODY, true);
			$this->unsetOption(CURLOPT_WRITEFUNCTION);
		}
		$query = $this->getOption(self::CURLOPT_QUERY);
		if(!empty($query)){
			$url = $url . '?' . http_build_query($query);
		}
		$this->_curl = curl_init($url);
		$options = $this->getOptions();
		$curlOptions = [];
		foreach($options as $key => $value){
			if(is_numeric($key)){
				$curlOptions[$key] = $value;
			}
		}
		curl_setopt_array($this->_curl, $curlOptions);
		$body = curl_exec($this->_curl);
		if($body === false){
			$errorCode  = curl_errno($this->_curl);
			Yii::error(['code' => $errorCode, 'type' => curl_strerror($errorCode), 'message' => curl_error($this->_curl), 'info' => curl_getinfo($this->_curl), 'version' => curl_version()], __METHOD__);
			switch($errorCode){
				case 7:
					throw new HttpException(504, 'CUrl requset timeout');
					break;
				default:
					throw new HttpException(502, 'CUrl requset error(' . $errorCode . ')');
					break;
			}
		}
		$this->_responseCode = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
		if(!$this->getOption(CURLOPT_HEADER)){
			$this->_response = $body;
		}else{
			$headerSize = curl_getinfo($this->_curl, CURLINFO_HEADER_SIZE);
			$this->_responseHeader = substr($body, 0, $headerSize);
			$this->_response = substr($body, $headerSize);
		}
		if($this->getOption(CURLOPT_CUSTOMREQUEST) === 'HEAD'){
			$this->reset();
			return true;
		}
		Yii::trace(['method' => $method, 'url' => $url, 'options' => $options, 'code' => $this->_responseCode, 'response' => $this->_response], __METHOD__);
		switch(strtoupper($dataType)){
			case 'JSON':
				try{
					$this->_response = Json::decode($this->_response);
				}catch(\Exception $e){
					Yii::warning(['method' => $method, 'url' => $url, 'options' => $options, 'responseCode' => $this->_responseCode, 'response' => $this->_response], __METHOD__);
				}
				break;

			case 'XML':
				try{
					$this->_response = XmlHelper::decode($this->_response);
				}catch(\Exception $e){
					Yii::warning(['method' => $method, 'url' => $url, 'options' => $options, 'responseCode' => $this->_responseCode, 'response' => $this->_response], __METHOD__);
				}
				break;
		}
		if($this->onlyContent === true){
			$response = $this->_response;
		}else{
			$response = ['code' => $this->responseCode, 'content' => $this->response, 'header' => $this->responseHeader];
		}
		$this->reset();
		return $response;
	}
}