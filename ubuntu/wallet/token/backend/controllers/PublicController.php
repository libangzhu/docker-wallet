<?php
namespace backend\controllers;

use Yii;

/**
 * Class PublicController
 * @package backend\controllers
 */
class PublicController extends BaseController
{
	public $enableCsrfValidation = false;

	/**
	 * [通用Error错误处理]
	 * @author: libingke
	 * @return string
	 */
	public function actionError()
	{
		$page = 'error';
		$e = Yii::$app->errorHandler->exception;
		$errMsg = $e->getMessage();
		$errCode = isset($e->statusCode) ? $e->statusCode : 
			(isset(Yii::$app->response->statusCode) ? Yii::$app->response->statusCode : 500);
		if ($e !== null) {

			switch($errCode)
			{
				case 404:
					$page = 404;
					break;
				case 500:
					$page = 500;
					break;
			}
		}

		return $this->render($page, [
			'code' => isset($errCode) ? $errCode :500,
			'message' => isset($errMsg) ? $errMsg : ''
		]);
	}


	/**
	 * ajax预请求 特殊处理
	 * @author: libingke
	 * @param string $case
	 */
	public function actionAjax($case = '')
	{
		try {
			switch($case)
			{
				//云矿机
				case 'miner_query':
					$data = $this->getMinerQuery();
					break;
				default:
					$data = [
						'message' => 'Unknown request.',
						'request' => ''
					];
			}

		} catch (\Exception $e) {
			$data = [
				'message' => $e->getMessage() . " [{$e->getTraceAsString()}]"  ,
				'request' => ''
			];
		}

		$response = Yii::$app->response;
		$response->format = yii\web\Response::FORMAT_JSON;
		$response->data = $data;
		$response->send();
	}

	/**
	 * 处理云矿机查询api
	 * @author: libingke
	 * @return array
	 */
	protected function getMinerQuery()
	{
		$req_type = 'post';
		$method	= strtolower(Yii::$app->request->$req_type('type', 'get'));
		$url	= Yii::$app->request->$req_type('url', '');
		$header = Yii::$app->request->$req_type('headers', []);
		$data	= $this->_formatData(Yii::$app->request->$req_type('data'));

		$query	= Yii::$app->request->$req_type('query', []);

		$return = [
			'message' => '',
			'request' => [
				'type'		=> $method,
				'url'		=> $url,
				'headers'	=> $header,
				'query'		=> $query,
				'data'		=> $data,
			],
			'response' => []
		];

		if (!empty($query)) {
			Yii::$app->cUrl->setQuery($query);
		}
		if (!empty($data)) {
			Yii::$app->cUrl->setData($data, 'JSON');
		}
		Yii::$app->cUrl->setHeader($header);
		//$options = Yii::$app->cUrl->getOptions();
		$response = Yii::$app->cUrl->$method($url, 'JSON');
		if (isset($response['content'])) {
			$return['response'] = $response['content'];
		} else {
			$return['message'] = "服务错误。[cUrl]";
		}

		return $return;
	}

	/**
	 * 格式化post数据
	 * @author: libingke
	 * @param $data
	 * @return array
	 */
	private function _formatData($data)
	{
		$ret = [];
		if (is_array($data) && $data) {
			foreach ($data as $row) {
				$key = isset($row['name']) ? $row['name'] : '';
				$value = isset($row['value']) ? $row['value'] : '';
				$key ? $ret[$key] = $value : null;
			}
		}

		return $ret;
	}

}
