<?php

namespace api\modules\v1\controllers;

use Yii;


use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\rest\ActiveController;;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;

use common\models\User;


class AccountController extends ActiveController
{
    public $modelClass = 'common\models\User';
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'data',
    ];

    public function actions()
    {
        $actions = parent::actions();
        // disable the "delete" and "create" actions
        unset($actions['delete'], $actions['create'], $actions['index'], $actions['view'], $actions['update']);
        return $actions;
    }

	public function afterAction ( $action , $result )
	{
		$result = parent::afterAction ( $action , $result ); // TODO: Change the autogenerated stub
		User::updateAll (['last_activity' => date ('Y-m-d H:i:s')], 'id = :id',[':id' => Yii::$app->user->id]);
		return $result;
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
        ];

	    $behaviors['access'] = [
		    'class' => AccessControl::className (),
		    'rules' => [
			    [
				    'allow' => true,
				    'matchCallback' => function ($rule, $action) {
					    return !(Yii::$app->user->identity->status !== 10);
				    }
			    ]
		    ]
	    ];
	    
        return $behaviors;
    }

	public function actionMe()
	{
		$model = $this->findModel(Yii::$app->user->id);
		return [
			'status' => 'success',
			'code' => Yii::$app->response->statusCode = 200,
			'data' => $model->toArray([], ['email'])
		];
	}

	public function actionEdit()
	{
		$model = $this->findModel(Yii::$app->user->id);
		$model->scenario = User::SCENARIO_UPDATE;
		
		if($model->load(Yii::$app->request->getBodyParams(), '') && $model->save()){
			return [
				'status' => 'success',
				'code' => Yii::$app->response->statusCode = 200,
				'data' => $model->toArray([], ['email', 'birthday'])
			];
		} else {
			return [
				'status' => 'bad request',
				'code' => Yii::$app->response->statusCode = 400,
				'message' => $model->getErrorSummary(false)
			];
		}
	}

	public function actionUpdateImage()
	{
		$image = Yii::$app->request->post('image');
		$data['User']['image'] = $image;

		$model =  $this->findModel(Yii::$app->user->id);
		$model->scenario = User::UPDATE_IMAGE;
		if($model->load($data) && $model->save()){
			return [
				'status' => 'sucess',
				'code' => Yii::$app->response->statusCode = 200,
				'data' => [
					'image' => $model->image
				]
			];
		} else {
			return [
				'status' => 'bad request',
				'code' => Yii::$app->response->statusCode = 400,
				'message' => $model->getFirstError('image')
			];
		}
	}

	protected function findModel($id)
	{
		if(($model = User::findOne($id)) !== null){
			return $model;
		}
		throw new NotFoundHttpException('akun tidak ditemukan');
	}
}
