<?php

namespace PennyBlack\App\ApiConnector;

use GuzzleHttp\Psr7\HttpFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PennyBlack\Api as PennyBlackApiClient;
use PennyBlack\App\Exception\MissingApiConfigException;

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
    public function getApiClient(): PennyBlackApiClient
    {
        $apiKey = $this->scopeConfig->getValue(self::API_KEY_CONFIG_PATH);
        $sandboxMode = $this->scopeConfig->getValue(self::SANDBOX_MODE_CONFIG_PATH);

        if ($apiKey === null || $sandboxMode === null) {
            throw new MissingApiConfigException('Cannot instantiate PennyBlack API, required config not set.');
        }

        $client = new \GuzzleHttp\Client();
        $streamFactory = new HttpFactory();
        $requestFactory = new HttpFactory();

        return new PennyBlackApiClient($client, $requestFactory, $streamFactory, $apiKey, $sandboxMode);
    }
}
