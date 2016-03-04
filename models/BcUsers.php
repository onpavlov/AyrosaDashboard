<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "bc_users".
 *
 * @property integer $id
 * @property string $login
 * @property string $firstname
 * @property string $lastname
 * @property integer $bc_user_id
 * @property string $bc_email
 * @property string $bc_avatar
 *
 * @property TasksBcUsers[] $tasksBcUsers
 * @property Tasks[] $tasks
 */
class BcUsers extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'bc_users';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login'], 'required'],
            [['bc_user_id'], 'integer'],
            [['login', 'firstname', 'bc_email', 'bc_avatar'], 'string', 'max' => 255],
            [['lastname'], 'string', 'max' => 50]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'firstname' => 'Firstname',
            'lastname' => 'Lastname',
            'bc_user_id' => 'Bc User ID',
            'bc_email' => 'Bc Email',
            'bc_avatar' => 'Bc Avatar',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasksBcUsers()
    {
        return $this->hasMany(TasksBcUsers::className(), ['bc_users_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Tasks::className(), ['id' => 'tasks_id'])->viaTable('tasks_bc_users', ['bc_users_id' => 'id']);
    }
}
