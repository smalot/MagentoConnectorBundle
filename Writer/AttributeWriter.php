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
                $indexedAttributes = $this->getIndexedAttributes($items);
                $errors = $this->getFailedAttributes($failedAttributes, $indexedAttributes);
                $this->manageFailedAttributes($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }

    /**
     * Gives lines mapped to attribute ids
     * Each error returned by API Import is associate to the index of the line in the sent array.
     * This method provide a way to know to which attribute is linked this index.
     * Returns ['index' => 'id', ...]
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function getIndexedAttributes(array $attributes)
    {
        $indexedAttributes  = [];
        $previousId = '';
        foreach ($attributes as $key => $attribute) {
            if (!empty($attribute[AttributeLabelDictionary::ID_HEADER])) {
                $indexedAttributes[$key] = $attribute[AttributeLabelDictionary::ID_HEADER];
                $previousId              = $attribute[AttributeLabelDictionary::ID_HEADER];
            } else {
                $indexedAttributes[$key] = $previousId;
            }
        }

        return $indexedAttributes;
    }

    /**
     * Get failed attributes with their id associated to errors
     * Returns [id => ['errors', '']]
     *
     * @param array $errors
     * @param array $indexedAttributes
     *
     * @return array
     */
    protected function getFailedAttributes(array $errors, array $indexedAttributes)
    {
        $failedAttributes = [];
        foreach ($errors as $error => $failedRows) {
            foreach ($failedRows as $row) {
                $failedAttributes[$indexedAttributes[$row]][] = $error;
            }
        }

        return $failedAttributes;
    }

    /**
     * Add a warning for each failed attribute
     *
     * @param array $failedAttributes
     */
    protected function manageFailedAttributes(array $failedAttributes)
    {
        foreach ($failedAttributes as $id => $errors) {
            foreach ($errors as $error) {
                $this->addWarning($error, [], [$id]);
            }
        }
    }
}
