<?php

namespace Pim\Bundle\MagentoConnectorBundle\Processor;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

/**
 * Magento configurable processor
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ConfigurableMagentoProcessor extends AbstractMagentoProcessor
{
    /**
     * {@inheritdoc}
     */
    public function process($items)
    {
        $this->magentoWebservice      = $this->magentoWebserviceGuesser->getWebservice($this->getClientParameters());
        $this->configurableNormalizer = $this->magentoNormalizerGuesser->getConfigurableNormalizer(
            $this->getClientParameters()
        );

        $processedItems = array();
    }
}

