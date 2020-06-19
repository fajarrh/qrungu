<?php

namespace common\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Conversation;

/**
 * StoriesSearch represents the model behind the search form of `common\models\Stories`.
 */
class ConversationSearch extends Conversation
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['story_id', 'from', 'to', 'status'], 'integer'],
            [['time'], 'safe'],
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
        $query = Conversation::find();
        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 25 ,
            ],
	        'sort' => [
	        	'defaultOrder' => [
	        		'time' => SORT_DESC
		        ]
	        ],

        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        $query->joinWith('story');
        // grid filtering conditions
        $query->andFilterWhere(['from' => Yii::$app->user->id, 'conversation.status' => 1])
	        ->orFilterWhere(['to' => Yii::$app->user->id, 'conversation.status' => 1]);
        return $dataProvider;
    }
}

