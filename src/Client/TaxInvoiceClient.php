<?php

namespace ExcelleInsights\Sage\Client;

class TaxInvoiceClient extends BaseClient
{
    public function getAll(): object
    {
        return $this->sendRequest('GET', 'TaxInvoice/Get');
    }

    public function getById(int $id): object
    {
        return $this->sendRequest('GET', "TaxInvoice/Get/{$id}");
    }

    public function create(array $data): object
    {
        $lines = array_map(fn($line) => array_filter([
            'LineType'             => $line['line_type']    ?? 1,
            'SelectionId'         => $line['selection_id'] ?? null,
            'Description'         => $line['description']  ?? null,
            'TaxTypeId'           => $line['tax_type_id']  ?? null,
            'Quantity'            => $line['quantity']      ?? 1,
            'UnitPriceExclusive'  => $line['unit_price'],
            'DiscountPercentage'  => $line['discount']      ?? 0,
        ], fn($v) => $v !== null), $data['lines']);

        $payload = array_filter([
            'Customer'               => ['ID' => $data['sage_customer_id']],
            'CustomerId'             => $data['sage_customer_id'],
            'SalesRepresentative'    => isset($data['sage_salesrep_id'])
                ? ['ID' => $data['sage_salesrep_id']]
                : null,
            'SalesRepresentativeId'  => $data['sage_salesrep_id'] ?? null,
            'Date'                   => $data['date'],
            'DueDate'                => $data['due_date']          ?? null,
            'CustomerReference'      => $data['customer_reference'] ?? null,
            'Lines'                  => $lines,
        ], fn($v) => $v !== null);

        return $this->sendRequest('POST', 'TaxInvoice/Save', $payload);
    }

    public function exportPdf(int $sageId): string
    {
        $url      = $this->auth->getApiUrl("TaxInvoice/Export/{$sageId}");
        $headers  = [
            'Authorization' => $this->auth->getAuthHeader(),
            'Accept'        => 'application/pdf',
        ];

        $response = $this->http->send('GET', $url, $headers, null);

        return $response['body'] ?? '';
    }
}
