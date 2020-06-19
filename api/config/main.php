<?php
$params = array_merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-api',
	'name' => 'Qrungu',
    'basePath' => dirname(__DIR__),
    'timeZone' => 'Asia/Jakarta',
    'bootstrap' => ['log'],
    'controllerNamespace' => 'api\controllers',
    'modules' => [
        'v1' => [
            'basePath' => '@app/modules/v1',
            'class' => 'api\modules\v1\Module',
        ],
    ],
    'components' => [
        'response' => [
            'format' => yii\web\Response::FORMAT_JSON,
            'charset' => 'UTF-8',
        ],
        'request' => [
            //'class' => '\yii\web\Request',
            //'enableCookieValidation' => false,
            'enableCsrfValidation' => false,
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ]
        ],
        'user' => [
            'identityClass' => 'common\models\User',
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-api', 'httpOnly' => true],
        ],
        'session' => [
            // this is the name of the session cookie used for login on the frontend
            'name' => 'advanced-api',
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',

        ],

        'urlManager' => [
            'enablePrettyUrl' => true,
            'enableStrictParsing' => true,
            'showScriptName' => false,
            'rules' => [
	            //TODO: URL DEFAULT
	            [
		            'class'      => 'yii\rest\UrlRule',
		            'controller' => 'v1/default',
		            'pluralize' => false,
		            'extraPatterns' => [
		            	'GET ' => 'index',
			            'GET weekly' => 'weekly'
		            ]
	            ],

	            //TODO: URL STORY
                [
                    'class'      => 'yii\rest\UrlRule',
                    'controller' => 'v1/story',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{access-token}' => '<access-token:\w+>',
                        '{lat}' => '<lat:[0-9]+(.[0-9]+)+>',
                        '{long}' => '<long:[0-9]+(.[0-9]+)+>'
                    ],
                    'pluralize'  => false,
                    'extraPatterns' => [
                    	//POST
                        'POST skip/{access-token}' => 'skip',
                        'POST report/{access-token}' => 'report',
                        'POST report-banned/{access-token}' => 'report-banned',
                        'POST create/{access-token}' => 'create',
						//GET
                        'GET {lat}/{long}/{access-token}' => 'index',
                        'GET report-list/{access-token}' => 'report-list',
                        'GET detail/{id}/{access-token}' => 'detail',
	                    'GET my-story/{access-token}' => 'my-story',
	                    'DELETE delete/{id}/{access-token}' => 'delete'
                     ],
                ],

	            //TODO: URL AUTH
	            [
                    'class'      => 'yii\rest\UrlRule',
                    'controller' =>'v1/auth',
                    'tokens' => [
                        '{access-token}' => '<access-token:\w+>',
                    ],
                    'pluralize'  => false,
                    'extraPatterns' => [
                        'POST login'  => 'login',
                        'POST logout/{access-token}' => 'logout',
                        'POST facebook' => 'facebook',
                        'POST google' => 'google',
                    ],
                ],

	            //TODO: URL CONVERSATION
	            [
                    'class'      => 'yii\rest\UrlRule',
                    'controller' =>'v1/conversation',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{access-token}' => '<access-token:\w+>',
                        '{latitude}' => '<latitude:[0-9]+(.[0-9]+)+>',
                        '{longitude}' => '<longitude:[0-9]+(.[0-9]+)+>'
                    ],
                    'pluralize'  => false,
                    'extraPatterns' => [
                    	// POST
                        'POST reply-conversation/{access-token}'  => 'reply-conversation',
                        'POST reply-story/{access-token}' => 'reply-story',
						// GET
	                    'GET conversation-delete/{id}/{access-token}' => 'conversation-delete',
                        'GET {access-token}' => 'index',
                        'GET detail/{id}/{latitude}/{longitude}/{access-token}'  => 'detail',
	                    'GET total-notif/{access-token}' => 'total-notif',
	                    'GET list/{id}/{access-token}' => 'list'
                    ],
                ],

	            //TODO: URL ACCOUNT
	            [
                'class'      => 'yii\rest\UrlRule',
                'controller' =>'v1/account',
                'tokens' => [
                    '{access-token}' => '<access-token:\w+>',
                ],
                'pluralize'  => false,
                'extraPatterns' => [
                	//POST
                    'POST edit/{access-token}' => 'edit',
	                'POST update-image/{access-token}' => 'update-image',
	                //GET
                    'GET me/{access-token}' => 'me'
                    ],
                ],

	            //TODO: URL WEB
	            [
		            'class'      => 'yii\rest\UrlRule',
		            'controller' =>'v1/web',
		            'pluralize'  => false,
		            'extraPatterns' => [
		            	//GET
			            'GET term-condition' => 'term-condition',
			            'GET privacy-policy' => 'privacy-policy',
			            'GET opensource-license' => 'opensource-license'
		            ],
	            ],

	            //TODO: URL FEEDBACK
	            [
		            'class'      => 'yii\rest\UrlRule',
		            'controller' =>'v1/feedback',
		            'tokens' => [
			            '{id}' => '<id:\d+>',
			            '{access-token}' => '<access-token:\w+>',
		            ],
		            'pluralize'  => false,
		            'extraPatterns' => [
		                //POST
			            'POST create/{access-token}' => 'create',

			            //GET
			            'GET {access-token}' => 'index',
			            'GET view/{id}/{access-token}' => 'view',

			            //DELETE
			            'DELETE delete/{id}/{access-token}' => 'delete',

		            ],
	            ],

	            //TODO: URL Activity
	            [
		            'class'      => 'yii\rest\UrlRule',
		            'controller' =>'v1/activity',
		            'tokens' => [
			            '{id}' => '<id:\d+>',
		            ],
		            'pluralize'  => false,
		            'extraPatterns' => [
			            'GET user' => 'user',
			            'GET story' => 'story',
			            'GET feedback' => 'feedback',
			            'GET activity-user' => 'activity-user',
			            'GET map-story' => 'map-story'

		            ],
	            ],

            ],
        ]
    ],
    'params' => $params,
];
