<?php

namespace PennyBlack\App\ApiConnector;

use Magento\Framework\App\Config\ScopeConfigInterface;
use PennyBlack\Api as PennyBlackApi;
use PennyBlack\App\Exception\MissingApiConfigException;
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

    /**
     * @throws MissingApiConfigException
     */
    public function getApiClient(): PennyBlackApi
    {
        $apiKey = $this->scopeConfig->getValue(self::API_KEY_CONFIG_PATH);
        $sandboxMode = $this->scopeConfig->getValue(self::SANDBOX_MODE_CONFIG_PATH);

        if ($apiKey === null || $sandboxMode === null) {
            throw new MissingApiConfigException('Cannot instantiate PennyBlack API, required config not set.');
        }

        return new PennyBlackApi(
            new PennyBlackClient(
                $apiKey,
                $sandboxMode
            )
        );
    }
}
