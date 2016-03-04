<?php

use yii\db\Migration;

class m160304_182740_bc_users extends Migration
{
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable("bc_users", [
            "id" => $this->primaryKey(),
            "login" => $this->string()->notNull(),
            "firstname" => $this->string(255),
            "lastname" => $this->string(50)
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable("bc_users");
    }
}
