<?php
/**
 * ProductProvider.php
 *
 * @category    Leonex
 * @package     ???
 * @author      Thomas Hampe <hampe@leonex.de>
 * @copyright   Copyright (c) 2020, LEONEX Internet GmbH
 */


namespace M2Boilerplate\CriticalCss\Provider;


use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;

class ProductProvider implements ProviderInterface
{

    const NAME = 'product';

    /**
     * @var CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var Status
     */
    protected $productStatus;

    /**
     * @var Visibility
     */
    protected $productVisibility;

    /**
     * @var UrlInterface
     */
    protected $url;
    /**
     * @var Registry
     */
    private $registry;

    public function __construct(
        Registry $registry,
        CollectionFactory $productCollectionFactory,
        Status $productStatus,
        Visibility $productVisibility,
        UrlInterface $url
    ) {

        $this->productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        $this->url = $url;
        $this->registry = $registry;
    }

    /**
     * @return string[]
     */
    public function getUrls(StoreInterface $store): array
    {
        $collection = $this->productCollectionFactory->create();
        $collection->setStore($store);
        $collection->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $collection->groupByAttribute('type_id');

        $urls = [];
        foreach ($collection->getItems() as $product) {
            /** @var $product \Magento\Catalog\Model\Product */
            $urls[$product->getTypeId()] = $product->getProductUrl();
        }
        return $urls;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return 1400;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if (!$request instanceof Http) {
            return null;
        }

        if ($request->getFullActionName('_') === 'catalog_product_view') {

            $product = $this->registry->registry('current_product');
            if (!$product instanceof Product && $product->getTypeId()) {
                return null;
            }

            return (string) $product->getTypeId();
        }

        return null;
    }
}