<?php

namespace app\controllers;

use Yii;
use Yii\web\Request;
use app\models\Tasks;
use app\models\Users;
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
                        'allow' => false,
                        'actions' => ['update'],
                        'roles' => ['?'],
                    ],
                ],
            ],
        ];
    }

    public function actionIndex($filter = "")
    {
        if (empty($filter)) {
            $tasks = Tasks::find()->orderBy("sort")->all();
        }

        $arTasks = array(
            "high" => array(),
            "middle" => array(),
            "low" => array(),
        );
        $arFilter["users"] = $this->getUsers();
        $arFilter["projects"] = $this->getProjects();

        foreach ($tasks as $task) {
            $project = $task->project;
            $users   = $task->users;

            $arTasks[$task->priority][] = array(
                "id" => $task->id,
                "project" => $project->project_name,
                "project_url" => $project->link,
                "name" => $task->task_name,
                "sort" => $task->sort,
                "task_url" => $task->link,
                "date" => Yii::$app->formatter->asDate($task->date, 'php:d-m-Y'),
                "user" => $users[0]->username
            );
        }

        return $this->render("index", array("tasks" => $arTasks, "filter" => $arFilter));
    }

    public function actionUpdate()
    {
        $r = new Request();
        $updateData = $r->post();
        $i = 0;

        foreach ($updateData["sort"] as $priority => $sort) {
            natsort($sort);

            if (!empty($sort))
                foreach ($sort as $s) {
                    $id = $updateData["id"][$i];

                    Tasks::updateAll(array("priority" => $priority, "sort" => $s * 1), "id = $id");
                    $i++;
                }
        }
    }

    /*
     * Возвращает список всех пользователей
     * */
    private function getUsers()
    {
        $arUsers    = Users::find()->all();
        $result     = array();

        foreach ($arUsers as $user) {
            $result[] = array(
                "id" => $user->id,
                "username" => $user->username,
                "email" => $user->email,
                "firstname" => $user->firstname,
                "lastname" => $user->lastname,
            );
        }

        return $result;
    }

    /*
     * Возвращает список всех проектов
     * */
    private function getProjects()
    {
        $arUsers    = Projects::find()->all();
        $result     = array();

        foreach ($arUsers as $user) {
            $result[] = array(
                "id" => $user->id,
                "project_name" => $user->project_name,
            );
        }

        return $result;
    }

}