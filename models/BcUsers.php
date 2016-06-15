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

    /*
     * Возвращает список всех пользователей
     * @return array
     * */
    public function getUsers()
    {
        return $this->find()->all();
    }

    /*
     * Выбирает необходимые данные из xml объекта и записывает в таблицу bc_users
     *
     * @param SiteXMLElement $xmlObject
     * @return array
     * */
    public function updateUsers(\SimpleXMLElement $xmlObject)
    {
        $updatedUsers   = array();
        $delete         = array();
        
        foreach ($xmlObject as $person) {
            $id         = (int) $person->id;
            $result     = array();

            $users      = ($this->findOne(["bc_user_id" => $id])) ? $this->findOne(["bc_user_id" => $id]) : new BcUsers();

            $users->bc_user_id  = $id;
            $users->login       = (string) $person->{"email-address"};
            $users->firstname   = (string) $person->{"first-name"};
            $users->lastname    = (string) $person->{"last-name"};
            $users->bc_email    = (string) $person->{"email-address"};
            $users->bc_avatar   = (string) $person->{"avatar-url"};

            if (!$users->save()) {
                if (($users->getIsNewRecord())) {
                    $result[] = array(
                        "status" => "error",
                        "message" => "Ошибка добавления данных пользователя " . (string) $person->{"first-name"} . " " . (string) $person->{"last-name"}
                    );
                } else {
                    $result[] = array(
                        "status" => "error",
                        "message" => "Ошибка обновления данных пользователя " . (string) $person->{"first-name"} . " " . (string) $person->{"last-name"}
                    );
                }
            }
            
            $updatedUsers[] = $id;
            unset($users);
        }

        $sqlUsers = $this->find()->select("bc_user_id")->all();

        foreach ($sqlUsers as $sqlUser) {
            if (!in_array($sqlUser->bc_user_id, $updatedUsers)) {
                $delete[] = $sqlUser->bc_user_id;
            }
        }

        $this->deleteUsers($delete);

        if (empty($result)) {
            $result[] = array(
                "status" => "success",
                "message" => "Данные пользователей успешно обновлены!"
            );
        }

        return $result;
    }

    /*
     * Удаляет пользователей из таблицы bc_users и деактивирует
     * соответствующего пользователя дашборда
     * */
    public function deleteUsers($id = [])
    {
        if (!empty($id)) {
            $bcUser = $this->findAll(["bc_user_id" => $id]);

            foreach ($bcUser as $item) {
                if ($user = \app\models\Users::findOne(["email" => $item->bc_email])) {
                    $user->status = 0;
                    $user->save();
                }
            }

            $this->deleteAll(["bc_user_id" => $id]);
        }
    }
}
