<?php

namespace Pim\Bundle\MagentoConnectorBundle\Reader;

use Oro\Bundle\BatchBundle\Item\ItemReaderInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;

/**
 * The magento product reader
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductMagentoReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function read()
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
