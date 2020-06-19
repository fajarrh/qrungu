<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\rest\ActiveController;
use yii\web\Response;

Class WebController extends ActiveController
{
	public $modelClass = false;
	public $layout = '@api/views/layouts/web';

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['delete'], $actions['create'], $actions['index'], $actions['view'], $actions['update']);
		return $actions;
	}

	public function behaviors ()
	{
		$behaviors = parent::behaviors ();
		$behaviors['contentNegotiator']['formats']['text/html'] = Response::FORMAT_HTML;
		$behaviors['authenticator'] = [
			'class' => CompositeAuth::className(),
			'authMethods' => [
				HttpBasicAuth::className(),
				HttpBearerAuth::className(),
				QueryParamAuth::className(),
			],
			'except' => ['privacy-policy', 'term-condition', 'opensource-license']
		];
		return $behaviors;
	}

	public function actionTermCondition()
	{
		return $this->render('term');
	}

	public function actionPrivacyPolicy()
	{
		return $this->render('privacy');
	}

	public function actionOpensourceLicense()
	{
		return $this->render('license');
	}
}
