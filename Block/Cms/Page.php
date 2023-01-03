<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 *
 */

namespace Risecommerce\HtmlSitemap\Block\Cms;

use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Risecommerce\HtmlSitemap\Model\Config;
use Magento\Store\Model\StoreManagerInterface;

class Page extends Template
{
    const XML_PATH_TO_CMS_PAGE_BLOCK_TITLE = 'rchs/cmspagelinks/title';
    const XML_PATH_TO_CMS_PAGE_LIMIT = 'rchs/cmspagelinks/maxnumberlinks';
    const XML_PATH_TO_CMS_PAGE_VIEW_MORE = 'rchs/cmspagelinks/displaymore';
    const XML_PATH_TO_PAGE_TITLE = 'rchs/seo/title';
    const XML_PATH_TO_PAGE_META_TITLE = 'rchs/seo/metatitle';
    const XML_PATH_TO_PAGE_META_DESCRIPTION = 'rchs/seo/metadescription';
    const XML_PATH_TO_PAGE_META_KEYWORDS = 'rchs/seo/metakeywords';

    /**
     * @var PageCollectionFactory
     */
    private $pageCollectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $ignoredLinks;

    /**
     * Page constructor.
     * @param Template\Context $context
     * @param PageCollectionFactory $pageCollectionFactory
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        PageCollectionFactory $pageCollectionFactory,
        Config $config,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->pageCollectionFactory = $pageCollectionFactory;
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->ignoredLinks = $config->getIgnoredLinks();
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_BLOCK_TITLE);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore()
    {
        if ($this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_VIEW_MORE)
            && count($this->getCmsPagesList()) > $this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_LIMIT)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPagesList()
    {
        $pages = $this->pageCollectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getId());
        if (!empty($this->ignoredLinks)) {
            $pages->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
        }

        $pages->addFieldToFilter('rc_exclude_html_sitemap', 0)
            ->addFieldToFilter('is_active', 1);

        return $pages;
    }

    /**
     * @return Collection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCmsPages()
    {
        $pageSize = $this->config->getConfig(self::XML_PATH_TO_CMS_PAGE_LIMIT);
        $pages = $this->pageCollectionFactory->create()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addFieldToFilter('is_active', 1);
        if (!empty($this->ignoredLinks)) {
            $pages->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
        }

        $pages->addFieldToFilter('rc_exclude_html_sitemap', 0)
            ->setPageSize($pageSize);

        return $pages;
    }

    /**
     * @return $this|Template
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getNameInLayout() == 'cms.page') {
            $title = $this->getBlockTitle();

            if ($title) {
                $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
            }
            return $this;
        }

        $metaTitle = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_TITLE);
        $title = $this->config->getConfig(self::XML_PATH_TO_PAGE_TITLE);
        $metaDescription = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_DESCRIPTION);
        $metaKeywords = $this->config->getConfig(self::XML_PATH_TO_PAGE_META_KEYWORDS);

        $this->pageConfig->getTitle()->set($metaTitle ?: $title);

        if (!empty($metaDescription)) {
            $this->pageConfig->setDescription($metaDescription);
        }

        if (!empty($metaKeywords)) {
            $this->pageConfig->setKeywords($metaKeywords);
        }

        $pageMainTitle = $this->getLayout()->getBlock('page.main.title');

        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle(
                $this->_escaper->escapeHtml($title)
            );
        }

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('cmspagelinks');
    }
}
