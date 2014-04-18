<?php

namespace Pim\Bundle\MagentoConnectorBundle\Webservice;

/**
 * A magento soap webservice that handle magento categories
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryWebservice extends AbstractWebservice
{
    /**
     * Get categories status from Magento
     * @return array
     */
    public function getCategoriesStatus()
    {
        if (!$this->categories) {
            $tree = $this->client->call(
                self::SOAP_ACTION_CATEGORY_TREE
            );

            $this->categories = $this->flattenCategoryTree($tree);
        }

        return $this->categories;
    }

    /**
     * Send new category
     * @param array $category
     *
     * @return int
     */
    public function sendNewCategory(array $category)
    {
        return $this->client->call(
            self::SOAP_ACTION_CATEGORY_CREATE,
            $category
        );
    }
    /**
     * Send update category
     * @param array $category
     *
     * @return int
     */
    public function sendUpdateCategory(array $category)
    {
        return $this->client->call(
            self::SOAP_ACTION_CATEGORY_UPDATE,
            $category
        );
    }

    /**
     * Send move category
     * @param array $category
     *
     * @return int
     */
    public function sendMoveCategory(array $category)
    {
        return $this->client->call(
            self::SOAP_ACTION_CATEGORY_MOVE,
            $category
        );
    }

    /**
     * Flatten the category tree from magento
     * @param array $tree
     *
     * @return array
     */
    protected function flattenCategoryTree(array $tree)
    {
        $result = array($tree['category_id'] => $tree);

        foreach ($tree['children'] as $children) {
            $result = $result + $this->flattenCategoryTree($children);
        }

        return $result;
    }

    /**
     * Disable the given category on Magento
     * @param string $categoryId
     *
     * @return int
     */
    public function disableCategory($categoryId)
    {
        return $this->client->call(
            self::SOAP_ACTION_CATEGORY_UPDATE,
            array(
                $categoryId,
                array(
                    'is_active'         => 0,
                    'available_sort_by' => 1,
                    'default_sort_by'   => 1
                )
            )
        );
    }

    /**
     * Delete the given category on Magento
     *
     * @param string $categoryId
     *
     * @return int
     */
    public function deleteCategory($categoryId)
    {
        return $this->client->call(
            self::SOAP_ACTION_CATEGORY_DELETE,
            array(
                $categoryId
            )
        );
    }
}
