<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tasks_bc_users".
 *
 * @property integer $tasks_id
 * @property integer $bc_users_id
 *
 * @property BcUsers $bcUsers
 * @property Tasks $tasks
 */
class TasksBcUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tasks_bc_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tasks_id', 'bc_users_id'], 'required'],
            [['tasks_id', 'bc_users_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tasks_id' => 'Tasks ID',
            'bc_users_id' => 'Bc Users ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBcUsers()
    {
        return $this->hasOne(BcUsers::className(), ['id' => 'bc_users_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasOne(Tasks::className(), ['id' => 'tasks_id']);
    }
}
