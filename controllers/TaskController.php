<?php

namespace app\controllers;

use Yii;
use Yii\web\Request;
use app\models\Tasks;
use app\models\BcUsers;
use app\models\Projects;
use yii\filters\AccessControl;

class TaskController extends \yii\web\Controller
{
    public $layout = "dashboard";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['update'],
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        /*if (!Yii::$app->user->can("seeTasks")) {
            die("You don't have permissions");
        }*/

        $tasks      = new Tasks();
        $users      = new BcUsers();
        $projects   = new Projects();
        $filter     = array();

        if (Yii::$app->request->cookies->getValue("user") || Yii::$app->request->cookies->getValue("project_id")) {
            $filter["user"] = Yii::$app->request->cookies->getValue("user");
            $filter["project_id"] = Yii::$app->request->cookies->getValue("project_id");
        }

        $arTasks = $tasks->getTasks($filter);
        $arFilterData["users"] = $users->getUsers();
        $arFilterData["projects"] = $projects->getProjects();

        return $this->render("index", array("tasks" => $arTasks, "filter" => $arFilterData, "filterValue" => $filter));
    }

    public function actionUpdate()
    {
        $tasks = new Tasks();
        $r = new Request();
        $updateData = $r->post();

        $tasks->updateSort($updateData);
    }

    public function actionAjax()
    {
        $implementer    = Yii::$app->request->get("implementer");
        $project        = Yii::$app->request->get("project");

        $ajax = new Tasks();

        $filter = array(
            "user" => $implementer,
            "project_id" => $project
        );

        Yii::$app->response->cookies->remove("user");
        Yii::$app->response->cookies->remove("project_id");

        Yii::$app->response->cookies->add(new \yii\web\Cookie(["name" => "user", "value" => $implementer, "expire" => time() + 86400 * 365]));
        Yii::$app->response->cookies->add(new \yii\web\Cookie(["name" => "project_id", "value" => $project, "expire" => time() + 86400 * 365]));

        return json_encode($ajax->getTasks($filter));
    }

}