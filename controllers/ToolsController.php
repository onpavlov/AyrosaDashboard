<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use app\models\Tasks;
use app\models\BcUsers;
use app\models\Projects;
use app\components\Curl;

class ToolsController extends \yii\web\Controller
{
    const ACTION_PROJECTS   = "projects.xml";
    const ACTION_PEOPLE     = "people.xml";
    const ACTION_TODO       = "todo_lists.xml";
    const ACTION_ITEMS      = "todo_items.xml";

    public $layout  = "dashboard";
    private $result = array();

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
        /*if (!Yii::$app->user->can("getTools")) {
            die("You don't have permissions");
        }*/

        return $this->render('index');
    }

    /*
     * Обновляет различные xml данные в зависимости от переданного GET параметра item
     * @return json
     * */
    public function actionUpdate()
    {
        $action = Yii::$app->request->get("action");
        $params = Yii::$app->request->get("params");

        switch($action) {
            case "usersUpdate":
                $users = new BcUsers();

                $this->result = $users->updateUsers($this->getPeople());
                break;

            case "projectsUpdate":
                $projects = new Projects();

                $this->result = $projects->updateProjects($this->getProjects());
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
        $projects = new Projects();
        echo json_encode($projects->getProjectsIds());
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
     * Получает xml данные со списком всех пользователей и возвращает объект для извлечения параметров
     * @return SimpleXMLElement object
     * */
    private function getPeople()
    {
        $xml = new \SimpleXMLElement($this->getXML(Yii::$app->params["BChost"] . self::ACTION_PEOPLE));
        return $xml;
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
     * Получает xml данные со списком типов задач и возвращает объект для извлечения параметров
     * @param integer $id ID проекта
     * @return SimpleXMLElement object
     * */
    private function getTaskType($id)
    {
        $xml = new \SimpleXMLElement($this->getXML(Yii::$app->params["BChost"] . "projects/" . $id . "/" . self::ACTION_TODO));
        return $xml;
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
     * Выбирает задачи соответствующие проекту и записывает в таблицу tasks
     * */

    private function updateTask($project_id)
    {
        $tasks  = new Tasks();
        $result = array();

        $project = Projects::findOne($project_id);
        $typesXml = $this->getTaskType($project->bc_project_id);

        foreach ($typesXml->{"todo-list"} as $type) {
            $typeId     = (int) $type->id;
            $tasksXml   = $this->getTasks($typeId);

            foreach ($tasksXml->{"todo-item"} as $task) {
                $result = array_merge($result, $tasks->saveTask($task, $type, $project));
            }
        }

        if (empty($result)) {
            $result[] = array(
                "status" => "success",
                "message" => "Задачи проекта " . $project->project_name . " успешно обновлены!"
            );
        }

        return $result;
    }

}
