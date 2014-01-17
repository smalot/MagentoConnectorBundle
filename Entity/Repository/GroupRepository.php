<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository as BaseGroupRepository;

/**
 * Custom group repository
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupRepository extends BaseGroupRepository
{
    const VARIANT_GROUP_CODE = 'VARIANT';

    /**
     * Get all variant groups ids
     * @return array
     */
    public function getVariantGroupIds()
    {
        $result = $this
            ->createQueryBuilder('g')
            ->select('g.id')
            ->leftJoin('g.type', 't')
            ->andWhere('t.code = :variant_code')
            ->setParameter(':variant_code', self::VARIANT_GROUP_CODE)
            ->getQuery()
            ->getResult();

        array_walk(
            $result,
            function (&$value) {
                $value = $value['id'];
            }
        );

        return $result;
    }
}
