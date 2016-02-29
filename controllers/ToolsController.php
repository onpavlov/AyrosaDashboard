<?php

namespace app\controllers;

use yii\filters\AccessControl;
use Yii;

class ToolsController extends \yii\web\Controller
{
    const BASECAMP_URL = "https://ayrosa.basecamphq.com/";
    const ACTION_PROJECTS = "projects.xml";
    const ACTION_PEOPLE = "people.xml";

    public $layout = "dashboard";

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
        return $this->render('index');
    }

    public function actionParse()
    {
        header("Content-Type: text/xml");
        echo $this->getPeople();
    }

    private function getXML($url)
    {
        $headers = array(
            "Accept: application/xml",
            "Content-Type: application/xml"
        );

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Ayrosa (4pavlovon@gmail.com)");
        curl_setopt($ch, CURLOPT_USERPWD, Yii::$app->params["BClogin"] . ":" . Yii::$app->params["BCpassword"]);

        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    private function getPeople()
    {
        return $this->getXML(self::BASECAMP_URL . self::ACTION_PEOPLE);
    }

}
