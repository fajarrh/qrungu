<?php
/**
 * Created by PhpStorm.
 * User: master
 * Date: 10/05/2018
 * Time: 7:26
 */

namespace common\components;

use Yii;
use yii\base\Component;
use common\models\User;

class FacebookLogin extends Component
{

    public $app_id;
    public $app_secret;
    public $default_graph_version;
    public $redirect;

    public function url(){
        $fb = new \Facebook\Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => $this->default_graph_version,
        ]);
        $helper = $fb->getRedirectLoginHelper();
        $permissions  = ['email'];
        $loginUrl = $helper->getLoginUrl($this->redirect,$permissions);
        return $loginUrl;
    }

    public function facebook(){

        $fb = new \Facebook\Facebook([
            'app_id' => $this->app_id,
            'app_secret' => $this->app_secret,
            'default_graph_version' => $this->default_graph_version,
        ]);

        $helper = $fb->getRedirectLoginHelper();
        try {
            $accessToken = $helper->getAccessToken();
        } catch(\Facebook\Exceptions\FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(\Facebook\Exceptions\FacebookSDKException $e) {
            // When validation fails or other local issues
            //  echo $_SESSION['FBRLH_' . 'state'];
            return [
                'status' => Yii::$app->response->statusCode = 400,
                'message' =>  'Disini Facebook SDK returned an error: ' . $e->getMessage()
            ];
            exit;
        }

        if (isset($accessToken)) {
            // Logged in!
            // Now you can redirect to another page and use the
            // access token from $_SESSION['facebook_access_token']
            // But we shall we the same page
            // Sets the default fallback access token so
            // we don't have to pass it to each request
            $fb->setDefaultAccessToken($accessToken);
            try {
                $response = $fb->get('/me?fields=id,email,name,gender,first_name,birthday,location');
                $userNode = $response->getGraphUser();
            }catch(\Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                echo 'Graph returned an error: ' . $e->getMessage();
                exit;
            } catch(\Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                echo 'Facebook SDK returned an error: ' . $e->getMessage();
                exit;
            }
            // Print the user Detail

            ($userNode->getEmail() == null ? $query = 0 : $query = User::find()->where('email =:email', [':email' => $userNode->getEmail()])->orWhere('facebook_id=:id', ['id' => $userNode->getId()])->count());
            $token = hash('sha256', time().rand(0,9999));
            if($query > 0){
                $tmp = User::find()->select('access_token, expire_token')->where('facebook_id=:id',[':id' => $userNode->getId()])->one();
                if(!$tmp->access_token == null ){
                    $tmp->access_token = '';
                    $tmp->expire_token = '';
                    $tmp->update();
                }

                $model = User::find()->where('email=:email',['email' => $userNode->getEmail()])->orWhere('facebook_id=:id',[':id' => $userNode->getId()])->one();
                $model->access_token = hash('sha256',$token);
                $model->expire_token = date('Y-m-d H:i:s', strtotime('+2 hour', time()));
                if($model->save()){
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    Yii::$app->session->set('access_token', $token);
                    Yii::$app->session->set('token_expired', $model->expire_token);
                    echo json_encode([
                        'status' => Yii::$app->response->statusCode = 200,
                        'message' => Yii::t('app', 'Login Succes'),
                        'data' => [
                            'access_token' => $token,
                            'token_expired' =>$model->expire_token
                        ]
                    ]);
                }
            }else {
                $model = new User();
                $model->name = strtolower($userNode->getName());
                $model->gender = (($userNode->getGender() == null) ? '' : ($userNode->getGender() == 'male' ? 'p' : 'l' ));
                $model->email  = $userNode->getEmail();
                $model->facebook_id = $userNode->getId();
                $model->access_token = hash('sha256', $token);
                $model->expire_token = date('Y-m-d H:i:s', strtotime('+2 hour', time()));

                if($model->save()){
                    Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                    Yii::$app->session->set('access_token', $token);
                    Yii::$app->session->set('token_expired', $model->expire_token);
                    echo json_encode([
                        'status' => Yii::$app->response->statusCode = 200,
                        'message' => Yii::t('app', 'Login Succes'),
                        'data' => [
                            'access_token' => $token,
                            'token_expired' => $model->expire_token
                        ]
                    ]);
                }
            }
        }
    }
}

