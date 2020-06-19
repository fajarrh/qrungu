<?php

namespace common\models;

use yii\db\Expression;

/**
 * This is the ActiveQuery class for [[User]].
 *
 * @see User
 */
class UserQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * {@inheritdoc}
     * @return User[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * {@inheritdoc}
     * @return User|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

	/**
	 * Query Interval Date
	 * ['num' => null, 'format' => null]
	 */
    public function dateInterval($operator, $column, $params = []){
    	return $this->andOnCondition([$operator, $column, new Expression("DATE_SUB(NOW(), INTERVAL $params[num] $params[format])")  ]);
    }

    /**
     * Cek user yang tidak aktif sudah lebih dari 7 minggu
    */
    public function weekly()
    {
		return $this->select(['notif_token', 'display_name', 'last_notif'])
			->where('status = :status', [':status' => User::STATUS_ACTIVE])
			->andWhere(['<=', 'last_activity', new Expression('DATE_SUB(NOW(), INTERVAL 1 WEEK)')]);
    }

}
