<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 *
 */

namespace Risecommerce\HtmlSitemap\Model\Config\Source;

class DisplayIn implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '1',
                'label' => __('Top Links')
            ],
            [
                'value' => '2',
                'label' => __('Footer Links')
            ]
        ];
    }
}
