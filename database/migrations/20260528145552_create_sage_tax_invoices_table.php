<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSageTaxInvoicesTable extends AbstractMigration
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
        // header table
        $table = $this->table('sage_tax_invoice');
        $table
            ->addColumn('local_id',          'integer',   ['null' => false, 'comment' => 'ID from WMS'])
            ->addColumn('sage_id',            'integer',   ['null' => true,  'comment' => 'ID from Sage'])
            ->addColumn('sage_customer_id',   'integer',   ['null' => true,  'comment' => 'Sage customer ID'])
            ->addColumn('sage_salesrep_id',   'integer',   ['null' => true,  'comment' => 'Sage sales rep ID'])
            ->addColumn('date',               'date',      ['null' => false])
            ->addColumn('due_date',           'date',      ['null' => true])
            ->addColumn('customer_reference', 'string',    ['null' => true])
            ->addColumn('inclusive',          'boolean',   ['default' => false])
            ->addColumn('status',             'string',    ['limit' => 20, 'default' => 'pending'])
            ->addColumn('error',              'text',      ['null' => true])
            ->addColumn('retry_count',        'integer',   ['default' => 0])
            ->addColumn('created_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();

        // lines table
        $lines = $this->table('sage_tax_invoice_items');
        $lines
            ->addColumn('invoice_id',      'integer',  ['null' => false, 'comment' => 'FK to sage_tax_invoices.id'])
            ->addColumn('line_type',       'integer',  ['default' => 1, 'comment' => '0=Item 1=Account'])
            ->addColumn('selection_id',    'integer',  ['null' => true,  'comment' => 'ID from Sage'])
            ->addColumn('description',     'string',   ['limit' => 255, 'null' => true])
            ->addColumn('tax_type_id',     'integer',  ['null' => true,  'comment' => 'Sage tax type ID'])
            ->addColumn('quantity',        'integer',  ['default' => 1])
            ->addColumn('unit_price',      'decimal',  ['precision' => 15, 'scale' => 2])
            ->addColumn('discount',        'decimal',  ['precision' => 5,  'scale' => 2, 'default' => 0.00])
            ->addColumn('created_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',  'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
