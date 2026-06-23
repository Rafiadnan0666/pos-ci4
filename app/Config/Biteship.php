<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Biteship extends BaseConfig
{
    public string $apiKey = '';

    public string $baseUrl = 'https://api.biteship.com/v1';

    public string $originLatitude = '-6.2146';

    public string $originLongitude = '106.8451';

    public string $originPostalCode = '10110';

    public string $originAddress = 'Jl. Contoh No. 123, Jakarta Pusat';

    public string $originContactName = 'Store Owner';

    public string $originContactPhone = '02112345678';

    public function __construct()
    {
        parent::__construct();

        $this->apiKey = trim(env('BITESHIP_API_KEY', ''));
    }
}
