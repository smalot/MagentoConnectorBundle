<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;
use Pim\Bundle\MagentoConnectorBundle\Webservice\SoapCallException;
use Oro\Bundle\BatchBundle\Item\InvalidItemException;

/**
 * Magento product association writer
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @HasValidCredentials()
 */
class ProductAssociationWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $productAssociationCallsBatchs)
    {
        $this->beforeExecute();

        foreach ($productAssociationCallsBatchs as $productAssociationCalls) {
            try {
                $this->handleProductAssociationCalls($productAssociationCalls);
            } catch (SoapCallException $e) {
                throw new InvalidItemException(
                    sprintf(
                        'An error occured during a product association call. This may be due to a linked ' .
                        'product that doesn\'t exist on Magento side. Error message : %s',
                        $e->getMessage()
                    ),
                    array()
                );
            }
        }
    }

    /**
     * Handle product association calls
     * @param array $productAssociationCalls
     *
     * @throws SopaCallException If a soap call fails
     */
    protected function handleProductAssociationCalls(array $productAssociationCalls)
    {
        foreach ($productAssociationCalls['remove'] as $productAssociationRemoveCall) {
            $this->webservice->removeProductAssociation($productAssociationRemoveCall);
        }

        foreach ($productAssociationCalls['create'] as $productAssociationCreateCall) {
            $this->webservice->createProductAssociation($productAssociationCreateCall);
        }
    }
}
