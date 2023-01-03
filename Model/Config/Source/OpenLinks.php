<?php
/**
 * Copyright © Risecommerce (support@risecommerce.com). All rights reserved.
 *
 */

namespace Risecommerce\HtmlSitemap\Model\Config\Source;

class OpenLinks implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => '1',
                'label' => __('In the same tab')
            ],
            [
                'value' => '2',
                'label' => __('In a new tab')
            ]
        ];
    }
}
