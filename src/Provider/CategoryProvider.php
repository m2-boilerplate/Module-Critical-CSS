<?php

namespace M2Boilerplate\CriticalCss\Provider;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;

class CategoryProvider implements ProviderInterface
{

    const NAME = 'category';

    /**
     * @var CollectionFactory
     */
    protected $categoryCollectionFactory;
    /**
     * @var Registry
     */
    protected $registry;

    public function __construct(
        Registry $registry,
        CollectionFactory $categoryCollectionFactory
    ) {
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->registry = $registry;
    }

    /**
     * @return string[]
     */
    public function getUrls(StoreInterface $store): array
    {
        $urls = [];
        try {
            // Get all Product Listings grouped by appearance settings
            $collection = $this->categoryCollectionFactory->create();
            $collection->setStore($store);
            $collection->addIsActiveFilter();
            $collection->addAttributeToFilter(
                [
                    ['attribute' => 'display_mode', 'eq' => Category::DM_PRODUCT],
                    ['attribute' => 'display_mode', 'null' => true]
                ],
                null,
                'left'
            );
            $collection->addAttributeToFilter('level', ['gt' => 1]);
            $collection->addUrlRewriteToResult();

            $collection->addAttributeToSelect('is_anchor','left');
            $collection->groupByAttribute('is_anchor');
            $collection->addAttributeToSelect('page_layout','left');
            $collection->groupByAttribute('page_layout');
            $collection->addAttributeToSelect('custom_design','left');
            $collection->groupByAttribute('custom_design');
            $collection->addAttributeToSort('children_count', \Magento\Framework\Data\Collection::SORT_ORDER_DESC);

            foreach ($collection->getItems() as $category) {
                /** @var $category Category */
                $urls[$this->getIdentifier($category)] = $category->getUrl();
            }
        } catch (LocalizedException $e) {}

        try {
            // Get all Landing Pages
            $collection = $this->categoryCollectionFactory->create();
            $collection->setStore($store);
            $collection->addIsActiveFilter();
            $collection->addAttributeToFilter('display_mode', ['neq' => Category::DM_PRODUCT]);
            $collection->addAttributeToFilter('level', ['gt' => 1]);
            $collection->addUrlRewriteToResult();

            foreach ($collection->getItems() as $category) {
                /** @var $category Category */
                $urls[$this->getIdentifier($category)] = $category->getUrl();
            }
        } catch (LocalizedException $e) {}

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
        return 1500;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if (!$request instanceof Http) {
            return null;
        }

        if ($request->getFullActionName('_') === 'catalog_category_view') {

            $category = $this->registry->registry('current_category');
            if (!$category instanceof Category) {
                return null;
            }

            return (string) $this->getIdentifier($category);
        }
        return null;
    }

    protected function getIdentifier(Category $category): string
    {
        if ($category->getDisplayMode() !== Category::DM_PRODUCT && $category->getDisplayMode() !== null) {
            return $category->getId();
        }

        return sprintf(
            'is_anchor:%s,page_layout:%s,custom_design:%s',
            (int) $category->getData('is_anchor'),
            (string) $category->getData('page_layout'),
            (string) $category->getData('custom_design')
        );
    }
}