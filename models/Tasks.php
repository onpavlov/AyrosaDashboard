<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tasks".
 *
 * @property integer $id
 * @property integer $project_id
 * @property string $task_name
 * @property string $priority
 * @property integer $sort
 * @property string $date
 * @property string $link
 *
 * @property Projects $project
 * @property TasksUsers[] $tasksUsers
 * @property Users[] $users
 */
class Tasks extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'tasks';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_id'], 'required'],
            [['project_id', 'sort'], 'integer'],
            [['date'], 'safe'],
            [['task_name', 'link'], 'string', 'max' => 255],
            [['priority'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_id' => 'Project ID',
            'task_name' => 'Task Name',
            'priority' => 'Priority',
            'sort' => 'Sort',
            'date' => 'Date',
            'link' => 'Link',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProject()
    {
        return $this->hasOne(Projects::className(), ['id' => 'project_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksUsers()
    {
        return $this->hasMany(TasksUsers::className(), ['tasks_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(Users::className(), ['id' => 'users_id'])->viaTable('tasks_users', ['tasks_id' => 'id']);
    }
}
