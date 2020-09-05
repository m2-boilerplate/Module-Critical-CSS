<?php

namespace M2Boilerplate\CriticalCss\Controller\CriticalCss;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class DefaultAction extends Action
{

    /**
     * @var PageFactory
     */
    private $pageFactory;

    public function __construct(
        PageFactory $pageFactory,
        Context $context
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $page = $this->pageFactory->create();
        $pageLayout = $this->getRequest()->getParam('page_layout');
        if ($pageLayout) {
            $page->getConfig()->setPageLayout($pageLayout);
            $page->getLayout()->getUpdate()->addHandle('m2bp-'.$pageLayout);
        }
        return $page;
    }


}