<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;

/**
 * Magento product processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        return $item;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
