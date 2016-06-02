<?php

namespace app\commands;

use yii;
use yii\console\Controller;
use app\models\Projects;
use app\models\Tasks;
use app\components\Curl;
use app\models\UpdateInfo;

/**
 * Controller for update tasks by crontab
 * */

class UpdateController extends Controller
{
    const ACTION_PROJECTS   = 'projects.xml';
    const ACTION_TODO       = 'todo_lists.xml';
    const ACTION_ITEMS      = 'todo_items.xml';
    const LOG_PATH          = '/logs/';
    const STATUS_UPDATING   = 'updating';
    const STATUS_COMPLETE   = 'complete';

    private $logPath;
    private $curDate;

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
        $result = $projects->updateProjects($this->getProjects());

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
        $typesXml = $this->getTaskType($project->bc_project_id);

        /* Деактивируем удаленные задачи */
        if (!$typesXml) {
            $project->deactivateProject($project_id["id"]);
            $tasks->deactivateTasks($project_id["id"]);
            return;
        }

        foreach ($typesXml->{"todo-list"} as $type) {
            $typeId     = (int) $type->id;
            $tasksXml   = $this->getTasks($typeId);

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
     * Получает xml данные при помощи CURL
     * @return string
     * */
    private function getXML($url)
    {
        $headers = array(
            "Accept: application/xml",
            "Content-Type: application/xml"
        );

        $config = array(
            "ssl_verifypeer" => 0,
            "ssl_verifyhost" => 0,
            "header" => 0,
            "timeout" => 30,
            "httpheader" => $headers,
            "returntransfer" => true,
            "useragent" => "Ayrosa (4pavlovon@gmail.com)",
            "userpwd" => Yii::$app->params["BClogin"] . ":" . Yii::$app->params["BCpassword"]
        );

        $curl = new Curl($url, $config);

        return $curl->execute();
    }

    /*
     * Получает xml данные со списком задач и возвращает объект для извлечения параметров
     * @param integer $id ID типа задачи
     * @return SimpleXMLElement object
     * */
    private function getTasks($id)
    {
        $xml = new \SimpleXMLElement($this->getXML(Yii::$app->params["BChost"] . "todo_lists/" . $id . "/" . self::ACTION_ITEMS));
        return $xml;
    }

    /*
     * Получает xml данные со списком типов задач и возвращает объект для извлечения параметров
     * @param integer $id ID проекта
     * @return SimpleXMLElement object
     * */
    private function getTaskType($id)
    {
        try {
            $result = new \SimpleXMLElement($this->getXML(Yii::$app->params["BChost"] . "projects/" . $id . "/" . self::ACTION_TODO));
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /*
     * Получает xml данные со списком всех проектов и возвращает объект для извлечения параметров
     * @return SimpleXMLElement object
     * */
    private function getProjects()
    {
        $xml = new \SimpleXMLElement($this->getXML(Yii::$app->params["BChost"] . self::ACTION_PROJECTS));
        return $xml;
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