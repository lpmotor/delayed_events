<?php

use Phinx\Migration\AbstractMigration;

class Events extends AbstractMigration
{
    public function up() {
        if (false === $this->hasTable('events')) {
            $this->table('events', [
                'id' => false,
                'primary_key' => ['id'],
                'comment' => 'События',
            ])
                 ->addColumn('id', 'biginteger', ['null' => false, 'signed' => false, 'identity' => true])
                 ->addColumn('parent_id', 'biginteger', ['null' => true, 'signed' => false, 'default' => null])
                 ->addColumn('name', 'string', ['null' => false, 'length' => 128])
                 ->addColumn('d_created', 'datetime', ['null' => false])
                 ->addColumn('d_execute', 'datetime', ['null' => false])
                 ->addColumn('duration', 'float', ['null' => true, 'signed' => false, 'default' => 0])
                 ->addColumn('d_status_change', 'datetime', ['null' => false])
                 ->addColumn('status_id', 'integer', ['null' => false, 'signed' => false, 'length' => 1])
                 ->addColumn('data', 'text', ['null' => true])
                 ->addColumn('log', 'text', ['null' => true])
                 ->create();
        }
    }

    public function down() {
        if ($this->hasTable('events')) {
            $this->table('events')->drop();
        }
    }
}
