<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[Skip]].
 *
 * @see Skip
 */
class SkipQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return Skip[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return Skip|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
