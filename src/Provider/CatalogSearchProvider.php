<?php

namespace M2Boilerplate\CriticalCss\Provider;


use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Search\Model\ResourceModel\Query\CollectionFactory;

class CatalogSearchProvider implements ProviderInterface
{
    const NAME = 'catalogsearch';

    /**
     * @var UrlInterface
     */
    protected $url;
    /**
     * @var CollectionFactory
     */
    protected $queryCollectionFactory;

    public function __construct(UrlInterface $url, CollectionFactory $queryCollectionFactory)
    {
        $this->url = $url;
        $this->queryCollectionFactory = $queryCollectionFactory;
    }


    public function getUrls(StoreInterface $store): array
    {
        $urls = [
            'catalogsearch_advanced_index' => $store->getUrl('catalogsearch/advanced'),
            'search_term_popular' => $store->getUrl('search/term/popular'),
        ];
        /** @var \Magento\Search\Model\Query $term */
        $term = $this->queryCollectionFactory
            ->create()
            ->setPopularQueryFilter($store->getId())
            ->setPageSize(1)->load()->getFirstItem();
        if ($term->getQueryText()) {
            $urls['catalogsearch_result_index'] = $store->getUrl(
                'catalogsearch/result',
                ['_query' => ['q' => $term->getQueryText()]]
            );
        }
        return $urls;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function isAvailable(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 1300;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if ($request->getModuleName() !== 'catalogsearch' || !$request instanceof Http) {
            return null;
        }

        $actionName = $request->getFullActionName('_');
        $supportedActions = [
            'catalogsearch_advanced_index',
            'catalogsearch_result_index',
            'search_term_popular',
        ];
        if (in_array($actionName, $supportedActions)) {
            return $actionName;
        }

        return null;
    }


}
