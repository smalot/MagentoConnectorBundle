<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\Repository\CategoryRepository as BaseCategoryRepository;

/**
 * Custom category repository
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryRepository extends BaseCategoryRepository
{
    /**
     * Get all categories in order
     * @return array
     */
    public function findOrderedCategories()
    {
        return $this
            ->createQueryBuilder('c')
            ->select('c')
            ->orderBy('c.level, c.left', 'ASC');
    }
}
