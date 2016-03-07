<?php

use yii\db\Migration;

class m160306_193534_tasks_add_fields extends Migration
{
    public function up()
    {
        $this->addColumn("tasks", "bc_task_id", "integer");
        $this->addColumn("tasks", "bc_type_id", "integer");
        $this->addColumn("tasks", "bc_type_name", "string");
        $this->addColumn("tasks", "status", "smallint");
        $this->addColumn("tasks", "comments_count", "integer");
    }

    public function down()
    {
        $this->dropColumn("tasks", "bc_task_id");
        $this->dropColumn("tasks", "bc_type_id");
        $this->dropColumn("tasks", "bc_type_name");
        $this->dropColumn("tasks", "status");
        $this->dropColumn("tasks", "comments_count");
    }
}
