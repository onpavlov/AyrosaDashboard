<?php

use yii\db\Migration;

class m160304_182516_tasks_projects extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable("tasks", [
            "id" => $this->primaryKey(),
            "project_id" => $this->integer()->notNull(),
            "task_name" => $this->string(255),
            "priority" => $this->string(50),
            "sort" => $this->integer(),
            "date" => $this->dateTime(),
            "link" => $this->string()
        ], $tableOptions);

        $this->createTable("projects", [
            "id" => $this->primaryKey(),
            "project_name" => $this->string(255),
            "link" => $this->string()
        ], $tableOptions);

        $this->createIndex("idx-projects-project_id", "tasks", "project_id");
        $this->addForeignKey("fk-projects-project_id", "tasks", "project_id", "projects", "id", "CASCADE");
    }

    public function safeDown()
    {
        $this->dropTable("tasks");
        $this->dropTable("projects");
    }

}
