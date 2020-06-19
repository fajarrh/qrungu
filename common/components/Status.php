<?php
namespace common\components;


use common\models\User;
use Yii;
use yii\base\Component;
use yii\httpclient\Client;

/**
 * AuthHandler handles successful authentication via Yii auth component
 */
class Status extends Component
{

    public function story($status)
    {
        $tmp = '';
        switch ($status){
            case 9 :
                $tmp = 'Not Available';
                break;

            default:
                $tmp = 'Available';
                break;
        }

        return $tmp;
    }

    public function pushNotif($id, $conversation,$option = [])
    {
		$user = User::find ()->select(['notif_token'])->where('id = :id', [':id' => $id])->asArray()->one ();
		$name = Yii::$app->user->identity->display_name;

		if($option['type'] === 'conversation'){
			$message = ' balasan baru dari ' . (empty($name) ? 'Tanpa Nama' : $name);
		} else {
			$message = (empty($name) ? 'Tanpa Nama' : $name) . ' baru saja membalas kiriman anda.';
		}

    	$client = new Client();
    	$response = $client->createRequest ()
		    ->setMethod ('POST')
		    ->setFormat (Client::FORMAT_JSON)
		    ->setUrl ("https://exp.host/--/api/v2/push/send")
		    ->setData ([
		    	'to' => $user['notif_token'],
			    'title' => 'Pesan Baru',
			    'body' => $message,
			    'data' => [
			    	'conversation_id' => $conversation
			    ]
		    ])
		    ->send ();
    	if ($response->isOk){
    		return true;
	    }
    	return false;
    }

    /**
     * params = ['to' => '', 'title' => '','message' => $message]
    */
    public function quickNotif($params = [])
    {
	    $client = new Client();
	    $response = $client->createRequest ()
		    ->setMethod ('POST')
		    ->setFormat (Client::FORMAT_JSON)
		    ->setUrl ("https://exp.host/--/api/v2/push/send")
		    ->setData ($params)
		    ->send ();
	    if ($response->isOk){
		    return true;
	    }
	    return false;
    }

    public function distance($currentLocation = [], $destination = [])
    {
    	if(!isset($currentLocation) && $destination){
    		return false;
	    }
	    $distance = ROUND(1.609344 * 3956 * acos( cos( deg2rad($currentLocation['latitude']) ) * cos( deg2rad($destination['latitude']) ) * cos( deg2rad($destination['longitude']) - deg2rad($currentLocation['longitude']) ) + sin( deg2rad($currentLocation['latitude']) ) * sin( deg2rad($destination['latitude']) ) ) ,8);
    	return $distance < 1 ? 'kurang dari 1 km.' : round ($distance) . ' km.';
    }
}