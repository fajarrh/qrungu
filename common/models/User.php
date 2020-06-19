<?php 
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $auth_key
 * @property string $birthday
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 * @property string $image
 * @property string $bio
 * @property string $last_activity
 * @property string $last_notif
 * @property string $notif_token
 */
class User extends ActiveRecord implements IdentityInterface
{
	const STATUS_DELETED = 0;
	const STATUS_ACTIVE = 10;
	const UPDATE_IMAGE = 'update-image';
	const SCENARIO_UPDATE = 'update';

	public $token;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName ()
	{
		return '{{%user}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function behaviors ()
	{
		return [
			TimestampBehavior::className() ,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules ()
	{
		return [
			[[ 'facebook_id' , 'name' ] , 'required' , 'on' => 'facebook' ],
			[[ 'email' , 'name' ] , 'required' , 'on' => 'google' ],
			[ 'image' , 'required' , 'on' => self::UPDATE_IMAGE ] ,
			[['display_name', 'birthday', 'gender'], 'required', 'on' => self::SCENARIO_UPDATE],
			[['display_name' , 'name' , 'gender' , 'notif_token' , 'image', 'bio', 'last_activity', 'last_notif'] , 'string' ] ,
			['gender','in','range' => ['male','female']],
			['status','default','value' => self::STATUS_ACTIVE ] ,
			[ 'status' , 'in' , 'range' => [ self::STATUS_ACTIVE , self::STATUS_DELETED, -1 ]],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function findIdentity ( $id )
	{
		return static::findOne( [ 'id' => $id , 'status' => self::STATUS_ACTIVE ] );
	}

	/**
	 * {@inheritdoc}
	 */
	public static function findIdentityByAccessToken ( $token , $type = null )
	{
		return static::findOne( [ 'access_token' => hash( 'sha256' , $token ) ] );
	}

	/**
	 * Finds user by username
	 *
	 * @param string $username
	 * @return static|null
	 */
	public static function findByUsername ( $username )
	{
		return static::findOne( [ 'username' => $username , 'status' => self::STATUS_ACTIVE ] );
	}

	/**
	 * Finds user by password reset token
	 *
	 * @param string $token password reset token
	 * @return static|null
	 */
	public static function findByPasswordResetToken ( $token )
	{
		if(!static::isPasswordResetTokenValid( $token )) {
			return null;
		}

		return static::findOne( [
			'password_reset_token' => $token ,
			'status' => self::STATUS_ACTIVE ,
		] );
	}

	/**
	 * Finds out if password reset token is valid
	 *
	 * @param string $token password reset token
	 * @return bool
	 */
	public static function isPasswordResetTokenValid ( $token )
	{
		if(empty( $token )) {
			return false;
		}

		$timestamp = (int)substr( $token , strrpos( $token , '_' ) + 1 );
		$expire = Yii::$app->params['user.passwordResetTokenExpire'];
		return $timestamp + $expire >= time();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getId ()
	{
		return $this->getPrimaryKey();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAuthKey ()
	{
		return $this->auth_key;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getStatus()
	{
		return $this->status;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateAuthKey ( $authKey )
	{
		return $this->getAuthKey() === $authKey;
	}

	/**
	 * Validates password
	 *
	 * @param string $password password to validate
	 * @return bool if password provided is valid for current user
	 */
	public function validatePassword ( $password )
	{
		return Yii::$app->security->validatePassword( $password , $this->password_hash );
	}

	/**
	 * Generates password hash from password and sets it to the model
	 *
	 * @param string $password
	 */
	public function setPassword ( $password )
	{
		$this->password_hash = Yii::$app->security->generatePasswordHash( $password );
	}

	/**
	 * Generates "remember me" authentication key
	 */
	public function generateAuthKey ()
	{
		$this->auth_key = Yii::$app->security->generateRandomString();
	}

	/**
	 * Generates new password reset token
	 */
	public function generatePasswordResetToken ()
	{
		$this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
	}

	/**
	 * Removes password reset token
	 */
	public function removePasswordResetToken ()
	{
		$this->password_reset_token = null;
	}

	public function generateToken ()
	{
		$token = hash( 'sha256' , time() . rand( 0 , 9999 ) );
		$this->token = $token;
		$this->access_token = hash( 'sha256' , $token );
		$this->expire_token = date( 'Y-m-d H:i:s' , strtotime( '+2 hour' , time() ) );
	}

	/**
	 * {@inheritdoc}
	 * @return UserQuery
	 */
	public static function find()
	{
		return new UserQuery(get_called_class());
	}


	public function fields ()
	{
		return [
			'name',
			'username',
			'display_name' => function ( $model ) {
				$name = $model->display_name;
				return (empty($name) ? 'Tanpa Nama' : $name);
			} ,
			'age' => function($model){
				if(!empty($model->birthday)){
					$today = date("Y-m-d");
					$diff = date_diff(date_create($model->birthday), date_create($today));
					return $diff->format('%y') . 'thn';
				}
				return '-';
			},
			'image' ,
			'gender',
			'birthday',
			'email',
			'bio'
		];
	}

	public function extraFields ()
	{
		return [
			'id',
			'access_token' ,
			'expire_token' ,
			'isMe' => function($model){
			return Yii::$app->user->id == $model->user_id ? true : false;
			},
			'last_activity',
			'last_notif',
			'created_at',
		];

	}
}

