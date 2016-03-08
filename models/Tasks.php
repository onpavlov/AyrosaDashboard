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
 * @property integer $bc_task_id
 * @property integer $bc_type_id
 * @property string $bc_type_name
 * @property integer $status
 * @property integer $comments_count
 *
 * @property Projects $project
 * @property TasksBcUsers[] $tasksBcUsers
 * @property BcUsers[] $bcUsers
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
            [['project_id', 'sort', 'bc_task_id', 'bc_type_id', 'status', 'comments_count'], 'integer'],
            [['date'], 'safe'],
            [['task_name', 'link', 'bc_type_name'], 'string', 'max' => 255],
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
            'bc_task_id' => 'Bc Task ID',
            'bc_type_id' => 'Bc Type ID',
            'bc_type_name' => 'Bc Type Name',
            'status' => 'Status',
            'comments_count' => 'Comments Count',
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
    public function getTasksBcUsers()
    {
        return $this->hasOne(TasksBcUsers::className(), ['tasks_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBcUsers()
    {
        return $this->hasOne(BcUsers::className(), ['id' => 'bc_users_id'])->viaTable('tasks_bc_users', ['tasks_id' => 'id']);
    }

    /**
     * @param $filter - ассоциативный массив с фильтром
     * @return array
     */
    public function getTasks($filter = array())
    {
        if ($filter["project_id"] > 0 && $filter["user"] > 0) {
            $user = BcUsers::findOne($filter["user"]);
            $tasks = $user->getTasks()->where(["project_id" => $filter["project_id"]])->orderBy("sort")->all();
        } elseif ($filter["user"] > 0) {
            $tasks = BcUsers::findOne($filter["user"])->getTasks()->orderBy("sort")->all();
        } elseif ($filter["project_id"] > 0) {
            $tasks = Tasks::find()->where(["project_id" => $filter["project_id"]])->orderBy("sort")->all();
        } else {
            $tasks = Tasks::find()->orderBy("sort")->all();
        }

        $arTasks = array(
            "high" => array(),
            "middle" => array(),
            "low" => array(),
        );

        foreach ($tasks as $task) {
            $project = $task->project;
            $users   = $task->bcUsers;

            $arTasks[$task->priority][] = array(
                "id" => $task->id,
                "project" => $project->project_name,
                "project_url" => $project->link,
                "name" => $task->task_name,
                "sort" => $task->sort,
                "task_url" => $task->link,
                "date" => Yii::$app->formatter->asDate($task->date, 'php:d-m-Y'),
                "user" => ($users->firstname && $users->lastname) ? $users->firstname . " " . $users->lastname : ""
            );
        }

        return $arTasks;
    }

    /*
     * Обновляет индексы сортировки и приоритера
     * */
    public function updateSort($updateData)
    {
        $i = 0;

        foreach ($updateData["sort"] as $priority => $sort) {
            natsort($sort);

            if (!empty($sort))
                foreach ($sort as $s) {
                    $id = $updateData["id"][$i];

                    $this->updateAll(array("priority" => $priority, "sort" => $s * 1), "id = $id");
                    $i++;
                }
        }
    }

    /*
     * Сохраняет / обновляет задачу
     * @param object SimpleXmlElement $task
     * @param object SimpleXmlElement $type
     * @param object Project $project
     *
     * @return array
     * */
    public function saveTask($task, $type, $project)
    {
        $id         = (int) $task->id;
        $date       = (string) $task->{"created-at"};
        $completed  = ((string) $task->completed == "true") ? 1 : 0;
        $result     = array();

        $object = $this->findOne(["bc_task_id" => $id, "project_id" => $project->id]);

        if ($completed && $object) { // Если задача закрыта - обновляем только статус
            $tasks = $object;
            $tasks->status = 0;

            $tasks->save();
        } elseif (!$completed) {
            $db         = new \yii\db\Query();
            $tasks      = ($object) ? $object : new Tasks();
            $maxSort    = $db->select("sort")->from("tasks")->max("sort"); // Выбираем максимальную сортировку

            $tasks->project_id      = $project->id;
            $tasks->task_name       = (string) $task->content;
            $tasks->priority        = ($tasks->getIsNewRecord()) ? "low" : $tasks->priority;
            $tasks->date            = date("Y-m-d h:i:s", strtotime($date));
            $tasks->bc_task_id      = (int) $task->id;
            $tasks->bc_type_id      = (int) $type->id;
            $tasks->bc_type_name    = (string) $type->name;
            $tasks->status          = 1;
            $tasks->comments_count  = (int) $task->{"comments-count"};
            $tasks->sort            = ($maxSort) ? $maxSort + 10 : 10;
            $tasks->link            = Yii::$app->params["BChost"] . "projects/" . $project->bc_project_id . "/todo_items/" . $id . "/comments";

            if (!$tasks->save()) {
                if (($tasks->getIsNewRecord())) {
                    $result[] = array(
                        "status" => "error",
                        "message" => "Ошибка добавления задачи " . (int) $task->id
                    );
                } else {
                    $result[] = array(
                        "status" => "error",
                        "message" => "Ошибка обновления задачи " . (int) $task->id
                    );
                }
            }

            $taskUsers  = (TasksBcUsers::findOne(["tasks_id" => $tasks->id])) ? TasksBcUsers::findOne(["tasks_id" => $tasks->id]) : new TasksBcUsers();
            $users      = BcUsers::findOne(["bc_user_id" => (string) $task->{"responsible-party-id"}]);

            $taskUsers->tasks_id    = $tasks->id;
            $taskUsers->bc_users_id = $users->id;
            $taskUsers->save();

            unset($tasks);
        }

        return $result;
    }
}
