<?php

namespace M2Boilerplate\CriticalCss\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{

    const CONFIG_PATH_ENABLED = 'dev/css/use_css_critical_path';
    const CONFIG_PATH_CRITICAL_BINARY = 'dev/css/critical_css_critical_binary';
    const CONFIG_PATH_PARALLEL_PROCESSES = 'dev/css/critical_css_parallel_processes';
    const CONFIG_PATH_USERNAME = 'dev/css/critical_css_username';
    const CONFIG_PATH_PASSWORD = 'dev/css/critical_css_password';
    const CONFIG_PATH_DIMENSIONS = 'dev/css/critical_css_dimensions';
    const CONFIG_PATH_FORCE_INCLUDE_CSS_SELECTORS = 'dev/css/critical_css_force_include_css_selectors';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    protected $defaultDimensions = [
        '375x812', // XS / iPhone X
        '576x1152', //SM
        '768x1024', //MD / iPad
        '1024x768', //LG / iPad
        '1280x720', //XL
    ];

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(self::CONFIG_PATH_ENABLED);
    }

    public function getForceIncludeCssSelectors(): array
    {
        $cssSelectors = $this->scopeConfig->getValue(self::CONFIG_PATH_FORCE_INCLUDE_CSS_SELECTORS);
        $cssSelectors = explode(',', $cssSelectors);
        $cssSelectors = array_map('trim', $cssSelectors);
        $cssSelectors = array_filter($cssSelectors);

        return $cssSelectors;
    }

    public function getDimensions(): array
    {
        $dimensions = $this->scopeConfig->getValue(self::CONFIG_PATH_DIMENSIONS);
        $dimensions = explode(',', $dimensions);
        $dimensions = array_map('trim', $dimensions);
        $dimensions = array_filter($dimensions);
        if (count($dimensions) === 0) {
            return $this->defaultDimensions;
        }
        return $dimensions;
    }

    public function getNumberOfParallelProcesses(): int
    {
        $processes = (int) $this->scopeConfig->getValue(self::CONFIG_PATH_PARALLEL_PROCESSES);
        return max(1, $processes);
    }

    public function getUsername($scopeCode = null): ?string
    {
        $username = $this->scopeConfig->getValue(self::CONFIG_PATH_USERNAME, ScopeInterface::SCOPE_STORE, $scopeCode);
        if (!$username) {
            return null;
        }
        return (string) $username;
    }

    public function getPassword($scopeCode = null): ?string
    {
        $password = $this->scopeConfig->getValue(self::CONFIG_PATH_PASSWORD, ScopeInterface::SCOPE_STORE, $scopeCode);
        if (!$password) {
            return null;
        }
        return (string) $password;
    }

    public function getCriticalBinary(): string
    {
        return $this->scopeConfig->getValue(self::CONFIG_PATH_CRITICAL_BINARY);
    }
}
