<?php

use Phinx\Migration\AbstractMigration;

class CreateIgnoringTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change()
    {
        if (! $this->hasTable('ignoring')) {
            $table = $this->table('ignoring', ['engine' => 'MyISAM', 'collation' => 'utf8mb4_unicode_ci']);
            $table->addColumn('user', 'string', ['limit' => 20])
                ->addColumn('name', 'string', ['limit' => 20])
                ->addColumn('text', 'text', ['null' => true])
                ->addColumn('time', 'integer')
                ->addIndex('time')
                ->addIndex('user')
                ->create();
        }
    }
}
