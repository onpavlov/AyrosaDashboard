<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tasks_users".
 *
 * @property integer $tasks_id
 * @property integer $users_id
 *
 * @property Users $users
 * @property Tasks $tasks
 */
class UsersTasks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tasks_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['tasks_id', 'users_id'], 'required'],
            [['tasks_id', 'users_id'], 'integer']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'tasks_id' => 'Tasks ID',
            'users_id' => 'Users ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasOne(Users::className(), ['id' => 'users_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasOne(Tasks::className(), ['id' => 'tasks_id']);
    }
}
