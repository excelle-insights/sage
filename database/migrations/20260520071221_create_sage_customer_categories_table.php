<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateSageCustomerCategoriesTable extends AbstractMigration
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
        $table = $this->table('sage_customer_categories');

        $table
            ->addColumn('local_id',     'integer',   ['null' => false, 'comment' => 'company_id'])
            ->addColumn('sage_id',      'integer',   ['null' => true,  'comment' => 'ID from Sage API'])
            ->addColumn('description',  'string')
            ->addColumn('status',      'string',    ['limit' => 100, 'default' => 'pending', 'comment' => 'pending|synced|failed'])
            ->addColumn('error',        'text',      ['null' => true])
            ->addColumn('created_at',   'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at',   'timestamp', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
