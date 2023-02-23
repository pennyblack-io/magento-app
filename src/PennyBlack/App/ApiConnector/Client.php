<?php

namespace PennyBlack\App\ApiConnector;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PennyBlack\Api as PennyBlackApi;
use PennyBlack\Client\PennyBlackClient;

class Client
{
    private const API_KEY_CONFIG_PATH = 'pennyblack/general/api_key';
    private const SANDBOX_MODE_CONFIG_PATH = 'pennyblack/general/sandbox_mode';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function getApiClient(): PennyBlackApi
    {
        return new PennyBlackApi(
            new PennyBlackClient(
                $this->scopeConfig->getValue(self::API_KEY_CONFIG_PATH),
                $this->scopeConfig->getValue(self::SANDBOX_MODE_CONFIG_PATH)
            )
        );
    }
}
