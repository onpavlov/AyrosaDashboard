<?php

namespace app\models;

use Yii;
use yii\caching\TagDependency;

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
    public function getTasks($filter = [])
    {
//        $tag1 = (isset($filter["user"])) ? "user" . $filter["user"] : "user0";
//        $tag2 = (isset($filter["project_id"])) ? "project" . $filter["project_id"] : "project0";

//        if (!$tasks = Yii::$app->cache->get($tag1.$tag2)) {
            if (isset($filter["project_id"]) && isset($filter["user"]) && $filter["project_id"] > 0 && $filter["user"] > 0) {
                $user = BcUsers::findOne($filter["user"]);
                $tasks = $user->getTasks()->where(["project_id" => $filter["project_id"]])->andWhere(["status" => 1])->orderBy("sort")->all();
            } elseif (isset($filter["user"]) && $filter["user"] > 0) {
                $tasks = BcUsers::findOne($filter["user"])->getTasks()->andWhere(["status" => 1])->orderBy("sort")->all();
            } elseif (isset($filter["project_id"]) && $filter["project_id"] > 0) {
                $tasks = Tasks::find()->where(["project_id" => $filter["project_id"]])->andWhere(["status" => 1])->orderBy("sort")->all();
            } else {
                $tasks = Tasks::find()->where(["status" => 1])->orderBy("sort")->all();
            }

//            Yii::$app->cache->set($tag1.$tag2, $tasks, 100);
//        }

        $arTasks = [
            "high" => [],
            "middle" => [],
            "low" => []
        ];

        foreach ($tasks as $task) {
            $project = $task->project;
            $users   = $task->bcUsers;

            $arTasks[$task->priority][] = [
                "id" => $task->id,
                "project" => $project->project_name,
                "project_url" => $project->link,
                "name" => $task->task_name,
                "sort" => $task->sort,
                "task_url" => $task->link,
                "date" => Yii::$app->formatter->asDate($task->date, 'php:d-m-Y'),
                "user" => ($users && $users->firstname && $users->lastname) ? $users->firstname . " " . $users->lastname : ""
            ];
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
        $result     = [];

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
            $tasks->sort            = ($tasks->sort) ? $tasks->sort: $maxSort + 10;
            $tasks->link            = Yii::$app->params["BChost"] . "projects/" . $project->bc_project_id . "/todo_items/" . $id . "/comments";

            if (!$tasks->save()) {
                if (($tasks->getIsNewRecord())) {
                    $result[] = [
                        "status" => "error",
                        "message" => "Ошибка добавления задачи " . (int) $task->id
                    ];
                } else {
                    $result[] = [
                        "status" => "error",
                        "message" => "Ошибка обновления задачи " . (int) $task->id
                    ];
                }
            }

            $taskUsers  = (TasksBcUsers::findOne(["tasks_id" => $tasks->id])) ? TasksBcUsers::findOne(["tasks_id" => $tasks->id]) : new TasksBcUsers();
            $users      = BcUsers::findOne(["bc_user_id" => (int) $task->{"responsible-party-id"}]);

            if ($taskUsers && $users) {
                $taskUsers->bc_users_id = $users->id;
                $taskUsers->tasks_id    = $tasks->id;
                $taskUsers->save();
            }

            unset($tasks);
        }

        return $result;
    }

    /*
     * Деактивирует задачи
     * @param $project_id integer ID Проекта
     * @param $tasks_id integer | array ID Задач
     * */
    public function deactivateTasks($project_id, $tasks_id = [])
    {
        $filter = empty($tasks_id) ? ["status" => 1, "project_id" => $project_id] : ["status" => 1, "id" => $tasks_id];
        $tasks  = $this->findAll($filter);

        foreach ($tasks as $task) {
            $task->status = 0;
            $task->save();
        }
    }

    /*
     * Принимает ID обновленных задач и возвращает массив
     * не затронутых задач для деактивации
     *
     * @param $project_id integer ID проекта
     * @param $tasks_id array ID обновленных задач
     * @return array
     * */
    public function getInactiveTasks($project_id, $tasks_id = [])
    {
        if (empty($tasks_id)) {
            return false;
        }

        $result = [];
        $db     = new \yii\db\Query();

        $tasks = $db->select("id")
                        ->from(Tasks::tableName())
                        ->where(["project_id" => $project_id])
                        ->andWhere(["not in", "bc_task_id", $tasks_id])
                        ->all();

        foreach ($tasks as $task) {
            $result[] = $task["id"];
        }

        return $result;
    }
}