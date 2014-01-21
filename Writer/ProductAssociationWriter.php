<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Validator\Constraints\HasValidCredentials;

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
        $this->beforeWrite();

        foreach ($productAssociationCallsBatchs as $productAssociationCalls) {
            foreach ($productAssociationCalls['remove'] as $productAssociationRemoveCall) {
                $this->webservice->deleteProductAssociation($productAssociationRemoveCall);
            }

            foreach ($productAssociationCalls['create'] as $productAssociationCreateCall) {
                $this->webservice->createProductAssociation($productAssociationCreateCall);
            }
        }
    }
}
