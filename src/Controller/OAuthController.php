<?php

namespace ExcelleInsights\Sage\Controller;

use ExcelleInsights\Sage\Facade\SageManager;

class OAuthController
{
    private SageManager $qbo;

    public function __construct(SageManager $qbo)
    {
        $this->qbo = $qbo;
    }

    /**
     * Returns the URL to redirect users to Sage for OAuth2.
     */
    public function redirectToSage(): void
    {
        $authUrl = $this->qbo->getAuthUrl();
        header("Location: $authUrl");
        exit;
    }

    /**
     * Handles the callback from Sage after user authorizes.
     * Stores access token automatically.
     */
    public function handleCallback(): string
    {
        $code = $_GET['code'] ?? null;
        $realmId = $_GET['realmId'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($error) {
            return "Sage OAuth error: " . htmlspecialchars($error);
        }

        if (!$code || !$realmId) {
            return "Missing code or realmId in callback.";
        }

        $this->qbo->authenticate($code, $realmId);

        return "Sage integration successful!";
    }
}
