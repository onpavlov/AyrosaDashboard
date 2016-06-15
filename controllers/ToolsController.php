<?php

namespace app\controllers;

use app\models\UpdateInfo;
use yii;
use yii\filters\AccessControl;
use app\models\Tasks;
use app\models\BcUsers;
use app\models\Projects;
use app\components\XmlHelper;

class ToolsController extends \yii\web\Controller
{
    const STATUS_UPDATING   = 'updating';
    const STATUS_COMPLETE   = 'complete';

    public $layout  = "dashboard";
    public $avatar  = "/images/avatar.gif";
    private $result = [];

    public function init()
    {
        if (!Yii::$app->user->isGuest) {
            $email          = Yii::$app->user->identity->email;
            $this->avatar   = BcUsers::findOne(["bc_email" => $email])->bc_avatar;
        }
    }

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['index'],
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        if (!Yii::$app->user->can("getTools")) {
            throw new \yii\web\HttpException(404, 'Запрашиваемая страница не найдена.');
        }

        $params = [];
        $updInfo = new UpdateInfo();
        $date = $updInfo->find()->orderBy(['id' => SORT_DESC])->one();
        if ($date) {
            $status = ($date->status == self::STATUS_UPDATING) ? 'В процессе' : 'Завершено';
            $params = [
                'date' => $date->last_update,
                'status' => $status
            ];
        }

        return $this->render('index', $params);
    }

    /*
     * Обновляет различные xml данные в зависимости от переданного GET параметра item
     * @return json
     * */
    public function actionUpdate()
    {
        if (!Yii::$app->user->can("getTools")) {
            throw new \yii\web\HttpException(404, 'Запрашиваемая страница не найдена.');
        }

        $action = Yii::$app->request->get("action");
        $params = Yii::$app->request->get("params");

        switch($action) {
            case "usersUpdate":
                $users = new BcUsers();
                $this->result = $users->updateUsers(XmlHelper::getPeople());
                break;

            case "taskUpdate":
                $this->result = $this->updateTask($params);
                break;
        }

        echo json_encode($this->result);
    }

    /*
     * Возвращает json массив с идентификаторами объектов
     * @return json
     * */
    public function actionProjects()
    {
        if (!Yii::$app->user->can("getTools")) {
            throw new \yii\web\HttpException(404, 'Запрашиваемая страница не найдена.');
        }

        $projects   = new Projects();
        $updInfo    = new UpdateInfo();
        $projects->updateProjects(XmlHelper::getProjects());
        
        /* Записываем текущую дату и время как дату последнего обновления */
        $updInfo->last_update = Yii::$app->formatter->asDatetime(time(), 'php:Y-m-d H:i:s');
        $updInfo->status = self::STATUS_COMPLETE;
        $updInfo->save();

        echo json_encode($projects->getProjectsIds());
    }

    /*
     * Выбирает задачи соответствующие проекту и записывает в таблицу tasks
     * 
     * @param integer $projectID
     * @return array
     * */

    private function updateTask($projectID)
    {
        $tasks      = new Tasks();
        $result     = [];
        $updated    = [];

        $project = Projects::findOne($projectID);
        $typesXml = XmlHelper::getTaskType($project->bc_project_id);
        
        /* Деактивируем удаленные задачи (когда вернулась пустая xml) */
        if (!$typesXml) {
            $project->deactivateProject($projectID["id"]);
            $tasks->deactivateTasks($projectID["id"]);
            return;
        }

        foreach ($typesXml->{"todo-list"} as $type) {
            $typeId     = (int) $type->id;
            $tasksXml   = XmlHelper::getTasks($typeId);

            foreach ($tasksXml->{"todo-item"} as $task) {
                $result     = array_merge($result, $tasks->saveTask($task, $type, $project));
                $updated[]  = (int) $task->id;
            }
        }

        /* Деактивируем удаленные задачи */
        $inactiveTasks = $tasks->getInactiveTasks($projectID["id"], $updated);

        if (!empty($inactiveTasks)) {
            $tasks->deactivateTasks($projectID["id"], $inactiveTasks);
        }

        if (empty($result)) {
            $result[] = [
                "status" => "success",
                "message" => "Задачи проекта " . $project->project_name . " успешно обновлены!"
            ];
        }

        return $result;
    }

}
