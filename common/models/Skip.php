<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "skip".
 *
 * @property int $skip_id
 * @property int $story_id
 * @property int $user_id
 * @property int $status
 * @property string time
 * @property Stories $story
 * @property User $user
 */
class Skip extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'skip';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['story_id'], 'required'],
            [['story_id', 'user_id', 'status'], 'integer'],
	        ['status', 'default', 'value' => 1],
	        ['status', 'in', 'range' => [0,1]],
	        ['time', 'string'],
            ['user_id', 'default', 'value' => Yii::$app->user->id],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stories::className(), 'targetAttribute' => ['story_id' => 'story_id']],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'skip_id' => Yii::t('app', 'Skip ID'),
            'story_id' => Yii::t('app', 'Story ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'status' => Yii::t('app', 'Status'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStory()
    {
        return $this->hasOne(Stories::className(), ['story_id' => 'story_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
	/**
     * {@inheritdoc}
     * @return SkipQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SkipQuery(get_called_class());
    }
}
