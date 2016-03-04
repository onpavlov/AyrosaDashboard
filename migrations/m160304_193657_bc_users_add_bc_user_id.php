<?php

use yii\db\Migration;

class m160304_193657_bc_users_add_bc_user_id extends Migration
{
    public function up()
    {
        $this->addColumn("bc_users", "bc_user_id", "integer");
        $this->addColumn("bc_users", "bc_email", "string");
        $this->addColumn("bc_users", "bc_avatar", "string");
    }

    public function down()
    {
        $this->dropColumn("bc_users", "bc_user_id");
        $this->dropColumn("bc_users", "bc_email");
        $this->dropColumn("bc_users", "bc_avatar");
    }
}
