<?php
// @codingStandardsIgnoreFile

use Phinx\Migration\AbstractMigration;

class Initial extends AbstractMigration
{
    /**
     * [up description]
     * @return void
     */
    public function up()
    {
        $this->table('images')
            ->addColumn('field_index', 'integer')
            ->addColumn('model', 'string')
            ->addColumn('foreign_key', 'integer')
            ->addColumn('field', 'string')
            ->addColumn('filename', 'string')
            ->addColumn('size', 'integer', [ 'length' => 20 ])
            ->addColumn('mime', 'string')
            ->addIndex(['model', 'foreign_key'])
            ->save();
    }

    /**
     * [down description]
     * @return void
     */
    public function down()
    {
        $this->dropTable('images');
    }
}
