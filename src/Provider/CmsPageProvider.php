<?php

namespace M2Boilerplate\CriticalCss\Provider;

use Magento\Cms\Api\PageRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Store\Api\Data\StoreInterface;

class CmsPageProvider implements ProviderInterface
{
    const NAME = 'cms_page';
    /**
     * @var UrlInterface
     */
    protected $url;
    /**
     * @var PageRepositoryInterface
     */
    protected $pageRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Cms\Helper\Page
     */
    protected $pageHelper;

    public function __construct(
        \Magento\Cms\Helper\Page $pageHelper,
        UrlInterface $url,
        PageRepositoryInterface $pageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->url = $url;
        $this->pageRepository = $pageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->pageHelper = $pageHelper;
    }


    public function getUrls(StoreInterface $store): array
    {
        $urls = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('is_active', '1')
            ->addFilter('store_id', [$store->getId(), 0], 'in')
            ->setPageSize(30)
            ->setCurrentPage(0)
            ->create();
        try {
            $pages = $this->pageRepository->getList($searchCriteria);
        }

        catch (LocalizedException $e) {
            return [];
        }
        $urls['cms_index_index'] = $store->getUrl('/');
        foreach ($pages->getItems() as $page) {
            $url = $this->pageHelper->getPageUrl($page->getId());
            if (!$url) {
                continue;
            }
            $urls[$page->getId()] = $store->getUrl("cms/page/view", ["id" => $page->getId()]);
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
        return 1000;
    }

    public function getCssIdentifierForRequest(RequestInterface $request, LayoutInterface $layout): ?string
    {
        if ($request->getModuleName() !== 'cms' || !$request instanceof Http) {
            return null;
        }
        if ($request->getFullActionName('_') === 'cms_index_index') {
            // home page
            return 'cms_index_index';
        }
        if ($request->getFullActionName('_') === 'cms_page_view') {
            // home page
            return $request->getParam('page_id');
        }

        return null;
    }

}
