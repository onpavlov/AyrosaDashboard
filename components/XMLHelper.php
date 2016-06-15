<?php

namespace app\components;

use yii\base\Object;
use yii;

class XmlHelper extends Object
{
    const ACTION_PROJECTS   = "projects.xml";
    const ACTION_PEOPLE     = "people.xml";
    const ACTION_TODO       = "todo_lists.xml";
    const ACTION_ITEMS      = "todo_items.xml";

    /*
     * Получает xml данные при помощи CURL
     * @return string
     * */
    public static function getXML($url)
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
    public static function getPeople()
    {
        $url = Yii::$app->params["BChost"] . self::ACTION_PEOPLE;
        $xml = new \SimpleXMLElement(self::getXML($url));
        return $xml;
    }

    /*
     * Получает xml данные со списком всех проектов и возвращает объект для извлечения параметров
     * @return SimpleXMLElement object
     * */
    public static function getProjects()
    {
        $url = Yii::$app->params["BChost"] . self::ACTION_PROJECTS;
        $xml = new \SimpleXMLElement(self::getXML($url));
        return $xml;
    }

    /*
     * Получает xml данные со списком типов задач и возвращает объект для извлечения параметров
     * @param integer $id ID проекта
     * @return SimpleXMLElement object
     * */
    public static function getTaskType($id)
    {
        try {
            $url = Yii::$app->params["BChost"] . "projects/" . $id . "/" . self::ACTION_TODO;
            $result = new \SimpleXMLElement(self::getXML($url));
        } catch (\Exception $e) {
            $result = false;
        }

        return $result;
    }

    /*
     * Получает xml данные со списком задач и возвращает объект для извлечения параметров
     * @param integer $id ID типа задачи
     * @return SimpleXMLElement object
     * */
    public static function getTasks($id)
    {
        $url = Yii::$app->params["BChost"] . "todo_lists/" . $id . "/" . self::ACTION_ITEMS;
        $xml = new \SimpleXMLElement(self::getXML($url));
        return $xml;
    }
}