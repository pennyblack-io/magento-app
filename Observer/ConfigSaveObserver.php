<?php

namespace PennyBlack\PennyBlack\Observer;

use Exception;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;
use PennyBlack\Api as PennyBlackApi;
use PennyBlack\Client\PennyBlackClient;
use Psr\Log\LoggerInterface;

class ConfigSaveObserver implements ObserverInterface
{
    private const API_KEY_CONFIG_PATH = 'pennyblack/general/api_key';
    private const SANDBOX_MODE_CONFIG_PATH = 'pennyblack/general/sandbox_mode';

    private ScopeConfigInterface $config;
    private StoreManagerInterface $storeManager;
    private LoggerInterface $logger;

    public function __construct(ScopeConfigInterface $config, StoreManager $storeManager, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $changedConfigPaths = $observer->getData('changed_paths');

        if (in_array(self::API_KEY_CONFIG_PATH, $changedConfigPaths)) {
            $api = new PennyBlackApi(
                new PennyBlackClient(
                    $this->config->getValue(self::API_KEY_CONFIG_PATH),
                    $this->config->getValue(self::SANDBOX_MODE_CONFIG_PATH)
                )
            );

            try {
                /** @var Store $store */
                $store = $this->storeManager->getStore();

                $api->install($store->getBaseUrl());
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
