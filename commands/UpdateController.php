<?php

namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\Projects;
use app\models\Tasks;
use app\models\UpdateInfo;

/**
 * Controller for update tasks by crontab
 * */

class UpdateController extends Controller
{
    const LOG_PATH          = '/logs/';
    const STATUS_UPDATING   = 'updating';
    const STATUS_COMPLETE   = 'complete';

    private $logPath;
    private $curDate;

    public function init()
    {
        parent::init();
        Yii::$classMap['app\components\XmlHelper'] = '@app/components/XmlHelper.php';
    }

    /**
     * This command running update tasks
     * */

    public function actionRun()
    {
        $timestamp = time();
        $this->curDate = Yii::$app->formatter->asDatetime($timestamp, 'php:Y-m-d');
        $time = Yii::$app->formatter->asDatetime($timestamp, 'php:H:i:s');
        $this->logPath = Yii::$app->basePath . self::LOG_PATH;
        $this->writeLog($this->curDate . " " . $time);

        /* Записываем текущую дату как дату последнего обновления */
        $updInfo = new UpdateInfo();
        $updInfo->last_update = Yii::$app->formatter->asDatetime(time(), 'php:Y-m-d H:i:s');
        $updInfo->status = self::STATUS_UPDATING;
        $updInfo->save();

        try {
            $projects = new Projects();
            $this->updateProjects();
            $arProjects = $projects->getProjectsIds();

            foreach ($arProjects as $project) {
                $this->updateTask($project);
            }

            if (Projects::find()->count() > 50) {
                Projects::find()->one()->delete();
            }
        } catch (yii\base\Exception $e) {
            $this->writeLog($e->getMessage());
        }

        $updInfo->status = self::STATUS_COMPLETE;
        $updInfo->save();

        $this->writeLog("Tasks were updated " . Yii::$app->formatter->asRelativeTime(time(), $timestamp));
        $this->writeLog("=============================================");
    }

    /*
     * Обновляет таблицу проектов
     * */
    private function updateProjects()
    {
        $projects = new Projects();
        $result = $projects->updateProjects(Yii::$app->xmlhelper->getProjects());

        if (!empty($result)) {
            $this->writeLog(print_r($result, true));
        }
    }

    /*
     * Выбирает задачи соответствующие проекту и записывает в таблицу tasks
     * @return array
     * */

    private function updateTask($project_id)
    {
        $tasks      = new Tasks();
        $errors     = [];
        $updated    = [];

        $project = Projects::findOne($project_id);
        $typesXml = Yii::$app->xmlhelper->getTaskType($project->bc_project_id);

        /* Деактивируем удаленные задачи */
        if (!$typesXml) {
            $project->deactivateProject($project_id["id"]);
            $tasks->deactivateTasks($project_id["id"]);
            return;
        }

        foreach ($typesXml->{"todo-list"} as $type) {
            $typeId     = (int) $type->id;
            $tasksXml   = Yii::$app->xmlhelper->getTasks($typeId);

            foreach ($tasksXml->{"todo-item"} as $task) {
                $errors     = array_merge($errors, $tasks->saveTask($task, $type, $project));
                $updated[]  = (int) $task->id;
            }
        }

        /* Деактивируем удаленные задачи */
        $inactiveTasks = $tasks->getInactiveTasks($project_id["id"], $updated);

        if (!empty($inactiveTasks)) {
            $tasks->deactivateTasks($project_id["id"], $inactiveTasks);
        }

        if (!empty($errors)) {
            $this->writeLog(print_r($errors, true));
        }
    }

    /*
     * Записывает переданную строку в лог-файл
     * @param string $message
     * */
    private function writeLog($message)
    {
        if (empty($message) && is_string($message)) {
            return;
        }

        if (!file_exists($this->logPath)) {
            mkdir($this->logPath);
        }

        $message .= "\n";
        file_put_contents($this->logPath . $this->curDate . '.log', $message, FILE_APPEND);
    }
}