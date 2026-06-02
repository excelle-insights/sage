<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSageTaxTypesTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('sage_tax_types');

        $table
            ->addColumn('sage_id',     'integer',  ['null' => false, 'comment' => 'ID from Sage'])
            ->addColumn('name',        'string')
            ->addColumn('percentage',  'decimal',  ['precision' => 5, 'scale' => 2])
            ->addColumn('active',      'boolean',  ['default' => true])
            ->addColumn('created_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
