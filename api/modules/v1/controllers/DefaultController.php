<?php

namespace api\modules\v1\controllers;

use common\models\Conversation;
use common\models\ConversationList;
use common\models\User;
use common\models\MessageInvites;

use Yii;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\auth\QueryParamAuth;
use yii\helpers\ArrayHelper;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\Response;

/**
 * Default controller for the `v1` module
 */

class DefaultController extends ActiveController
{
	public $modelClass = false;

	public function actions()
	{
		$actions = parent::actions();
		unset($actions['delete'], $actions['create'], $actions['index'], $actions['view'], $actions['update']);
		return $actions;
	}

	public function behaviors ()
	{
		$behaviors = parent::behaviors ();
		$behaviors['contentNegotiator']['formats']= [
			'application/json' => Response::FORMAT_JSON
		];
		$behaviors['authenticator'] = [
			'class' => CompositeAuth::className(),
			'authMethods' => [
				HttpBasicAuth::className(),
				HttpBearerAuth::className(),
				QueryParamAuth::className(),
			],
			'except' => ['index', 'weekly']
		];

		return $behaviors;
	}

    /**
     * Renders the index view for the module
     * @return array
     */
    public function actionIndex()
    {

    	$query = ConversationList::find()
		    ->where('list_id = :id',[':id' => ConversationList::find ()
			    ->where('conversation_id = :id', [':id' => 169])
			    ->max ('list_id')
		    ])
		    ->asArray()
		    ->one();

    	print_r ($query['user_id']);
    	exit();
        return [
	        'message' => 'Selamat Datang Di Qrungu.'
        ];
    }

    /**
     * Notifikasi cek user yang nganggue seminggu
    */
    public function actionWeekly()
    {

		$model = User::find ()->weekly()->all();
	    $msg   = MessageInvites::getMessage();

	    $totalUser = count ($model);
	    $status = false;
		$today = date_create (date ('Y-m-d H:i:s'));

	    if($model != null && $totalUser > 0){
		    $notifJson = [];
		    foreach ($model as $item) {
		    	$date = date_create ($item->last_notif);
		    	$diff = date_diff ($today, $date);
		    	$day  = $diff->d;

				if(!empty($item->notif_token) && ($day >= 7)){

					if($msg == null){
						$text = Yii::$app->params['default_msg'];
					} else {
						$text = $msg->message;
					}

					if (preg_match('/\bname\b/', $text)){
						$text = str_replace ('$name', $item->display_name, $text);
					}

					$notifJson [] = [
						'to' => $item->notif_token,
						'title' => 'Qrungu',
						'body' => $text
					];
				}
		    }

		    if(!empty($notifJson)){
			    Yii::$app->status->quickNotif($notifJson);
			    User::updateAll (['last_notif' => date ('Y-m-d H:i:s', strtotime ('-5 days'))], ['notif_token' => ArrayHelper::getColumn($notifJson, 'to')]);
			    $status = true;
		    }
	    }

	    return [
	    	'status' => 'success',
		    'code' => Yii::$app->response->statusCode = 200,
		    'message' => $status ? 'Notif Terkirim' : 'Tidak ada aktivitas'
	    ];

	}
	
	public function actionTes(){
		return "hai";
	}

}
