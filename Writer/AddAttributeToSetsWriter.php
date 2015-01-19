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
        $associations = $this->getFlattenedAssociations($items);

        try {
            $this->client->addAttributeToSets($associations);
        } catch (\SoapFault $e) {
            $indexedNames = $this->getIndexedNames($associations);
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

    /**
     * Flatten associations by concatenating association parts into one array
     * $items = [association1, a2, a3, ...]
     * association = [part1, part2, p3, ...]
     * Returns [association1 part1, a1p2, a2p1, a2p2, a3p1, ...]
     *
     * @param array $items Items received from ItemStep
     *
     * @return array
     */
    protected function getFlattenedAssociations(array $items)
    {
        $associations = [];
        foreach ($items as $item) {
            $associations = array_merge($associations, $item);
        }

        return $associations;
    }
}
