<?php

namespace backend\controllers;

class SiteController extends BaseController
{
	/**
	 * [默认首页]
	 * @author libingke
	 * @return string
	 */
	public function actionIndex()
	{
		return $this->render('index');
	}
}
