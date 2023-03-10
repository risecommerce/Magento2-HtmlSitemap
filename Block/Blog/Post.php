<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 *
 */

namespace Risecommerce\HtmlSitemap\Block\Blog;

use Risecommerce\HtmlSitemap\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Risecommerce\HtmlSitemap\Model\BlogFactory;

class Post extends Template
{
    const XML_PATH_TO_BLOG_POST_TITLE = 'rchs/blogpostlinks/title';
    const XML_PATH_TO_BLOG_POSTS_LIMIT = 'rchs/blogpostlinks/maxnumberlinks';
    const XML_PATH_TO_BLOG_POST_VIEW_MORE = 'rchs/blogcategorylinks/displaymore';

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
     * @var BlogFactory
     */
    private $blogFactory;

    /**
     * Post constructor.
     * @param Template\Context $context
     * @param Config $config
     * @param StoreManagerInterface $storeManager
     * @param BlogFactory $blogFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        Config $config,
        StoreManagerInterface $storeManager,
        BlogFactory $blogFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->blogFactory = $blogFactory;
        $this->ignoredLinks = $config->getIgnoredLinks();
    }

    /**
     * @return string
     */
    public function getBlockTitle()
    {
        return (string)$this->config->getConfig(self::XML_PATH_TO_BLOG_POST_TITLE);
    }

    /**
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function showViewMore()
    {
        if ($this->config->getConfig(self::XML_PATH_TO_BLOG_POST_VIEW_MORE)
            && count($this->getAllPostCollection()) > $this->config->getConfig(self::XML_PATH_TO_BLOG_POSTS_LIMIT)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPostsCollection()
    {
        $pageSize = $this->config->getConfig(self::XML_PATH_TO_BLOG_POSTS_LIMIT);
        $posts = $this->blogFactory->createPostCollection()
            ->addStoreFilter($this->storeManager->getStore()->getId())
            ->addFieldToFilter('is_active', 1);
        if (!empty($this->ignoredLinks)) {
            $posts->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
        }

        $posts->addFieldToFilter('rc_exclude_html_sitemap', 0)
            ->setPageSize($pageSize);

        return $posts;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllPostCollection()
    {
        $posts = $this->blogFactory->createPostCollection()
            ->addStoreFilter($this->storeManager->getStore()->getId());
        if (!empty($this->ignoredLinks)) {
            $posts->addFieldToFilter('identifier', ['nin' => $this->config->getIgnoredLinks()]);
        }
        $posts->addFieldToFilter('is_active', 1)
            ->addFieldToFilter('rc_exclude_html_sitemap', 0);

        return $posts;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->config->getSortOrder('blogpostlinks');
    }

    public function toHtml()
    {
        if (!$this->config->isBlogEnabled()) {
            return '';
        }
        return parent::toHtml(); // TODO: Change the autogenerated stub
    }

    /**
     * @return $this|Post
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $title = $this->getBlockTitle();

        if ($title) {
            $this->pageConfig->getTitle()->set( __('Sitemap') . ' - ' .  $this->getBlockTitle());
        }

        return $this;
    }
}
