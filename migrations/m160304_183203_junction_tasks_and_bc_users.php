<?php

use yii\db\Migration;

class m160304_183203_junction_tasks_and_bc_users extends Migration
{
    public function up()
    {
        $this->createTable('tasks_bc_users', [
            'tasks_id' => $this->integer(),
            'bc_users_id' => $this->integer(),
            'PRIMARY KEY(tasks_id)'
        ]);

        $this->createIndex('idx-tasks_users-tasks_id', 'tasks_bc_users', 'tasks_id');
        $this->createIndex('idx-tasks_users-users_id', 'tasks_bc_users', 'bc_users_id');

        $this->addForeignKey('fk-tasks_users-tasks_id', 'tasks_bc_users', 'tasks_id', 'tasks', 'id', 'CASCADE');
        $this->addForeignKey('fk-tasks_users-users_id', 'tasks_bc_users', 'bc_users_id', 'bc_users', 'id', 'CASCADE');
    }

    public function down()
    {
        $this->dropTable('tasks_bc_users');
    }
}
