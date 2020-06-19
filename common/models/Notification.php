<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "notification".
 *
 * @property int $notif_id
 * @property int $user_id
 * @property int $story_id
 * @property int $status
 * @property string $time
 *
 * @property Stories $story
 * @property User $user
 */
class Notification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notification';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'story_id'], 'required'],
            [['user_id', 'story_id', 'status'], 'integer'],
            [['time'], 'safe'],
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
            'notif_id' => Yii::t('app', 'Notif ID'),
            'user_id' => Yii::t('app', 'User ID'),
            'story_id' => Yii::t('app', 'Story ID'),
            'status' => Yii::t('app', 'Status'),
            'time' => Yii::t('app', 'Time'),
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
     * @return NotificationQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new NotificationQuery(get_called_class());
    }
}
