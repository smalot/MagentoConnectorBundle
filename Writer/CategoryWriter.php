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
        $categories = [];
        foreach ($items as $item) {
            $categories = array_merge($categories, $item);
        }

        $mappedNames = $this->getMappedNames($categories);
        try {
            $this->client->exportCategories($categories);
        } catch (\SoapFault $e) {
            $failedCategories = json_decode($e->getMessage(), true);

            if (null !== $failedCategories) {
                $errors = $this->getFailedCategories($failedCategories, $mappedNames);
                $this->manageFailedCategories($errors);
            } else {
                $this->addWarning($e->getMessage());
            }
        }
    }

    /**
     * Gives lines mapped to categories name
     *
     * @param array $categories
     *
     * @return array
     */
    protected function getMappedNames(array $categories)
    {
        $mappedNames  = [];
        $previousName = '';
        foreach ($categories as $key => $category) {
            if (!empty($category[CategoryLabelDictionary::NAME_HEADER])) {
                $mappedNames[$key] = $category[CategoryLabelDictionary::NAME_HEADER];
                $previousName      = $category[CategoryLabelDictionary::NAME_HEADER];
            } else {
                $mappedNames[$key] = $previousName;
            }
        }

        return $mappedNames;
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
}
