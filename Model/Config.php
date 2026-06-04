<?php
declare(strict_types=1);

namespace Etechflow\AiSeo\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class Config
{
    public function __construct(
        private ScopeConfigInterface $scopeConfig,
        private EncryptorInterface $encryptor
    ) {
    }

    public function isEnabled($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag('etechflow_aiseo/general/enabled', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getProvider($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue('etechflow_aiseo/general/provider', ScopeInterface::SCOPE_STORE, $storeId) ?: 'anthropic';
    }

    public function getModel($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue('etechflow_aiseo/general/model', ScopeInterface::SCOPE_STORE, $storeId) ?: 'claude-sonnet-4-6';
    }

    public function getApiKey($storeId = null): string
    {
        $value = (string)$this->scopeConfig->getValue('etechflow_aiseo/general/api_key', ScopeInterface::SCOPE_STORE, $storeId);
        return $value !== '' ? (string)$this->encryptor->decrypt($value) : '';
    }

    public function getTitleMax($storeId = null): int
    {
        return (int)($this->scopeConfig->getValue('etechflow_aiseo/output/title_max', ScopeInterface::SCOPE_STORE, $storeId) ?: 60);
    }

    public function getDescriptionMax($storeId = null): int
    {
        return (int)($this->scopeConfig->getValue('etechflow_aiseo/output/description_max', ScopeInterface::SCOPE_STORE, $storeId) ?: 160);
    }

    public function getBrandTone($storeId = null): string
    {
        return (string)$this->scopeConfig->getValue('etechflow_aiseo/output/brand_tone', ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isAutoApply($storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag('etechflow_aiseo/general/auto_apply', ScopeInterface::SCOPE_STORE, $storeId);
    }
}
