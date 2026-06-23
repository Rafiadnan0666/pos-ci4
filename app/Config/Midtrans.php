<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Midtrans extends BaseConfig
{
    public string $serverKey = '';

    public string $clientKey = '';

    public bool $isProduction = false;

    public string $snapUrlSandbox = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

    public string $snapUrlProduction = 'https://app.midtrans.com/snap/v1/transactions';

    public function __construct()
    {
        parent::__construct();

        $this->serverKey = trim(env('MIDTRANS_SERVER_KEY', ''));
        $this->clientKey = trim(env('MIDTRANS_CLIENT_KEY', ''));
        $this->isProduction = (bool) env('MIDTRANS_IS_PRODUCTION', false);
    }

    public function getSnapUrl(): string
    {
        return $this->isProduction
            ? $this->snapUrlProduction
            : $this->snapUrlSandbox;
    }

    public function getNotificationUrl(): string
    {
        return base_url('midtrans/callback');
    }
}
