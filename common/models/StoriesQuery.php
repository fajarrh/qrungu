<?php

namespace common\models;


use yii\db\Expression;
/**
 * This is the ActiveQuery class for [[Stories]].
 *
 * @see Stories
 */
class StoriesQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Stories[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Stories|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function skip()
    {
        return $this->innerJoinWith('user')
            ->where(['NOT IN', 'story_id', Skip::find()->select('story_id')->where([ 'user_id' => \Yii::$app->user->id ])->asArray()->column() ])
            ->andWhere(['NOT IN', 'story_id', Conversation::find()->select('story_id')->asArray()->where([ 'from' => \Yii::$app->user->id ])->column() ])
            ->andWhere(['NOT IN', 'story_id', Report::find()->select('story_id')->where(['status' => 9])->asArray()->column()]);
    }

    public function distance($latitude, $longitude)
    {
        return $this->select(['stories.file_name', 'stories.time', 'stories.status', 'stories.latitude', 'stories.longitude', 'stories.user_id', 'stories.story_id', 'distance' => new Expression(" ROUND(1.609344 * 3956 * acos( cos( radians({$latitude}) ) * cos( radians(stories.latitude) ) * cos( radians(stories.longitude) - radians({$longitude}) ) + sin( radians({$latitude}) ) * sin( radians(stories.latitude) ) ) ,8)")]);
    }
}
