<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\CategoryLabelDictionary;

/**
 * Category writer
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $categories = $this->getFlattenedItems($items);
        try {
            $this->client->exportCategories($categories);
        } catch (\SoapFault $e) {
            $failedCategories = json_decode($e->getMessage(), true);

            if (null !== $failedCategories) {
                $indexedNames = $this->errorHelper->getIndexedEntities(
                    $categories,
                    CategoryLabelDictionary::NAME_HEADER
                );
                $errors = $this->errorHelper->getSortedFailedEntities($failedCategories, $indexedNames);
                $this->manageFailedEntities($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }
}
