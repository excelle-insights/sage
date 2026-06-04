<?php

namespace ExcelleInsights\Sage\Client;

class AllocationClient extends BaseClient
{
    public function allocate(array $data): object
    {
        $payload = [
            'SourceDocumentId'      => $data['receipt_sage_id'],
            'AllocatedToDocumentId' => $data['invoice_sage_id'],
            'Total'                 => $data['total'],
            'Discount'              => $data['discount'] ?? 0,
        ];

        return $this->sendRequest('POST', 'Allocation/Save', $payload);
    }
}
