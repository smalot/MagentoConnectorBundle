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
        $categories   = $this->getFlattenedCategories($items);
        $indexedNames = $this->getIndexedNames($categories);

        try {
            $this->client->exportCategories($categories);
        } catch (\SoapFault $e) {
            $failedCategories = json_decode($e->getMessage(), true);

            if (null !== $failedCategories) {
                $errors = $this->getFailedCategories($failedCategories, $indexedNames);
                $this->manageFailedCategories($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }

    /**
     * Gives lines mapped to categories name
     * Each error returned by API Import is associate to the index of the line in the sent array.
     * This method provide a way to know to which category is linked this index.
     * Returns ['index' => 'name', ...]
     *
     * @param array $categories
     *
     * @return array
     */
    protected function getIndexedNames(array $categories)
    {
        $indexedNames = [];
        $previousName = '';
        foreach ($categories as $key => $category) {
            if (!empty($category[CategoryLabelDictionary::NAME_HEADER])) {
                $indexedNames[$key] = $category[CategoryLabelDictionary::NAME_HEADER];
                $previousName       = $category[CategoryLabelDictionary::NAME_HEADER];
            } else {
                $indexedNames[$key] = $previousName;
            }
        }

        return $indexedNames;
    }

    /**
     * Get failed categories with their name associated to errors
     * Returns [name => ['errors', '']]
     *
     * @param array $errors
     * @param array $mappedNames
     *
     * @return array
     */
    protected function getFailedCategories(array $errors, array $mappedNames)
    {
        $failedCategories = [];
        foreach ($errors as $error => $failedRows) {
            foreach ($failedRows as $row) {
                $failedCategories[$mappedNames[$row]][] = $error;
            }
        }

        return $failedCategories;
    }

    /**
     * Add a warning for each failed category
     *
     * @param array $failedCategories
     */
    protected function manageFailedCategories(array $failedCategories)
    {
        foreach ($failedCategories as $name => $errors) {
            foreach ($errors as $error) {
                $this->addWarning($error, [], [$name]);
            }
        }
    }

    /**
     * Flatten categories by concatenating categories parts into one array
     * $items = [category1, c2, c3, ...]
     * category = [part1, part2, p3, ...]
     * Returns [category1 part1, c1p2, c2p1, c2p2, c3p1, ...]
     *
     * @param array $items Items received from ItemStep
     *
     * @return array
     */
    protected function getFlattenedCategories(array $items)
    {
        $categories = [];
        foreach ($items as $item) {
            $categories = array_merge($categories, $item);
        }

        return $categories;
    }
}
