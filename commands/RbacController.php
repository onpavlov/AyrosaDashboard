<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

/**
 * This command create "admin" roles for some users.
 *
 */
class RbacController extends Controller
{
    /**
     * This command initiates roles for users.
     */
    public function actionInit()
    {
        $auth = Yii::$app->authManager;

        // Добавляем разрешение seeTasks
        $seeTasks = $auth->createPermission("seeTasks");
        $seeTasks->description = "See tasks";
        $auth->add($seeTasks);

        // Добавляем разрешение getTools
        $getTools = $auth->createPermission("getTools");
        $getTools->description = "Can use tools";
        $auth->add($getTools);

        // Добавляем разрешение updatePriority
        $updatePriority = $auth->createPermission("updatePriority");
        $updatePriority->description = "Can update the priority and sort tasks";
        $auth->add($updatePriority);

        // Добавляем роль user
        $user = $auth->createRole("user");
        $auth->add($user);
        $auth->addChild($user, $seeTasks);

        // Добавляем роль superuser
        $superuser = $auth->createRole("superuser");
        $auth->add($superuser);
        $auth->addChild($superuser, $updatePriority);
        $auth->addChild($superuser, $user);

        // Добавляем роль admin
        $admin = $auth->createRole("admin");
        $auth->add($admin);
        $auth->addChild($admin, $getTools);
        $auth->addChild($admin, $superuser);

        // Назначаем роли пользователям
        $auth->assign($admin, 1);
    }
}
