<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\FamilyLabelDictionary;

/**
 * Associates attributes to their attribute sets and groups
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeToSetsWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        $associations = $this->getFlattenedItems($items);
        try {
            $this->client->addAttributeToSets($associations);
        } catch (\SoapFault $e) {
            $failedSets   = json_decode($e->getMessage(), true);

            if (null !== $failedSets) {
                $indexedIds = $this->errorHelper->getIndexedEntities(
                    $associations,
                    FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER
                );
                $errors = $this->errorHelper->getSortedFailedEntities($failedSets, $indexedIds);
                $this->manageFailedEntities($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }
}
