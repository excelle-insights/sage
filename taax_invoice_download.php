Sage (invoice created)
│
│ GET /TaxInvoice/Get/{id}
▼
Your Package (fetches full invoice data)
│
│ returns structured data to WMS
▼
WMS (generates PDF from data)
│
│ stores PDF locally
▼
User downloads from WMS for tax filing


curl --location 'https://resellers.accounting.sageone.co.za/api/2.0.0/TaxInvoice/Get?APIKey=C5403EA0-3795-475E-9242-86DE8FA69256&companyid=17160' \
--header 'Authorization: Basic YW50b2luZXR0ZW9qd2FuZ0BnbWFpbC5jb206QUFudG9pbmV0dGVAQEA1NA=='

client:
public function getById(int $id): object
{
return $this->sendRequest('GET', "TaxInvoice/Get/{$id}");
}

public function getAll(): object
{
return $this->sendRequest('GET', 'TaxInvoice/Get');
}

Manager:
public function getTaxInvoice(int $sageInvoiceId): object
{
$client = new TaxInvoiceClient(
$this->baseUrl,
$this->companyId,
$this->auth,
$this->http
);

return $client->getById($sageInvoiceId);
}
$invoice = $manager->getTaxInvoice(12345);

// WMS uses dompdf/mpdf to render
$pdf = $pdfGenerator->generate([
'invoice_number' => $invoice->InvoiceNumber,
'customer' => $invoice->Customer->Name,
'lines' => $invoice->Lines,
'total' => $invoice->Total,
'tax' => $invoice->Tax,
'date' => $invoice->Date,
]);

what pass as line type:
{
"Customer": {
"ID": 869136
},
"CustomerId": 869136,
"SalesRepresentative": {
"ID": 6097
},
"SalesRepresentativeId": 6097,
"Date": "2026-05-28",
"DueDate": "2026-06-28",
"CustomerReference": "REF-001",
"Lines": [
{
"LineType": 1,
"SelectionId": ???,
"Description": "Legal Fees",
"TaxTypeId": ???,
"Quantity": 1,
"UnitPriceExclusive": 5000.00,
"DiscountPercentage": 0
}
]
}