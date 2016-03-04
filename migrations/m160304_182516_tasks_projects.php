<?php

use yii\db\Migration;

class m160304_182516_tasks_projects extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->createTable("tasks", [
            "id" => $this->primaryKey(),
            "project_id" => $this->integer()->notNull(),
            "task_name" => $this->string(255),
            "priority" => $this->string(50),
            "sort" => $this->integer(),
            "date" => $this->dateTime(),
            "link" => $this->string()
        ]);

        $this->createTable("projects", [
            "id" => $this->primaryKey(),
            "project_name" => $this->string(255),
            "link" => $this->string()
        ]);

        $this->createIndex("idx-projects-project_id", "tasks", "project_id");
        $this->addForeignKey("fk-projects-project_id", "tasks", "project_id", "projects", "id", "CASCADE");
    }

    public function safeDown()
    {
        $this->dropTable("tasks");
        $this->dropTable("projects");
    }

}
