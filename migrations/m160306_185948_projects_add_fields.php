<?php

use yii\db\Migration;

class m160306_185948_projects_add_fields extends Migration
{
    public function up()
    {
        $this->addColumn("projects", "bc_project_id", "integer");
        $this->addColumn("projects", "last_change", "datetime");
        $this->addColumn("projects", "status", "smallint");
    }

    public function down()
    {
        $this->dropColumn("projects", "bc_project_id");
        $this->dropColumn("projects", "last_change");
        $this->dropColumn("projects", "status");
    }
}
