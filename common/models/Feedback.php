<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "feedback".
 *
 * @property int $feedback_id
 * @property int $user_id
 * @property string $feedback
 * @property string $created_at
 *
 * @property User $user
 */
class Feedback extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'feedback';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['feedback'], 'required'],
	        ['user_id' , 'default', 'value' => Yii::$app->user->id],
            [['user_id'], 'integer'],
            [['feedback'], 'string'],
            [['created_at'], 'safe'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'feedback_id' => 'Feedback ID',
            'user_id' => 'User ID',
            'feedback' => 'Feedback',
            'created_at' => 'Created At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function fields ()
    {
	    return [
	        'user' => function($model){
	    	return $model->user->name;
	        },
		    'feedback',
		    'time' => function($model){
	    	return Yii::$app->formatter->asDate ($model->created_at, 'php:Y-m-d H:i:s');
		    }
	    ];
    }
}
