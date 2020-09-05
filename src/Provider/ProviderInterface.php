<?php
/**
 * PageTypeInterface.php
 *
 * @category    Leonex
 * @package     ???
 * @author      Thomas Hampe <hampe@leonex.de>
 * @copyright   Copyright (c) 2020, LEONEX Internet GmbH
 */


namespace M2Boilerplate\CriticalCss\Provider;


use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

interface ProviderInterface
{

    /**
     * @return string[]
     */
    public function getUrls(StoreInterface $store): array;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @return int
     */
    public function getPriority(): int;

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string;
}