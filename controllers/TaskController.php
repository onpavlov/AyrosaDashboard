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

    public function actionIndex($filter = array())
    {
        $tasks      = new Tasks();
        $users      = new BcUsers();
        $projects   = new Projects();

        $arTasks = $tasks->getTasks($filter);
        $arFilter["users"] = $users->getUsers();
        $arFilter["projects"] = $projects->getProjects();

        return $this->render("index", array("tasks" => $arTasks, "filter" => $arFilter));
    }

    public function actionUpdate()
    {
        $tasks = new Tasks();
        $r = new Request();
        $updateData = $r->post();

        $tasks->updateSort($updateData);
    }

}