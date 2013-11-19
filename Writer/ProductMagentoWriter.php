<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;

/**
 * Magento product writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoWriter extends AbstractConfigurableStepElement implements ItemWriterInterface
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array();
    }
}
