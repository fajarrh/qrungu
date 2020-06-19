<?php

namespace common\models;

use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "message_invites".
 *
 * @property int $id
 * @property string $message
 * @property int $hit
 * @property string $last_use
 * @property int $status
 */
class MessageInvites extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'message_invites';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['message'], 'required'],
            [['message'], 'string'],
            [['hit', 'status'], 'integer'],
	        ['status', 'default', 'value' => 1],
	        ['status', 'in', 'range' => [0,1]],
            [['last_use'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'message' => 'Message',
            'hit' => 'Hit',
            'last_use' => 'Last Use',
            'status' => 'Status',
        ];
    }

	/**
	 * {@inheritdoc}
	 */
    public static function getMessage()
    {
    	$model = self::find()
	    ->select('message, id, hit')
	    ->where('status = 1')
	    ->andWhere(['<=', 'hit', 2])
	    ->orderBy(new Expression('rand()'))
	    ->one ();

	    if($model === null){
		    MessageInvites::updateAll (['hit' => 0], 'status = 1');
	    } else {
		    MessageInvites::updateAll (['hit' => ++$model->hit, 'last_use' => date ('Y-m-d H:i:s')], 'id = :id', [':id' => $model->id]);
	    }

    	return $model;
    }
}
