<?php

namespace app\controllers;

use yii\filters\AccessControl;
use Yii;
use app\models\BcUsers;

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
        $item = Yii::$app->request->get("item");

        switch($item) {
            case "users":
                echo json_encode($this->updateUsers($this->getPeople()));
                break;
        }
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
        $xml = new \SimpleXMLElement($this->getXML(self::BASECAMP_URL . self::ACTION_PEOPLE));
        return $xml;
    }

    private function updateUsers($xmlObject)
    {
        $result = array();

        foreach ($xmlObject as $person) {
            $users = new BcUsers();
            $id = (int) $person->{"id"};

            /* Проверяем новый пользователь или нет */
            if ($users->findOne(["bc_user_id" => $id]))
                $users->setIsNewRecord(false);
            else
                $users->setIsNewRecord(true);

            $users->bc_user_id  = (int) $person->{"id"};
            $users->login       = (string) $person->{"email-address"};
            $users->firstname   = (string) $person->{"first-name"};
            $users->lastname    = (string) $person->{"last-name"};
            $users->bc_email    = (string) $person->{"email-address"};
            $users->bc_avatar   = (string) $person->{"avatar-url"};

            if ($users->save()) {
                if (($users->getIsNewRecord())) {
                    $result[] = array(
                        "status" => "success",
                        "message" => "Добавлен пользователь " . (string) $person->{"first-name"} . " " . (string) $person->{"last-name"}
                    );
                } else {
                    $result[] = array(
                        "status" => "success",
                        "message" => "Данные пользователя " . (string) $person->{"first-name"} . " " . (string) $person->{"last-name"} . " обновлены"
                    );
                }
            } else {
                if (($users->getIsNewRecord())) {
                    $result[] = array(
                        "status" => "success",
                        "message" => "Ошибка добавления данных пользователя " . (string) $person->{"first-name"} . " " . (string) $person->{"last-name"}
                    );
                } else {
                    $result[] = array(
                        "status" => "success",
                        "message" => "Ошибка обновления данных пользователя " . (string) $person->{"first-name"} . " " . (string) $person->{"last-name"}
                    );
                }
            }

            unset($users);
        }

        return $result;
    }

}
