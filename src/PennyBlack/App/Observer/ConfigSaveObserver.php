<?php

namespace PennyBlack\App\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use PennyBlack\App\ApiConnector\Client;
use Psr\Log\LoggerInterface;

class ConfigSaveObserver implements ObserverInterface
{
    private const API_KEY_CONFIG_PATH = 'pennyblack/general/api_key';

    private Client $client;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(Client $client, StoreManagerInterface $storeManager, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(Observer $observer): void
    {
        $changedConfigPaths = $observer->getData('changed_paths') ?? [];

        if (in_array(self::API_KEY_CONFIG_PATH, $changedConfigPaths)) {
            try {
                /** @var Store $store */
                $store = $this->storeManager->getStore();
                $client = $this->client->getApiClient();

                $client->install($store->getBaseUrl());
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
