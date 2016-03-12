<?php

use yii\db\Migration;

class m160312_222123_users_del_role extends Migration
{
    public function up()
    {
        $this->dropColumn("users", "role");
    }

    public function down()
    {
        $this->addColumn("users", "role", "int");
    }
}
