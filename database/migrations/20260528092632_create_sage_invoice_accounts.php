<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSageInvoiceAccounts extends AbstractMigration
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
        $table = $this->table('sage_invoice_accounts');

        $table
            ->addColumn('local_id',    'integer',   ['null' => false, 'comment' => 'ID from WMS'])
            ->addColumn('sage_id',     'integer',   ['null' => true,  'comment' => 'ID from Sage API after sync'])
            ->addColumn('name',        'string',    ['limit' => 100])
            ->addColumn('description', 'string',    ['limit' => 255, 'null' => true])
            ->addColumn('category_id', 'integer',   ['null' => true, 'comment' => 'Sage category ID'])
            ->addColumn('active',      'boolean',   ['default' => true])
            ->addColumn('balance',     'decimal',   ['precision' => 10, 'scale' => 2, 'default' => 0.00])
            ->addColumn('status',      'string',    ['limit' => 20, 'default' => 'pending'])
            ->addColumn('error',       'text',      ['null' => true])
            ->addColumn('retry_count', 'integer',   ['default' => 0])
            ->addColumn('created_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
