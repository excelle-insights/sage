<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddTaxReferenceToSageTaxInvoice extends AbstractMigration
{
    public function change(): void
    {
        $this->table('sage_tax_invoice')
            ->addColumn('tax_reference', 'string', ['null' => true, 'after' => 'customer_reference'])
            ->update();
    }
}
