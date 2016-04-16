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
    public $avatar = "/images/avatar.gif";
    private $bcUser;
    public $email;

    public function init()
    {
        if (!Yii::$app->user->isGuest) {
            $this->email    = Yii::$app->user->identity->email;
            $this->bcUser   = BcUsers::findOne(["bc_email" => $this->email]);
            $this->avatar   = $this->bcUser->bc_avatar;
        }
    }

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
        if (!Yii::$app->user->can("updatePriority")) {
            return $this->redirect("/mytasks");
        }

        $tasks      = new Tasks();
        $users      = new BcUsers();
        $projects   = new Projects();
        $filter     = array();

        /* Берем фильтры из куки если существуют */
        if (Yii::$app->request->cookies->getValue("user") || Yii::$app->request->cookies->getValue("project_id")) {
            $filter["user"] = Yii::$app->request->cookies->getValue("user");
            $filter["project_id"] = Yii::$app->request->cookies->getValue("project_id");
        }

        $arTasks = $tasks->getTasks($filter);
        $arFilterData["users"] = $users->getUsers();
        $arFilterData["projects"] = $projects->getProjects();

        return $this->render("index", array("tasks" => $arTasks, "filter" => $arFilterData, "filterValue" => $filter));
    }

    public function actionMytasks()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect("/login");
        }

        $tasks      = new Tasks();
        $filter     = array();

        $filter["user"] = $this->bcUser->id;
        $arTasks        = $tasks->getTasks($filter);

        return $this->render("mytasks", array("tasks" => $arTasks, "filterValue" => $filter));
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

        $result = $ajax->getTasks($filter);
        $result["updatePriority"] = (Yii::$app->user->can("updatePriority")) ? true : false;

        return json_encode($result);
    }

}