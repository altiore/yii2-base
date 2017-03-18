<?php
namespace altiore\base\migrations;

use altiore\base\console\Migration;

class m170318_000000_create_image_table extends Migration
{
    private $table = '{{%image}}';

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable($this->table, [
            'id'          => $this->primaryKey(),
            'title'       => $this->string()->notNull(),
            'url'         => $this->string()->notNull()->unique(),
            'ext'         => $this->string(4)->notNull(),
            'created_at'  => $this->integer()->notNull(),
            'updated_at'  => $this->integer()->notNull(),
            'created_by'  => $this->integer(),
            'updated_by'  => $this->integer(),
        ], $tableOptions);
    }

    public function safeDown()
    {
        $this->dropTable($this->table);
    }
}
