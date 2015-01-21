<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\ProductLabelDictionary;

/**
 * Product writer used to send products in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $products = $this->getFlattenedItems($items);
        try {
            $this->client->exportProducts($products);
        } catch (\SoapFault $e) {
            $failedProducts = json_decode($e->getMessage(), true);

            if (null !== $failedProducts) {
                $indexedSkus = $this->errorHelper->getIndexedEntities($products, ProductLabelDictionary::SKU_HEADER);
                $errors = $this->errorHelper->getSortedFailedEntities($failedProducts, $indexedSkus);
                $this->manageFailedEntities($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }
}
