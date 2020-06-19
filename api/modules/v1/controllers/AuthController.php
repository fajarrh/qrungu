<?php

namespace api\modules\v1\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\rest\ActiveController;
use yii\helpers\Url;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\web\BadRequestHttpException;
use yii\helpers\Json;

use common\models\LoginForm;
use common\models\User;

class AuthController extends ActiveController
{
	public $modelClass = 'common\models\User';
	public function actions()
	{
	    $actions = parent::actions();
	    // disable the "delete" and "create" actions
		   unset($actions['delete'], $actions['create'], $actions['index'], $actions['view'], $actions['update']);
        $actions['auth'] = [
            'class' => 'yii\authclient\AuthAction',
            'successCallback' => [$this, 'onAuthSuccess'],
        ];
	    return $actions;
	}

	public function behaviors()
	{
	    $behaviors = parent::behaviors();
	    $behaviors['contentNegotiator']['formats'] = [
	    	'application/json' => Response::FORMAT_JSON
	    ];

	    $behaviors['authenticator'] = [
        'class' => CompositeAuth::className(),
        'authMethods' => [
            HttpBasicAuth::className(),
            HttpBearerAuth::className(),
            QueryParamAuth::className(),
        ],
        'except' => ['login', 'facebook','fb', 'google']
    ];

	    return $behaviors;
	}

    public function actionLogin()
    {
    	$model = new LoginForm;
    	if($model->load(Yii::$app->request->getBodyParams(), '') && $model->login()){
    	   return $this->generateToken($model->user->id);
    	} else {
    		return $model->errors;
    	}
    }

	public function actionLogout()
	{
		$model  = User::findOne(['id' => Yii::$app->user->id, 'status' => User::STATUS_ACTIVE]);

		if($model == null){
			throw new NotFoundHttpException('akun tidak ditemukan');
		}

		$model->access_token = '';
		$model->expire_token = '';
		$model->notif_token = '';

		if($model->update(false)){
			return [
				'status' => 'success',
				'code' => Yii::$app->response->statusCode = 200,
				'message' => 'logout berhasil'
			];
		}

		return [];
	}

//    public function actionNotifToken()
//    {
//        $model = User::findOne(Yii::$app->user->id);
//        if($model === null){
//            throw new NotFoundHttpException('Page Not Found');
//        }
//        $model->notif_token = Yii::$app->request->post('notif_token');
//        if ($model->update()) {
//            return [
//                'status' => Yii::$app->response->statusCode = 200,
//                'message' => Yii::t('app', 'Token has benn added')
//            ];
//        } else{
//            return $model->errors;
//        }
//        return [];
//    }

	public function actionFacebook()
	{
		$token = Yii::$app->request->bodyParams;
		if (!isset($token)) {
			throw new BadRequestHttpException('Token must be valid', 400);
		}

		$client = new \GuzzleHttp\Client(['base_uri' => Yii::$app->params['url_fb']]);
		try {
			$res = $client->request('GET', 'me', ['query' => ['access_token' => $token['token']]]);
			if ($res->getStatusCode() == 200) {
				return $this->fbCek($res->getBody(), $token['notif_token']);
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function actionGoogle()
	{
		$token = Yii::$app->request->bodyParams;
		
		if (!isset($token)) {
			throw new BadRequestHttpException('Token must be valid', 400);
		}

		$accessToken = $token['token'];
		
		$client = new \GuzzleHttp\Client(['base_uri' => 'https://www.googleapis.com/']);
		try {
			$res = $client->request('GET', 'userinfo/v2/me', 
				['headers' => 
					[
						'Authorization' => "Bearer {$accessToken}",      
						'accept-charset'        => 'utf-8',
						'Content-Type'  => 'application/x-www-form-urlencoded'
					]
				]
			);
			
			if ($res->getStatusCode() == 200) {
				return $this->googleCek($res->getBody()->getContents(), $token['notif_token']);
			}
		} catch (\Exception $e) {
			return ["error"=>$e->getMessage()] ;
		}
	}

	protected function fbCek($resp, $notif_token)
	{
		$model = Json::decode($resp, true);
		$cek = User::find()->where(['facebook_id' => (int) $model['id']])->one();

		if($cek != null) {
			if($cek->status != 10){
				return [
					'status' => Yii::$app->response->statusCode = 403,
					'message' => 'Anda tidak dapat mengakses akun anda.'
				];
			}

			User::updateAll (['notif_token' => $notif_token], 'id = :id',[':id' => $cek->id]);
			return $this->generateToken($cek->id);
		}

		$user = new User(['scenario' => 'facebook']);
		$user->facebook_id = $model['id'];
		$user->name = $model['name'];
		$user->display_name = $model['name'];
		$user->notif_token = $notif_token;
		$user->created_at = date('Y-m-d H:i:s');
		$user->generateAuthKey();
		$user->save(false);
		return $this->generateToken($user->id);
	}

	protected function googleCek($resp, $notif_token)
	{
		$model = Json::decode($resp, true);
		$cek = User::find()->where(['email' => $model['email']])->one();
		
		
		if($cek != null) {
			if($cek->status != 10){
				return [
					'status' => Yii::$app->response->statusCode = 403,
					'message' => 'Anda tidak dapat mengakses akun anda.'
				];
			}

			User::updateAll (['notif_token' => $notif_token], 'id = :id',[':id' => $cek->id]);
			return $this->generateToken($cek->id);
		}

		$user = new User(['scenario' => 'google']);
		$user->email = $model['email'];
		$user->google_id = $model['id'];
		$user->name = $model['name'];
		$user->display_name = $model['name'];
		//$user->image = $model['photoUrl'];
		$user->notif_token = $notif_token;
		$user->created_at = date('Y-m-d H:i:s');
		$user->generateAuthKey();
		$user->save(false);
		return $this->generateToken($user->id);
	}

	protected function generateToken($id)
	{
		$token = hash('sha256', time() . rand(0, 9999));
		$exp = date('Y-m-d H:i:s', strtotime('+2 hour'));

		$model = User::findOne($id);
		$model->access_token = hash('sha256', $token);
		$model->expire_token = $exp;
		$model->update(false);

		return [
			'status' => Yii::$app->response->statusCode = 200,
			'data' => [
				'access_token' => $token,
				'expire_token' => $exp,
				'name' => $model->name,
				'display_name' => $model->display_name,
				'image' => $model->image,
				'birthday' => $model->birthday,
				'gender' => $model->gender,
				'bio' => $model->bio
			]
		];
	}
}
