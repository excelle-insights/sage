<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSageBankAccountsTable extends AbstractMigration
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
        $table = $this->table('sage_bank_accounts');

        $table
            ->addColumn('local_id',        'integer',  ['null' => false, 'comment' => 'ID from WMS'])
            ->addColumn('sage_id',         'integer',  ['null' => true,  'comment' => 'ID from Sage'])
            ->addColumn('account_name',    'string',   ['limit' => 255])
            ->addColumn('bank_name',       'string',   ['limit' => 255, 'null' => true])
            ->addColumn('account_number',  'string',   ['limit' => 255, 'null' => true])
            ->addColumn('branch_name',     'string',   ['limit' => 255, 'null' => true])
            ->addColumn('branch_code',     'string',   ['limit' => 255, 'null' => true])
            ->addColumn('description',     'string',   ['limit' => 255, 'null' => true])
            ->addColumn('payment_method',  'string',   ['limit' => 255, 'null' => true])
            ->addColumn('opening_balance', 'decimal',  ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('active',          'boolean',  ['default' => true])
            ->addColumn('is_default',      'boolean',  ['default' => false])
            ->addColumn('status',          'string',   ['limit' => 20, 'default' => 'pending'])
            ->addColumn('error',           'text',     ['null' => true])
            ->addColumn('retry_count',     'integer',  ['default' => 0])
            ->addColumn('created_at',      'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',      'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}

