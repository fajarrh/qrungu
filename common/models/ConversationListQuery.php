<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[ConversationList]].
 *
 * @see ConversationList
 */
class ConversationListQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return ConversationList[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return ConversationList|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
