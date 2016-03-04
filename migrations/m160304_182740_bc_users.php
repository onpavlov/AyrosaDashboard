<?php

use yii\db\Migration;

class m160304_182740_bc_users extends Migration
{
    public function up()
    {
        $this->createTable("bc_users", [
            "id" => $this->primaryKey(),
            "login" => $this->string()->notNull(),
            "firstname" => $this->string(255),
            "lastname" => $this->string(50)
        ]);
    }

    public function down()
    {
        $this->dropTable("bc_users");
    }
}
