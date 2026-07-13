<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RenameSageInvoiceAccountsToSageAccounts extends AbstractMigration
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
    public function up(): void
    {
        $this->execute(
            "RENAME TABLE sage_invoice_accounts TO sage_accounts"
        );
    }

    public function down(): void
    {
        $this->execute(
            "RENAME TABLE sage_accounts TO sage_invoice_accounts"
        );
    }
}
