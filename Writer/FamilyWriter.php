<?php

namespace Pim\Bundle\MagentoConnectorBundle\Writer;

use Pim\Bundle\MagentoConnectorBundle\Normalizer\Dictionary\FamilyLabelDictionary;

/**
 * Family writer use to send attribute sets and attribute groups in Api Import
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyWriter extends AbstractWriter
{
    /**
     * {@inheritdoc}
     */
    public function write(array $items)
    {
        try {
            $this->client->exportAttributeSets($items);
        } catch (\SoapFault $e) {
            $indexedNames = $this->getIndexedNames($items);
            $failedSets   = json_decode($e->getMessage(), true);

            if (null !== $failedSets) {
                $errors = $this->getFailedSets($failedSets, $indexedNames);
                $this->manageFailedSets($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }

    /**
     * Gives lines mapped to set name
     * Each error returned by API Import is associate to the index of the line in the sent array.
     * This method provide a way to know to which set is linked this index.
     * Returns ['index' => 'name', ...]
     *
     * @param array $sets
     *
     * @return array
     */
    protected function getIndexedNames(array $sets)
    {
        $indexedNames = [];
        $previousName = '';
        foreach ($sets as $key => $set) {
            if (!empty($set[FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER])) {
                $indexedNames[$key] = $set[FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER];
                $previousName       = $set[FamilyLabelDictionary::ATTRIBUTE_SET_ID_HEADER];
            } else {
                $indexedNames[$key] = $previousName;
            }
        }

        return $indexedNames;
    }

    /**
     * Get failed sets with their name associated to errors
     * Returns [name => ['errors', '']]
     *
     * @param array $errors
     * @param array $indexedNames
     *
     * @return array
     */
    protected function getFailedSets(array $errors, array $indexedNames)
    {
        $failedSets = [];
        foreach ($errors as $error => $failedRows) {
            foreach ($failedRows as $row) {
                $failedSets[$indexedNames[$row]][] = $error;
            }
        }

        return $failedSets;
    }

    /**
     * Add a warning for each failed set
     *
     * @param array $failedSets
     */
    protected function manageFailedSets(array $failedSets)
    {
        foreach ($failedSets as $name => $errors) {
            foreach ($errors as $error) {
                $this->addWarning($error, [], [$name]);
            }
        }
    }
}
