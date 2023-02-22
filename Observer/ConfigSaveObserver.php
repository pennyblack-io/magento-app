<?php

namespace PennyBlack\PennyBlack\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use PennyBlack\Api as PennyBlackApi;
use PennyBlack\Client\PennyBlackClient;

class ConfigSaveObserver implements ObserverInterface
{
    private const API_KEY_CONFIG_PATH = 'pennyblack/general/api_key';
    private const SANDBOX_MODE_CONFIG_PATH = 'pennyblack/general/sandbox_mode';

    private ScopeConfigInterface $config;
    private StoreManagerInterface $storeManager;

    public function __construct(ScopeConfigInterface $config, StoreManagerInterface $storeManager)
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
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

            $store = $this->storeManager->getStore()->getBaseUrl();

            $api->install($this->storeManager->getStore()->getBaseUrl());
        }
    }
}
