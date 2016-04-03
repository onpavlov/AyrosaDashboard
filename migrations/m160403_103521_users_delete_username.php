<?php

use yii\db\Migration;

class m160403_103521_users_delete_username extends Migration
{
    public function up()
    {
        $this->dropColumn("users", "username");
    }

    public function down()
    {
        $this->addColumn("users", "username", "string");
    }
}
