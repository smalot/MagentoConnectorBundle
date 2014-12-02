<?php

namespace Pim\Bundle\MagentoConnectorBundle\Entity\Repository;

use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;

/**
 * MagentoConfiguration repository
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MagentoConfigurationRepository extends ReferableEntityRepository
{
    /**
     * Returns code and label for each Magento configuration in database
     * Returns [['code' => '', 'label' => ''], []]
     *
     * @return array
     */
    public function getChoices()
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c.code', 'c.label');

        return $qb->getQuery()->getArrayResult();
    }
}
