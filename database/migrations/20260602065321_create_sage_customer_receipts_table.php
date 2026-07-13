<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSageCustomerReceiptsTable extends AbstractMigration
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
        $table = $this->table('sage_customer_receipts');

        $table
            ->addColumn('invoice_id',        'integer',  ['null' => false, 'comment' => 'FK to sage_tax_invoice.id'])
            ->addColumn('sage_id',          'integer',  ['null' => true,  'comment' => 'ID from Sage'])
            ->addColumn('sage_customer_id', 'integer',  ['null' => true])
            ->addColumn('sage_invoice_id',  'integer',  ['null' => true,  'comment' => 'Sage invoice ID'])
            ->addColumn('bank_account_id',  'integer',  ['null' => true,  'comment' => 'Sage bank account ID'])
            ->addColumn('date',             'date',     ['null' => false])
            ->addColumn('total',             'decimal',   ['precision' => 15, 'scale' => 2, 'default' => 0.00])
            ->addColumn('reference',        'string',   ['limit' => 255, 'null' => true])
            ->addColumn('description',      'string',   ['limit' => 255, 'null' => true])
            ->addColumn('comments',         'string',   ['limit' => 255, 'null' => true])
            ->addColumn('payment_method',   'string',   ['limit' => 255])
            ->addColumn('reconciled',       'boolean',  ['default' => false])
            ->addColumn('status',           'string',   ['limit' => 20,  'default' => 'pending'])
            ->addColumn('error',            'text',     ['null' => true])
            ->addColumn('retry_count',      'integer',  ['default' => 0])
            ->addColumn('created_at',       'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',       'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
