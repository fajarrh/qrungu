<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Stories;
use yii\db\Expression;

/**
 * StoriesSearch represents the model behind the search form of `common\models\Stories`.
 */
class StoriesSearch extends Stories
{
    /**
     * {@inheritdoc}
     */
    public $distance;

    public function rules()
    {
        return [
            [['story_id', 'user_id', 'status'], 'integer'],
            [['time', 'file_name'], 'safe'],
            [['latitude', 'longitude'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Stories::find();

        $query->distance($this->latitude, $this->longitude);

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 1,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->skip();

        // grid filtering conditions
        $query->andFilterWhere([
            'time' => $this->time,
            'stories.status' => 0,
        ]);

        $query->andFilterWhere(['!=', 'user_id', \Yii::$app->user->id]);

        $query->orderBy(['distance' => SORT_ASC]);

        return $dataProvider;
    }
}

