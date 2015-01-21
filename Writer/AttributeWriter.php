<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\AttributeLabelDictionary;

/**
 * Attribute writer use to send attributes in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        try {
            $this->client->exportAttributes($items);
        } catch (\SoapFault $e) {
            $failedAttributes = json_decode($e->getMessage(), true);

            if (null !== $failedAttributes) {
                $indexedAttributes = $this->errorHelper->getIndexedEntities(
                    $items,
                    AttributeLabelDictionary::ID_HEADER
                );
                $errors = $this->errorHelper->getSortedFailedEntities($failedAttributes, $indexedAttributes);
                $this->manageFailedEntities($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }
}
