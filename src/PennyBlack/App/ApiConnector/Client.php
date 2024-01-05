<?php

namespace PennyBlack\App\ApiConnector;

use GuzzleHttp\Client as GuzzleClient;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Http\Factory\Guzzle\RequestFactory;
use Http\Factory\Guzzle\StreamFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use PennyBlack\Api as PennyBlackApiClient;
use PennyBlack\App\Exception\MissingApiConfigException;
use Psr\Http\Client\ClientInterface;

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
            throw new MissingApiConfigException('Cannot instantiate Penny Black API, required config not set.');
        }

        $client = new GuzzleClient();

        // we may be running in a Magento instance that is locked to Guzzle 6.x, so we check if the client implements
        // the required PSR client interface and, if not, wrap it with an adapter (the user must install the
        // php-http/guzzle6-adapter package in their Magento instance to make this work)
        if (!$client instanceof ClientInterface) {
            $client = new GuzzleAdapter($client);
        }

        return new PennyBlackApiClient($client, new RequestFactory(), new StreamFactory(), $apiKey, $sandboxMode);
    }
}
