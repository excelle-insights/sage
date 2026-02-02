<?php

require dirname(__DIR__, 4) . '/vendor/autoload.php';

use ExcelleInsights\Sage\Facade\SageManager;
use ExcelleInsights\Sage\Repositories\PaymentRepository;
use ExcelleInsights\Sage\Repositories\PaymentItemRepository;

// Init
$qbo = new SageManager();
$pdo = $qbo->getPdo();

// Repos
$repo = new PaymentRepository($pdo);
$itemRepo = new PaymentItemRepository($pdo);

// Fetch eligible payments
$payments = $repo->getUnsynced(50);

foreach ($payments as $payment) {

    // 🔒 Atomic claim
    if (!$repo->claimForProcessing((int) $payment['id'])) {
        continue; // another worker got it
    }

    try {
        $payment['items'] = $itemRepo->getByPaymentId($payment['id']);

        $result = $qbo->createPayment($payment);

        $qboId = $result->Payment->Id ?? null;
        $syncToken = $result->Payment->SyncToken ?? null;

        if ($qboId) {
            $repo->markSynced(
                (int) $payment['id'],
                $qboId,
                $syncToken
            );
        }

    } catch (\Throwable $e) {
        error_log("QBO payment sync failed: {$payment['id']} → {$e->getMessage()}");

        $repo->markFailed(
            (int) $payment['id'],
            $e->getMessage()
        );
    }
}

echo "Payment retry finished at " . date('Y-m-d H:i:s') . PHP_EOL;
