<?php

namespace altiore\base\console;


class Migration extends \yii\db\Migration
{
    /**
     * @param string $table table name, for example '{{%users}}'
     * @return string clear table name
     */
    public function getTableName($table)
    {
        $table = str_replace(['{{', '}}'], '', $table);
        return str_replace('%', $this->getDb()->tablePrefix, $table);
    }

    protected function getKeyName($type, $table, $columns)
    {
        $str_columns = is_array($columns) ? implode('_', $columns) : preg_replace("/\,\s*/", '_', $columns);
        return implode('_', [$type, $this->getTableName($table), $str_columns]);
    }

    /**
     * @param string       $name
     * @param string       $table
     * @param array|string $columns
     * @param bool         $unique
     */
    public function createIndex($name, $table, $columns, $unique = false)
    {
        if ($name === null) {
            $name = $this->getKeyName('i', $table, $columns);
        }
        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @param string $name
     * @param string $table
     * @param null   $columns
     */
    public function dropIndex($name, $table, $columns = null)
    {
        if ($name === null) {
            $name = $this->getKeyName('i', $table, $columns);
        }
        parent::dropIndex($name, $table);
    }

    /**
     * @param string       $name
     * @param string       $table
     * @param array|string $columns
     * @param string       $refTable
     * @param array|string $refColumns
     * @param null         $delete
     * @param null         $update
     */
    public function addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        if ($name === null) {
            $name = $this->getKeyName('fk', $table, $columns);
        }
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete,
            $update);
    }

    /**
     * @param string $name
     * @param string $table
     * @param null   $columns
     */
    public function dropForeignKey($name, $table, $columns = null)
    {
        if ($name === null) {
            $name = $this->getKeyName('fk', $table, $columns);
        }
        parent::dropForeignKey($name, $table);
    }
}
