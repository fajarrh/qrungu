<?php

namespace common\models;

use Yii;
use yii\helpers\Url;

/**
 * This is the model class for table "report".
 *
 * @property int $report_id
 * @property int $story_id
 * @property int $status
 * @property string $time
 * @property string $type
 * @property string $description
 * @property Stories $story
 */
class Report extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'report';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['story_id', 'type'], 'required'],
            [['story_id', 'status', 'report_by'], 'integer'],
	        ['type', 'in', 'range' => ['porn','race','harsm','other']],
	        ['status', 'default','value' => 0],
	        ['status', 'in', 'range' => [0,1]],
            ['report_by', 'default', 'value' => Yii::$app->user->id],
            [['description'] , 'string'],
            [['time'], 'safe'],
            [['report_by'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['report_by' => 'id']],
            [['story_id'], 'exist', 'skipOnError' => true, 'targetClass' => Stories::className(), 'targetAttribute' => ['story_id' => 'story_id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'report_id' => Yii::t('app', 'Report ID'),
            'story_id' => Yii::t('app', 'Story ID'),
            'status' => Yii::t('app', 'Status'),
            'time' => Yii::t('app', 'Time'),
	        'description' => 'Description',
	        'type' => 'Type'
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getStory()
    {
        return $this->hasOne(Stories::className(), ['story_id' => 'story_id']);
    }

    public function getReportBy()
    {
        return $this->hasOne(User::className(), ['id' => 'report_by']);
    }

    public function afterSave ( $insert , $changedAttributes )
    {
    	if($insert){
    		$total = self::find ()->where(['story_id' => $this->story_id, 'status' => 0])->count();
    		if($total >= 50){
    			self::updateAll (['status' => 1], ['story_id' => $this->story_id]);
    			Stories::updateAll (['status' =>  0], 'story_id = :id',[':id' => $this->story_id]);
		    }
	    }
	    parent::afterSave ( $insert , $changedAttributes ); // TODO: Change the autogenerated stub
    }

	/**
     * {@inheritdoc}
     * @return ReportQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new ReportQuery(get_called_class());
    }

    public function fields()
    {
        return [
            'report_id' => 'report_id',
            'story_id'  => 'story_id',
            'report_by' => function($model){
                return $model->reportBy->display_name;
            },
            'audio'     => function($model){
                return Url::to("@web/audio/{$model->story->file_name}", true);
            },
	        'type',
            'description',
            'time'      => 'time'
        ];
    }
}
