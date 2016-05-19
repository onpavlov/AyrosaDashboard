<?php

use yii\db\Migration;

class m160518_103552_update_info extends Migration
{
    public function up()
    {
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable(
            'update_info',
            [
                'id' => $this->primaryKey(),
                'last_update' => $this->dateTime(),
                'status' => $this->string()
            ],
            $tableOptions
        );
    }

    public function down()
    {
        $this->dropTable('update_info');
    }
}
