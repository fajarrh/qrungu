<?php

namespace api\modules\v1\controllers;

use common\models\Feedback;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\rest\ActiveController;
use yii\web\UploadedFile;
use yii\helpers\Url;
use yii\web\Response;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\filters\AccessControl;

use common\models\Skip;
use common\models\Stories;
use common\models\Report;
use common\models\Conversation;
use common\models\User;


class ActivityController extends ActiveController
{
	public $modelClass = false;
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
			'except' => [
				'user',
				'story',
				'feedback',
				'activity-user',
				'map-story'
			]
		];

		return $behaviors;
	}


	public function actionUser()
	{
		$query = User::find ()->where(['status' => User::STATUS_ACTIVE]);
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => [
					'name' => SORT_ASC
				]
			],
			'pagination' => false
		]);
		$model = $dataProvider->getModels ();
		foreach ($model as $item){
			$data[] = $item->toArray([], ['last_activity', 'last_notif', 'created_at']);
		}
		$dataProvider->setModels ($data);
		return $dataProvider;
	}

	public function actionStory()
	{
		$query = Stories::find ();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => [
					'time' => SORT_DESC
				]
			],
			'pagination' => false
		]);
		return $dataProvider;
	}

	public function actionFeedback()
	{
		$query = Feedback::find ();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => [
					'created_at' => SORT_DESC
				]
			],
			'pagination' => false
		]);
		return $dataProvider;
	}

	public function actionActivityUser()
	{
		$query1 = (new Query())
			->select(['user_id', 'time' => 'DATE_FORMAT(time, "%Y-%m-%d")'])
			->from('skip');
		$query2 = (new Query())
			->select(['user_id', 'time' => 'DATE_FORMAT(time, "%Y-%m-%d")'])
			->from('stories');
		$query3 = (new Query())
			->select(['user_id', 'time' => 'DATE_FORMAT(time, "%Y-%m-%d")'])
			->from('conversation_list');

		$result = (new Query())->select(['q.user_id', 'q.time'])
			->from(['q' => $query1->union($query2, true)->union($query3, true)])
			->where(['MONTH(time)' => date('m'), 'YEAR(time)' => date('Y')])
			->groupBy(['DATE_FORMAT(q.time, "%Y-%m-%d")', 'q.user_id'])
			->all();

		$resultIndex = ArrayHelper::index ($result, null, 'time');
		$resp = [];
		foreach ($resultIndex as $index => $item){
			$total = count ($item);
			$resp['label'][] = $index;
			$resp['data']['backgroundColor'] = '#FFDF0032';
			$resp['data']['borderColor'] = '#FFDF00';
			$resp['data']['label'] = 'User Active (Skip, Story and Conversation)';
			$resp['data']['data'][] = (int) $total;
		}

		return $resp;
	}

	public function actionMapStory()
	{
		$model = Stories::find ()
			->where(['>=', 'time', new Expression('DATE_SUB(NOW(), INTERVAL 24 HOUR)')])
			->all();
		$resp = [];
		$total = count($model);
		foreach ($model as $index => $item)

			$resp [] = [
				'name' => $item->user->display_name,
				'lat' => (double) $item->latitude,
				'long' => (double) $item->longitude,
				'audio' => $item->audio_file,
				'image' => $item->user->image,
				'age' => ArrayHelper::getValue ($item->user->toArray (['age']), 'age'),
				'gender' => $item->user->gender === 'male' ? '<i class="fa fa-male" aria-hidden="true"></i>' : '<i class="fa fa-female" aria-hidden="true"></i>',
				'time' => ArrayHelper::getValue ($item->toArray (['time_ago']), 'time_ago'),
				'index' =>  $index < 1 ? $total : --$total
			];
		return $resp;
	}
}
