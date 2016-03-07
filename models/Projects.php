<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "projects".
 *
 * @property integer $id
 * @property string $project_name
 * @property string $link
 *
 * @property Tasks[] $tasks
 */
class Projects extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'projects';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['project_name', 'link'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'project_name' => 'Project Name',
            'link' => 'Link',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTasks()
    {
        return $this->hasMany(Tasks::className(), ['project_id' => 'id']);
    }

    /*
     * Возвращает список всех проектов
     * */
    public function getProjects()
    {
        return $this->find()->all();
    }

    /*
     * Возвращает массив с идентификаторами всех проектов
     * */
    public function getProjectsIds()
    {
        $db = new \yii\db\Query();
        return $db->select("id")->from("projects")->all();
    }

    /*
     * Выбирает необходимые данные из xml объекта и записывает в таблицу projects
     * */
    public function updateProjects(\SimpleXMLElement $xmlObject)
    {
        $result     = array();

        foreach ($xmlObject->project as $project) {
            $id         = (int) $project->id;
            $date       = (string) $project->{"last-changed-on"};
            $status     = (string) $project->status;

            $projects   = ($this->findOne(["bc_project_id" => $id])) ? $this->findOne(["bc_project_id" => $id]) : new Projects();

            $projects->project_name = (string) $project->name;
            $projects->bc_project_id = $id;
            $projects->last_change = date("Y-m-d h:i:s", strtotime($date));
            $projects->status = ($status == "active") ? (int) 1 : (int) 0;
            $projects->link = Yii::$app->params["BChost"] . "projects/" . $id . "/";

            if (!$projects->save()) {
                if (($projects->getIsNewRecord())) {
                    $result[] = array(
                        "status" => "error",
                        "message" => "Ошибка добавления данных проекта " . (string) $project->name
                    );
                } else {
                    $result[] = array(
                        "status" => "error",
                        "message" => "Ошибка обновления данных проекта " . (string) $project->name
                    );
                }
            }

            unset($projects);
        }

        if (empty($result)) {
            $result[] = array(
                "status" => "success",
                "message" => "Данные проектов успешно обновлены!"
            );
        }

        return $result;
    }
}
